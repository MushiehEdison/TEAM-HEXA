<?php
// pages/forecasting.php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';
require_once '../classes/Forecasting.php';

$auth = new Auth();
$auth->requireLogin();

$forecasting = new Forecasting();
$current_user = $auth->getCurrentUser();

// Get forecast data
$dashboard_forecast = $forecasting->getForecastDashboard(null, 30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forecasting Dashboard - Blood Bank Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-tint"></i>
                    <span>Blood Bank</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="blood-inventory.php">
                            <i class="fas fa-flask"></i>
                            <span>Blood Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="donations.php">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Donations</span>
                        </a>
                    </li>
                    <li>
                        <a href="requests.php">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Blood Requests</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="forecasting.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Forecasting</span>
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li>
                        <a href="staff.php">
                            <i class="fas fa-users"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Forecasting Dashboard</h1>
                </div>
                
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <span class="user-role"><?php echo ucfirst($current_user['role']); ?></span>
                        </div>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="dropdown">
                            <a href="settings.php"><i class="fas fa-user-cog"></i> Profile</a>
                            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="dashboard-content">
                <!-- Controls Section -->
                <div class="forecast-controls">
                    <div class="control-group">
                        <label for="bloodTypeSelect">Blood Type:</label>
                        <select id="bloodTypeSelect" onchange="updateForecast()">
                            <option value="">All Types</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label for="forecastDays">Forecast Period:</label>
                        <select id="forecastDays" onchange="updateForecast()">
                            <option value="7">7 Days</option>
                            <option value="14">14 Days</option>
                            <option value="30" selected>30 Days</option>
                            <option value="60">60 Days</option>
                            <option value="90">90 Days</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-primary" onclick="generateForecastData()">
                        <i class="fas fa-sync-alt"></i> Regenerate Data
                    </button>
                </div>

                <!-- Forecast Summary Cards -->
                <div class="forecast-summary">
                    <div class="summary-card demand">
                        <div class="card-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="card-content">
                            <h3 id="avgDemand">-</h3>
                            <p>Avg Daily Demand</p>
                            <span class="trend" id="demandTrend">-</span>
                        </div>
                    </div>
                    
                    <div class="summary-card supply">
                        <div class="card-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="card-content">
                            <h3 id="avgSupply">-</h3>
                            <p>Avg Daily Supply</p>
                            <span class="trend" id="supplyTrend">-</span>
                        </div>
                    </div>
                    
                    <div class="summary-card ratio">
                        <div class="card-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="card-content">
                            <h3 id="avgRatio">-</h3>
                            <p>Supply/Demand Ratio</p>
                            <span class="status" id="ratioStatus">-</span>
                        </div>
                    </div>
                    
                    <div class="summary-card accuracy">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="card-content">
                            <h3 id="modelAccuracy">-</h3>
                            <p>Model Accuracy</p>
                            <span class="confidence" id="confidenceLevel">-</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="forecast-charts">
                    <!-- Demand vs Supply Forecast -->
                    <div class="chart-container large">
                        <div class="chart-header">
                            <h3>Demand vs Supply Forecast (Linear Regression)</h3>
                            <div class="chart-legend">
                                <span class="legend-item demand">
                                    <span class="color-box"></span> Demand Forecast
                                </span>
                                <span class="legend-item supply">
                                    <span class="color-box"></span> Supply Forecast
                                </span>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="demandSupplyChart"></canvas>
                        </div>
                    </div>

                    <!-- Supply/Demand Ratio Forecast -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Supply/Demand Ratio Trend</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="ratioChart"></canvas>
                        </div>
                    </div>

                    <!-- Blood Type Distribution Forecast -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Blood Type Demand Distribution</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Forecasts Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Detailed Forecast Analysis</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary" onclick="exportForecast()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table" id="forecastTable">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Avg Demand</th>
                                    <th>Avg Supply</th>
                                    <th>Ratio</th>
                                    <th>Status</th>
                                    <th>Model R²</th>
                                    <th>Confidence</th>
                                    <th>Action Needed</th>
                                </tr>
                            </thead>
                            <tbody id="forecastTableBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expiry Forecast -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Blood Expiry Predictions</h3>
                        <p class="table-description">Predicted blood units expiring in the forecast period</p>
                    </div>
                    
                    <div class="expiry-grid" id="expiryGrid">
                        <!-- Expiry data will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Calculating forecasts...</p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Global variables for charts
        let demandSupplyChart, ratioChart, distributionChart;
        let currentForecastData = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialForecast();
        });

        // Load initial forecast data
        async function loadInitialForecast() {
            showLoading();
            
            try {
                const result = await ForecastingManager.getDashboardForecast(30);
                
                if (result.success) {
                    currentForecastData = result;
                    updateDashboard(result);
                } else {
                    AlertSystem.show('Failed to load forecast data: ' + result.message, 'error');
                }
            } catch (error) {
                AlertSystem.show('Error loading forecast data', 'error');
                console.error(error);
            } finally {
                hideLoading();
            }
        }

        // Update forecast based on controls
        async function updateForecast() {
            const bloodType = document.getElementById('bloodTypeSelect').value;
            const daysAhead = parseInt(document.getElementById('forecastDays').value);
            
            showLoading();
            
            try {
                const result = await ForecastingManager.getDashboardForecast(daysAhead);
                
                if (result.success) {
                    currentForecastData = result;
                    updateDashboard(result);
                } else {
                    AlertSystem.show('Failed to update forecast: ' + result.message, 'error');
                }
            } catch (error) {
                AlertSystem.show('Error updating forecast', 'error');
                console.error(error);
            } finally {
                hideLoading();
            }
        }

        // Generate new forecast data
        async function generateForecastData() {
            if (!confirm('This will regenerate forecasting data from historical records. Continue?')) {
                return;
            }
            
            showLoading();
            
            try {
                const result = await ForecastingManager.generateForecastData();
                
                if (result.success) {
                    AlertSystem.show(result.message, 'success');
                    // Reload forecast data
                    setTimeout(() => loadInitialForecast(), 2000);
                } else {
                    AlertSystem.show('Failed to generate data: ' + result.message, 'error');
                }
            } catch (error) {
                AlertSystem.show('Error generating forecast data', 'error');
                console.error(error);
            } finally {
                hideLoading();
            }
        }

        // Update dashboard with forecast data
        function updateDashboard(data) {
            if (!data.success || !data.blood_type_forecasts) {
                console.error('Invalid forecast data');
                return;
            }

            updateSummaryCards(data);
            updateCharts(data);
            updateForecastTable(data);
            updateExpiryGrid(data.expiry_forecast);
        }

        // Update summary cards
        function updateSummaryCards(data) {
            let totalDemand = 0;
            let totalSupply = 0;
            let totalAccuracy = 0;
            let validForecasts = 0;

            Object.values(data.blood_type_forecasts).forEach(forecast => {
                if (forecast.demand_forecast && forecast.demand_forecast.success) {
                    const demandAvg = forecast.demand_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.demand_forecast.forecasts.length;
                    totalDemand += demandAvg;
                    totalAccuracy += forecast.demand_forecast.model_accuracy || 0;
                    validForecasts++;
                }
                
                if (forecast.supply_forecast && forecast.supply_forecast.success) {
                    const supplyAvg = forecast.supply_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.supply_forecast.forecasts.length;
                    totalSupply += supplyAvg;
                }
            });

            const avgDemand = totalDemand / validForecasts || 0;
            const avgSupply = totalSupply / validForecasts || 0;
            const avgRatio = avgDemand > 0 ? avgSupply / avgDemand : 1;
            const avgAccuracy = totalAccuracy / validForecasts || 0;

            document.getElementById('avgDemand').textContent = Math.round(avgDemand);
            document.getElementById('avgSupply').textContent = Math.round(avgSupply);
            document.getElementById('avgRatio').textContent = avgRatio.toFixed(2);
            document.getElementById('modelAccuracy').textContent = Math.round(avgAccuracy * 100) + '%';

            // Update status indicators
            const demandTrend = avgDemand > avgSupply ? 'trending-up' : 'trending-down';
            const supplyTrend = avgSupply > avgDemand ? 'trending-up' : 'trending-down';
            const ratioStatus = avgRatio < 0.8 ? 'shortage' : (avgRatio > 1.5 ? 'surplus' : 'balanced');

            document.getElementById('demandTrend').className = `trend ${demandTrend}`;
            document.getElementById('supplyTrend').className = `trend ${supplyTrend}`;
            document.getElementById('ratioStatus').className = `status ${ratioStatus}`;
            document.getElementById('ratioStatus').textContent = ratioStatus.charAt(0).toUpperCase() + ratioStatus.slice(1);
            
            const confidenceLevel = avgAccuracy > 0.8 ? 'high' : (avgAccuracy > 0.6 ? 'medium' : 'low');
            document.getElementById('confidenceLevel').className = `confidence ${confidenceLevel}`;
            document.getElementById('confidenceLevel').textContent = confidenceLevel.charAt(0).toUpperCase() + confidenceLevel.slice(1);
        }

        // Update charts
        function updateCharts(data) {
            updateDemandSupplyChart(data);
            updateRatioChart(data);
            updateDistributionChart(data);
        }

        // Update demand vs supply chart
        function updateDemandSupplyChart(data) {
            const ctx = document.getElementById('demandSupplyChart').getContext('2d');
            
            // Destroy existing chart
            if (demandSupplyChart) {
                demandSupplyChart.destroy();
            }

            // Prepare data for all blood types combined
            const combinedData = {};
            
            Object.values(data.blood_type_forecasts).forEach(forecast => {
                if (forecast.demand_forecast && forecast.demand_forecast.success) {
                    forecast.demand_forecast.forecasts.forEach(f => {
                        if (!combinedData[f.date]) {
                            combinedData[f.date] = { demand: 0, supply: 0 };
                        }
                        combinedData[f.date].demand += f.forecast;
                    });
                }
                
                if (forecast.supply_forecast && forecast.supply_forecast.success) {
                    forecast.supply_forecast.forecasts.forEach(f => {
                        if (!combinedData[f.date]) {
                            combinedData[f.date] = { demand: 0, supply: 0 };
                        }
                        combinedData[f.date].supply += f.forecast;
                    });
                }
            });

            const sortedDates = Object.keys(combinedData).sort();
            const labels = sortedDates.map(date => new Date(date).toLocaleDateString());
            const demandData = sortedDates.map(date => combinedData[date].demand);
            const supplyData = sortedDates.map(date => combinedData[date].supply);

            demandSupplyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Demand Forecast',
                            data: demandData,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: false,
                            tension: 0.4
                        },
                        {
                            label: 'Supply Forecast',
                            data: supplyData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Units'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Update ratio chart
        function updateRatioChart(data) {
            const ctx = document.getElementById('ratioChart').getContext('2d');
            
            if (ratioChart) {
                ratioChart.destroy();
            }

            // Prepare ratio data
            const ratioData = {};
            
            Object.values(data.blood_type_forecasts).forEach(forecast => {
                if (forecast.ratio_forecast && forecast.ratio_forecast.success) {
                    forecast.ratio_forecast.forecasts.forEach(f => {
                        if (!ratioData[f.date]) {
                            ratioData[f.date] = { total: 0, count: 0 };
                        }
                        ratioData[f.date].total += f.ratio;
                        ratioData[f.date].count++;
                    });
                }
            });

            const sortedDates = Object.keys(ratioData).sort();
            const labels = sortedDates.map(date => new Date(date).toLocaleDateString());
            const ratios = sortedDates.map(date => ratioData[date].total / ratioData[date].count);

            ratioChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Supply/Demand Ratio',
                        data: ratios,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Ratio'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Update distribution chart
        function updateDistributionChart(data) {
            const ctx = document.getElementById('distributionChart').getContext('2d');
            
            if (distributionChart) {
                distributionChart.destroy();
            }

            // Calculate average demand by blood type
            const bloodTypeDemand = {};
            
            Object.entries(data.blood_type_forecasts).forEach(([bloodType, forecast]) => {
                if (forecast.demand_forecast && forecast.demand_forecast.success) {
                    const avgDemand = forecast.demand_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.demand_forecast.forecasts.length;
                    bloodTypeDemand[bloodType] = avgDemand;
                }
            });

            const labels = Object.keys(bloodTypeDemand);
            const demandData = Object.values(bloodTypeDemand);

            distributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: demandData,
                        backgroundColor: [
                            '#ef4444', '#f97316', '#eab308', '#22c55e',
                            '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Update forecast table
        function updateForecastTable(data) {
            const tbody = document.getElementById('forecastTableBody');
            tbody.innerHTML = '';

            Object.entries(data.blood_type_forecasts).forEach(([bloodType, forecast]) => {
                const row = document.createElement('tr');
                
                let avgDemand = 0;
                let avgSupply = 0;
                let ratio = 1;
                let modelAccuracy = 0;
                let confidence = 0;

                if (forecast.demand_forecast && forecast.demand_forecast.success) {
                    avgDemand = forecast.demand_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.demand_forecast.forecasts.length;
                    modelAccuracy = forecast.demand_forecast.model_accuracy || 0;
                    confidence = forecast.demand_forecast.forecasts[0]?.confidence || 0;
                }

                if (forecast.supply_forecast && forecast.supply_forecast.success) {
                    avgSupply = forecast.supply_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.supply_forecast.forecasts.length;
                }

                if (avgDemand > 0) {
                    ratio = avgSupply / avgDemand;
                }

                const status = ratio < 0.8 ? 'shortage' : (ratio > 1.5 ? 'surplus' : 'balanced');
                const action = ratio < 0.8 ? 'Increase Supply' : (ratio > 1.5 ? 'Reduce Waste' : 'Maintain');

                row.innerHTML = `
                    <td><span class="blood-type">${bloodType}</span></td>
                    <td>${Math.round(avgDemand)}</td>
                    <td>${Math.round(avgSupply)}</td>
                    <td>${ratio.toFixed(2)}</td>
                    <td><span class="status status-${status}">${status}</span></td>
                    <td>${Math.round(modelAccuracy * 100)}%</td>
                    <td>${Math.round(confidence)}%</td>
                    <td><span class="action-needed">${action}</span></td>
                `;

                tbody.appendChild(row);
            });
        }

        // Update expiry grid
        function updateExpiryGrid(expiryData) {
            const grid = document.getElementById('expiryGrid');
            grid.innerHTML = '';

            if (!expiryData || !expiryData.success) {
                grid.innerHTML = '<p class="no-data">No expiry data available</p>';
                return;
            }

            expiryData.forecasts.forEach(item => {
                const card = document.createElement('div');
                card.className = 'expiry-card';
                
                card.innerHTML = `
                    <div class="expiry-header">
                        <span class="blood-type">${item.blood_type}</span>
                        <span class="expiry-count">${item.total_expiring} units</span>
                    </div>
                    <div class="expiry-schedule">
                        ${item.expiry_schedule.slice(0, 3).map(schedule => `
                            <div class="schedule-item">
                                <span class="date">${new Date(schedule.date).toLocaleDateString()}</span>
                                <span class="quantity">${schedule.quantity} units</span>
                                <span class="days-left">${schedule.days_remaining} days</span>
                            </div>
                        `).join('')}
                        ${item.expiry_schedule.length > 3 ? `<div class="more-items">+${item.expiry_schedule.length - 3} more</div>` : ''}
                    </div>
                `;

                grid.appendChild(card);
            });

            if (expiryData.forecasts.length === 0) {
                grid.innerHTML = '<p class="no-data">No blood units expiring in forecast period</p>';
            }
        }

        // Export forecast data
        function exportForecast() {
            if (!currentForecastData) {
                AlertSystem.show('No forecast data to export', 'warning');
                return;
            }

            // Create CSV content
            let csvContent = 'Blood Type,Avg Demand,Avg Supply,Ratio,Status,Model Accuracy,Confidence\n';
            
            Object.entries(currentForecastData.blood_type_forecasts).forEach(([bloodType, forecast]) => {
                let avgDemand = 0;
                let avgSupply = 0;
                let ratio = 1;
                let modelAccuracy = 0;
                let confidence = 0;

                if (forecast.demand_forecast && forecast.demand_forecast.success) {
                    avgDemand = forecast.demand_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.demand_forecast.forecasts.length;
                    modelAccuracy = forecast.demand_forecast.model_accuracy || 0;
                    confidence = forecast.demand_forecast.forecasts[0]?.confidence || 0;
                }

                if (forecast.supply_forecast && forecast.supply_forecast.success) {
                    avgSupply = forecast.supply_forecast.forecasts.reduce((sum, f) => sum + f.forecast, 0) / forecast.supply_forecast.forecasts.length;
                }

                if (avgDemand > 0) {
                    ratio = avgSupply / avgDemand;
                }

                const status = ratio < 0.8 ? 'shortage' : (ratio > 1.5 ? 'surplus' : 'balanced');

                csvContent += `${bloodType},${Math.round(avgDemand)},${Math.round(avgSupply)},${ratio.toFixed(2)},${status},${Math.round(modelAccuracy * 100)}%,${Math.round(confidence)}%\n`;
            });

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `blood_forecast_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            AlertSystem.show('Forecast data exported successfully', 'success');
        }

        // Show/hide loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>

    <style>
        .forecast-controls {
            display: flex;
            gap: 2rem;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .control-group label {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .control-group select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 150px;
        }

        .forecast-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }

        .summary-card .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .summary-card.demand .card-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .summary-card.supply .card-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .summary-card.ratio .card-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .summary-card.accuracy .card-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .summary-card .card-content h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .summary-card .card-content p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .trend {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .trend.trending-up {
            background: #dcfce7;
            color: #166534;
        }

        .trend.trending-down {
            background: #fee2e2;
            color: #991b1b;
        }

        .status {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .status.shortage {
            background: #fee2e2;
            color: #991b1b;
        }

        .status.surplus {
            background: #dbeafe;
            color: #1e40af;
        }

        .status.balanced {
            background: #dcfce7;
            color: #166534;
        }

        .confidence {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .confidence.high {
            background: #dcfce7;
            color: #166534;
        }

        .confidence.medium {
            background: #fef3c7;
            color: #92400e;
        }

        .confidence.low {
            background: #fee2e2;
            color: #991b1b;
        }

        .forecast-charts {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container.large {
            grid-column: span 3;
        }

        .chart-legend {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .legend-item .color-box {
            width: 16px;
            height: 16px;
            border-radius: 2px;
        }

        .legend-item.demand .color-box {
            background: #ef4444;
        }

        .legend-item.supply .color-box {
            background: #10b981;
        }

        .table-description {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .expiry-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .expiry-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }

        .expiry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .expiry-count {
            font-weight: 600;
            color: #d97706;
        }

        .expiry-schedule {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .schedule-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #fef3c7;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .schedule-item .date {
            font-weight: 500;
        }

        .schedule-item .days-left {
            color: #d97706;
            font-weight: 500;
        }

        .more-items {
            text-align: center;
            color: #666;
            font-size: 0.8rem;
            font-style: italic;
            padding: 0.25rem;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
            grid-column: 1 / -1;
        }

        .action-needed {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: #f3f4f6;
            color: #374151;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            max-width: 300px;
        }

        .loading-content i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .loading-content p {
            margin: 0;
            color: #666;
        }

        @media (max-width: 1024px) {
            .forecast-charts {
                grid-template-columns: 1fr;
            }

            .chart-container.large {
                grid-column: span 1;
            }
        }

        @media (max-width: 768px) {
            .forecast-controls {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .control-group {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .forecast-summary {
                grid-template-columns: 1fr;
            }

            .expiry-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>