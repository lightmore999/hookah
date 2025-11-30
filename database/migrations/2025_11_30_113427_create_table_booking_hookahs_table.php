<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_booking_hookahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_booking_id')->constrained('table_bookings')->onDelete('cascade');
            $table->foreignId('hookah_id')->constrained('hookahs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_booking_hookahs');
    }
};
