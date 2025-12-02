<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jobs_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eo_id')->constrained('event_organizers')->cascadeOnDelete();
            $table->string('role');
            $table->integer('slots')->default(1);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('jobs_listings');
    }
};