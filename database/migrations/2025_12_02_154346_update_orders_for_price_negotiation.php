<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            // Remove price from order creation
            $table->decimal('total', 15, 2)->default(0)->nullable()->change();
            
            // Add negotiated price from EO
            $table->decimal('negotiated_price', 15, 2)->nullable()->after('total');
            $table->text('price_breakdown')->nullable()->after('negotiated_price');
            $table->boolean('price_agreed')->default(false)->after('price_breakdown');
            $table->timestamp('price_agreed_at')->nullable()->after('price_agreed');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['negotiated_price', 'price_breakdown', 'price_agreed', 'price_agreed_at']);
        });
    }
};