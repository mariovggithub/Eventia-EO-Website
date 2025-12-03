<?php
namespace App\Http\Controllers;

use App\Models\EventType;
use App\Models\EventOrganizer;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function create(Request $request) {
        $eventTypes = EventType::all();
        $eos = EventOrganizer::where('is_active', true)->whereNotNull('user_id')->get();
        $vendorCategories = VendorCategory::with(['products' => function($query) {
            $query->orderBy('average_rating', 'desc');
        }])->get();
        
        $selectedEoId = $request->get('eo_id');
        
        return view('order.create', compact('eventTypes', 'eos', 'vendorCategories', 'selectedEoId'));
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

        // Validate event date is at least 14 days from now
        $eventDate = \Carbon\Carbon::parse($validated['event_date']);
        $daysFromNow = \Carbon\Carbon::now()->diffInDays($eventDate, false);
        
        if ($daysFromNow < 14) {
            return back()->withInput()->with('error', 'Tanggal event minimal 14 hari dari sekarang untuk memungkinkan revisi.');
        }

        // Create order without price
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
            'total' => null,
            'negotiated_price' => null
        ]);

        // Attach vendors if A la Carte
        if ($validated['vendor_choice'] === 'ala' && !empty($validated['vendors'])) {
            $order->vendors()->attach($validated['vendors']);
        }

        // Create initial chat message
        $vendorList = '';
        if ($validated['vendor_choice'] === 'ala' && !empty($validated['vendors'])) {
            $vendors = VendorProduct::whereIn('id', $validated['vendors'])->get();
            $vendorList = "\n\nVendor yang dipilih:\n";
            foreach ($vendors as $vendor) {
                $vendorList .= "• " . $vendor->name . " (" . $vendor->category->name . ")\n";
            }
        }

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => "📋 Pesanan baru dibuat!\n" .
                        "Event: " . $order->eventType->name . "\n" .
                        "Tanggal: " . $order->event_date->format('d F Y') . "\n" .
                        "Kapasitas: " . $order->capacity . " orang\n" .
                        "Vendor: " . ($validated['vendor_choice'] === 'package' ? 'Paket EO' : 'A la Carte') .
                        $vendorList
        ]);

        $successMessage = 'Pesanan berhasil dibuat! ';
        
        if ($validated['self_organized']) {
            $successMessage .= 'Silakan tunggu penawaran harga dari vendor.';
        } else {
            $successMessage .= 'Menunggu persetujuan dan penawaran harga dari EO.';
        }

        return redirect()->route('order.my-orders')->with('success', $successMessage);
    }

    public function show(Order $order) {
        $user = Auth::user();
        $canAccess = false;

        if ($order->user_id === $user->id) {
            $canAccess = true;
        }
        
        if ($user->role === 'eo' && $order->eo_id === $user->eo_id) {
            $canAccess = true;
        }
        
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

        $order->load([
            'eventType', 
            'eventOrganizer', 
            'vendors.category', 
            'chats.user', 
            'revisions.user',
            'ratings.rateable'
        ]);
        
        $canRate = $order->canRate() && !$order->hasBeenRatedBy($user->id);
        
        return view('order.show', compact('order', 'canRate'));
    }

    public function myOrders(Request $request) {
        $query = Order::where('user_id', Auth::id())
            ->with(['eventType', 'eventOrganizer', 'vendors']);

        $status = $request->get('status');
        if ($status) {
            if ($status === 'pending') {
                $query->where('approval_status', 'pending');
            } elseif ($status === 'approved') {
                $query->where('approval_status', 'approved')->where('payment_status', 'unpaid');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            }
        }
        
        $orders = $query->latest()->paginate(10);
        
        $statusCounts = [
            'all' => Order::where('user_id', Auth::id())->count(),
            'pending' => Order::where('user_id', Auth::id())->where('approval_status', 'pending')->count(),
            'approved' => Order::where('user_id', Auth::id())->where('approval_status', 'approved')->where('payment_status', 'unpaid')->count(),
            'paid' => Order::where('user_id', Auth::id())->where('payment_status', 'paid')->count(),
            'completed' => Order::where('user_id', Auth::id())->where('status', 'completed')->count(),
        ];
        
        return view('order.my-orders', compact('orders', 'statusCounts', 'status'));
    }

    public function agreePrice(Order $order) {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->negotiated_price || $order->negotiated_price <= 0) {
            return back()->with('error', 'Belum ada penawaran harga dari EO.');
        }

        if ($order->price_agreed) {
            return back()->with('info', 'Anda sudah menyetujui harga ini sebelumnya.');
        }

        $order->update([
            'price_agreed' => true,
            'price_agreed_at' => now(),
            'total' => $order->negotiated_price
        ]);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '✅ Customer menyetujui harga penawaran: ' . $order->formatted_total
        ]);

        return redirect()->route('order.payment', $order)
            ->with('success', 'Harga telah disetujui. Silakan lanjutkan pembayaran.');
    }

    public function rejectPrice(Request $request, Order $order) {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $order->update(['price_agreed' => false]);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '❌ Customer menolak harga penawaran. Alasan: ' . $validated['reason']
        ]);

        return back()->with('info', 'Penolakan harga telah dikirim ke EO. Silakan ajukan revisi atau diskusikan di chat.');
    }

    public function showPayment(Order $order) {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (!$order->isApproved()) {
            return redirect()->route('order.show', $order)
                ->with('error', 'Order belum disetujui oleh EO.');
        }

        if (!$order->negotiated_price || $order->negotiated_price <= 0) {
            return redirect()->route('order.show', $order)
                ->with('error', 'EO belum memberikan penawaran harga.');
        }

        if (!$order->price_agreed) {
            return redirect()->route('order.show', $order)
                ->with('error', 'Silakan setujui harga terlebih dahulu sebelum melakukan pembayaran.');
        }
        
        if ($order->isPaid()) {
            return redirect()->route('order.show', $order)
                ->with('info', 'Order sudah dibayar.');
        }
        
        return view('order.payment', compact('order'));
    }

    public function processPayment(Request $request, Order $order) {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,credit_card,e_wallet'
        ]);

        if (!$order->isApproved() || !$order->price_agreed || $order->isPaid()) {
            return back()->with('error', 'Tidak dapat memproses pembayaran.');
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'ongoing'
        ]);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '💰 Pembayaran berhasil dikonfirmasi!\n' .
                        'Metode: ' . strtoupper(str_replace('_', ' ', $validated['payment_method'])) . "\n" .
                        'Total: ' . $order->formatted_total
        ]);

        return redirect()->route('order.show', $order)
            ->with('success', 'Pembayaran berhasil! Order Anda sedang diproses.');
    }

    public function markCompleted(Order $order) {
        if ($order->user_id !== Auth::id() && !Auth::user()->role === 'eo') {
            abort(403);
        }

        if (!$order->isPaid()) {
            return back()->with('error', 'Order belum dibayar.');
        }

        $order->update(['status' => 'completed']);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '🎉 Event telah selesai! Silakan berikan rating untuk pengalaman Anda.'
        ]);

        return back()->with('success', 'Order ditandai sebagai selesai.');
    }

    public function cancel(Request $request, Order $order) {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->approval_status !== 'pending') {
            return back()->with('error', 'Order yang sudah disetujui tidak dapat dibatalkan.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $order->update([
            'approval_status' => 'rejected',
            'rejection_reason' => 'Dibatalkan oleh customer: ' . $validated['reason'],
            'status' => 'cancelled'
        ]);

        OrderChat::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'message' => '🚫 Order dibatalkan oleh customer. Alasan: ' . $validated['reason']
        ]);

        return redirect()->route('order.my-orders')->with('success', 'Order berhasil dibatalkan.');
    }

    public function getVendorsByCategory($categoryId) {
        $vendors = VendorProduct::where('vendor_category_id', $categoryId)
            ->orderBy('average_rating', 'desc')
            ->get();
            
        return response()->json($vendors);
    }
}