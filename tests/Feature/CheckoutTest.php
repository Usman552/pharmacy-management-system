<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\PrescriptionItem;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function details(array $o = []): array
    {
        return $o + [
            'customer_name' => 'Ayesha Khan', 'customer_email' => 'a@example.com', 'customer_phone' => '0300 1234567',
            'shipping_address' => 'House 1, Street 2', 'shipping_city' => 'Multan', 'payment_method' => 'cod',
        ];
    }

    public function test_empty_cart_cannot_check_out(): void
    {
        $this->get('/checkout')->assertRedirect('/cart');
        $this->post('/checkout/place', $this->details())->assertRedirect('/cart');
        $this->assertSame(0, Order::count());
    }

    public function test_order_is_priced_from_the_database_and_stock_is_reduced_and_logged(): void
    {
        $p = $this->makeProduct(['price' => 100, 'stock' => 5]);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 2])->assertOk();

        // Tampered extra totals in the request are ignored.
        $this->post('/checkout/place', $this->details(['total_amount' => 1, 'subtotal' => 1, 'shipping_charge' => 0]))
            ->assertRedirect();

        $order = Order::with('items', 'payment')->firstOrFail();
        $this->assertEquals(200, $order->subtotal);
        $this->assertEquals(150, $order->shipping_charge);   // under the free-delivery threshold
        $this->assertEquals(350, $order->total_amount);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);
        $this->assertSame(3, $p->fresh()->stock);

        $move = StockMovement::where('product_id', $p->id)->firstOrFail();
        $this->assertSame(-2, $move->change);
        $this->assertSame(3, $move->balance);
        $this->assertSame($order->order_number, $move->reference);

        $this->assertSame([], session('cart', []));
    }

    public function test_online_payment_is_not_accepted_without_a_gateway(): void
    {
        $p = $this->makeProduct();
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();

        $this->post('/checkout/place', $this->details(['payment_method' => 'online']))->assertSessionHasErrors('payment_method');
        $this->assertSame(0, Order::count());
    }

    public function test_checkout_validates_required_fields(): void
    {
        $p = $this->makeProduct();
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();

        $this->post('/checkout/place', [])->assertSessionHasErrors(['customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'shipping_city', 'payment_method']);
    }

    public function test_cannot_oversell_when_stock_dropped_after_adding_to_cart(): void
    {
        $p = $this->makeProduct(['stock' => 5]);
        $this->postJson('/cart/add', ['product_id' => $p->id, 'quantity' => 4])->assertOk();

        $p->update(['stock' => 1]);   // someone else bought it meanwhile

        $this->post('/checkout/place', $this->details())->assertRedirect('/cart')->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $p->fresh()->stock);
    }

    public function test_coupon_discount_is_recalculated_and_usage_counted(): void
    {
        $p = $this->makeProduct(['price' => 1000]);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true, 'usage_limit' => 1]);

        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();
        $this->postJson('/cart/coupon', ['code' => 'SAVE10'])->assertOk();
        $this->post('/checkout/place', $this->details(['discount' => 999]))->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertEquals(100, $order->discount);
        // 1000 - 100 = 900, which is under the Rs. 999 free-delivery threshold, so Rs. 150 delivery applies.
        $this->assertEquals(150, $order->shipping_charge);
        $this->assertEquals(1050, $order->total_amount);
        $this->assertSame(1, Coupon::where('code', 'SAVE10')->value('used_count'));
    }

    public function test_exhausted_coupon_is_ignored(): void
    {
        $p = $this->makeProduct(['price' => 1000]);
        Coupon::create(['code' => 'ONCE', 'type' => 'fixed', 'value' => 100, 'is_active' => true, 'usage_limit' => 1, 'used_count' => 1]);

        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();
        $this->postJson('/cart/coupon', ['code' => 'ONCE'])->assertStatus(422);
    }

    public function test_prescription_allowance_is_consumed_by_an_order(): void
    {
        $rx = $this->makeProduct(['requires_prescription' => true, 'stock' => 10, 'price' => 100]);
        $customer = $this->makeUser();
        $prescription = $this->verifiedPrescription($customer, $rx, 2);

        $this->actingAs($customer);
        $this->postJson('/cart/add', ['product_id' => $rx->id, 'quantity' => 2])->assertOk();
        $this->post('/checkout/place', $this->details())->assertRedirect();

        $this->assertSame(2, PrescriptionItem::first()->fulfilled_qty);
        $this->assertSame('fulfilled', $prescription->fresh()->status);

        // Nothing left: cannot buy it again on the same prescription.
        $this->postJson('/cart/add', ['product_id' => $rx->id])->assertStatus(422);
    }

    public function test_order_confirmation_is_private(): void
    {
        $p = $this->makeProduct();
        $this->postJson('/cart/add', ['product_id' => $p->id])->assertOk();
        $this->post('/checkout/place', $this->details());
        $order = Order::firstOrFail();

        $this->get('/order/success/' . $order->order_number)->assertOk();

        // A different browser session (and a stranger) cannot open it.
        $this->flushSession();
        $this->get('/order/success/' . $order->order_number)->assertNotFound();
    }
}
