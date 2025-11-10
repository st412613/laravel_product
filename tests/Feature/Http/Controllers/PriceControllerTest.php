<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

/**
 * @see \App\Http\Controllers\PriceController
 */
final class PriceControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $prices = Price::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('prices.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PriceController::class,
            'store',
            \App\Http\Requests\PriceStoreRequest::class
        );
    }

    #[Test]
     public function test_store_saves(): void
    {

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create(['user_id' => $user->id]);
        $currency = Currency::factory()->create(['user_id' => $user->id]);

        $amount = fake()->randomFloat(2, 0.01, 99999999.99);


        $response = $this->postJson(route('prices.store'), [
            'product_id' => $product->id,
            'currency_id' => $currency->id,
            'amount' => $amount,
        ]);

        $prices = Price::query()
            ->where('product_id', $product->id)
            ->where('currency_id', $currency->id)
            ->where('amount', $amount)
            ->get();

        $this->assertCount(1, $prices);
        $price = $prices->first();

        //  Response validation
        $response->assertStatus(201); // or assertCreated()
        $response->assertJsonStructure([]);
}



    #[Test]
    public function show_behaves_as_expected(): void
    {
        $price = Price::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('prices.show', $price));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PriceController::class,
            'update',
            \App\Http\Requests\PriceUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $currency = Currency::factory()->create();
        $product = Product::factory()->create();
        $price = Price::factory()->create();
        Sanctum::actingAs(User::factory()->create());
        $amount = fake()->randomFloat(2, 0.01, 99999999.99);
        
        $response = $this->put(route('prices.update', $price), [
            'product_id' => $product->id,
            'currency_id' => $currency->id,
            'amount' => $amount,
        ]);
        
        $price->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($product->id, $price->product_id);
        $this->assertEquals($currency->id, $price->currency_id);
        $this->assertEquals($amount, $price->amount);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $price = Price::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->delete(route('prices.destroy', $price));

        $response->assertNoContent();

        $this->assertModelMissing($price);
    }
}
