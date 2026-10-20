<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prescription extends Model
{
    protected $fillable = [
        'reference', 'customer_id', 'uploaded_by', 'file_path', 'original_name', 'mime_type',
        'file_size', 'doctor_name', 'prescribed_on', 'status', 'extraction_status',
        'staff_notes', 'rejection_reason', 'reviewed_by', 'reviewed_at', 'expires_at',
    ];

    protected $casts = [
        'prescribed_on' => 'date',
        'reviewed_at'   => 'datetime',
        'expires_at'    => 'datetime',
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function items()    { return $this->hasMany(PrescriptionItem::class); }
    public function logs()     { return $this->hasMany(PrescriptionLog::class)->latest('id'); }

    public static function newReference(): string
    {
        return 'RX-' . date('ymd') . '-' . strtoupper(Str::random(4));
    }

    public function isUsable(): bool
    {
        return $this->status === 'verified' && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function log(string $action, ?string $detail = null): void
    {
        $this->logs()->create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'detail'     => $detail,
            'ip_address' => request()->ip(),
        ]);
    }
}
