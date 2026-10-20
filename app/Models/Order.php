<?php
// ─── app/Models/Order.php ───
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    /** Allowed next statuses. Anything else is rejected by the admin screen. */
    public const TRANSITIONS = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered', 'cancelled'],
        'delivered'  => ['refunded'],
        'cancelled'  => [],
        'refunded'   => [],
    ];

    protected $fillable = [
        'order_number','user_id','customer_name','customer_email','customer_phone',
        'shipping_address','shipping_city','shipping_area','subtotal','discount',
        'shipping_charge','total_amount','coupon_code','status','payment_status',
        'payment_method','customer_note','admin_note','tracking_number',
        'courier_name','shipped_at','delivered_at'
    ];
    protected $casts = ['shipped_at' => 'datetime', 'delivered_at' => 'datetime'];

    public function user()    { return $this->belongsTo(User::class); }
    public function items()   { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function tracking(){ return $this->hasMany(OrderTracking::class); }

    public function allowedNextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, $this->allowedNextStatuses(), true);
    }

    public static function generateOrderNumber() {
        do {
            $number = 'CD-' . date('Ymd') . '-' . strtoupper(\Str::random(5));
        } while (static::where('order_number', $number)->exists());
        return $number;
    }
}
