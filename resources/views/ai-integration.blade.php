<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
       
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            width: 250px;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #ced4da;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sales-data {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 15px;
            margin: 15px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .recommendations {
            background-color: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 0.375rem;
            padding: 15px;
            margin: 15px 0;
        }
        .loading {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <div class="sidebar">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/ai-integration">
                                AI Integration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/weather-integration">
                                Weather Integration
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">AI Integration</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">AI-Powered Sales Recommendations</h5>
                            </div>
                            <div class="card-body">
                                <p>Get AI-powered product promotion and strategic suggestions based on your sales data.</p>
                                
                                <button id="getRecommendations" class="btn btn-primary">
                                    Get AI Recommendations
                                </button>
                                <div id="loading" class="loading mt-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2">Analyzing sales data with AI...</span>
                                </div>
                                
                                <div id="salesDataSection" class="mt-4" style="display: none;">
                                    <h6>Sales Data Being Analyzed:</h6>
                                    <div id="salesData" class="sales-data">
                                       
                                    </div>
                                </div>
                                
                                <div id="recommendationsSection" class="mt-4" style="display: none;">
                                    <h6>AI Recommendations:</h6>
                                    <div id="recommendations" class="recommendations">
                                        
                                    </div>
                                </div>
                                
                                <div id="errorSection" class="mt-4" style="display: none;">
                                    <div class="alert alert-danger" id="errorMessage">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('getRecommendations').addEventListener('click', function() {
            const button = this;
            const loading = document.getElementById('loading');
            const salesDataSection = document.getElementById('salesDataSection');
            const recommendationsSection = document.getElementById('recommendationsSection');
            const errorSection = document.getElementById('errorSection');
            const salesData = document.getElementById('salesData');
            const recommendations = document.getElementById('recommendations');
            salesDataSection.style.display = 'none';
            recommendationsSection.style.display = 'none';
            errorSection.style.display = 'none';
            button.disabled = true;
            loading.style.display = 'block';
            fetch('/api/recommendations', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                button.disabled = false;
                
                if (data.success) {
                    salesData.innerHTML = '<pre>' + JSON.stringify(data.sales_data, null, 2) + '</pre>';
                    salesDataSection.style.display = 'block';
                    recommendations.innerHTML = data.recommendations.replace(/\n/g, '<br>');
                    recommendationsSection.style.display = 'block';
                } else {
                    document.getElementById('errorMessage').textContent = data.message || 'An error occurred while getting recommendations.';
                    errorSection.style.display = 'block';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                button.disabled = false;
                document.getElementById('errorMessage').textContent = 'Network error: ' + error.message;
                errorSection.style.display = 'block';
            });
        });
    </script>
</body>
</html>