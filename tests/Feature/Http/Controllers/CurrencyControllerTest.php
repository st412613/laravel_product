<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;


/**
 * @see \App\Http\Controllers\CurrencyController
 */
final class CurrencyControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $currencies = Currency::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('currencies.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CurrencyController::class,
            'store',
            \App\Http\Requests\CurrencyStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $code =  fake()->regexify('[A-Za-z0-9]{3}');
        $name = fake()->name();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post(route('currencies.store'), [
            'code' => $code,
            'name' => $name,
            'user_id' => $user->id,
        ]);

        $currencies = Currency::query()
            ->where('code', $code)
            ->where('name', $name)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $currencies);
        $currency = $currencies->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


   #[Test]
    public function show_behaves_as_expected(): void
    {
        // Create a user and a currency belonging to that user
        $user = User::factory()->create();
        $currency = Currency::factory()->create(['user_id' => $user->id]);

        // Authenticate as the owner
        Sanctum::actingAs($user);

        $response = $this->get(route('currencies.show', $currency));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'code',
                'user_id',
                
            ],
        ]);

        // Test that the JSON contains the correct data
        $response->assertJson([
            'data' => [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
                'user_id' => $user->id,
            ],
        ]);

        // Case: another user cannot view this currency
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->get(route('currencies.show', $currency));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to view this currency.'
        ]);
    }



    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CurrencyController::class,
            'update',
            \App\Http\Requests\CurrencyUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        
        $code =  'USD';
        $name = "SHS";
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $currency = Currency::factory()->create(['user_id' => $user->id]);
        
        $response = $this->put(route('currencies.update', $currency), [
            'code' => $code,
            'name' => $name,
            // 'user_id' => $user->id,
        ]);
        
        $currency->refresh();
        
        $this->assertEquals($code, $currency->code);
        $this->assertEquals($name, $currency->name);
        $this->assertEquals($user->id, $currency->user_id);
        $response->assertOk();
        $response->assertJsonStructure([]);
    }


   #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        // Create a currency owned by a specific user
        $owner = User::factory()->create();
        $currency = Currency::factory()->create(['user_id' => $owner->id]);

        // Case 1: Owner can delete
        Sanctum::actingAs($owner);

        $response = $this->delete(route('currencies.destroy', $currency));
        $response->assertNoContent(); // 204
        $this->assertModelMissing($currency);

        // Case 2: Non-owner cannot delete
        $anotherCurrency = Currency::factory()->create(['user_id' => $owner->id]);
        $nonOwner = User::factory()->create();
        Sanctum::actingAs($nonOwner);

        $response = $this->delete(route('currencies.destroy', $anotherCurrency));
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'You are not authorized to delete this currency.'
        ]);

        // The currency should still exist
        $this->assertModelExists($anotherCurrency);
    }


    #[Test]
    public function store_fails_with_invalid_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(route('currencies.store'), [
            'code' => '', // required
            'name' => '', // required
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['code', 'name']);
    }

    #[Test]
    public function update_fails_with_invalid_data(): void
    {
        $currency = Currency::factory()->create();
        Sanctum::actingAs($currency->user);

        $response = $this->putJson(route('currencies.update', $currency), [
            'code' => 'TOOLONG', // max:3
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['code', 'name']);
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->getJson(route('currencies.index'));
        $response->assertUnauthorized();
    }

    #[Test]
    public function store_requires_authentication(): void
    {
        $response = $this->postJson(route('currencies.store'), [
            'code' => 'USD',
            'name' => 'Dollar',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function update_fails_if_user_does_not_own_currency(): void
    {
        $currency = Currency::factory()->create(); // belongs to user A
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->putJson(route('currencies.update', $currency), [
            'code' => 'EUR',
            'name' => 'Euro',
        ]);

        $response->assertForbidden();
    }
    
    #[Test]
public function user_cannot_update_currency_of_another_user(): void
{
    // Create two different users
    $owner = User::factory()->create(); // the one who owns the currency
    $otherUser = User::factory()->create(); // the one who will try to update it

    // Create a currency belonging to the first user
    $currency = Currency::factory()->create(['user_id' => $owner->id]);

    // Act as the second user
    Sanctum::actingAs($otherUser);

    // Attempt to update the other user's currency
    $response = $this->put(route('currencies.update', $currency), [
        'code' => 'EUR',
        'name' => 'Euro',
    ]);

    // Assert the response is forbidden (403)
    $response->assertStatus(403);

    // Ensure the currency was not modified
    $currency->refresh();
    $this->assertNotEquals('EUR', $currency->code);
    $this->assertNotEquals('Euro', $currency->name);
}


}
