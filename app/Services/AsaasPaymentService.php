<?php

namespace App\Services;

use App\Services\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de integração com a API do Asaas para gerenciamento de pagamentos.
 */
class AsaasPaymentService 
{
    /**
     * @var Client Cliente HTTP para comunicação com a API do Asaas.
     */
    private Client $client;

    /**
     * Construtor da classe.
     * Inicializa o cliente HTTP com as configurações necessárias.
     */
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

    /**
     * Cria um cliente na API do Asaas.
     *
     * @param array $data Dados do cliente (nome, CPF/CNPJ, email, telefone).
     * @return array Resposta da API com os dados do cliente criado.
     * @throws \RuntimeException Em caso de erro na comunicação com a API.
     */
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

    /**
     * Cria um pagamento por boleto na API do Asaas.
     *
     * @param array $data Dados do pagamento (ID do cliente, valor, descrição).
     * @return array Resposta da API com os dados do boleto criado.
     * @throws \RuntimeException Em caso de erro na comunicação com a API.
     */
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

    /**
     * Cria um pagamento por PIX na API do Asaas.
     *
     * @param array $data Dados do pagamento (ID do cliente, valor, descrição).
     * @return array Resposta da API com os dados do pagamento PIX criado.
     * @throws \RuntimeException Em caso de erro na comunicação com a API.
     */
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

    /**
     * Cria um pagamento por cartão de crédito na API do Asaas.
     *
     * @param array $data Dados do pagamento e do cartão de crédito.
     * @return array Resposta da API com os dados do pagamento criado.
     * @throws \RuntimeException Em caso de erro na comunicação com a API.
     */
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

    /**
     * Obtém o QR Code de um pagamento PIX na API do Asaas.
     *
     * @param string $paymentId ID do pagamento.
     * @return array Resposta da API com os dados do QR Code.
     * @throws \RuntimeException Em caso de erro na comunicação com a API.
     */
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

    /**
     * Trata exceções lançadas durante a comunicação com a API do Asaas.
     *
     * @param GuzzleException $e Exceção capturada.
     * @throws \RuntimeException Lança uma exceção genérica para o sistema.
     */
    private function handleException(GuzzleException $e): void
    {
        Log::error('Asaas API Error: ' . $e->getMessage());
        throw new \RuntimeException('Erro temporário no processamento. Tente novamente mais tarde.', 503);
    }
}
