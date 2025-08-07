<?php
// classes/Forecasting.php
class Forecasting {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Generate forecasting data from historical data
    public function generateForecastingData() {
        try {
            // Clear existing forecasting data
            $this->db->exec("DELETE FROM forecasting_data");
            
            // Get historical data and generate forecasting dataset
            $query = "
                SELECT 
                    DATE(bi.created_at) as date,
                    bi.blood_type,
                    SUM(bi.quantity) as quantity,
                    COALESCE(req_data.demand, 0) as demand,
                    SUM(bi.quantity) as supply,
                    COALESCE(exp_data.expiry_count, 0) as expiry_count,
                    CASE 
                        WHEN MONTH(bi.created_at) IN (3,4,5) THEN 'spring'
                        WHEN MONTH(bi.created_at) IN (6,7,8) THEN 'summer'
                        WHEN MONTH(bi.created_at) IN (9,10,11) THEN 'fall'
                        ELSE 'winter'
                    END as season,
                    CASE 
                        WHEN DAYOFWEEK(bi.created_at) IN (1,7) THEN 1
                        ELSE 0
                    END as holiday
                FROM blood_inventory bi
                LEFT JOIN (
                    SELECT DATE(created_at) as req_date, blood_type, SUM(quantity_needed) as demand
                    FROM blood_requests 
                    GROUP BY DATE(created_at), blood_type
                ) req_data ON DATE(bi.created_at) = req_data.req_date AND bi.blood_type = req_data.blood_type
                LEFT JOIN (
                    SELECT DATE(created_at) as exp_date, blood_type, COUNT(*) as expiry_count
                    FROM blood_inventory 
                    WHERE status = 'expired'
                    GROUP BY DATE(created_at), blood_type
                ) exp_data ON DATE(bi.created_at) = exp_date.exp_date AND bi.blood_type = exp_data.blood_type
                WHERE DATE(bi.created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
                GROUP BY DATE(bi.created_at), bi.blood_type
                ORDER BY DATE(bi.created_at), bi.blood_type
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $historical_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Insert into forecasting_data table
            foreach ($historical_data as $data) {
                $insert_query = "INSERT INTO forecasting_data 
                                (date, blood_type, quantity, demand, supply, expiry_count, season, holiday) 
                                VALUES (:date, :blood_type, :quantity, :demand, :supply, :expiry_count, :season, :holiday)";
                $insert_stmt = $this->db->prepare($insert_query);
                $insert_stmt->execute([
                    ':date' => $data['date'],
                    ':blood_type' => $data['blood_type'],
                    ':quantity' => $data['quantity'],
                    ':demand' => $data['demand'],
                    ':supply' => $data['supply'],
                    ':expiry_count' => $data['expiry_count'],
                    ':season' => $data['season'],
                    ':holiday' => $data['holiday']
                ]);
            }
            
            return ['success' => true, 'message' => 'Forecasting data generated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Safe Linear Regression Implementation
    public function linearRegression($x_values, $y_values) {
        $n = count($x_values);
        
        // Check if we have enough data points
        if ($n < 2) {
            return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];
        }
        
        $x_sum = array_sum($x_values);
        $y_sum = array_sum($y_values);
        
        $xx_sum = 0;
        $xy_sum = 0;
        $yy_sum = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $xx_sum += $x_values[$i] * $x_values[$i];
            $xy_sum += $x_values[$i] * $y_values[$i];
            $yy_sum += $y_values[$i] * $y_values[$i];
        }
        
        // Calculate denominator and check for zero
        $denominator = $n * $xx_sum - $x_sum * $x_sum;
        
        if ($denominator == 0) {
            // If denominator is zero, return flat line at average y value
            $avg_y = $y_sum / $n;
            return [
                'slope' => 0, 
                'intercept' => $avg_y,
                'r_squared' => 0
            ];
        }
        
        // Calculate slope and intercept
        $slope = ($n * $xy_sum - $x_sum * $y_sum) / $denominator;
        $intercept = ($y_sum - $slope * $x_sum) / $n;
        
        // Calculate R-squared (coefficient of determination)
        $ss_total = $yy_sum - ($y_sum * $y_sum) / $n;
        $ss_res = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x_values[$i] + $intercept;
            $ss_res += ($y_values[$i] - $predicted) * ($y_values[$i] - $predicted);
        }
        
        $r_squared = ($ss_total > 0) ? 1 - ($ss_res / $ss_total) : 0;
        
        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $r_squared
        ];
    }
    
    // Enhanced Demand Forecasting with Seasonal Adjustment
    public function forecastDemand($blood_type, $days_ahead = 30) {
        try {
            $query = "SELECT 
                        DATEDIFF(date, (SELECT MIN(date) FROM forecasting_data)) as day_number,
                        demand,
                        CASE season 
                            WHEN 'spring' THEN 1 WHEN 'summer' THEN 2 
                            WHEN 'fall' THEN 3 ELSE 4 
                        END as season_num,
                        holiday,
                        expiry_count
                      FROM forecasting_data 
                      WHERE blood_type = :blood_type 
                      AND demand > 0  -- Only include days with actual demand
                      ORDER BY date";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':blood_type', $blood_type);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($data) < 2) {
                return [
                    'success' => false, 
                    'message' => 'Insufficient demand data available for forecasting'
                ];
            }
            
            // Prepare data for regression
            $x_days = array_column($data, 'day_number');
            $y_demand = array_column($data, 'demand');
            
            // Simple linear regression on time series
            $regression = $this->linearRegression($x_days, $y_demand);
            
            // Calculate seasonal factors only if we have enough data
            $seasonal_factors = [1 => 1, 2 => 1, 3 => 1, 4 => 1]; // Default neutral factors
            
            if (count($data) > 30) { // Only calculate seasonal factors if we have sufficient data
                $seasonal_data = [];
                foreach ($data as $row) {
                    $season = $row['season_num'];
                    if (!isset($seasonal_data[$season])) {
                        $seasonal_data[$season] = [];
                    }
                    $seasonal_data[$season][] = $row['demand'];
                }
                
                $overall_mean = array_sum($y_demand) / count($y_demand);
                
                foreach ($seasonal_data as $season => $demands) {
                    if (count($demands) > 0) {
                        $seasonal_mean = array_sum($demands) / count($demands);
                        $seasonal_factors[$season] = $overall_mean > 0 ? $seasonal_mean / $overall_mean : 1;
                    }
                }
            }
            
            // Generate forecasts
            $forecasts = [];
            $last_day = max($x_days);
            
            for ($i = 1; $i <= $days_ahead; $i++) {
                $future_day = $last_day + $i;
                $base_forecast = $regression['slope'] * $future_day + $regression['intercept'];
                
                // Apply seasonal adjustment
                $future_date = date('Y-m-d', strtotime('+' . $i . ' days'));
                $future_month = date('n', strtotime($future_date));
                
                $season_num = 4; // default winter
                if (in_array($future_month, [3,4,5])) $season_num = 1;
                elseif (in_array($future_month, [6,7,8])) $season_num = 2;
                elseif (in_array($future_month, [9,10,11])) $season_num = 3;
                
                $seasonal_factor = $seasonal_factors[$season_num] ?? 1;
                $adjusted_forecast = max(0, $base_forecast * $seasonal_factor);
                
                $forecasts[] = [
                    'date' => $future_date,
                    'forecast' => round($adjusted_forecast),
                    'confidence' => $this->calculateConfidence($regression['r_squared'])
                ];
            }
            
            return [
                'success' => true,
                'forecasts' => $forecasts,
                'model_accuracy' => $regression['r_squared'],
                'blood_type' => $blood_type,
                'seasonal_factors' => $seasonal_factors
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Calculate confidence based on R-squared
    private function calculateConfidence($r_squared) {
        $confidence = $r_squared * 100;
        return min(95, max(60, $confidence)); // Cap between 60-95%
    }
    
    // Forecast blood supply with safety checks
    public function forecastSupply($blood_type, $days_ahead = 30) {
        try {
            $query = "SELECT 
                        DATEDIFF(date, (SELECT MIN(date) FROM forecasting_data)) as day_number,
                        supply,
                        CASE season 
                            WHEN 'spring' THEN 1 WHEN 'summer' THEN 2 
                            WHEN 'fall' THEN 3 ELSE 4 
                        END as season_num,
                        holiday
                      FROM forecasting_data 
                      WHERE blood_type = :blood_type 
                      AND supply > 0  -- Only include days with actual supply
                      ORDER BY date";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':blood_type', $blood_type);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($data) < 2) {
                return [
                    'success' => false, 
                    'message' => 'Insufficient supply data available for forecasting'
                ];
            }
            
            $x_days = array_column($data, 'day_number');
            $y_supply = array_column($data, 'supply');
            
            $regression = $this->linearRegression($x_days, $y_supply);
            
            // Generate supply forecasts
            $forecasts = [];
            $last_day = max($x_days);
            
            for ($i = 1; $i <= $days_ahead; $i++) {
                $future_day = $last_day + $i;
                $forecast = max(0, $regression['slope'] * $future_day + $regression['intercept']);
                
                $forecasts[] = [
                    'date' => date('Y-m-d', strtotime('+' . $i . ' days')),
                    'forecast' => round($forecast),
                    'confidence' => $this->calculateConfidence($regression['r_squared'])
                ];
            }
            
            return [
                'success' => true,
                'forecasts' => $forecasts,
                'model_accuracy' => $regression['r_squared'],
                'blood_type' => $blood_type
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Calculate supply-demand ratio forecast with safety checks
    public function forecastSupplyDemandRatio($blood_type, $days_ahead = 30) {
        $demand_forecast = $this->forecastDemand($blood_type, $days_ahead);
        $supply_forecast = $this->forecastSupply($blood_type, $days_ahead);
        
        if (!$demand_forecast['success'] || !$supply_forecast['success']) {
            return [
                'success' => false, 
                'message' => 'Unable to generate ratio forecast: ' . 
                             ($demand_forecast['message'] ?? '') . ' ' . 
                             ($supply_forecast['message'] ?? '')
            ];
        }
        
        // Ensure we have matching forecast periods
        $days_ahead = min(count($demand_forecast['forecasts']), count($supply_forecast['forecasts']));
        $ratio_forecasts = [];
        
        for ($i = 0; $i < $days_ahead; $i++) {
            $demand = $demand_forecast['forecasts'][$i]['forecast'];
            $supply = $supply_forecast['forecasts'][$i]['forecast'];
            
            // Safely calculate ratio
            if ($demand <= 0 && $supply <= 0) {
                $ratio = 1; // Neutral ratio when no demand or supply
            } elseif ($demand <= 0) {
                $ratio = 100; // Infinite ratio represented as 100 when no demand
            } else {
                $ratio = $supply / $demand;
            }
            
            $status = 'balanced';
            if ($ratio < 0.8) {
                $status = 'shortage';
            } elseif ($ratio > 1.5) {
                $status = 'surplus';
            }
            
            $ratio_forecasts[] = [
                'date' => $demand_forecast['forecasts'][$i]['date'],
                'ratio' => round($ratio, 2),
                'supply' => $supply,
                'demand' => $demand,
                'status' => $status,
                'confidence' => min(
                    $demand_forecast['forecasts'][$i]['confidence'], 
                    $supply_forecast['forecasts'][$i]['confidence']
                )
            ];
        }
        
        return [
            'success' => true,
            'forecasts' => $ratio_forecasts,
            'blood_type' => $blood_type
        ];
    }
    
    // Predict blood expiry with enhanced safety
    public function forecastExpiry($days_ahead = 30) {
        try {
            $query = "SELECT 
                        blood_type,
                        expiry_date,
                        quantity,
                        DATEDIFF(expiry_date, CURDATE()) as days_to_expiry
                      FROM blood_inventory 
                      WHERE status = 'available' 
                      AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                      ORDER BY expiry_date, blood_type";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':days', $days_ahead, PDO::PARAM_INT);
            $stmt->execute();
            $expiry_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by blood type and calculate expiry predictions
            $expiry_forecasts = [];
            $total_expiring = 0;
            
            foreach ($expiry_data as $item) {
                $blood_type = $item['blood_type'];
                
                if (!isset($expiry_forecasts[$blood_type])) {
                    $expiry_forecasts[$blood_type] = [
                        'blood_type' => $blood_type,
                        'total_expiring' => 0,
                        'expiry_schedule' => []
                    ];
                }
                
                $expiry_forecasts[$blood_type]['total_expiring'] += (int)$item['quantity'];
                $expiry_forecasts[$blood_type]['expiry_schedule'][] = [
                    'date' => $item['expiry_date'],
                    'quantity' => (int)$item['quantity'],
                    'days_remaining' => (int)$item['days_to_expiry']
                ];
                
                $total_expiring += (int)$item['quantity'];
            }
            
            return [
                'success' => true,
                'forecasts' => array_values($expiry_forecasts),
                'total_units_expiring' => $total_expiring,
                'timeframe' => "$days_ahead days"
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Get comprehensive forecast dashboard data with error handling
    public function getForecastDashboard($blood_type = null, $days_ahead = 30) {
        try {
            // Validate days_ahead parameter
            $days_ahead = max(1, min(365, (int)$days_ahead)); // Limit to 1-365 days
            
            $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            
            if ($blood_type && in_array($blood_type, $blood_types)) {
                $blood_types = [$blood_type];
            }
            
            $dashboard_data = [];
            $errors = [];
            
            foreach ($blood_types as $type) {
                $demand_forecast = $this->forecastDemand($type, $days_ahead);
                $supply_forecast = $this->forecastSupply($type, $days_ahead);
                $ratio_forecast = $this->forecastSupplyDemandRatio($type, $days_ahead);
                
                $dashboard_data[$type] = [
                    'blood_type' => $type,
                    'demand_forecast' => $demand_forecast,
                    'supply_forecast' => $supply_forecast,
                    'ratio_forecast' => $ratio_forecast
                ];
                
                // Collect any errors
                if (!$demand_forecast['success']) {
                    $errors[] = "Demand forecast failed for $type: " . ($demand_forecast['message'] ?? 'Unknown error');
                }
                if (!$supply_forecast['success']) {
                    $errors[] = "Supply forecast failed for $type: " . ($supply_forecast['message'] ?? 'Unknown error');
                }
                if (!$ratio_forecast['success']) {
                    $errors[] = "Ratio forecast failed for $type: " . ($ratio_forecast['message'] ?? 'Unknown error');
                }
            }
            
            $expiry_forecast = $this->forecastExpiry($days_ahead);
            
            return [
                'success' => true,
                'blood_type_forecasts' => $dashboard_data,
                'expiry_forecast' => $expiry_forecast,
                'generated_at' => date('Y-m-d H:i:s'),
                'warnings' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Forecast generation failed: ' . $e->getMessage()
            ];
        }
    }
}