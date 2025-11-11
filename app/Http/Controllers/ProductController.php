<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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

    public function show(Request $request, Product $product): ProductResource|JsonResponse
    {   
        if ($product->user_id !== $request->user()->id) {
             return response()->json([
            'message' => 'You are not authorized to view this product.'
        ], 403);
        }

        return new ProductResource($product);
    }

    public function update(ProductUpdateRequest $request, Product $product): ProductResource|JsonResponse
    {
    
        if ($product->user_id !== $request->user()->id) {
             return response()->json([
            'message' => 'You are not authorized to update this product.'
        ], 403);
         
        }
        $product->update($request->validated());

        return new ProductResource($product);
        
    }

    public function destroy(Request $request, Product $product): Response|JsonResponse
    {
        if ($product->user_id !== $request->user()->id) { // no parentheses
            return response()->json([
                'message' => 'You are not authorized to delete this product.'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 200);
    }

}