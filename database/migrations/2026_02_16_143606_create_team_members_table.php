<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->enum('role_type', ['OPERASIONAL', 'TUTOR'])->default('OPERASIONAL');
            $table->string('photo');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('role_type');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
