<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRevision;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RevisionController extends Controller
{
    /**
     * Store revision request
     * Rules: Max 3 times, minimum 14 days before event
     */
    public function store(Request $request, Order $order)
    {
        // Check authorization
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak dapat mengajukan revisi untuk order ini.');
        }

        // Validate revision limits
        $validation = $this->validateRevisionLimits($order);
        
        if (!$validation['allowed']) {
            return back()->with('error', $validation['message']);
        }

        $validated = $request->validate([
            'revision_note' => 'required|string|max:1000'
        ]);

        // Get current revision count
        $currentCount = $order->revisions()->count();

        // Create revision
        $revision = OrderRevision::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'revision_note' => $validated['revision_note'],
            'status' => 'pending'
        ]);

        // Calculate remaining
        $remainingRevisions = 3 - ($currentCount + 1);
        $daysUntilEvent = Carbon::now()->diffInDays($order->event_date, false);

        // Send chat notification
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '📝 Permintaan Revisi #' . ($currentCount + 1) . "\n\n" .
                        $validated['revision_note'] . "\n\n" .
                        '⚠️ Sisa revisi: ' . $remainingRevisions . ' kali\n' .
                        '📅 Hari menuju event: ' . $daysUntilEvent . ' hari'
        ]);

        return back()->with('success', 
            'Permintaan revisi berhasil diajukan. ' .
            'Sisa revisi: ' . $remainingRevisions . ' kali. ' .
            'Revisi hanya dapat diajukan hingga H-14 sebelum event.'
        );
    }

    /**
     * EO responds to revision
     */
    public function respond(Request $request, OrderRevision $revision)
    {
        $order = $revision->order;
        
        // Check authorization
        if (!Auth::user()->role === 'eo' || $order->eo_id !== Auth::user()->eo_id) {
            abort(403, 'Anda tidak memiliki akses untuk merespon revisi ini.');
        }

        if ($revision->status !== 'pending') {
            return back()->with('error', 'Revisi ini sudah direspon sebelumnya.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'response_note' => 'required|string|max:1000'
        ]);

        $revision->update([
            'status' => $validated['status'],
            'response_note' => $validated['response_note'],
            'responded_at' => now()
        ]);

        $emoji = $validated['status'] === 'approved' ? '✅' : '❌';
        $statusText = $validated['status'] === 'approved' ? 'disetujui' : 'ditolak';

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $emoji . ' Revisi #' . ($order->revisions()->count()) . ' ' . $statusText . "\n\n" .
                        'Respon EO: ' . $validated['response_note']
        ]);

        return back()->with('success', 'Respon revisi berhasil dikirim.');
    }

    /**
     * Validate revision limits
     */
    private function validateRevisionLimits(Order $order): array
    {
        // Check order status
        if (!in_array($order->approval_status, ['pending', 'approved'])) {
            return [
                'allowed' => false,
                'message' => 'Revisi hanya dapat diajukan untuk order yang pending atau sudah disetujui.'
            ];
        }

        // Check if paid
        if ($order->isPaid()) {
            return [
                'allowed' => false,
                'message' => 'Revisi tidak dapat diajukan setelah pembayaran.'
            ];
        }

        // Check revision count (max 3)
        $currentCount = $order->revisions()->count();
        if ($currentCount >= 3) {
            return [
                'allowed' => false,
                'message' => '❌ Anda telah mencapai batas maksimal 3 kali pengajuan revisi untuk order ini.'
            ];
        }

        // Check time limit (at least 14 days before event)
        $daysUntilEvent = Carbon::now()->diffInDays($order->event_date, false);
        
        if ($daysUntilEvent < 14) {
            return [
                'allowed' => false,
                'message' => '❌ Revisi hanya dapat diajukan minimal 2 minggu (14 hari) sebelum tanggal event. ' .
                           'Saat ini tersisa ' . $daysUntilEvent . ' hari menuju event. ' .
                           'Silakan diskusikan perubahan melalui fitur chat.'
            ];
        }

        // All validations passed
        $remainingRevisions = 3 - $currentCount;
        
        return [
            'allowed' => true,
            'message' => 'Anda dapat mengajukan revisi. Sisa: ' . $remainingRevisions . ' kali.'
        ];
    }

    /**
     * Check revision limits (AJAX)
     */
    public function checkLimits(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $validation = $this->validateRevisionLimits($order);
        $currentCount = $order->revisions()->count();
        $daysUntilEvent = Carbon::now()->diffInDays($order->event_date, false);

        return response()->json([
            'allowed' => $validation['allowed'],
            'message' => $validation['message'],
            'current_count' => $currentCount,
            'remaining_count' => max(0, 3 - $currentCount),
            'days_until_event' => $daysUntilEvent,
            'deadline_passed' => $daysUntilEvent < 14
        ]);
    }
}