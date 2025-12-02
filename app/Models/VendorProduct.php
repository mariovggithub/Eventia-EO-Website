<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_category_id', 'user_id', 'name', 'price', 'quantity', 'image'];

    public function category() {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }
    public function vendor() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orders() {
        return $this->belongsToMany(Order::class, 'order_vendors');
    }
    
    public function getFormattedPriceAttribute() {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}