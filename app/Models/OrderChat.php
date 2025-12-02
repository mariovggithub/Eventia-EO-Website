<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChat extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'user_id',
        'message',
        'attachment',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function markAsRead() {
        if (!$this->is_read) {
            $this->update(['is_read' => true]);
        }
    }
}