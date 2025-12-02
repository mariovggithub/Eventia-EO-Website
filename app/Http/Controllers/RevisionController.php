<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRevision;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevisionController extends Controller
{
    // Customer request revision
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        if (!$order->canRequestRevision()) {
            return back()->with('error', 'Revisi tidak dapat diajukan pada status order ini.');
        }

        $validated = $request->validate([
            'revision_note' => 'required|string|max:1000'
        ]);

        $revision = OrderRevision::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'revision_note' => $validated['revision_note'],
            'status' => 'pending'
        ]);

        // Send notification to chat
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '📝 Permintaan revisi diajukan: ' . $validated['revision_note']
        ]);

        return back()->with('success', 'Permintaan revisi berhasil diajukan.');
    }

    // EO respond to revision
    public function respond(Request $request, OrderRevision $revision)
    {
        $order = $revision->order;
        
        if (!Auth::user() || Auth::user()->role !== 'eo' || $order->eo_id !== Auth::id()) {
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

        // Send notification to chat
        $emoji = $validated['status'] === 'approved' ? '✅' : '❌';
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => "$emoji Revisi " . ($validated['status'] === 'approved' ? 'disetujui' : 'ditolak') . ": " . $validated['response_note']
        ]);

        return back()->with('success', 'Respon revisi berhasil dikirim.');
    }
}