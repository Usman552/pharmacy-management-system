<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountSignupTest extends TestCase
{
    use RefreshDatabase;

    private function details(array $o = []): array
    {
        return $o + [
            'customer_name' => 'Sara Ali', 'customer_email' => 'sara@example.com', 'customer_phone' => '0300 1234567',
            'shipping_address' => 'House 5, Street 9', 'shipping_city' => 'Multan', 'payment_method' => 'cod',
        ];
    }

    private function cartWithItem(): void
    {
        $p = $this->makeProduct(['price' => 100, 'stock' => 5]);
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();
    }

    // ── Optional account at checkout ────────────────────────────────

    public function test_guest_checkout_without_ticking_creates_no_account(): void
    {
        $this->cartWithItem();
        $this->post('/checkout/place', $this->details())->assertRedirect();

        $this->assertSame(0, User::count());
        $this->assertNull(Order::first()->user_id);
        $this->assertGuest();
    }

    public function test_guest_can_create_an_account_while_checking_out(): void
    {
        $this->cartWithItem();
        $this->post('/checkout/place', $this->details(['create_account' => 1, 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1']))
            ->assertRedirect();

        $user = User::where('email', 'sara@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertTrue(Hash::check('secret-pass-1', $user->password));
        $this->assertNotSame('secret-pass-1', $user->password);
        $this->assertNull($user->email_verified_at, 'Email was not verified, so it must not be marked verified');
        $this->assertSame($user->id, Order::first()->user_id);
        $this->assertAuthenticatedAs($user);

        // They can immediately see the order in their account.
        $this->get('/account/orders/' . Order::first()->order_number)->assertOk();
    }

    public function test_account_needs_a_valid_password_and_unused_email(): void
    {
        $this->cartWithItem();

        $this->post('/checkout/place', $this->details(['create_account' => 1, 'password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');
        $this->post('/checkout/place', $this->details(['create_account' => 1, 'password' => 'long-enough-1', 'password_confirmation' => 'different']))
            ->assertSessionHasErrors('password');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, User::count());

        User::create(['name' => 'Existing', 'email' => 'sara@example.com', 'password' => 'whatever123', 'role' => 'customer', 'is_active' => true]);
        $this->post('/checkout/place', $this->details(['create_account' => 1, 'password' => 'long-enough-1', 'password_confirmation' => 'long-enough-1']))
            ->assertSessionHasErrors('customer_email');
        $this->assertSame(0, Order::count());
    }

    public function test_failed_order_does_not_leave_a_half_created_account(): void
    {
        $this->cartWithItem();
        \App\Models\Product::query()->update(['stock' => 0]);   // goes out of stock before checkout

        $this->post('/checkout/place', $this->details(['create_account' => 1, 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1']))
            ->assertRedirect('/cart');

        $this->assertSame(0, User::count());
        $this->assertSame(0, Order::count());
        $this->assertGuest();
    }

    public function test_logged_in_customers_are_not_asked_to_create_an_account(): void
    {
        $this->actingAs($this->makeUser());
        $this->cartWithItem();
        $this->get('/checkout')->assertOk()->assertDontSee('Create an account for faster checkout');
    }

    // ── Google sign-in ───────────────────────────────────────────────

    private function enableGoogle(): void
    {
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
    }

    private function fakeGoogle(array $profile): void
    {
        Http::fake([
            'oauth2.googleapis.com/token'               => Http::response(['access_token' => 'tok']),
            'openidconnect.googleapis.com/v1/userinfo'  => Http::response($profile),
        ]);
    }

    private function startFlow(string $next = ''): string
    {
        $this->get('/auth/google' . ($next ? "?next=$next" : ''))->assertRedirectContains('accounts.google.com');
        return session('google_state');
    }

    public function test_google_is_hidden_and_disabled_until_configured(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Continue with Google');
        $this->get('/auth/google')->assertNotFound();

        $this->enableGoogle();
        $this->get('/login')->assertOk()->assertSee('Continue with Google');
        $this->get('/register')->assertOk()->assertSee('Continue with Google');
    }

    public function test_google_creates_a_new_customer_and_signs_them_in(): void
    {
        $this->enableGoogle();
        $this->fakeGoogle(['sub' => 'g-123', 'email' => 'New.Person@Gmail.com', 'email_verified' => true, 'name' => 'New Person']);

        $state = $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/account');

        $user = User::where('email', 'new.person@gmail.com')->firstOrFail();
        $this->assertSame('g-123', $user->google_id);
        $this->assertSame('customer', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_links_an_existing_customer_with_the_same_verified_email(): void
    {
        $this->enableGoogle();
        $existing = $this->makeUser('customer', ['email' => 'old@gmail.com']);
        $this->fakeGoogle(['sub' => 'g-9', 'email' => 'old@gmail.com', 'email_verified' => true, 'name' => 'Old']);

        $state = $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=' . $state);

        $this->assertSame(1, User::where('email', 'old@gmail.com')->count());
        $this->assertSame('g-9', $existing->fresh()->google_id);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_google_never_signs_into_staff_accounts(): void
    {
        $this->enableGoogle();
        $admin = $this->makeUser('admin', ['email' => 'boss@gmail.com']);
        $this->fakeGoogle(['sub' => 'g-1', 'email' => 'boss@gmail.com', 'email_verified' => true, 'name' => 'Boss']);

        $state = $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/login')->assertSessionHas('error');

        $this->assertGuest();
        $this->assertNull($admin->fresh()->google_id);
    }

    public function test_google_rejects_unverified_email_bad_state_and_inactive_accounts(): void
    {
        $this->enableGoogle();

        // Unverified email
        $this->fakeGoogle(['sub' => 'g-2', 'email' => 'x@gmail.com', 'email_verified' => false, 'name' => 'X']);
        $state = $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame(0, User::count());

        // Wrong state (CSRF protection)
        $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=forged')->assertRedirect('/login');
        $this->assertGuest();

        // Deactivated customer
        $this->makeUser('customer', ['email' => 'gone@gmail.com', 'is_active' => false]);
        $this->fakeGoogle(['sub' => 'g-3', 'email' => 'gone@gmail.com', 'email_verified' => true, 'name' => 'Gone']);
        $state = $this->startFlow();
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_google_from_checkout_returns_to_checkout_and_keeps_the_cart(): void
    {
        $this->enableGoogle();
        $this->cartWithItem();
        $this->fakeGoogle(['sub' => 'g-5', 'email' => 'buyer@gmail.com', 'email_verified' => true, 'name' => 'Buyer']);

        $state = $this->startFlow('checkout');
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/checkout');

        $this->get('/checkout')->assertOk();   // cart survived the login
    }

    public function test_open_redirects_are_not_possible_through_next(): void
    {
        $this->enableGoogle();
        $this->fakeGoogle(['sub' => 'g-6', 'email' => 'z@gmail.com', 'email_verified' => true, 'name' => 'Z']);

        $state = $this->startFlow('https://evil.example');
        $this->get('/auth/google/callback?code=abc&state=' . $state)->assertRedirect('/account');
    }
}
