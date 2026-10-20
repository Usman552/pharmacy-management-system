<?php

namespace Tests;

use App\Models\Category;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeUser(string $role = 'customer', array $o = []): User
    {
        static $n = 0;
        $n++;

        return User::create($o + [
            'name'      => ucfirst($role) . " $n",
            'email'     => "{$role}{$n}@test.local",
            'password'  => 'password123',
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    protected function makeProduct(array $o = []): Product
    {
        static $n = 0;
        $n++;
        $category = Category::first() ?? Category::create(['name' => 'Medicines', 'slug' => 'medicines']);

        return Product::create($o + [
            'category_id' => $category->id,
            'name'        => "Test Product $n",
            'slug'        => "test-product-$n",
            'price'       => 100,
            'stock'       => 10,
            'is_active'   => true,
        ]);
    }

    /** A prescription already verified for $customer covering $qty units of $product. */
    protected function verifiedPrescription(User $customer, Product $product, int $qty = 2): Prescription
    {
        $rx = Prescription::create([
            'reference' => Prescription::newReference(), 'customer_id' => $customer->id,
            'file_path' => 'prescriptions/test.jpg', 'original_name' => 'test.jpg', 'mime_type' => 'image/jpeg',
            'file_size' => 100, 'status' => 'verified', 'expires_at' => now()->addDays(30),
        ]);
        PrescriptionItem::create([
            'prescription_id' => $rx->id, 'written_name' => $product->name, 'product_id' => $product->id,
            'approved_qty' => $qty, 'is_verified' => true,
        ]);

        return $rx;
    }
}
