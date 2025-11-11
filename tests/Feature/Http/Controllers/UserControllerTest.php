<?php

// namespace Tests\Feature\Http\Controllers;

// use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
// use JMac\Testing\Traits\AdditionalAssertions;
// use PHPUnit\Framework\Attributes\Test;
// use Tests\TestCase;

/**
 * @see \App\Http\Controllers\UserController
 */
// final class UserControllerTest extends TestCase
// {
//     use AdditionalAssertions, RefreshDatabase, WithFaker;

//     #[Test]
//     public function index_behaves_as_expected(): void
//     {
//         $users = User::factory()->count(3)->create();

//         $response = $this->get(route('users.index'));

//         $response->assertOk();
//         $response->assertJsonStructure([]);
//     }


//     #[Test]
//     public function store_uses_form_request_validation(): void
//     {
//         $this->assertActionUsesFormRequest(
//             \App\Http\Controllers\UserController::class,
//             'store',
//             \App\Http\Requests\UserStoreRequest::class
//         );
//     }

//     #[Test]
//     public function store_saves(): void
//     {
//         $name = fake()->name();
//         $email = fake()->safeEmail();
//         $password = fake()->password();

//         $response = $this->post(route('users.store'), [
//             'name' => $name,
//             'email' => $email,
//             'password' => $password,
//         ]);

//         $users = User::query()
//             ->where('name', $name)
//             ->where('email', $email)
//             ->where('password', $password)
//             ->get();
//         $this->assertCount(1, $users);
//         $user = $users->first();

//         $response->assertCreated();
//         $response->assertJsonStructure([]);
//     }


//     #[Test]
//     public function show_behaves_as_expected(): void
//     {
//         $user = User::factory()->create();

//         $response = $this->get(route('users.show', $user));

//         $response->assertOk();
//         $response->assertJsonStructure([]);
//     }


//     #[Test]
//     public function update_uses_form_request_validation(): void
//     {
//         $this->assertActionUsesFormRequest(
//             \App\Http\Controllers\UserController::class,
//             'update',
//             \App\Http\Requests\UserUpdateRequest::class
//         );
//     }

//     #[Test]
//     public function update_behaves_as_expected(): void
//     {
//         $user = User::factory()->create();
//         $name = fake()->name();
//         $email = fake()->safeEmail();
//         $password = fake()->password();

//         $response = $this->put(route('users.update', $user), [
//             'name' => $name,
//             'email' => $email,
//             'password' => $password,
//         ]);

//         $user->refresh();

//         $response->assertOk();
//         $response->assertJsonStructure([]);

//         $this->assertEquals($name, $user->name);
//         $this->assertEquals($email, $user->email);
//         $this->assertEquals($password, $user->password);
//     }


//     #[Test]
//     public function destroy_deletes_and_responds_with(): void
//     {
//         $user = User::factory()->create();

//         $response = $this->delete(route('users.destroy', $user));

//         $response->assertNoContent();

//         $this->assertModelMissing($user);
//     }
// }


namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

/**
 * @see \App\Http\Controllers\UserController
 */
final class UserControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function show_behaves_as_expected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Since we only allow logged-in user to see their own data
        $response = $this->getJson('/api/users'); // Changed to /api/user

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
            ],
        ]);
    }

    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\UserController::class,
            'update',
            \App\Http\Requests\UserUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $name = fake()->name();
        $email = fake()->safeEmail();
        $password = fake()->password();

        // Update without passing ID — controller uses logged-in user
        $response = $this->putJson('/api/users', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user->refresh();

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
            ],
        ]);



        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Delete logged-in user
        $response = $this->deleteJson('/api/users');

        $response->assertNoContent();
        $this->assertModelMissing($user);
    }
}
