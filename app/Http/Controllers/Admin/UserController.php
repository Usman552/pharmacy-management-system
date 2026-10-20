<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%");
            });
        }

        if ($request->status === 'active')   $query->where('is_active', true);
        if ($request->status === 'inactive') $query->where('is_active', false);

        $users = $query->withCount('orders')->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function admins()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin', 'pharmacist'])->latest()->get();
        return view('admin.users.admins', compact('admins'));
    }

    /** Only a super admin may create staff accounts. */
    public function storeStaff(Request $request)
    {
        abort_unless($request->user()->role === 'super_admin', 403, 'Only a super admin can add staff.');

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'role'     => 'required|in:pharmacist,admin',
            'password' => 'required|string|min:10|confirmed',
        ]);

        User::create($data + ['is_active' => true, 'email_verified_at' => now()]);

        return redirect()->route('admin.users.admins')->with('success', 'Staff account created for ' . $data['name'] . '.');
    }

    public function show(User $user)
    {
        $user->load(['orders' => fn ($q) => $q->latest()->take(20)]);
        $prescriptions = $user->role === 'customer'
            ? \App\Models\Prescription::where('customer_id', $user->id)->latest()->take(10)->get()
            : collect();
        $lifetime = (float) $user->orders()->where('payment_status', 'paid')->whereNotIn('status', ['cancelled', 'refunded'])->sum('total_amount');
        return view('admin.users.show', compact('user', 'prescriptions', 'lifetime'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id() || $user->isAdmin()) {
            return back()->with('error', 'Staff accounts and your own account cannot be deleted here.');
        }
        // Keep order and prescription history intact: deactivate instead.
        if ($user->orders()->exists() || \App\Models\Prescription::where('customer_id', $user->id)->exists()) {
            $user->update(['is_active' => false]);
            return redirect()->route('admin.users.index')
                ->with('success', 'This customer has order or prescription history, so the account was deactivated instead of deleted.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Customer deleted.');
    }
}
