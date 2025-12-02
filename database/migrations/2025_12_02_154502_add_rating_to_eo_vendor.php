<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('event_organizers', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0)->after('experience_years');
            $table->integer('total_ratings')->default(0)->after('average_rating');
            
            // Remove price fields from display
            $table->decimal('price_min', 15, 2)->default(0)->nullable()->change();
            $table->decimal('price_max', 15, 2)->default(0)->nullable()->change();
        });

        Schema::table('vendor_products', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0)->after('image');
            $table->integer('total_ratings')->default(0)->after('average_rating');
            
            // Remove price from vendor display
            $table->decimal('price', 15, 2)->default(0)->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('event_organizers', function (Blueprint $table) {
            $table->dropColumn(['average_rating', 'total_ratings']);
        });

        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropColumn(['average_rating', 'total_ratings']);
        });
    }
};