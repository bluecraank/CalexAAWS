<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_events', function (Blueprint $table) {

            $table->string('ews_item_id')->nullable()->after('end');
            $table->string('ews_change_key')->nullable()->after('ews_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_events', function (Blueprint $table) {
            //
        });
    }
};
