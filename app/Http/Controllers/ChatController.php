<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function store(Request $request, Order $order)
    {
        // Check if user is part of this order (customer, EO, or vendor)
        $this->authorizeOrderAccess($order);

        $validated = $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $chat = OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => $validated['message']
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'message' => $chat->message,
                    'user_name' => $chat->user->name,
                    'user_role' => $chat->user->role,
                    'created_at' => $chat->created_at->format('H:i'),
                    'is_own' => $chat->user_id === Auth::id()
                ]
            ]);
        }

        return back()->with('success', 'Pesan terkirim');
    }

    public function getMessages(Order $order)
    {
        $this->authorizeOrderAccess($order);

        $chats = $order->chats()
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
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

    private function authorizeOrderAccess(Order $order)
    {
        $user = Auth::user();
        
        // Customer
        if ($order->user_id === $user->id) return;
        
        // EO
        if ($user->role === 'eo' && $order->eo_id === $user->eo_id) return;
        
        // Vendor - check if their product is in this order
        if ($user->role === 'vendor') {
            $vendorProductIds = $user->vendorProducts->pluck('id');
            $orderVendorIds = $order->vendors->pluck('id');
            if ($vendorProductIds->intersect($orderVendorIds)->isNotEmpty()) return;
        }
        
        abort(403, 'Anda tidak memiliki akses ke chat ini.');
    }
}