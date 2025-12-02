<?php
namespace App\Http\Controllers;

use App\Models\EventType;
use App\Models\EventOrganizer;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function create() {
        $eventTypes = EventType::all();
        // Only show active EOs
        $eos = EventOrganizer::where('is_active', true)->get();
        $vendorCategories = VendorCategory::with('products')->get();
        return view('order.create', compact('eventTypes', 'eos', 'vendorCategories'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'self_organized' => 'required|boolean',
            'eo_id' => 'required_if:self_organized,0|nullable|exists:event_organizers,id',
            'event_date' => 'required|date|after:today',
            'capacity' => 'required|integer|min:1',
            'vendor_choice' => 'required|in:package,ala',
            'vendors' => 'nullable|array',
            'vendors.*' => 'exists:vendor_products,id'
        ]);

        // Calculate total
        $total = 0;
        
        // If not self-organized, add EO price
        if (!$validated['self_organized'] && $validated['eo_id']) {
            $eo = EventOrganizer::find($validated['eo_id']);
            $total += $eo->price_min;
        }

        // Add vendor prices if A la Carte
        if ($validated['vendor_choice'] === 'ala' && !empty($validated['vendors'])) {
            $vendorTotal = VendorProduct::whereIn('id', $validated['vendors'])->sum('price');
            $total += $vendorTotal;
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'event_type_id' => $validated['event_type_id'],
            'eo_id' => $validated['self_organized'] ? null : $validated['eo_id'],
            'self_organized' => $validated['self_organized'],
            'event_date' => $validated['event_date'],
            'capacity' => $validated['capacity'],
            'vendor_choice' => $validated['vendor_choice'],
            'status' => 'booked',
            'approval_status' => $validated['self_organized'] ? 'approved' : 'pending',
            'approved_at' => $validated['self_organized'] ? now() : null,
            'payment_status' => 'unpaid',
            'total' => $total
        ]);

        if ($validated['vendor_choice'] === 'ala' && !empty($validated['vendors'])) {
            $order->vendors()->attach($validated['vendors']);
        }

        return redirect()->route('order.my-orders')->with('success', 'Pesanan berhasil dibuat!');
    }

    public function show(Order $order) {
        // Authorization check
        $user = Auth::user();
        $canAccess = false;

        // Customer can access their own orders
        if ($order->user_id === $user->id) {
            $canAccess = true;
        }
        
        // EO can access orders assigned to them
        if ($user->role === 'eo' && $order->eo_id === $user->eo_id) {
            $canAccess = true;
        }
        
        // Vendor can access if their product is in the order
        if ($user->role === 'vendor') {
            $vendorProductIds = $user->vendorProducts->pluck('id');
            $orderVendorIds = $order->vendors->pluck('id');
            if ($vendorProductIds->intersect($orderVendorIds)->isNotEmpty()) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }

        $order->load(['eventType', 'eventOrganizer', 'vendors.category', 'chats.user', 'revisions.user']);
        
        return view('order.show', compact('order'));
    }

    public function myOrders() {
        $orders = Order::where('user_id', Auth::id())
            ->with(['eventType', 'eventOrganizer', 'vendors'])
            ->latest()
            ->paginate(10);
        
        return view('order.my-orders', compact('orders'));
    }

    public function showPayment(Order $order) {
        if ($order->user_id !== Auth::id()) abort(403);
        
        if (!$order->isApproved()) {
            return redirect()->route('order.show', $order)
                ->with('error', 'Order belum disetujui oleh EO.');
        }
        
        if ($order->isPaid()) {
            return redirect()->route('order.show', $order)
                ->with('info', 'Order sudah dibayar.');
        }
        
        return view('order.payment', compact('order'));
    }

    public function processPayment(Request $request, Order $order) {
        if ($order->user_id !== Auth::id()) abort(403);
        
        $request->validate([
            'payment_method' => 'required|in:bank_transfer,credit_card,e_wallet'
        ]);

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'ongoing'
        ]);

        // Send chat notification
        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'message' => '✅ Pembayaran telah berhasil dikonfirmasi. Total: ' . $order->formatted_total
        ]);

        return redirect()->route('order.show', $order)
            ->with('success', 'Pembayaran berhasil! Order Anda sedang diproses.');
    }
}