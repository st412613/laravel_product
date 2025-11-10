<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request): ProductCollection
    {
        $user = Auth::user();
        $products = Product::where('user_id', $user->id)->get();
        return new ProductCollection($products);
    }

    public function store(ProductStoreRequest $request): ProductResource
    {   
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $product = Product::create($data);
        return new ProductResource($product);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(ProductUpdateRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product): Response
    {
        $product->delete();
        return response()->noContent();
    }
    
}
