<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{

    public function createCustomer(array $data): array;
    public function createPayment(array $data): array;
    public function getPixQrCode(string $paymentId): array;


}
