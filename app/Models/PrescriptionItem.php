<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id', 'written_name', 'written_strength', 'written_form',
        'prescribed_qty', 'product_id', 'approved_qty', 'fulfilled_qty', 'is_verified',
    ];

    protected $casts = ['is_verified' => 'boolean'];

    public function prescription() { return $this->belongsTo(Prescription::class); }
    public function product()      { return $this->belongsTo(Product::class); }

    public function getRemainingQtyAttribute(): int
    {
        return max(0, $this->approved_qty - $this->fulfilled_qty);
    }
}
