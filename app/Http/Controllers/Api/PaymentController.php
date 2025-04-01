<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\AsaasPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        private readonly AsaasPaymentService $paymentService
    ) {}

    public function processPayment(PaymentRequest $request): JsonResponse
    {
            dd('asdasdasdasdasd');

        try {

            $data = $request->validated();

            // Verifica ou cria o cliente
            $customer = $this->findOrCreateCustomer($data['customer']);

            // Define a data de vencimento conforme o método de pagamento
            $data['dueDate'] = $this->getDueDate($data['billingType']);

            $paymentData = [
                'customer' => $customer->asaas_id,
                'billingType' => $data['billingType'],
                'value' => $data['value'],
                'dueDate' => $this->getDueDate($data['billingType']),
                'description' => $data['description'] ?? null
            ];
            $payment = $this->paymentService->createPayment($paymentData);
            // Se for PIX, obtém o QR Code
            if ($data['billingType'] === 'PIX') {
                $pixData = $this->paymentService->getPixQrCode($payment['id']);
                $payment['pixQrCode'] = $pixData['encodedImage'];
                $payment['pixPayload'] = $pixData['payload'];
            }

            // Persiste localmente
            $localPayment = $this->savePayment($customer, $payment, $data);

            return response()->json([
                'success' => true,
                'data' => new PaymentResource($localPayment),
                'redirect_url' => route('payment.thank-you', $localPayment->id),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $this->getFriendlyErrorMessage($e),
                'error_code' => $e->getCode(),
            ], $e->getCode() >= 400 && $e->getCode() < 500 ? $e->getCode() : 400);
        }
    }

    private function findOrCreateCustomer(array $customerData): Customer
    {
       $customer = Customer::where('cpf_cnpj', $customerData['cpfCnpj'])->first();
        if ($customer) {
            return $customer;
        }

        dd($customerData);
        // Cria no Asaas
        $asaasCustomer = $this->paymentService->createCustomer([
            'name' => $customerData['name'],
            'cpfCnpj' => $customerData['cpfCnpj'],
            'email' => $customerData['email'],
            'mobilePhone' => $customerData['mobilePhone'],
        ]);
        dd($asaasCustomer);

        // Salva localmente
        return Customer::create([
            'asaas_id' => $asaasCustomer['id'],
            'name' => $asaasCustomer['name'],
            'cpf_cnpj' => $asaasCustomer['cpfCnpj'],
            'email' => $asaasCustomer['email'],
            'mobile_phone' => $asaasCustomer['mobilePhone'] ?? $asaasCustomer['phone'],
            'postal_code' => $asaasCustomer['postalCode'],
            'address' => $asaasCustomer['address'],
            'address_number' => $asaasCustomer['addressNumber'],
            'address_complement' => $asaasCustomer['complement'],
            'metadata' => json_encode($asaasCustomer)
        ]);
    }

    private function getDueDate(string $billingType): string
    {
        return now()
            ->addDays($billingType === 'BOLETO' ? 3 : 1)
            ->format('Y-m-d');
    }

    private function savePayment(Customer $customer, array $asaasPayment, array $requestData): Payment
    {
        return Payment::create([
            'external_id' => $asaasPayment['id'],
            'customer_id' => $customer->id,
            'payment_method' => strtolower($asaasPayment['billingType']),
            'amount' => $asaasPayment['value'],
            'due_date' => $asaasPayment['dueDate'],
            'status' => $asaasPayment['status'],
            'description' => $requestData['description'] ?? null,
            'external_reference' => $requestData['externalReference'] ?? null,
            'invoice_url' => $asaasPayment['invoiceUrl'] ?? null,
            'bank_slip_url' => $asaasPayment['bankSlipUrl'] ?? null,
            'pix_qr_code' => $asaasPayment['pixQrCode'] ?? null,
            'pix_payload' => $asaasPayment['pixPayload'] ?? null,
            'metadata' => [
                'asaas_response' => $asaasPayment,
            ],
        ]);
    }

    private function getFriendlyErrorMessage(\Exception $e): string
    {
        return match ($e->getCode()) {
            400, 422 => $e->getMessage(),
            401 => 'Erro de autenticação com o gateway de pagamento',
            403 => 'Sem permissão para realizar esta operação',
            404 => 'Recurso não encontrado',
            429 => 'Muitas requisições. Por favor, aguarde um momento',
            503 => 'Serviço temporariamente indisponível',
            default => 'Ocorreu um erro ao processar seu pagamento. Por favor, tente novamente mais tarde.',
        };
    }

}
