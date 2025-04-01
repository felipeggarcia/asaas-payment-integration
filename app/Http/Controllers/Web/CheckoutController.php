<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Customer;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class CheckoutController extends Controller
{
    private PaymentGatewayInterface $asaasPaymentService; 

    public function __construct(PaymentGatewayInterface $asaasPaymentService)
    {
        $this->asaasPaymentService = $asaasPaymentService;
    }

    public function processCheckout(PaymentRequest $request)
    {
        dd('customer');
        $validatedData = $request->validated();
       // Verificar se o CPF/CNPJ já existe no banco
       $customer = Customer::where('cpf_cnpj', $validatedData['customer']['cpfCnpj'])->first();

       dd($customer);

       if ($customer && $customer['asaas_id']) {
           // Cliente já cadastrado no Asaas
           $customer->update([
                'email' => $validatedData['customer']['email'],
                'phone' => $validatedData['customer']['phone'],
            ]);
            $validatedData['asaas_id']  = $customer['asaas_id'];
       }else{
            $clienteData =$validatedData['customer'];
    
            $asaasResponse = $this->asaasPaymentService->createCustomer($clienteData);
            if (!empty($asaasResponse['id'])) {
                $custumer = Customer::updateOrCreate(
                    ['asaas_id' => $asaasResponse['id']],
                    ['name' => $validatedData['name']],
                    ['email' => $validatedData['email']],
                    ['phone' => $validatedData['phone']],
                    ['cpf_cnpj' => $validatedData['cpf_cnpj']],
                );
            }
            $validatedData['asaas_id']  = $asaasResponse['id'];
       }

    //    $response = Http::post(route('api.payment.process'), $validatedData);
    //    dd($response);
       
    }
    public function showCheckoutForm()
    {
        return view('checkout');
    }

    public function thankYou($paymentId)
    {
        $payment = Payment::with('customer')->findOrFail($paymentId);

        return view('thank-you', [
            'payment' => $payment,
            'paymentMethod' => $payment->payment_method,
            'customer' => $payment->customer,
        ]);
    }

    private function calculateDueDate(string $billingType): string
    {
        return match ($billingType) {
            'BOLETO' => Carbon::now()->addDays(3)->format('Y-m-d'),
            default => Carbon::now()->addDay()->format('Y-m-d'), // PIX e Cartão
        };
    }
    private function errorResponse($message, $statusCode = 400)
    {
        return response()->json([
            'error' => true,
            'message' => $message
        ], $statusCode);
    }

}
