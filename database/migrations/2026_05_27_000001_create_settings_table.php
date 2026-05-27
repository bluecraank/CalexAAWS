<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'logo_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('logo_url')->nullable();
            $table->string('logo_path')->nullable();
        });
    }
};
