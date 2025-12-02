<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'event_type_id', 
        'eo_id', 
        'self_organized',
        'event_date', 
        'capacity', 
        'vendor_choice', 
        'status', 
        'approval_status',
        'rejection_reason',
        'approved_at',
        'payment_status',
        'paid_at',
        'total'
    ];

    protected $casts = [
        'event_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'self_organized' => 'boolean'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function eventType() {
        return $this->belongsTo(EventType::class);
    }
    
    public function eventOrganizer() {
        return $this->belongsTo(EventOrganizer::class, 'eo_id');
    }
    
    public function vendors() {
        return $this->belongsToMany(VendorProduct::class, 'order_vendors');
    }
    
    public function chats() {
        return $this->hasMany(OrderChat::class);
    }
    
    public function revisions() {
        return $this->hasMany(OrderRevision::class);
    }
    
    public function getFormattedTotalAttribute() {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
    
    // Status helpers
    public function isPending() {
        return $this->approval_status === 'pending';
    }
    
    public function isApproved() {
        return $this->approval_status === 'approved';
    }
    
    public function isRejected() {
        return $this->approval_status === 'rejected';
    }
    
    public function isPaid() {
        return $this->payment_status === 'paid';
    }
    
    public function canChat() {
        return $this->isApproved();
    }
    
    public function canRequestRevision() {
        return $this->isPending() || $this->isApproved();
    }
}