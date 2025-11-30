<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_booking_id',
        'hookahs_amount',
        'tips_amount',
        'sales_amount',
        'discount_amount',
        'discount_type',
        'payment_method',
        'employee_id',
        'comment',
        'total_amount',
        'closed_at',
    ];

    protected $casts = [
        'hookahs_amount' => 'decimal:2',
        'tips_amount' => 'decimal:2',
        'sales_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function tableBooking()
    {
        return $this->belongsTo(Table::class, 'table_booking_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
