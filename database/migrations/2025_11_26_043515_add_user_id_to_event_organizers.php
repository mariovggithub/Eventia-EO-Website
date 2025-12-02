<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('event_organizers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(false)->after('price_max');
            $table->string('phone')->nullable()->after('description');
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->text('portfolio')->nullable()->after('city');
            $table->integer('experience_years')->default(0)->after('portfolio');
        });
    }

    public function down(): void {
        Schema::table('event_organizers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_active', 'phone', 'address', 'city', 'portfolio', 'experience_years']);
        });
    }
};