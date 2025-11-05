<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceStoreRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Resources\PriceCollection;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PriceController extends Controller
{
    public function index(Request $request): PriceCollection
    {
        $prices = Price::all();

        return new PriceCollection($prices);
    }

    public function store(PriceStoreRequest $request): PriceResource
    {
        $price = Price::create($request->validated());

        return new PriceResource($price);
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
