<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model {
    protected $fillable = ['title','subtitle','description','image','button_text','button_link','badge_text','background_color','text_color','position','is_active','sort_order'];
    protected $casts    = ['is_active' => 'boolean'];
}
