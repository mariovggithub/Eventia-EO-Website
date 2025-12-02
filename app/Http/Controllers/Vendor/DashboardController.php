<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VendorProduct;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function orders() {
        $user = Auth::user();
        $productIds = VendorProduct::where('user_id', $user->id)->pluck('id');
        
        $orders = Order::whereHas('vendors', function($q) use ($productIds) {
            $q->whereIn('vendor_product_id', $productIds);
        })->with(['user', 'eventType', 'eventOrganizer', 'vendors'])->latest()->get();
        
        return view('vendor.orders', compact('orders', 'productIds'));
    }
}