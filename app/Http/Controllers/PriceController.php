<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceStoreRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Resources\PriceCollection;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

class PriceController extends Controller
{
    public function index(Request $request): PriceCollection
    {
         $userId = $request->user()->id;

         $prices = Price::whereHas('product', function ($q) use ($userId) {
            $q->where('user_id', $userId);
         })
         ->whereHas('currency', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->get();

        return new PriceCollection($prices); $prices = Price::all();

    }

    public function store(PriceStoreRequest $request): PriceResource| JsonResponse
    {
        $user = $request->user();

        // Check if product belongs to logged-in user
        $product = Product::where('id', $request->product_id)
            ->where('user_id', $user->id)
            ->first();

        // Check if currency belongs to logged-in user
        $currency = Currency::where('id', $request->currency_id)
            ->where('user_id', $user->id)
            ->first();

        //If product or currency not owned by this user, stop
        if (! $product || ! $currency) {
            return response()->json([
                'message' => 'You are not authorized to create a price for this product or currency.'
            ], 403);
        }

        // Create the price
        $price = Price::create([
            'product_id' => $request->product_id,
            'currency_id' => $request->currency_id,
            'amount' => $request->amount,
        ]);

        return new PriceResource($price);
    }

    public function show(Request $request, Price $price): PriceResource|JsonResponse
    {
        // Check if logged-in user owns the product associated with this price
        if ($price->product->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to view this price.'
            ], 403);
        }

        return new PriceResource($price);
    }

    public function update(PriceUpdateRequest $request, Price $price): PriceResource|JsonResponse
    {
        if ($price->product->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this price.'
            ], 403);
        }

        $price->update($request->validated());

        return new PriceResource($price);
    }

    public function destroy(Request $request, Price $price): JsonResponse
    {
        if ($price->product->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this price.'
            ], 403);
        }

        $price->delete();

        return response()->json([
            'message' => 'Price deleted successfully.'
        ], 200);
    }

}
