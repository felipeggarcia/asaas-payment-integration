<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'external_id',
        'customer_id',
        'payment_method',
        'amount',
        'due_date',
        'status',
        'description',
        'metadata',
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code',
        'pix_payload',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
