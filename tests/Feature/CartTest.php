<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_uses_database_prices_not_browser_values(): void
    {
        $p = $this->makeProduct(['price' => 100, 'stock' => 5]);

        // A tampered request that also sends a price must not change anything.
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 2, 'price' => 1, 'name' => 'x'])->assertOk();

        $this->get('/cart')->assertOk()->assertSee('Rs. 200');
        $this->assertSame([$p->id => ['product_id' => $p->id, 'quantity' => 2]], session('cart'));
    }

    public function test_sale_price_is_applied_from_the_database(): void
    {
        $p = $this->makeProduct(['price' => 100, 'sale_price' => 80]);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 1])->assertOk();
        $this->get('/cart')->assertSee('Rs. 80');
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $p = $this->makeProduct(['stock' => 2]);

        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 3])->assertStatus(422);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 2])->assertOk();
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 1])->assertStatus(422);
    }

    public function test_inactive_and_out_of_stock_products_are_refused(): void
    {
        $inactive = $this->makeProduct(['is_active' => false]);
        $empty = $this->makeProduct(['stock' => 0]);

        $this->postJson('/cart/add', ['product_id' => $inactive->id])->assertStatus(422);
        $this->postJson('/cart/add', ['product_id' => $empty->id])->assertStatus(422);
    }

    public function test_quantity_update_is_validated_against_stock(): void
    {
        $p = $this->makeProduct(['stock' => 3]);
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();

        $this->postJson('/cart/update', ['cart_id' => $p->id, 'quantity' => 9])->assertStatus(422);
        $this->postJson('/cart/update', ['cart_id' => $p->id, 'quantity' => 3])->assertOk();
    }

    public function test_free_delivery_threshold(): void
    {
        $cheap = $this->makeProduct(['price' => 100]);
        $this->postJson('/cart/add', ['product_id' => $cheap->id])->assertOk();
        $this->get('/cart')->assertSee('Rs. 150');   // flat delivery

        $big = $this->makeProduct(['price' => 1200]);
        $this->postJson('/cart/add', ['product_id' => $big->id])->assertOk();
        $this->get('/cart')->assertSee('Free');
    }

    public function test_prescription_medicine_cannot_be_added_by_guest_or_without_prescription(): void
    {
        $rx = $this->makeProduct(['requires_prescription' => true]);

        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);

        $this->actingAs($this->makeUser());
        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);
    }

    public function test_prescription_medicine_is_limited_to_the_verified_quantity(): void
    {
        $rx = $this->makeProduct(['requires_prescription' => true, 'stock' => 10]);
        $customer = $this->makeUser();
        $this->verifiedPrescription($customer, $rx, 2);

        $this->actingAs($customer);
        $this->postJson('/cart/add', ['product_id' => $rx->id, 'quantity' => 2])->assertOk();
        $this->postJson('/cart/add', ['product_id' => $rx->id, 'quantity' => 1])->assertStatus(422);
    }

    public function test_someone_elses_prescription_does_not_unlock_a_medicine(): void
    {
        $rx = $this->makeProduct(['requires_prescription' => true]);
        $owner = $this->makeUser();
        $this->verifiedPrescription($owner, $rx, 2);

        $this->actingAs($this->makeUser());
        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);
    }

    public function test_pending_or_expired_prescriptions_do_not_unlock_a_medicine(): void
    {
        $rx = $this->makeProduct(['requires_prescription' => true]);
        $customer = $this->makeUser();
        $prescription = $this->verifiedPrescription($customer, $rx, 2);

        $this->actingAs($customer);
        $prescription->update(['status' => 'pending']);
        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);

        $prescription->update(['status' => 'verified', 'expires_at' => now()->subDay()]);
        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);
    }

    public function test_coupon_is_validated_on_the_server(): void
    {
        $p = $this->makeProduct(['price' => 1000]);
        \App\Models\Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        \App\Models\Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 50, 'is_active' => true, 'expires_at' => now()->subDay()]);

        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();

        $this->postJson('/cart/coupon', ['code' => 'OLD'])->assertStatus(422);
        $this->postJson('/cart/coupon', ['code' => 'NOPE'])->assertStatus(422);
        $this->postJson('/cart/coupon', ['code' => 'save10'])->assertOk();
        // 1000 - 10% = 900, under the free-delivery threshold, so Rs. 150 delivery: total Rs. 1,050.
        $this->get('/cart')->assertSee('− Rs. 100')->assertSee('Rs. 1,050');
    }
}
