<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    private function rules(?Coupon $coupon = null): array
    {
        return [
            'code'        => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description' => 'nullable|string|max:255',
            'type'        => 'required|in:percentage,fixed',
            'value'       => ['required', 'numeric', 'gt:0', fn ($a, $v, $fail) =>
                request('type') === 'percentage' && $v > 100 ? $fail('A percentage discount cannot exceed 100.') : null],
            'min_order'   => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at'  => 'nullable|date',
        ];
    }

    private function payload(Request $request): array
    {
        $data = $request->only(['code', 'description', 'type', 'value', 'min_order', 'usage_limit', 'expires_at']);
        $data['code']      = strtoupper($data['code']);
        $data['min_order'] = $data['min_order'] ?? 0;
        $data['usage_limit'] = $request->filled('usage_limit') ? $data['usage_limit'] : null;
        $data['expires_at']  = $request->filled('expires_at') ? $data['expires_at'] : null;
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        Coupon::create($this->payload($request));
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate($this->rules($coupon));
        $coupon->update($this->payload($request));
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }
}
