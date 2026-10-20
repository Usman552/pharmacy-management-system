<?php
// ============================================================
// CITY DRUG - Models
// Each model is in its own file in app/Models/
// ============================================================

// ─── app/Models/Category.php ───
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model {
    use HasFactory;
    protected $fillable = ['name','slug','description','image','icon','color','is_active','sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function products() { return $this->hasMany(Product::class); }

    /** Font Awesome icon for the category tile (the stored emoji icon is not used in the UI). */
    public function getIconClassAttribute(): string
    {
        $map = [
            'supplement' => 'fa-leaf', 'vitamin' => 'fa-leaf', 'personal' => 'fa-pump-soap',
            'baby' => 'fa-baby', 'sport' => 'fa-dumbbell', 'herb' => 'fa-seedling',
            'device' => 'fa-stethoscope', 'skin' => 'fa-hand-sparkles', 'eye' => 'fa-eye',
            'dental' => 'fa-tooth', 'digest' => 'fa-bowl-food', 'diabet' => 'fa-droplet',
            'medicine' => 'fa-pills',
        ];
        foreach ($map as $needle => $icon) {
            if (str_contains($this->slug, $needle)) return $icon;
        }
        return 'fa-capsules';
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && is_file(public_path('images/categories/' . basename($this->image)))) {
            return asset('images/categories/' . basename($this->image));
        }
        return null;
    }
    public function activeProducts() { return $this->hasMany(Product::class)->where('is_active', true); }

    public static function boot() {
        parent::boot();
        static::creating(function($category) {
            if(empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }
}
