<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'eo', 'vendor'])->default('user')->after('password');
            $table->foreignId('eo_id')->nullable()->constrained('event_organizers')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['eo_id']);
            $table->dropColumn(['role', 'eo_id']);
        });
    }
};