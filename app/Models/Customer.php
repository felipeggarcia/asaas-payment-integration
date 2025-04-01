<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'asaas_id',
        'name',
        'cpf_cnpj',
        'email',
        'mobile_phone',
        'postal_code',
        'address',
        'address_number',
        'address_complement',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id', 'id');
    }
}
