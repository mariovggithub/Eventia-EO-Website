<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'eo_id'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function eventOrganizer() {
        return $this->belongsTo(EventOrganizer::class, 'eo_id');
    }
    public function orders() {
        return $this->hasMany(Order::class);
    }
    public function vendorProducts() {
        return $this->hasMany(VendorProduct::class);
    }
    public function jobApplications() {
        return $this->hasMany(JobApplication::class);
    }
    
    public function isUser(){ 
        return $this->role === 'user'; }
    public function isEO() { 
        return $this->role === 'eo'; }
    public function isVendor() { 
        return $this->role === 'vendor'; }
}