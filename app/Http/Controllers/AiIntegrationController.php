<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class AiIntegrationController extends Controller
{
    public function getRecommendations()
    {
        try {
            $salesData = $this->getSalesData();
            
            if (empty($salesData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sales data found for analysis.'
                ]);
            }
            
            $recommendations = $this->getAiRecommendations($salesData);
            
            return response()->json([
                'success' => true,
                'sales_data' => $salesData,
                'recommendations' => $recommendations
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting AI recommendations: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getSalesData()
    {
        $salesData = DB::table('orders')
            ->select([
                'id',
                'product_id',
                'quantity',
                'price',
                'total',
                'created_at',
                DB::raw('(quantity * price) as calculated_revenue')
            ])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
            
        
        return $salesData;
    }
    
    private function getAiRecommendations($salesData)
    {
        $apiKey = config('services.gemini.api_key');
        
        if (!$apiKey) {
            throw new Exception('Gemini API key not configured. Please add GEMINI_API_KEY to your .env file.');
        }
    
        $prompt = $this->buildPrompt($salesData);
        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ]
            ]);
            
        if ($response->failed()) {
            throw new Exception('Failed to get response from Gemini API: ' . $response->body());
        }
        
        $responseData = $response->json();
        
        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid response format from Gemini API');
        }
        
        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    }
    
    private function buildPrompt($salesData)
    {
        $totalRevenue = 0;
        $totalQuantity = 0;
        $dataString = '';
        $productSummary = [];
        
        foreach ($salesData as $item) {
            $revenue = $item->total ?? ($item->quantity * $item->price);
            $totalRevenue += $revenue;
            $totalQuantity += $item->quantity;
            if (!isset($productSummary[$item->product_id])) {
                $productSummary[$item->product_id] = [
                    'total_quantity' => 0,
                    'total_revenue' => 0,
                    'order_count' => 0,
                    'avg_price' => 0
                ];
            }
            
            $productSummary[$item->product_id]['total_quantity'] += $item->quantity;
            $productSummary[$item->product_id]['total_revenue'] += $revenue;
            $productSummary[$item->product_id]['order_count']++;
            $productSummary[$item->product_id]['avg_price'] = $item->price;
            
            $dataString .= "Order ID: {$item->id}, ";
            $dataString .= "Product ID: {$item->product_id}, ";
            $dataString .= "Quantity: {$item->quantity}, ";
            $dataString .= "Unit Price: $" . number_format($item->price, 2) . ", ";
            $dataString .= "Total: $" . number_format($revenue, 2) . ", ";
            $dataString .= "Date: " . date('Y-m-d', strtotime($item->created_at)) . "\n";
        }
        $productSummaryString = '';
        foreach ($productSummary as $productId => $summary) {
            $productSummaryString .= "Product ID: {$productId}, ";
            $productSummaryString .= "Total Orders: {$summary['order_count']}, ";
            $productSummaryString .= "Total Quantity: {$summary['total_quantity']}, ";
            $productSummaryString .= "Total Revenue: $" . number_format($summary['total_revenue'], 2) . ", ";
            $productSummaryString .= "Avg Price: $" . number_format($summary['avg_price'], 2) . "\n";
        }
        
        $avgPrice = $totalQuantity > 0 ? $totalRevenue / $totalQuantity : 0;
        
        return "Given this sales data, which products should we promote for higher
revenue?
        
        Overall Sales Summary:
        Total Revenue: $" . number_format($totalRevenue, 2) . "
        Total Quantity Sold: {$totalQuantity}
        Average Price per Unit: $" . number_format($avgPrice, 2) . "
        
        Product Performance Summary:
        {$productSummaryString}
        
        Individual Order Details:
        {$dataString}
        
        Please provide actionable business recommendations based on this sales data to maximize revenue. Focus on which product IDs are performing best and worst.";
    }
}