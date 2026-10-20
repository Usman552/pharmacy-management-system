<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['prescription_id', 'user_id', 'action', 'detail', 'ip_address'];
    protected $casts = ['created_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
