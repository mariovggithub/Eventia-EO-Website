<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            // Fitur 1: User bisa handle sendiri (tanpa EO)
            $table->boolean('self_organized')->default(false)->after('eo_id');
            $table->foreignId('eo_id')->nullable()->change(); // Make EO optional
            
            // Fitur 3: Status approval dari EO
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            
            // Payment status
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->after('approved_at');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['self_organized', 'approval_status', 'rejection_reason', 'approved_at', 'payment_status', 'paid_at']);
        });
    }
};