<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService 
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
        try {
            $response = $this->client->post('customers', [
                'json' => [
                    'name' => $data['name'],
                    'cpfCnpj' => $data['cpfCnpj'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    public function createBoletoPayment(array $data)
    {
        try {
            $response = $this->client->post('payments', [
                'json' => [
                    'billingType' => 'BOLETO',
                    'customer' => $data['asaas_id'],
                    'value' => $data['value'],
                    'description' => $data['description'] ?? 'Pagamento via sistema',
                    'dueDate' => now()->addDays(3)->format('Y-m-d'),
                    'daysAfterDueDateToRegistrationCancellation' => 1       
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }
    
    public function createPixPayment(array $data)
    {
        try {
            $response = $this->client->post('payments', [
                'json' => [
                    'billingType' => 'PIX',
                    'customer' => $data['asaas_id'],
                    'value' => $data['value'],
                    'description' => $data['description'] ?? 'Pagamento via sistema',
                    'dueDate' => now()->addDays(1)->format('Y-m-d'),
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    public function createCreditCardPayment(array $data)
    {
        try {

            $response = $this->client->post('payments', [
            'json' => [
                'billingType'=> 'CREDIT_CARD',
                'customer'=> $data['asaas_id'],
                'value'=>$data['value'],
                'dueDate'=> now()->addDays(1)->format('Y-m-d'),
                'description' => $data['description'] ?? 'Pagamento via sistema',
        
                'creditCard'=> [
                  'holderName'=> $data['creditCard']['holderName'],
                  'number'=> $data['creditCard']['number'],
                  'expiryMonth'=> $data['creditCard']['expiryMonth'],
                  'expiryYear'=> $data['creditCard']['expiryYear'],
                  'ccv'=> $data['creditCard']['ccv'],
                ],
                'creditCardHolderInfo'=> [
                  'name'=> $data['creditCardHolderInfo']['name'],
                  'cpfCnpj'=> $data['creditCardHolderInfo']['cpfCnpj'],
                  'postalCode'=> $data['creditCardHolderInfo']['postalCode'],
                  'addressNumber'=> $data['creditCardHolderInfo']['addressNumber'],
                  'email'=> $data['creditCardHolderInfo']['email'],
                  'phone'=> $data['creditCardHolderInfo']['phone'],
                ],
                'remoteIp'=> request()->ip(),
            ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            if ($e->getResponse()) {
            return json_decode($e->getResponse()->getBody(), true);
            }
            $this->handleException($e);
        }
    }

    public function getPixQrCode(string $paymentId): array
    {
        try {
            $response = $this->client->get("payments/{$paymentId}/pixQrCode", [
                'http_errors' => false
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    private function handleException(GuzzleException $e): void
    {
        Log::error('Asaas API Error: ' . $e->getMessage());
        throw new \RuntimeException('Erro temporário no processamento. Tente novamente mais tarde.', 503);
    }
}
