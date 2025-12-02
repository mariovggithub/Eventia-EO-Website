<?php
namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function orders() {
        $user = Auth::user();
        $query = Order::with(['user', 'eventType', 'eventOrganizer', 'vendors.category']);
        
        if ($user->eo_id) {
            $query->where('eo_id', $user->eo_id);
        }
        
        $orders = $query->latest()->get();
        return view('eo.orders', compact('orders'));
    }

    // Approve order
    public function approveOrder(Order $order) {
        $user = Auth::user();
        
        if (!$user || !$user->eo_id || $order->eo_id !== $user->eo_id) {
            abort(403);
        }

        if ($order->approval_status !== 'pending') {
            return back()->with('error', 'Order sudah diproses.');
        }

        $order->update([
            'approval_status' => 'approved',
            'approved_at' => now()
        ]);

        // Send notification to chat
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '✅ Pesanan Anda telah disetujui oleh EO. Silakan lanjutkan ke pembayaran.'
        ]);

        return back()->with('success', 'Pesanan berhasil disetujui. Customer akan diarahkan untuk pembayaran.');
    }

    // Reject order
    public function rejectOrder(Request $request, Order $order) {
        $user = Auth::user();
        
        if (!$user || !$user->eo_id || $order->eo_id !== $user->eo_id) {
            abort(403);
        }

        if ($order->approval_status !== 'pending') {
            return back()->with('error', 'Order sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $order->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason']
        ]);

        // Send notification to chat
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '❌ Pesanan ditolak. Alasan: ' . $validated['rejection_reason']
        ]);

        return back()->with('success', 'Pesanan berhasil ditolak.');
    }
}