<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;

/**
 * Classe responsável pelo processamento de pagamentos.
 */
class PaymentService
{
    /**
     * Serviço de integração com o Asaas.
     *
     * @var AsaasPaymentService
     */
    protected $asaasPaymentService;

    /**
     * Serviço de gerenciamento de clientes.
     *
     * @var CustomerService
     */
    protected $customerService;

    /**
     * Construtor da classe.
     *
     * @param AsaasPaymentService $asaasPaymentService Serviço de integração com o Asaas.
     * @param CustomerService $customerService Serviço de gerenciamento de clientes.
     */
    public function __construct(AsaasPaymentService $asaasPaymentService, CustomerService $customerService)
    {
        $this->asaasPaymentService = $asaasPaymentService;
        $this->customerService = $customerService;
    }

    /**
     * Processa o pagamento com base no tipo de cobrança.
     *
     * @param array $data Dados do pagamento.
     * @return array Dados relevantes para a próxima página.
     * @throws \RuntimeException Em caso de erro no processamento do pagamento.
     * @throws \InvalidArgumentException Se o tipo de pagamento não for suportado.
     */
    public function processPayment($data)
    {
        switch ($data['billingType']) {
            case 'BOLETO':
                try {
                    $asaasPaymentResponse = $this->asaasPaymentService->createBoletoPayment($data);
                    $formattedPayload = $this->buildPaymentPayload($asaasPaymentResponse);
                    $paymentRecord = $this->createPayment($formattedPayload);

                    return [
                        'billingType'  => $paymentRecord->payment_method,
                        'payment_id'   => $paymentRecord->id,
                        'bank_slip_url'=> $paymentRecord->bank_slip_url,
                    ];
                } catch (\Exception $e) {
                    throw new \RuntimeException('Erro ao processar pagamento via boleto: ' . $e->getMessage());
                }

            case 'PIX':
                try {
                    $paymentResponse = $this->asaasPaymentService->createPixPayment($data);
                    $pixData = $this->asaasPaymentService->getPixQrCode($paymentResponse['id']);
                    $paymentResponse = array_merge($paymentResponse, [
                        'pix_qr_code' => $pixData['encodedImage'],
                        'pix_payload' => $pixData['payload']
                    ]);
                    $formattedPayload = $this->buildPaymentPayload($paymentResponse);
                    $paymentRecord = $this->createPayment($formattedPayload);

                    return [
                        'billingType'   => $paymentRecord->payment_method,
                        'payment_id'    => $paymentRecord->id,
                        'pix_qr_code'   => $paymentRecord->pix_qr_code,
                        'pix_payload'   => $paymentRecord->pix_payload,
                    ];
                } catch (\Exception $e) {
                    throw new \RuntimeException('Erro ao processar pagamento via PIX: ' . $e->getMessage());
                }

            case 'CREDIT_CARD':
                try {
                    $paymentResponse = $this->asaasPaymentService->createCreditCardPayment($data);

                    if (isset($paymentResponse['errors']) && is_array($paymentResponse['errors'])) {
                        $firstError = $paymentResponse['errors'][0]['description'] ?? 'Erro desconhecido';
                        return [
                            'billingType'   => 'CREDIT_CARD',
                            'error'   => true,
                            'message' => $firstError,
                        ];
                    }

                    $formattedPayload = $this->buildPaymentPayload($paymentResponse);
                    $paymentRecord = $this->createPayment($formattedPayload);

                    return [
                        'billingType'   => $paymentRecord->payment_method,
                        'payment_id'    => $paymentRecord->id,
                    ];
                } catch (\Exception $e) {
                    throw new \RuntimeException('Erro ao processar pagamento via cartão de crédito: ' . $e->getMessage());
                }

            default:
                throw new \InvalidArgumentException('Tipo de pagamento não suportado: ' . $data['billingType']);
        }
    }

    /**
     * Cria um registro de pagamento no banco de dados.
     *
     * @param array $payload Dados do pagamento.
     * @return Payment Registro de pagamento criado.
     * @throws \InvalidArgumentException Se o payload for inválido.
     */
    public function createPayment($payload)
    {
        $validator = Validator::make($payload, [
            'external_id'    => 'required|string|max:36',
            'customer_id'    => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'amount'         => 'required|numeric',
            'due_date'       => 'required|date',
            'status'         => 'required|string|max:255',
            'description'    => 'required|string|max:255',
            'invoice_url'    => 'required|url',
            'bank_slip_url'  => 'nullable|url',
            'pix_qr_code'    => 'nullable|string',
            'pix_payload'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Payload inválido para a função createPayment: ' . $validator->errors());
        }

        $customer = $this->customerService->getCustomerByExternalId($payload['customer_id']);

        $payment = Payment::create([
            'external_id' => $payload['external_id'],
            'customer_id' => $customer->id,
            'payment_method' => $payload['payment_method'],
            'amount' => $payload['amount'],
            'due_date' => $payload['due_date'],
            'status' => $payload['status'],
            'description' => $payload['description'],
            'invoice_url' => $payload['invoice_url'],
            'bank_slip_url' => $payload['bank_slip_url'],
            'pix_qr_code' => $payload['pix_qr_code'],
            'pix_payload' => $payload['pix_payload'],
        ]);

        return $payment;
    }

    /**
     * Constrói o payload do pagamento com base na resposta do Asaas.
     *
     * @param array $asaasResponse Resposta da API do Asaas.
     * @return array Payload formatado.
     */
    private function buildPaymentPayload(array $asaasResponse): array
    {
        return [
            'external_id'   => $asaasResponse['id'] ?? null,
            'customer_id'   => $asaasResponse['customer'] ?? null,
            'payment_method'=> $asaasResponse['billingType'] ?? null,
            'amount'        => $asaasResponse['value'] ?? 0.0,
            'due_date'      => $asaasResponse['dueDate'] ?? null,
            'status'        => $asaasResponse['status'] ?? null,
            'description'   => $asaasResponse['description'] ?? '',
            'invoice_url'   => $asaasResponse['invoiceUrl'] ?? '',
            'bank_slip_url' => $asaasResponse['bankSlipUrl'] ?? '',
            'pix_qr_code'   => $asaasResponse['pix_qr_code'] ?? '',
            'pix_payload'   => $asaasResponse['pix_payload'] ?? '',
        ];
    }
}