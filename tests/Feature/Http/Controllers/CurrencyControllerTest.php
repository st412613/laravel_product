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
        $code = fake()->word();
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
        $currency = Currency::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('currencies.show', $currency));

        $response->assertOk();
        $response->assertJsonStructure([]);
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
        $currency = Currency::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $code = fake()->word();
        $name = fake()->name();
        $user = User::factory()->create();

        $response = $this->put(route('currencies.update', $currency), [
            'code' => $code,
            'name' => $name,
            'user_id' => $user->id,
        ]);

        $currency->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($code, $currency->code);
        $this->assertEquals($name, $currency->name);
        $this->assertEquals($user->id, $currency->user_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $currency = Currency::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->delete(route('currencies.destroy', $currency));

        $response->assertNoContent();

        $this->assertModelMissing($currency);
    }
}
