<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_category_id', 
        'user_id', 
        'name', 
        'price', 
        'quantity', 
        'image',
        'average_rating',
        'total_ratings'
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer'
    ];

    public function category() {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }
    
    public function vendor() {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function orders() {
        return $this->belongsToMany(Order::class, 'order_vendors');
    }

    public function ratings() {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function getStarRatingAttribute() {
        return $this->average_rating > 0 ? number_format($this->average_rating, 1) : 'Belum ada rating';
    }

    public function getStarDisplayAttribute() {
        $rating = round($this->average_rating);
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        return $stars;
    }
}