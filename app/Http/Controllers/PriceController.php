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

        // return new PriceCollection($prices);
    }

    public function store(PriceStoreRequest $request): PriceResource
    {
        $user = $request->user();
        $product = Product::where('id', $request->product_id)
        ->where('user_id', $user->id)
        ->first();

        $currency = Currency::where('id', $request->currency_id)
        ->where('user_id', $user->id)
        ->first();

    if (! $product || ! $currency) {
        abort(403, 'Unauthorized action.');
    }

        $price = Price::create($request->validated());

        return new PriceResource($price);$price = Price::create($request->validated());

        // return new PriceResource($price);
    }

    public function show(Request $request, Price $price): PriceResource
    {
        return new PriceResource($price);
    }

    public function update(PriceUpdateRequest $request, Price $price): PriceResource
    {
        $price->update($request->validated());

        return new PriceResource($price);
    }

    public function destroy(Request $request, Price $price): Response
    {
        $price->delete();

        return response()->noContent();
    }
}
