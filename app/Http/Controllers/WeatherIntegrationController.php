<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class WeatherIntegrationController extends Controller
{
    public function getWeatherData()
    {
        try {
            $weatherData = $this->getCurrentWeather();
            $recommendations = $this->generateProductRecommendations($weatherData);
            $pricingAdjustments = $this->generatePricingAdjustments($weatherData);
            
            return response()->json([
                'success' => true,
                'weather' => $weatherData,
                'recommendations' => $recommendations,
                'pricing_adjustments' => $pricingAdjustments
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching weather data: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getCurrentWeather()
    {
        $apiKey = config('services.openweather.api_key');
        $city = config('services.openweather.default_city', 'Cairo');
        
        if (!$apiKey) {
            return [
                'temp' => rand(15, 35),
                'feels_like' => rand(15, 35),
                'condition' => collect(['Clear Sky', 'Partly Cloudy', 'Cloudy', 'Light Rain', 'Sunny'])->random(),
                'humidity' => rand(30, 80),
                'wind_speed' => rand(1, 10),
                'location' => $city
            ];
        }
        
        $response = Http::timeout(10)
            ->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);
            
        if ($response->failed()) {
            throw new Exception('Failed to fetch weather data from OpenWeather API');
        }
        
        $data = $response->json();
        
        return [
            'temp' => round($data['main']['temp']),
            'feels_like' => round($data['main']['feels_like']),
            'condition' => $data['weather'][0]['description'],
            'humidity' => $data['main']['humidity'],
            'wind_speed' => round($data['wind']['speed']),
            'location' => $data['name'] . ', ' . $data['sys']['country']
        ];
    }
    
    private function generateProductRecommendations($weather)
    {
        $temp = $weather['temp'];
        $condition = strtolower($weather['condition']);
        $recommendations = [];
        
        // Hot weather recommendations
        if ($temp >= 25) {
            $recommendations[] = [
                'category' => 'Cold Beverages',
                'reason' => 'Temperature is ' . $temp . '°C - perfect weather for cold drinks',
                'boost' => '+25-40%'
            ];
            
            $recommendations[] = [
                'category' => 'Ice Cream & Frozen Desserts',
                'reason' => 'Hot weather increases demand for cooling products',
                'boost' => '+30-50%'
            ];
            
            $recommendations[] = [
                'category' => 'Summer Clothing',
                'reason' => 'Light clothing and swimwear demand increases in hot weather',
                'boost' => '+20-35%'
            ];
            
            if (strpos($condition, 'sun') !== false || strpos($condition, 'clear') !== false) {
                $recommendations[] = [
                    'category' => 'Sunscreen & Sun Protection',
                    'reason' => 'Sunny weather drives sun protection product sales',
                    'boost' => '+40-60%'
                ];
            }
        }

        elseif ($temp <= 15) {
            $recommendations[] = [
                'category' => 'Hot Beverages',
                'reason' => 'Temperature is ' . $temp . '°C - ideal for hot drinks like coffee, tea, and hot chocolate',
                'boost' => '+25-40%'
            ];
            
            $recommendations[] = [
                'category' => 'Warm Clothing',
                'reason' => 'Cold weather increases demand for jackets, sweaters, and warm accessories',
                'boost' => '+30-45%'
            ];
            
            $recommendations[] = [
                'category' => 'Comfort Food',
                'reason' => 'Cold weather drives demand for soups, hot meals, and comfort foods',
                'boost' => '+20-30%'
            ];
        }
        else {
            $recommendations[] = [
                'category' => 'Outdoor Activities',
                'reason' => 'Perfect weather (' . $temp . '°C) for outdoor activities and sports equipment',
                'boost' => '+15-25%'
            ];
            
            $recommendations[] = [
                'category' => 'Light Clothing',
                'reason' => 'Mild weather is ideal for light jackets and transitional clothing',
                'boost' => '+10-20%'
            ];
        }
        if (strpos($condition, 'rain') !== false) {
            $recommendations[] = [
                'category' => 'Rain Gear',
                'reason' => 'Rainy weather increases demand for umbrellas, raincoats, and waterproof items',
                'boost' => '+50-80%'
            ];
            
            $recommendations[] = [
                'category' => 'Indoor Entertainment',
                'reason' => 'People stay indoors during rain - books, games, streaming services see increased demand',
                'boost' => '+15-25%'
            ];
        }
        
        return $recommendations;
    }
    
    private function generatePricingAdjustments($weather)
    {
        $temp = $weather['temp'];
        $condition = strtolower($weather['condition']);
        $adjustments = [];
        if ($temp >= 25) {
            $adjustments[] = [
                'category' => 'Cold Beverages',
                'adjustment' => 15,
                'reason' => 'High demand due to hot weather'
            ];
            
            $adjustments[] = [
                'category' => 'Ice Cream',
                'adjustment' => 20,
                'reason' => 'Peak demand in hot weather'
            ];
            
            $adjustments[] = [
                'category' => 'Hot Beverages',
                'adjustment' => -10,
                'reason' => 'Lower demand in hot weather'
            ];
            
            if (strpos($condition, 'sun') !== false) {
                $adjustments[] = [
                    'category' => 'Sunscreen Products',
                    'adjustment' => 25,
                    'reason' => 'High UV exposure increases demand'
                ];
            }
        }

        elseif ($temp <= 15) {
            $adjustments[] = [
                'category' => 'Hot Beverages',
                'adjustment' => 15,
                'reason' => 'High demand due to cold weather'
            ];
            
            $adjustments[] = [
                'category' => 'Warm Clothing',
                'adjustment' => 20,
                'reason' => 'Seasonal demand increase'
            ];
            
            $adjustments[] = [
                'category' => 'Cold Beverages',
                'adjustment' => -15,
                'reason' => 'Lower demand in cold weather'
            ];
            
            $adjustments[] = [
                'category' => 'Heating Products',
                'adjustment' => 30,
                'reason' => 'High demand for warmth'
            ];
        }

        else {
            $adjustments[] = [
                'category' => 'Outdoor Equipment',
                'adjustment' => 10,
                'reason' => 'Perfect weather for outdoor activities'
            ];
            
            $adjustments[] = [
                'category' => 'Seasonal Items',
                'adjustment' => -5,
                'reason' => 'Neutral demand in mild weather'
            ];
        }

        if (strpos($condition, 'rain') !== false) {
            $adjustments[] = [
                'category' => 'Umbrellas',
                'adjustment' => 50,
                'reason' => 'Immediate need due to rain'
            ];
            
            $adjustments[] = [
                'category' => 'Waterproof Items',
                'adjustment' => 35,
                'reason' => 'High demand for rain protection'
            ];
            
            $adjustments[] = [
                'category' => 'Outdoor Activities',
                'adjustment' => -20,
                'reason' => 'Lower demand due to rain'
            ];
        }

        if ($weather['humidity'] > 70) {
            $adjustments[] = [
                'category' => 'Dehumidifiers',
                'adjustment' => 25,
                'reason' => 'High humidity (' . $weather['humidity'] . '%) increases demand'
            ];
        }
        
        return $adjustments;
    }
}