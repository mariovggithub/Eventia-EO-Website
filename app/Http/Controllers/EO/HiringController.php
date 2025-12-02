<?php
namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\EventOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HiringController extends Controller
{
    public function index() {
        $user = Auth::user();
        $eoId = $user->eo_id ?? EventOrganizer::first()?->id ?? 1;
        $jobs = JobListing::where('eo_id', $eoId)->with('applications')->get();
        return view('eo.hiring', compact('jobs'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'role' => 'required|string|max:255',
            'slots' => 'required|integer|min:1',
            'image' => 'nullable|string'
        ]);

        $user = Auth::user();
        $eoId = $user->eo_id ?? EventOrganizer::first()?->id ?? 1;

        JobListing::create([
            'eo_id' => $eoId,
            'role' => $validated['role'],
            'slots' => $validated['slots'],
            'image' => $validated['image'] ?? 'https://placehold.co/400x200/925E30/fff?text=Job'
        ]);

        return back()->with('success', 'Lowongan berhasil dibuat!');
    }

    public function destroy(JobListing $job) {
        $job->delete();
        return back()->with('success', 'Lowongan berhasil dihapus.');
    }
}