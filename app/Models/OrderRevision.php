<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRevision extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'user_id',
        'revision_note',
        'status',
        'response_note',
        'responded_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function isPending() {
        return $this->status === 'pending';
    }
    
    public function isApproved() {
        return $this->status === 'approved';
    }
    
    public function isRejected() {
        return $this->status === 'rejected';
    }
}