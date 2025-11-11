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
        // Create a product owned by a specific user
        $owner = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);

        // Case 1: Owner can view the product
        Sanctum::actingAs($owner);
        $response = $this->get(route('products.show', $product));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'user_id',
                // add other fields if needed
            ]
        ]);

        // Case 2: Non-owner cannot view the product
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);
        $response = $this->get(route('products.show', $product));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to view this product.'
        ]);
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
        // Create a product owned by a specific user
        $owner = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);
        $newName = fake()->name();

        // Case 1: Owner can update
        Sanctum::actingAs($owner);
        $response = $this->put(route('products.update', $product), [
            'name' => $newName,
            // Do NOT send user_id, ownership cannot change
        ]);

        $product->refresh();

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'user_id',
                // Add other fields if needed
            ]
        ]);

        $this->assertEquals($newName, $product->name);
        $this->assertEquals($owner->id, $product->user_id);

        // Case 2: Non-owner cannot update
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);
        $response = $this->put(route('products.update', $product), [
            'name' => fake()->name(),
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to update this product.'
        ]);
    }

   #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        // Create a product owned by a specific user
        $owner = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);

        // Case 1: Owner can delete
        Sanctum::actingAs($owner);
        $response = $this->delete(route('products.destroy', $product));
        $response->assertStatus(200); // 200 because controller returns JSON
        $response->assertJson([
            'message' => 'Product deleted successfully.'
        ]);
        $this->assertModelMissing($product);

        // Case 2: Non-owner cannot delete
        $anotherProduct = Product::factory()->create(['user_id' => $owner->id]);
        $nonOwner = User::factory()->create();
        Sanctum::actingAs($nonOwner);

        $response = $this->delete(route('products.destroy', $anotherProduct));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to delete this product.'
        ]);

        // The product should still exist
        $this->assertModelExists($anotherProduct);
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
