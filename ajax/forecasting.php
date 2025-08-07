<?php
// ajax/forecasting.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Forecasting.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$forecasting = new Forecasting();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'generate_data':
        $result = $forecasting->generateForecastingData();
        echo json_encode($result);
        break;
        
    case 'forecast_demand':
        $result = $forecasting->forecastDemand(
            $_POST['blood_type'],
            $_POST['days_ahead'] ?? 30
        );
        echo json_encode($result);
        break;
        
    case 'forecast_supply':
        $result = $forecasting->forecastSupply(
            $_POST['blood_type'],
            $_POST['days_ahead'] ?? 30
        );
        echo json_encode($result);
        break;
        
    case 'forecast_ratio':
        $result = $forecasting->forecastSupplyDemandRatio(
            $_POST['blood_type'],
            $_POST['days_ahead'] ?? 30
        );
        echo json_encode($result);
        break;
        
    case 'forecast_expiry':
        $result = $forecasting->forecastExpiry($_POST['days_ahead'] ?? 30);
        echo json_encode($result);
        break;
        
    case 'get_dashboard_forecast':
        $result = $forecasting->getForecastDashboard(
            $_POST['blood_type'] ?? null,
            $_POST['days_ahead'] ?? 30
        );
        echo json_encode($result);
        break;
        
    case 'get_forecast':
        // Get specific forecast type
        $forecast_type = $_POST['forecast_type'] ?? 'demand';
        $blood_type = $_POST['blood_type'];
        $days_ahead = $_POST['days_ahead'] ?? 30;
        
        switch ($forecast_type) {
            case 'demand':
                $result = $forecasting->forecastDemand($blood_type, $days_ahead);
                break;
            case 'supply':
                $result = $forecasting->forecastSupply($blood_type, $days_ahead);
                break;
            case 'ratio':
                $result = $forecasting->forecastSupplyDemandRatio($blood_type, $days_ahead);
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid forecast type'];
        }
        
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>