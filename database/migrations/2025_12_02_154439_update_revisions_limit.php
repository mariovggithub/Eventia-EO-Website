<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('order_revisions', function (Blueprint $table) {
            $table->integer('revision_count')->default(0)->after('order_id');
        });
    }

    public function down(): void {
        Schema::table('order_revisions', function (Blueprint $table) {
            $table->dropColumn('revision_count');
        });
    }
};