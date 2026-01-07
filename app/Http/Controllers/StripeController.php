<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function getProducts(Request $request)
    {
        Stripe::setApiKey(config('cashier.secret'));

        $productId = config('services.stripe.product');

        if (empty($productId)) {
            return response()->json(['status' => false, 'message' => 'Stripe product ID is not configured in .env file.'], 500);
        }

        try {
            $product = \Stripe\Product::retrieve($productId);
            $prices = \Stripe\Price::all(['product' => $product->id, 'active' => true]);

            $productsData = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'prices' => $prices->data,
            ];

            return response()->json(['status' => true, 'data' => [$productsData]]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
