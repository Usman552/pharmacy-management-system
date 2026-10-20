<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Services\CartService;
use App\Services\PrescriptionService;
use Illuminate\Support\Facades\Storage;

/** Customer side: a customer only ever sees their own prescriptions. */
class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::where('customer_id', auth()->id())
            ->withCount('items')->latest()->paginate(10);

        return view('pages.prescriptions', compact('prescriptions'));
    }

    public function show(Prescription $prescription)
    {
        $this->authorize('view', $prescription);
        abort_unless($prescription->customer_id === auth()->id(), 404);

        $prescription->load('items.product');

        return view('pages.prescription-detail', ['rx' => $prescription]);
    }

    public function file(Prescription $prescription)
    {
        $this->authorize('view', $prescription);
        abort_unless($prescription->customer_id === auth()->id(), 404);
        abort_unless(Storage::disk(PrescriptionService::DISK)->exists($prescription->file_path), 404);

        return Storage::disk(PrescriptionService::DISK)->response($prescription->file_path, null, [
            'Content-Type'           => $prescription->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }

    /** Puts verified items in the cart for review. Does not place an order. */
    public function addToCart(Prescription $prescription, CartService $cart)
    {
        $this->authorize('useItems', $prescription);

        $result = $cart->addPrescription($prescription);
        $prescription->log('added_to_cart', "{$result['added']} item(s) added to the customer's cart");

        $msg = $result['added'] . ' item(s) added to your cart. Please review them before checkout.';
        if ($result['skipped']) {
            $msg .= ' Not added: ' . implode(', ', $result['skipped']) . '.';
        }

        return redirect()->route('cart.index')->with('success', $msg);
    }
}
