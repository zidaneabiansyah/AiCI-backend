<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('quote');
            $table->string('photo')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
