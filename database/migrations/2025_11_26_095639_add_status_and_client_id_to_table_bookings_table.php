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
        if (!Schema::hasColumn('table_bookings', 'client_id')) {
            Schema::table('table_bookings', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->onDelete('set null');
            });
        }
        
        if (!Schema::hasColumn('table_bookings', 'status')) {
            Schema::table('table_bookings', function (Blueprint $table) {
                $table->enum('status', ['not_opened', 'opened', 'closed'])->default('not_opened')->after('comment');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('table_bookings', 'client_id')) {
            Schema::table('table_bookings', function (Blueprint $table) {
                try {
                    $table->dropForeign(['client_id']);
                } catch (\Exception $e) {
                    // Внешний ключ может не существовать, игнорируем ошибку
                }
                $table->dropColumn('client_id');
            });
        }
        
        if (Schema::hasColumn('table_bookings', 'status')) {
            Schema::table('table_bookings', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
