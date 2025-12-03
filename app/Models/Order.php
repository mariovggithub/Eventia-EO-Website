<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'event_type_id', 'eo_id', 'self_organized',
        'event_date', 'capacity', 'vendor_choice', 'status', 
        'approval_status', 'rejection_reason', 'approved_at',
        'payment_status', 'paid_at', 'total', 'negotiated_price',
        'price_breakdown', 'price_agreed', 'price_agreed_at'
    ];

    protected $casts = [
        'event_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'price_agreed_at' => 'datetime',
        'self_organized' => 'boolean',
        'price_agreed' => 'boolean'
    ];

    // Relationships
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
    
    // Formatted Attributes
    public function getFormattedTotalAttribute() {
        $amount = $this->negotiated_price ?? $this->total ?? 0;
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getFinalPriceAttribute() {
        return $this->negotiated_price ?? $this->total ?? 0;
    }
    
    // Status Helpers
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
    
    /**
     * Check if user can request revision
     * Rules: Max 3 times, minimum 14 days before event
     */
    public function canRequestRevision(): bool
    {
        // Must be pending or approved
        if (!in_array($this->approval_status, ['pending', 'approved'])) {
            return false;
        }

        // Cannot revise after payment
        if ($this->isPaid()) {
            return false;
        }

        // Check revision count (max 3)
        if ($this->revisions()->count() >= 3) {
            return false;
        }

        // Check time limit (at least 14 days before event)
        $daysUntilEvent = Carbon::now()->diffInDays($this->event_date, false);
        if ($daysUntilEvent < 14) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining revision count
     */
    public function getRemainingRevisions(): int
    {
        return max(0, 3 - $this->revisions()->count());
    }

    /**
     * Get days until event
     */
    public function getDaysUntilEvent(): int
    {
        return Carbon::now()->diffInDays($this->event_date, false);
    }

    /**
     * Check if revision deadline has passed
     */
    public function isRevisionDeadlinePassed(): bool
    {
        return $this->getDaysUntilEvent() < 14;
    }

    /**
     * Get revision deadline date
     */
    public function getRevisionDeadlineAttribute(): Carbon
    {
        return $this->event_date->copy()->subDays(14);
    }

    /**
     * Get comprehensive revision info
     */
    public function getRevisionInfoAttribute(): array
    {
        $currentCount = $this->revisions()->count();
        $remaining = $this->getRemainingRevisions();
        $daysUntil = $this->getDaysUntilEvent();
        $deadlinePassed = $this->isRevisionDeadlinePassed();
        
        return [
            'current_count' => $currentCount,
            'remaining_count' => $remaining,
            'days_until_event' => $daysUntil,
            'deadline_passed' => $deadlinePassed,
            'deadline_date' => $this->revision_deadline->format('d F Y'),
            'can_request' => $this->canRequestRevision(),
            'reason' => $this->getRevisionBlockReason()
        ];
    }

    /**
     * Get reason why revision is blocked
     */
    public function getRevisionBlockReason(): ?string
    {
        if ($this->canRequestRevision()) {
            return null;
        }

        if ($this->isPaid()) {
            return 'Revisi tidak dapat diajukan setelah pembayaran.';
        }

        if ($this->revisions()->count() >= 3) {
            return 'Batas maksimal 3 kali revisi telah tercapai.';
        }

        if ($this->isRevisionDeadlinePassed()) {
            return 'Deadline revisi telah lewat (minimal H-14). Sisa ' . $this->getDaysUntilEvent() . ' hari.';
        }

        return 'Status order tidak memungkinkan revisi.';
    }

    /**
     * Check if user can rate
     */
    public function canRate(): bool
    {
        return $this->isCompleted() && $this->isPaid();
    }

    /**
     * Check if already rated by user
     */
    public function hasBeenRatedBy($userId): bool
    {
        return $this->ratings()->where('user_id', $userId)->exists();
    }
}