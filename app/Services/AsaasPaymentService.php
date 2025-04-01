<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService implements PaymentGatewayInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.asaas.base_url'),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => config('services.asaas.api_key'),
            ],
            'timeout' => 30,
            'verify' => storage_path('certs/cacert.pem'),
        ]);
    }

    public function createCustomer(array $data): array
    {
        $response = $this->client->post('customers', [
            'json' => [
                'name' => $data['name'],
                'cpfCnpj' => $data['cpfCnpj'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function createPayment(array $data): array
    {
        try {
            $response = $this->client->post('payments', [
                'json' => $this->formatPaymentData($data),
                'http_errors' => false
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    public function getPixQrCode(string $paymentId): array
    {
        try {
            $response = $this->client->get("payments/{$paymentId}/pixQrCode", [
                'http_errors' => false
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    private function formatCustomerData(array $data): array
    {
        return [
            'name' => $data['name'],
            'cpfCnpj' => $data['cpfCnpj'],
            'email' => $data['email'],
            'mobilePhone' => $data['mobilePhone'],
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['addressNumber'] ?? null,
            'complement' => $data['addressComplement'] ?? null,
            'province' => $data['province'] ?? null,
            'postalCode' => $data['postalCode'] ?? null,
        ];
    }

    private function formatPaymentData(array $data): array
    {
        $paymentData = [
            'customer' => $data['customer'],
            'billingType' => $data['billingType'],
            'value' => $data['value'],
            'dueDate' => $data['dueDate'],
            'description' => $data['description'] ?? 'Pagamento via sistema',
            'externalReference' => $data['externalReference'] ?? null,
        ];

        if ($data['billingType'] === 'CREDIT_CARD') {
            $paymentData['creditCard'] = $data['creditCard'];
            $paymentData['creditCardHolderInfo'] = $data['creditCardHolderInfo'];
            $paymentData['remoteIp'] = request()->ip();
        }

        if ($data['billingType'] === 'BOLETO') {
            $paymentData['daysAfterDueDateToRegistrationCancellation'] = 3;
        }

        if ($data['billingType'] === 'PIX') {
            $paymentData['daysAfterDueDateToRegistrationCancellation'] = 1;
        }

        return $paymentData;
    }

    private function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $content = json_decode($response->getBody()->getContents(), true);

        if ($statusCode >= 400) {
            throw new \RuntimeException(
                $content['errors'][0]['description'] ?? 'Erro na comunicação com o gateway de pagamento',
                $statusCode
            );
        }

        return $content;
    }

    private function handleException(GuzzleException $e): void
    {
        Log::error('Asaas API Error: ' . $e->getMessage());
        throw new \RuntimeException('Erro temporário no processamento. Tente novamente mais tarde.', 503);
    }

}
