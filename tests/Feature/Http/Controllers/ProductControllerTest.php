<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

/**
 * @see \App\Http\Controllers\ProductController
 */
final class ProductControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $products = Product::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create());



        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'store',
            \App\Http\Requests\ProductStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = fake()->name();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        

        $response = $this->post(route('products.store'), [
            'name' => $name,
            'user_id' => $user->id,
        ]);

        $products = Product::query()
            ->where('name', $name)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $products);
        $product = $products->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $product = Product::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'update',
            \App\Http\Requests\ProductUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $product = Product::factory()->create();
        $name = fake()->name();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->put(route('products.update', $product), [
            'name' => $name,
            'user_id' => $user->id,
        ]);

        $product->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $product->name);
        $this->assertEquals($user->id, $product->user_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $product = Product::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->delete(route('products.destroy', $product));

        $response->assertNoContent();

        $this->assertModelMissing($product);
    }

    #[Test]
    public function index_returns_only_authenticated_users_products(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myProducts = Product::factory()->count(2)->for($user)->create();
        Product::factory()->count(2)->for($otherUser)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(route('products.index'));

        $response->assertOk();
        $response->assertJsonCount(2, 'data'); // assuming a 'data' wrapper in your response
        foreach ($myProducts as $product) {
            $response->assertJsonFragment(['name' => $product->name]);
        }
    }

    #[Test]
    public function store_fails_validation_when_required_fields_missing(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(route('products.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
    

}
