<?php
namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index() {
        $jobs = JobListing::with('eventOrganizer')->get();
        return view('jobs.index', compact('jobs'));
    }

    public function apply(Request $request, JobListing $job) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'experience' => 'nullable|string'
        ]);

        JobApplication::create([
            'job_id' => $job->id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'experience' => $validated['experience']
        ]);

        return back()->with('success', 'Lamaran berhasil dikirim!');
    }
}