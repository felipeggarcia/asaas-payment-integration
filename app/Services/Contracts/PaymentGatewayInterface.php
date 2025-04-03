<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{

    public function createCustomer(array $data): array;
    public function createBoletoPayment(array $data): array;
    public function createCreditCardPayment(array $data): array;
    public function createPixPayment(array $data): array;
    public function getPixQrCode(string $paymentId): array;


}
