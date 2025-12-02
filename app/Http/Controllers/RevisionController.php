<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRevision;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevisionController extends Controller
{
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        // Check revision limit
        if (!$order->canRequestRevision()) {
            $remaining = $order->getRemainingRevisions();
            $daysUntil = $order->getDaysUntilEvent();
            
            if ($remaining <= 0) {
                return back()->with('error', 'Anda telah mencapai batas maksimal 3 kali revisi.');
            }
            
            if ($daysUntil < 7) {
                return back()->with('error', 'Revisi hanya dapat diajukan minimal H-7 sebelum tanggal event.');
            }
        }

        $validated = $request->validate([
            'revision_note' => 'required|string|max:1000'
        ]);

        OrderRevision::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'revision_note' => $validated['revision_note'],
            'status' => 'pending'
        ]);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '📝 Permintaan revisi diajukan (Sisa: ' . ($order->getRemainingRevisions() - 1) . ' kali): ' . $validated['revision_note']
        ]);

        return back()->with('success', 'Permintaan revisi berhasil diajukan. Sisa revisi: ' . ($order->getRemainingRevisions() - 1));
    }

    public function respond(Request $request, OrderRevision $revision)
    {
        $order = $revision->order;
        
        if (Auth::user()->role !== 'eo' || $order->eo_id !== Auth::id()) {
            abort(403);
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
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => "$emoji Revisi " . ($validated['status'] === 'approved' ? 'disetujui' : 'ditolak') . ": " . $validated['response_note']
        ]);

        return back()->with('success', 'Respon revisi berhasil dikirim.');
    }
}