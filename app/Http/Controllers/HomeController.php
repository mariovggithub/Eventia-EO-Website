<?php
namespace App\Http\Controllers;

use App\Models\EventOrganizer;
use App\Models\EventType;

class HomeController extends Controller
{
    public function index() {
        // Only show active EOs with complete profiles
        $eos = EventOrganizer::where('is_active', true)
            ->whereNotNull('user_id')
            ->get();
            
        $stats = [
            'projects' => \App\Models\Order::count() ?: 500,
            'rating' => 4.8,
            'vendors' => \App\Models\VendorProduct::count() ?: 120
        ];
        
        return view('layouts/home', compact('eos', 'stats'));
    }

    public function about() {
        return view('layouts/about');
    }
}