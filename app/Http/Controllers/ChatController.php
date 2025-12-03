<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Send a chat message (Simple version without broadcasting)
     */
    public function store(Request $request, Order $order)
    {
        // Check if user is part of this order
        $this->authorizeOrderAccess($order);

        // Check if chat is allowed
        if (!$order->canChat()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat hanya tersedia setelah order disetujui.'
                ], 403);
            }
            return back()->with('error', 'Chat hanya tersedia setelah order disetujui.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        // Create chat message
        $chat = OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_read' => false
        ]);

        // Load user relationship
        $chat->load('user');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'message' => $chat->message,
                    'user_name' => $chat->user->name,
                    'user_role' => $chat->user->role,
                    'created_at' => $chat->created_at->format('d M H:i'),
                    'is_own' => $chat->user_id === Auth::id()
                ]
            ]);
        }

        return back()->with('success', 'Pesan terkirim');
    }

    /**
     * Get chat messages for an order
     */
    public function getMessages(Order $order)
    {
        $this->authorizeOrderAccess($order);

        $chats = $order->chats()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        $order->chats()
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'chats' => $chats->map(function($chat) {
                return [
                    'id' => $chat->id,
                    'message' => $chat->message,
                    'user_name' => $chat->user->name,
                    'user_role' => $chat->user->role,
                    'created_at' => $chat->created_at->format('d M H:i'),
                    'is_own' => $chat->user_id === Auth::id()
                ];
            })
        ]);
    }

    /**
     * Check if user has access to order chat
     */
    private function authorizeOrderAccess(Order $order)
    {
        $user = Auth::user();
        
        // Customer
        if ($order->user_id === $user->id) return;
        
        // EO
        if ($user->role === 'eo' && $order->eo_id === $user->eo_id) return;
        
        // Vendor
        if ($user->role === 'vendor') {
            $vendorProductIds = $user->vendorProducts->pluck('id');
            $orderVendorIds = $order->vendors->pluck('id');
            if ($vendorProductIds->intersect($orderVendorIds)->isNotEmpty()) return;
        }
        
        abort(403, 'Anda tidak memiliki akses ke chat ini.');
    }
}