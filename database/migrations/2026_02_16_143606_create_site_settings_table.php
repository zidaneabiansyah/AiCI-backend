<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('AiCI');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('site_settings')->insert([
            'site_name' => 'AiCI',
            'address' => 'Jl. Contoh No. 123, Jakarta',
            'email' => 'info@aici.id',
            'phone' => '+62 21 1234567',
            'whatsapp' => '+62 812 3456 7890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
