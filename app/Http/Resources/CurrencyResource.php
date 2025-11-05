<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'prices' => PriceCollection::make($this->whenLoaded('prices')),
        ];
    }
}
