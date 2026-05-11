<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_custom_inertia_screen(): void
    {
        $this->seed();

        $response = $this->get(route('login'));

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('Auth/Login')
                ->has('demoAccounts', 2)
                ->where('demoAccounts.0.email', 'owner@kantordigital.test')
            );
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        $this->seed();

        $response = $this->post(route('login.store'), [
            'email' => 'owner@kantordigital.test',
            'password' => 'password',
            'remember' => true,
        ]);

        $user = User::query()->where('email', 'owner@kantordigital.test')->firstOrFail();

        $response->assertRedirect(route('app.home'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_google_login_route_is_not_available_anymore(): void
    {
        $this->get('/auth/google/redirect')->assertNotFound();
    }
}
