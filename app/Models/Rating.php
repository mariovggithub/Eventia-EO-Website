<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'user_id',
        'rateable_type',
        'rateable_id',
        'rating',
        'review'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function rateable() {
        return $this->morphTo();
    }

    // Update average rating for the rateable model
    public static function boot() {
        parent::boot();

        static::saved(function ($rating) {
            $rating->updateAverageRating();
        });

        static::deleted(function ($rating) {
            $rating->updateAverageRating();
        });
    }

    public function updateAverageRating() {
        $model = $this->rateable;
        if (!$model) return;

        $ratings = Rating::where('rateable_type', $this->rateable_type)
            ->where('rateable_id', $this->rateable_id)
            ->get();

        $average = $ratings->avg('rating');
        $total = $ratings->count();

        $model->update([
            'average_rating' => round($average, 2),
            'total_ratings' => $total
        ]);
    }
}