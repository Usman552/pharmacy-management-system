<?php

namespace Tests\Feature;

use App\Models\Prescription;
use App\Models\PrescriptionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('prescriptions');
    }

    private function upload($customer, $file = null)
    {
        return $this->post('/admin/prescriptions', [
            'customer_id' => $customer->id,
            'file'        => $file ?? UploadedFile::fake()->image('rx.jpg', 600, 800),
            'doctor_name' => 'Dr. Test',
        ]);
    }

    public function test_staff_uploads_a_prescription_for_a_customer_and_it_is_private(): void
    {
        $customer = $this->makeUser();
        $this->actingAs($this->makeUser('pharmacist'));

        $this->upload($customer)->assertRedirect();

        $rx = Prescription::firstOrFail();
        $this->assertSame($customer->id, $rx->customer_id);
        $this->assertSame('pending', $rx->status);
        $this->assertTrue(Storage::disk('prescriptions')->exists($rx->file_path));
        // Stored on the private disk under a random name, never under public/.
        $this->assertStringStartsWith(date('Y/m') . '/', $rx->file_path);
        $this->assertStringNotContainsString('rx.jpg', $rx->file_path);
        $this->assertFileDoesNotExist(public_path('storage/' . $rx->file_path));
        $this->assertSame('uploaded', PrescriptionLog::first()->action);
    }

    public function test_only_images_and_pdfs_are_accepted(): void
    {
        $customer = $this->makeUser();
        $this->actingAs($this->makeUser('pharmacist'));

        $this->upload($customer, UploadedFile::fake()->create('notes.txt', 10, 'text/plain'))->assertSessionHasErrors('file');
        $this->upload($customer, UploadedFile::fake()->create('evil.php', 10, 'application/x-php'))->assertSessionHasErrors('file');
        $this->upload($customer, UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf'))->assertSessionHasErrors('file');
        $this->assertSame(0, Prescription::count());

        $this->upload($customer, UploadedFile::fake()->create('ok.pdf', 100, 'application/pdf'))->assertRedirect();
        $this->assertSame(1, Prescription::count());
    }

    public function test_customer_cannot_upload_or_review(): void
    {
        $customer = $this->makeUser();
        $this->actingAs($customer);

        $this->upload($customer)->assertForbidden();
        $this->assertSame(0, Prescription::count());
    }

    public function test_cannot_verify_with_unmatched_or_oversized_lines(): void
    {
        $customer = $this->makeUser();
        $product = $this->makeProduct(['requires_prescription' => true, 'stock' => 5]);
        $this->actingAs($this->makeUser('pharmacist'));
        $this->upload($customer);
        $rx = Prescription::firstOrFail();

        // No lines at all
        $this->post("/admin/prescriptions/{$rx->id}/verify", ['confirm' => 1])->assertSessionHas('error');

        $this->post("/admin/prescriptions/{$rx->id}/items", ['written_name' => 'Amoxil', 'written_strength' => '250mg', 'prescribed_qty' => 2])->assertSessionHas('success');
        $item = $rx->items()->first();

        // Unmatched line
        $this->post("/admin/prescriptions/{$rx->id}/verify", ['confirm' => 1])->assertSessionHas('error');

        // Over the prescribed quantity
        $this->put("/admin/prescriptions/{$rx->id}/items/{$item->id}", ['product_id' => $product->id, 'approved_qty' => 3])->assertSessionHasErrors('approved_qty');
        // Over available stock
        $item->update(['prescribed_qty' => null]);
        $this->put("/admin/prescriptions/{$rx->id}/items/{$item->id}", ['product_id' => $product->id, 'approved_qty' => 9])->assertSessionHasErrors('approved_qty');

        $this->assertSame('pending', $rx->fresh()->status);
    }

    public function test_verification_requires_the_confirmation_box(): void
    {
        $customer = $this->makeUser();
        $product = $this->makeProduct(['requires_prescription' => true, 'stock' => 5]);
        $this->actingAs($this->makeUser('pharmacist'));
        $this->upload($customer);
        $rx = Prescription::firstOrFail();
        $this->post("/admin/prescriptions/{$rx->id}/items", ['written_name' => 'X', 'prescribed_qty' => 2]);
        $item = $rx->items()->first();
        $this->put("/admin/prescriptions/{$rx->id}/items/{$item->id}", ['product_id' => $product->id, 'approved_qty' => 2]);

        $this->post("/admin/prescriptions/{$rx->id}/verify", [])->assertSessionHasErrors('confirm');
        $this->assertSame('pending', $rx->fresh()->status);
    }

    public function test_full_workflow_ends_with_the_customer_reviewing_items_in_their_own_cart(): void
    {
        $customer = $this->makeUser();
        $stranger = $this->makeUser();
        $product = $this->makeProduct(['requires_prescription' => true, 'stock' => 5, 'price' => 250, 'name' => 'Amoxil 250mg']);

        // Staff: upload → add line → match → verify
        $staff = $this->makeUser('pharmacist');
        $this->actingAs($staff);
        $this->upload($customer);
        $rx = Prescription::firstOrFail();
        $this->post("/admin/prescriptions/{$rx->id}/items", ['written_name' => 'Amoxil', 'written_strength' => '250mg', 'prescribed_qty' => 2]);
        $item = $rx->items()->first();
        $this->put("/admin/prescriptions/{$rx->id}/items/{$item->id}", ['product_id' => $product->id, 'approved_qty' => 2])->assertSessionHas('success');
        $this->post("/admin/prescriptions/{$rx->id}/verify", ['confirm' => 1])->assertSessionHas('success');

        $rx->refresh();
        $this->assertSame('verified', $rx->status);
        $this->assertSame($staff->id, $rx->reviewed_by);
        $this->assertNotNull($rx->expires_at);
        $this->assertTrue($item->fresh()->is_verified);
        $this->assertSame(0, \App\Models\Order::count(), 'Verification must not place an order');

        // Verified lines are read-only
        $this->put("/admin/prescriptions/{$rx->id}/items/{$item->id}", ['product_id' => $product->id, 'approved_qty' => 1])->assertForbidden();
        $this->post("/admin/prescriptions/{$rx->id}/verify", ['confirm' => 1])->assertForbidden();

        // A stranger cannot use it
        $this->actingAs($stranger);
        $this->post("/account/prescriptions/{$rx->id}/add-to-cart")->assertForbidden();
        $this->get("/account/prescriptions/{$rx->id}")->assertForbidden();

        // The right customer reviews it and adds it to their cart (still no order)
        $this->actingAs($customer);
        $this->get("/account/prescriptions/{$rx->id}")->assertOk()->assertSee('Amoxil 250mg');
        $this->post("/account/prescriptions/{$rx->id}/add-to-cart")->assertRedirect('/cart');
        $this->get('/cart')->assertOk()->assertSee('Amoxil 250mg')->assertSee('Rs. 500');
        $this->assertSame(0, \App\Models\Order::count());
    }

    public function test_rejected_prescription_unlocks_nothing(): void
    {
        $customer = $this->makeUser();
        $product = $this->makeProduct(['requires_prescription' => true]);
        $this->actingAs($this->makeUser('pharmacist'));
        $this->upload($customer);
        $rx = Prescription::firstOrFail();

        $this->post("/admin/prescriptions/{$rx->id}/reject", [])->assertSessionHasErrors('rejection_reason');
        $this->post("/admin/prescriptions/{$rx->id}/reject", ['rejection_reason' => 'Illegible handwriting'])->assertSessionHas('success');
        $this->assertSame('rejected', $rx->fresh()->status);

        $this->actingAs($customer);
        $this->postJson('/cart/add', ['product_id' => $product->id])->assertStatus(422);
        $this->post("/account/prescriptions/{$rx->id}/add-to-cart")->assertForbidden();
    }

    public function test_prescription_files_are_only_served_to_staff_and_the_owner(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $this->actingAs($this->makeUser('pharmacist'));
        $this->upload($owner);
        $rx = Prescription::firstOrFail();

        // Staff
        $this->get("/admin/prescriptions/{$rx->id}/file")->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        // Owner (customer route) ok; other customers refused on both routes
        $this->actingAs($owner)->get("/account/prescriptions/{$rx->id}/file")->assertOk();
        $this->actingAs($other)->get("/account/prescriptions/{$rx->id}/file")->assertForbidden();
        $this->actingAs($other)->get("/admin/prescriptions/{$rx->id}/file")->assertForbidden();

        // Guests are sent to login
        auth()->logout();
        $this->get("/admin/prescriptions/{$rx->id}/file")->assertRedirect('/login');
        $this->get("/account/prescriptions/{$rx->id}/file")->assertRedirect('/login');
    }

    public function test_customer_list_shows_only_their_prescriptions(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $this->verifiedPrescription($a, $this->makeProduct(['name' => 'ZZZ-A-Medicine']), 1);
        $this->verifiedPrescription($b, $this->makeProduct(['name' => 'ZZZ-B-Medicine']), 1);

        $this->actingAs($a)->get('/account/prescriptions')->assertOk()->assertSee(Prescription::where('customer_id', $a->id)->value('reference'))
            ->assertDontSee(Prescription::where('customer_id', $b->id)->value('reference'));
    }

    public function test_suggested_matches_flag_uncertainty_and_never_auto_select(): void
    {
        $customer = $this->makeUser();
        $this->makeProduct(['name' => 'Amoxil 250mg Capsules', 'dosage' => '250mg']);
        $this->makeProduct(['name' => 'Amoxil 500mg Capsules', 'dosage' => '500mg']);
        $this->actingAs($this->makeUser('pharmacist'));
        $this->upload($customer);
        $rx = Prescription::firstOrFail();
        $this->post("/admin/prescriptions/{$rx->id}/items", ['written_name' => 'Amoxil', 'written_strength' => '250mg']);

        $page = $this->get("/admin/prescriptions/{$rx->id}")->assertOk();
        $page->assertSee('Name and strength match')->assertSee('Strength differs or not listed');
        $this->assertNull($rx->items()->first()->product_id, 'No product may be picked automatically');
    }
}
