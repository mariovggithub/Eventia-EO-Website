<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;
    
    protected $table = 'jobs_listings';
    
    protected $fillable = ['eo_id', 'role', 'slots', 'image'];

    public function eventOrganizer() {
        return $this->belongsTo(EventOrganizer::class, 'eo_id');
    }
    
    public function applications() {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
}