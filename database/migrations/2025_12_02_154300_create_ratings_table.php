<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who rates
            $table->string('rateable_type'); // EventOrganizer or VendorProduct
            $table->unsignedBigInteger('rateable_id');
            $table->integer('rating')->unsigned()->comment('1-5 stars');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->index(['rateable_type', 'rateable_id']);
            $table->unique(['order_id', 'user_id', 'rateable_type', 'rateable_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('ratings');
    }
};