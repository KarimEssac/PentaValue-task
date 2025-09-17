<?php
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('dashboard');
});
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/ai-integration', function () {
    return view('ai-integration');
});

Route::get('/weather-integration', function () {
    return view('weather-integration');
});
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
