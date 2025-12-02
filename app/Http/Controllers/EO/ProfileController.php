<?php
namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show() {
        $user = Auth::user();
        // Ensure $user is an Eloquent model instance
        $user = \App\Models\User::find($user->id);
        $eo = $user->eventOrganizer;
        
        // If EO doesn't exist, create one
        if (!$eo) {
            $eo = EventOrganizer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'is_active' => false
            ]);
            
            $user->eo_id = $eo->id;
            $user->save();
        }
        
        return view('eo.profile', compact('eo'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $eo = $user->eventOrganizer;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'price_min' => 'required|numeric|min:0',
            'price_max' => 'required|numeric|min:0|gte:price_min',
            'portfolio' => 'nullable|string|max:2000',
            'experience_years' => 'nullable|integer|min:0',
            'logo' => 'nullable|string|max:500'
        ]);

        // Activate profile after first complete update
        if ($eo->isProfileComplete() || 
            (!empty($validated['name']) && !empty($validated['description']) && !empty($validated['phone']) && $validated['price_min'] > 0)) {
            $validated['is_active'] = true;
        }

        $eo->update($validated);

        return back()->with('success', 'Profile berhasil diperbarui!');
    }
}