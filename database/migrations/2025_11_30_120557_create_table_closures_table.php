<?php

use App\Models\Table;
use App\Models\User;
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
        if (Schema::hasTable('table_closures')) {
            Schema::dropIfExists('table_closures');
        }
        
        Schema::create('table_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Table::class, 'table_booking_id')->constrained('table_bookings')->cascadeOnDelete();
            $table->decimal('hookahs_amount', 12, 2)->default(0);
            $table->decimal('tips_amount', 12, 2)->default(0);
            $table->decimal('sales_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->enum('discount_type', ['rub', 'percent'])->default('rub');
            $table->enum('payment_method', ['cash', 'card']);
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->timestamp('closed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_closures');
    }
};
