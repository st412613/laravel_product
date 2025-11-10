<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyStoreRequest;
use App\Http\Requests\CurrencyUpdateRequest;
use App\Http\Resources\CurrencyCollection;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class CurrencyController extends Controller
{
    public function index(Request $request): CurrencyCollection
    {
         $user = $request->user();
         $currencies = Currency::where('user_id', $user->id)->get();
         return new CurrencyCollection($currencies);
    }

    public function store(CurrencyStoreRequest $request): CurrencyResource
    {   
         $data = $request->validated();
        // $currency = Currency::create($request->validated());
         $data['user_id'] = $request->user()->id;

         $currency = Currency::create($data);

        return new CurrencyResource($currency);
    }

    public function show(Request $request, Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }

    public function update(CurrencyUpdateRequest $request, Currency $currency): CurrencyResource
    {   
        //  $this->authorize('update', $currency);
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $currency->update($data);

        return new CurrencyResource($currency);
    }
    
    public function destroy(Request $request, Currency $currency): Response
    {
        $currency->delete();

        return response()->noContent();
    }
}
