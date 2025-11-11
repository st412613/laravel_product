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
        // Create a user
        $owner = User::factory()->create();

        // Create a product owned by this user
        $product = Product::factory()->create(['user_id' => $owner->id]);

        // Create a price for that product
        $price = Price::factory()->create(['product_id' => $product->id]);

        // Acting as the owner
        Sanctum::actingAs($owner);

        // Owner can view the price
        $response = $this->get(route('prices.show', $price));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'product_id',
                'currency_id',
                'amount',
            ],
        ]);

        // Acting as another user (non-owner)
        $nonOwner = User::factory()->create();
        Sanctum::actingAs($nonOwner);

        // Non-owner cannot view the price
        $response = $this->get(route('prices.show', $price));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to view this price.'
        ]);
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
        $price = Price::factory()->create();
        Sanctum::actingAs($price->product->user);

        $amount = fake()->randomFloat(2, 0.01, 99999999.99);

        $response = $this->put(route('prices.update', $price), [
            'amount' => $amount,
        ]);

        $price->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($amount, $price->amount);
        $this->assertEquals($price->product_id, $price->product_id);
        $this->assertEquals($price->currency_id, $price->currency_id); 
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        // Owner scenario
        $owner = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);
        $price = Price::factory()->create(['product_id' => $product->id]);

        Sanctum::actingAs($owner);

        $response = $this->delete(route('prices.destroy', $price));
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Price deleted successfully.'
        ]);
        $this->assertModelMissing($price);

        // Non-owner scenario
        $anotherProduct = Product::factory()->create(['user_id' => $owner->id]);
        $price2 = Price::factory()->create(['product_id' => $anotherProduct->id]);
        $nonOwner = User::factory()->create();
        Sanctum::actingAs($nonOwner);

        $response = $this->delete(route('prices.destroy', $price2));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to delete this price.'
        ]);
        $this->assertModelExists($price2);
    }

}
