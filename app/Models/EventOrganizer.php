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
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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
    
    public function getFormattedPriceAttribute() {
        return 'Rp ' . number_format($this->price_min, 0, ',', '.') . ' - Rp ' . number_format($this->price_max, 0, ',', '.');
    }

    public function isProfileComplete() {
        return !empty($this->name) && 
               !empty($this->description) && 
               !empty($this->phone) &&
               $this->price_min > 0;
    }
}