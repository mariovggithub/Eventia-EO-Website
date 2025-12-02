<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOrganizer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'name', 
        'logo', 
        'description', 
        'price_min', 
        'price_max',
        'phone',
        'address',
        'city',
        'portfolio',
        'experience_years',
        'average_rating',
        'total_ratings',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->hasMany(User::class, 'eo_id');
    }
    
    public function orders() {
        return $this->hasMany(Order::class, 'eo_id');
    }
    
    public function jobs() {
        return $this->hasMany(JobListing::class, 'eo_id');
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

    public function isProfileComplete() {
        return !empty($this->name) && 
               !empty($this->description) && 
               !empty($this->phone);
    }
}