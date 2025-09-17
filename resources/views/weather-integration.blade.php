<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Integration</title>
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
            width: 250px;
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
        .weather-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="sidebar">
                <div class="pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/ai-integration">
                                AI Integration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/weather-integration">
                                Weather Integration
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Weather Integration</h1>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="weather-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4>Current Weather</h4>
                                <button class="btn btn-primary" onclick="checkWeather()">
                                    <i class="fas fa-sync-alt"></i> Check Weather
                                </button>
                            </div>
                            <div id="weatherDisplay" class="mt-3">
                                <p>Click "Check Weather" to get current conditions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>Drink Suggestions</h5>
                                <div id="drinkSuggestions" class="mt-3">
                                    <p>Weather-based drink recommendations will appear here</p>
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
        function checkWeather() {
            const apiKey = '7a238bb3bb12946eeea944cb653a5063';

            const weatherDisplay = document.getElementById('weatherDisplay');
            weatherDisplay.innerHTML = '<p>Loading weather...</p>';

            const success = (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                fetchWeather(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`);
            };

            const error = () => {
                fetchWeather(`https://api.openweathermap.org/data/2.5/weather?q=${defaultCity}&appid=${apiKey}&units=metric`);
            };

            const fetchWeather = (url) => {
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Weather API request failed');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const temp = data.main.temp;
                        const city = data.name;
                        let currentWeather = '';
                        let weatherInfo = '';
                        let weatherIcon = '';

                        if (temp >= 30) {
                            currentWeather = 'hot';
                            weatherInfo = `Hot Weather (${temp}°C)`;
                            weatherIcon = '<i class="fas fa-sun text-warning"></i>';
                        } else if (temp < 20) {
                            currentWeather = 'cold';
                            weatherInfo = `Cold Weather (${temp}°C)`;
                            weatherIcon = '<i class="fas fa-snowflake text-info"></i>';
                        } else {
                            currentWeather = 'mild';
                            weatherInfo = `Mild Weather (${temp}°C)`;
                            weatherIcon = '<i class="fas fa-cloud-sun text-secondary"></i>';
                        }

                        weatherDisplay.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div style="font-size: 2rem; margin-right: 10px;">${weatherIcon}</div>
                                <div>
                                    <h5 class="mb-0">${weatherInfo}</h5>
                                    <small class="text-muted">In ${city}, updated just now</small>
                                </div>
                            </div>
                        `;

                        showDrinkSuggestions(currentWeather);
                    })
                    .catch(err => {
                        console.error(err);
                        weatherDisplay.innerHTML = '<p>Failed to load weather. Please try again.</p>';
                    });
            };

            if (!navigator.geolocation) {
                error();
            } else {
                navigator.geolocation.getCurrentPosition(success, error);
            }
        }
        
        function showDrinkSuggestions(weather) {
            const suggestions = {
                hot: [
                    'Iced Coffee',
                    'Cold Lemonade', 
                    'Iced Tea',
                    'Fruit Smoothie',
                    'Ice Cold Water'
                ],
                cold: [
                    'Hot Coffee',
                    'Hot Chocolate',
                    'Green Tea',
                    'Warm Milk',
                    'Hot Herbal Tea'
                ],
                mild: [
                    'Fresh Juice',
                    'Regular Coffee',
                    'Sparkling Water',
                    'Light Tea',
                    'Room Temperature Water'
                ]
            };
            
            const drinkList = suggestions[weather] || [];
            const suggestionsContainer = document.getElementById('drinkSuggestions');
            
            let suggestionsHTML = `<h6>Recommended Drinks:</h6><ul class="list-unstyled">`;
            drinkList.forEach(drink => {
                suggestionsHTML += `<li><i class="fas fa-check text-success"></i> ${drink}</li>`;
            });
            suggestionsHTML += '</ul>';
            
            suggestionsContainer.innerHTML = suggestionsHTML;
        }
    </script>
</body>
</html>