<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    /** Staff who may handle prescriptions. */
    private function isStaff(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'super_admin', 'pharmacist'], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user);
    }

    /** Staff, or the customer the prescription belongs to. */
    public function view(User $user, Prescription $prescription): bool
    {
        return $this->isStaff($user) || $prescription->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Prescription $prescription): bool
    {
        return $this->isStaff($user) && $prescription->status === 'pending';
    }

    public function verify(User $user, Prescription $prescription): bool
    {
        return $this->isStaff($user) && $prescription->status === 'pending';
    }

    public function useItems(User $user, Prescription $prescription): bool
    {
        return $prescription->customer_id === $user->id && $prescription->isUsable();
    }
}
