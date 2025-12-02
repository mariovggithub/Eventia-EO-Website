<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rating;
use App\Models\EventOrganizer;
use App\Models\VendorProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, Order $order)
    {
        // Check authorization
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak dapat memberi rating untuk order ini.');
        }

        // Check if order is completed
        if (!$order->canRate()) {
            return back()->with('error', 'Rating hanya dapat diberikan setelah event selesai.');
        }

        // Check if already rated
        if ($order->hasBeenRatedBy(Auth::id())) {
            return back()->with('error', 'Anda sudah memberikan rating untuk order ini.');
        }

        $validated = $request->validate([
            'eo_rating' => 'nullable|integer|min:1|max:5',
            'eo_review' => 'nullable|string|max:500',
            'vendors' => 'nullable|array',
            'vendors.*.id' => 'required|exists:vendor_products,id',
            'vendors.*.rating' => 'required|integer|min:1|max:5',
            'vendors.*.review' => 'nullable|string|max:500'
        ]);

        // Rate EO
        if (!$order->self_organized && $order->eo_id && !empty($validated['eo_rating'])) {
            Rating::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'rateable_type' => EventOrganizer::class,
                'rateable_id' => $order->eo_id,
                'rating' => $validated['eo_rating'],
                'review' => $validated['eo_review'] ?? null
            ]);
        }

        // Rate Vendors
        if (!empty($validated['vendors'])) {
            foreach ($validated['vendors'] as $vendorData) {
                Rating::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'rateable_type' => VendorProduct::class,
                    'rateable_id' => $vendorData['id'],
                    'rating' => $vendorData['rating'],
                    'review' => $vendorData['review'] ?? null
                ]);
            }
        }

        // Mark order as rated
        $order->update(['status' => 'completed']);

        return back()->with('success', 'Terima kasih atas rating Anda!');
    }
}