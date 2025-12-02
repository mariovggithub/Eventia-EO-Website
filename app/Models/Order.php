<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'total',
        'negotiated_price',
        'price_breakdown',
        'price_agreed',
        'price_agreed_at'
    ];

    protected $casts = [
        'event_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'price_agreed_at' => 'datetime',
        'self_organized' => 'boolean',
        'price_agreed' => 'boolean'
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

    public function ratings() {
        return $this->hasMany(Rating::class);
    }
    
    public function getFormattedTotalAttribute() {
        $amount = $this->negotiated_price ?? $this->total ?? 0;
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getFinalPriceAttribute() {
        return $this->negotiated_price ?? $this->total ?? 0;
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

    public function isCompleted() {
        return $this->status === 'completed';
    }
    
    public function canChat() {
        return $this->isApproved();
    }
    
    public function canRequestRevision() {
        // Max 3 revisions
        $revisionCount = $this->revisions()->count();
        if ($revisionCount >= 3) {
            return false;
        }

        // Only H-7 or more before event
        $daysUntilEvent = Carbon::now()->diffInDays($this->event_date, false);
        if ($daysUntilEvent < 7) {
            return false;
        }

        return $this->isPending() || ($this->isApproved() && !$this->isPaid());
    }

    public function canRate() {
        return $this->isCompleted() && $this->isPaid();
    }

    public function hasBeenRatedBy($userId) {
        return $this->ratings()->where('user_id', $userId)->exists();
    }

    public function getRemainingRevisions() {
        return max(0, 3 - $this->revisions()->count());
    }

    public function getDaysUntilEvent() {
        return Carbon::now()->diffInDays($this->event_date, false);
    }
}