<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherIntegrationController;
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/weather-data', [WeatherIntegrationController::class, 'getWeatherData']);
Route::get('/analytics', [AnalyticsController::class, 'index']);
Route::get('/recommendations', [App\Http\Controllers\AiIntegrationController::class, 'getRecommendations']);
Route::get('/orders/recent', function() {
    $orders = \App\Models\Order::orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    return response()->json($orders);
});