<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id','name','slug','sku','brand','description','details',
        'image','images','image_credit','image_credit_url','price','sale_price','stock','min_stock_alert',
        'expiry_date','manufacturer','composition','dosage','weight',
        'rating','reviews_count','is_active','is_featured','is_new',
        'is_popular','is_sale','requires_prescription','sort_order'
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_popular' => 'boolean',
        'is_sale' => 'boolean',
        'requires_prescription' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    public function getFinalPriceAttribute() {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentAttribute() {
        if($this->sale_price && $this->price > $this->sale_price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    public function isLowStock() {
        return $this->stock > 0 && $this->stock < $this->min_stock_alert;
    }

    /** Public URL of the main image, or null when none exists on disk (views show a fallback tile). */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && is_file(public_path('images/products/' . basename($this->image)))) {
            return asset('images/products/' . basename($this->image));
        }
        return null;
    }

    /** All gallery URLs that actually exist, main image first. */
    public function getGalleryUrlsAttribute(): array
    {
        $files = array_filter(array_merge([$this->image], $this->images ?? []));
        $urls = [];
        foreach (array_unique($files) as $f) {
            if (is_file(public_path('images/products/' . basename($f)))) {
                $urls[] = asset('images/products/' . basename($f));
            }
        }
        return $urls;
    }

    public function stockStatus(): string
    {
        if ($this->stock < 1) return 'out';
        return $this->stock < $this->min_stock_alert ? 'low' : 'in';
    }

    public function isExpiringSoon() {
        return $this->expiry_date && $this->expiry_date <= now()->addDays(30);
    }

    public static function boot() {
        parent::boot();
        static::creating(function($product) {
            if(empty($product->slug)) {
                $product->slug = \Str::slug($product->name);
            }
            if(empty($product->sku)) {
                $product->sku = 'CD-' . strtoupper(\Str::random(8));
            }
        });
    }
}
