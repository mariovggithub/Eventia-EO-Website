<?php
namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show EO orders dashboard
     */
    public function orders(Request $request) {
        $user = Auth::user();
        
        // Check if EO has profile
        if (!$user->eventOrganizer || !$user->eventOrganizer->isProfileComplete()) {
            return redirect()->route('eo.profile')
                ->with('info', 'Silakan lengkapi profile EO Anda terlebih dahulu.');
        }
        
        $query = Order::with(['user', 'eventType', 'eventOrganizer', 'vendors.category']);
        
        // Filter only this EO's orders
        if ($user->eo_id) {
            $query->where('eo_id', $user->eo_id);
        }

        // Filter by status if provided
        $status = $request->get('status');
        if ($status) {
            if ($status === 'pending') {
                $query->where('approval_status', 'pending');
            } elseif ($status === 'approved') {
                $query->where('approval_status', 'approved');
            } elseif ($status === 'ongoing') {
                $query->where('status', 'ongoing')->where('payment_status', 'paid');
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            }
        }
        
        $orders = $query->latest()->get();

        // Count by status
        $statusCounts = [
            'all' => Order::where('eo_id', $user->eo_id)->count(),
            'pending' => Order::where('eo_id', $user->eo_id)->where('approval_status', 'pending')->count(),
            'approved' => Order::where('eo_id', $user->eo_id)->where('approval_status', 'approved')->count(),
            'ongoing' => Order::where('eo_id', $user->eo_id)->where('status', 'ongoing')->count(),
            'completed' => Order::where('eo_id', $user->eo_id)->where('status', 'completed')->count(),
        ];
        
        return view('eo.orders', compact('orders', 'statusCounts', 'status'));
    }

    /**
     * Approve order with price negotiation
     */
    public function approveOrder(Request $request, Order $order) {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
            abort(403, 'Anda tidak memiliki akses untuk menyetujui order ini.');
        }

        // Check if already processed
        if ($order->approval_status !== 'pending') {
            return back()->with('error', 'Order sudah diproses sebelumnya.');
        }

        // Validate price input
        $validated = $request->validate([
            'negotiated_price' => 'required|numeric|min:1000000',
            'price_breakdown' => 'nullable|string|max:2000'
        ]);

        // Update order
        $order->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'negotiated_price' => $validated['negotiated_price'],
            'price_breakdown' => $validated['price_breakdown'],
            'total' => $validated['negotiated_price']
        ]);

        // Format price breakdown for display
        $breakdownText = '';
        if (!empty($validated['price_breakdown'])) {
            $breakdownText = "\n\nRincian Biaya:\n" . $validated['price_breakdown'];
        }

        // Send notification to chat
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '✅ Pesanan Anda telah disetujui oleh EO!' . "\n\n" .
                        '💰 Penawaran Harga: Rp ' . number_format($validated['negotiated_price'], 0, ',', '.') .
                        $breakdownText . "\n\n" .
                        'Silakan review penawaran harga dan lanjutkan ke pembayaran jika setuju. ' .
                        'Jika ada yang perlu didiskusikan, silakan gunakan fitur chat atau ajukan revisi.'
        ]);

        return back()->with('success', 'Pesanan berhasil disetujui dengan penawaran harga Rp ' . number_format($validated['negotiated_price'], 0, ',', '.'));
    }

    /**
     * Update negotiated price (if customer rejects and negotiates)
     */
    public function updatePrice(Request $request, Order $order) {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
            abort(403);
        }

        // Must be approved but not yet paid
        if ($order->approval_status !== 'approved' || $order->isPaid()) {
            return back()->with('error', 'Harga tidak dapat diubah untuk order ini.');
        }

        $validated = $request->validate([
            'negotiated_price' => 'required|numeric|min:1000000',
            'price_breakdown' => 'nullable|string|max:2000'
        ]);

        $oldPrice = $order->negotiated_price;
        
        $order->update([
            'negotiated_price' => $validated['negotiated_price'],
            'price_breakdown' => $validated['price_breakdown'],
            'total' => $validated['negotiated_price'],
            'price_agreed' => false, // Reset agreement
            'price_agreed_at' => null
        ]);

        // Send notification
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '💰 Harga penawaran telah diperbarui!' . "\n" .
                        'Harga sebelumnya: Rp ' . number_format($oldPrice, 0, ',', '.') . "\n" .
                        'Harga baru: Rp ' . number_format($validated['negotiated_price'], 0, ',', '.') . "\n\n" .
                        ($validated['price_breakdown'] ? "Rincian:\n" . $validated['price_breakdown'] : '')
        ]);

        return back()->with('success', 'Harga penawaran berhasil diperbarui.');
    }

    /**
     * Reject order
     */
    public function rejectOrder(Request $request, Order $order) {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
            abort(403, 'Anda tidak memiliki akses untuk menolak order ini.');
        }

        // Check if already processed
        if ($order->approval_status !== 'pending') {
            return back()->with('error', 'Order sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $order->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'status' => 'cancelled'
        ]);

        // Send notification to chat
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '❌ Pesanan ditolak oleh EO.' . "\n\n" .
                        'Alasan: ' . $validated['rejection_reason'] . "\n\n" .
                        'Mohon maaf atas ketidaknyamanannya. Silakan hubungi kami jika ada pertanyaan.'
        ]);

        return back()->with('success', 'Pesanan berhasil ditolak.');
    }

    /**
     * Update order status (ongoing -> completed)
     */
    public function updateStatus(Request $request, Order $order) {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:ongoing,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        $statusEmoji = [
            'ongoing' => '🔄',
            'completed' => '✅',
            'cancelled' => '❌'
        ];

        $statusText = [
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $statusEmoji[$validated['status']] . ' Status order diperbarui: ' . $statusText[$validated['status']]
        ]);

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    /**
     * View order detail (EO perspective)
     */
    public function showOrder(Order $order) {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }

        $order->load([
            'user',
            'eventType',
            'vendors.category',
            'chats.user',
            'revisions.user',
            'ratings'
        ]);

        return view('eo.order-detail', compact('order'));
    }

    /**
     * Get statistics for EO dashboard
     */
    public function getStats() {
        $user = Auth::user();
        
        if (!$user->eo_id) {
            return response()->json(['error' => 'EO not found'], 404);
        }

        $stats = [
            'total_orders' => Order::where('eo_id', $user->eo_id)->count(),
            'pending_orders' => Order::where('eo_id', $user->eo_id)->where('approval_status', 'pending')->count(),
            'ongoing_orders' => Order::where('eo_id', $user->eo_id)->where('status', 'ongoing')->count(),
            'completed_orders' => Order::where('eo_id', $user->eo_id)->where('status', 'completed')->count(),
            'total_revenue' => Order::where('eo_id', $user->eo_id)
                ->where('payment_status', 'paid')
                ->sum('total'),
            'average_rating' => $user->eventOrganizer->average_rating ?? 0,
            'total_ratings' => $user->eventOrganizer->total_ratings ?? 0
        ];

        return response()->json($stats);
    }

    /**
 * Mark order as completed
 */
public function completeOrder(Order $order) {
    $user = Auth::user();
    
    // Check authorization
    if (!$user->eventOrganizer || $order->eo_id !== $user->eo_id) {
        abort(403, 'Anda tidak memiliki akses untuk menyelesaikan order ini.');
    }

    // Check if order is paid
    if (!$order->isPaid()) {
        return back()->with('error', 'Order belum dibayar.');
    }

    // Check if already completed
    if ($order->isCompleted()) {
        return back()->with('info', 'Order sudah selesai sebelumnya.');
    }

    // Update status to completed
    $order->update([
        'status' => 'completed'
    ]);

    // Send notification to chat
    OrderChat::create([
        'order_id' => $order->id,
        'user_id' => Auth::id(),
        'message' => '🎉 Event telah selesai dilaksanakan!' . "\n\n" .
                    'Terima kasih telah mempercayai kami. ' .
                    'Silakan berikan rating untuk membantu kami meningkatkan layanan.'
    ]);

    return back()->with('success', 'Order berhasil ditandai sebagai selesai. Customer dapat memberikan rating sekarang.');
}
}