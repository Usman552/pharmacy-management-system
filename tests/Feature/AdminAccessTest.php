<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/prescriptions')->assertRedirect('/login');
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_customers_cannot_open_any_admin_area(): void
    {
        $this->actingAs($this->makeUser('customer'));

        foreach (['/admin', '/admin/products', '/admin/orders', '/admin/inventory', '/admin/coupons', '/admin/users', '/admin/prescriptions', '/admin/prescriptions/create', '/admin/messages'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/inventory/1/adjust', ['mode' => 'add', 'qty' => 1])->assertForbidden();
    }

    public function test_pharmacist_reaches_prescriptions_only(): void
    {
        $this->actingAs($this->makeUser('pharmacist'));

        $this->get('/admin/prescriptions')->assertOk();
        $this->get('/admin/prescriptions/create')->assertOk();
        $this->get('/admin')->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_deactivated_staff_lose_access(): void
    {
        $this->actingAs($this->makeUser('admin', ['is_active' => false]));
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $p = $this->makeProduct();

        foreach (['/admin', '/admin/products', '/admin/products/create', "/admin/products/{$p->id}/edit", '/admin/categories', '/admin/inventory', "/admin/inventory/{$p->id}/history",
                  '/admin/coupons', '/admin/orders', '/admin/payments', '/admin/users', '/admin/users/admins', '/admin/prescriptions', '/admin/messages', '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    // ── Orders ───────────────────────────────────────────────────────

    private function order(string $status = 'pending', ?int $userId = null): Order
    {
        $p = $this->makeProduct(['stock' => 8]);
        $o = Order::create([
            'order_number' => 'CD-T-' . random_int(1000, 9999), 'user_id' => $userId, 'customer_name' => 'N', 'customer_email' => 'n@x.com',
            'customer_phone' => '0300', 'shipping_address' => 'a', 'shipping_city' => 'c', 'subtotal' => 200, 'total_amount' => 350,
            'shipping_charge' => 150, 'status' => $status, 'payment_status' => 'pending', 'payment_method' => 'cod',
        ]);
        OrderItem::create(['order_id' => $o->id, 'product_id' => $p->id, 'product_name' => $p->name, 'quantity' => 2, 'unit_price' => 100, 'total_price' => 200]);
        return $o;
    }

    public function test_order_status_cannot_skip_steps(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $o = $this->order('pending');

        $this->post("/admin/orders/{$o->id}/status", ['status' => 'delivered'])->assertSessionHas('error');
        $this->assertSame('pending', $o->fresh()->status);

        $this->post("/admin/orders/{$o->id}/status", ['status' => 'processing'])->assertSessionHas('success');
        $this->assertSame('processing', $o->fresh()->status);
    }

    public function test_cancelling_returns_stock(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $o = $this->order('pending');
        $productId = $o->items->first()->product_id;

        $this->post("/admin/orders/{$o->id}/status", ['status' => 'cancelled'])->assertSessionHas('success');

        $this->assertSame(10, Product::find($productId)->stock);   // 8 + 2
        $this->assertSame('cancellation', StockMovement::where('product_id', $productId)->value('reason'));

        // A closed order cannot be reopened.
        $this->post("/admin/orders/{$o->id}/status", ['status' => 'processing'])->assertSessionHas('error');
    }

    public function test_cod_delivery_marks_payment_paid(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $o = $this->order('shipped');
        $o->payment()->create(['amount' => 350, 'method' => 'cod', 'status' => 'pending']);

        $this->post("/admin/orders/{$o->id}/status", ['status' => 'delivered']);

        $this->assertSame('paid', $o->fresh()->payment_status);
        $this->assertSame('completed', $o->payment->fresh()->status);
    }

    public function test_only_cancelled_orders_can_be_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $o = $this->order('pending');

        $this->delete("/admin/orders/{$o->id}")->assertSessionHas('error');
        $this->assertNotNull(Order::find($o->id));
    }

    public function test_customers_only_see_their_own_orders(): void
    {
        $mine = $this->makeUser();
        $other = $this->makeUser();
        $o = $this->order('pending', $other->id);

        $this->actingAs($mine)->get('/account/orders/' . $o->order_number)->assertNotFound();
        $this->actingAs($other)->get('/account/orders/' . $o->order_number)->assertOk();
    }

    // ── Catalogue management ─────────────────────────────────────────

    public function test_product_with_orders_is_deactivated_not_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $o = $this->order();
        $productId = $o->items->first()->product_id;

        $this->delete("/admin/products/{$productId}")->assertSessionHas('success');

        $this->assertTrue(Product::whereKey($productId)->exists());
        $this->assertFalse(Product::find($productId)->is_active);
    }

    public function test_category_with_products_cannot_be_deleted_without_reassignment(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $p = $this->makeProduct();
        $cat = $p->category;

        $this->delete("/admin/categories/{$cat->id}")->assertSessionHas('error');
        $this->assertTrue(\App\Models\Category::whereKey($cat->id)->exists());

        $other = \App\Models\Category::create(['name' => 'Other', 'slug' => 'other']);
        $this->delete("/admin/categories/{$cat->id}", ['move_to' => $other->id])->assertSessionHas('success');
        $this->assertSame($other->id, $p->fresh()->category_id);
    }

    public function test_inventory_adjustment_is_logged_and_cannot_go_negative(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $p = $this->makeProduct(['stock' => 5]);

        $this->post("/admin/inventory/{$p->id}/adjust", ['mode' => 'add', 'qty' => 10, 'note' => 'Delivery'])->assertSessionHas('success');
        $this->assertSame(15, $p->fresh()->stock);

        $this->post("/admin/inventory/{$p->id}/adjust", ['mode' => 'remove', 'qty' => 99])->assertSessionHas('error');
        $this->assertSame(15, $p->fresh()->stock);

        $this->assertSame(1, StockMovement::where('product_id', $p->id)->count());
    }

    public function test_product_form_ignores_unlisted_fields(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $p = $this->makeProduct(['rating' => 0]);

        $this->put("/admin/products/{$p->id}", [
            'name' => 'Renamed', 'category_id' => $p->category_id, 'price' => 120, 'stock' => $p->stock,
            'rating' => 5, 'reviews_count' => 999, 'slug' => 'hacked',
        ])->assertRedirect();

        $fresh = $p->fresh();
        $this->assertSame('Renamed', $fresh->name);
        $this->assertEquals(0, $fresh->rating);
        $this->assertSame(0, (int) $fresh->reviews_count);
        $this->assertNotSame('hacked', $fresh->slug);
    }

    public function test_sale_price_must_be_lower_than_price(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $p = $this->makeProduct();

        $this->put("/admin/products/{$p->id}", ['name' => 'X', 'category_id' => $p->category_id, 'price' => 100, 'sale_price' => 150, 'stock' => 1])
            ->assertSessionHasErrors('sale_price');
    }

    public function test_dashboard_numbers_come_from_the_database(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $paid = $this->order('delivered');
        $paid->update(['payment_status' => 'paid', 'total_amount' => 4321]);
        $cancelled = $this->order('cancelled');
        $cancelled->update(['payment_status' => 'paid', 'total_amount' => 9999]);

        $response = $this->get('/admin')->assertOk();
        // Revenue counts only paid, non-reversed orders: the cancelled Rs. 9,999 order is excluded.
        $this->assertEquals(4321, $response->viewData('stats')['revenue']);
        $this->assertSame(2, $response->viewData('stats')['total_orders']);
    }
}
