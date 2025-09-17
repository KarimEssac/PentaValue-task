<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\OrderCreated;
use App\Events\AnalyticsUpdated;
use App\Http\Controllers\Api\AnalyticsController;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $order = Order::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->quantity * $request->price
        ]);

        // Broadcast the new order
        event(new OrderCreated($order));
        
        // Update and broadcast analytics
        $analyticsController = new AnalyticsController();
        $analytics = $analyticsController->getAnalyticsData();
        event(new AnalyticsUpdated($analytics));

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
    }
}