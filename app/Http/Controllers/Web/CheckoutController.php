<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\CustomerService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class CheckoutController extends Controller
{
    protected $customerService;
    protected $paymentService;

    public function __construct(CustomerService $customerService, PaymentService $paymentService)
    {
        $this->customerService = $customerService;
        $this->paymentService = $paymentService;
    }

    public function processCheckout(PaymentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            $customer = $this->customerService->getOrCreateCustomer($validatedData['customer']);
        
            $validatedData['asaas_id'] = $customer->asaas_id;
        
            $paymentData = $this->paymentService->processPayment($validatedData);
            
            return $this->showThankYou($paymentData);
        } catch (\Exception $e) {
            $errorData = [
            'billingType'  => $validatedData['billingType'],
            'error'   => true,
            'message' => 'Ocorreu um problema ao processar seu pagamento. Por favor, tente novamente mais tarde.',
            ];
            return $this->showThankYou($errorData);
        }

    }
    public function showCheckoutForm()
    {
        return view('checkout');
    }
    

    public function showThankYou($data)
    {
        $validator = \Validator::make($data, [
            'billingType'  => ['required', 'in:BOLETO,PIX,CREDIT_CARD'],
            'payment_id'    => 'nullable|integer',
            'bank_slip_url' => 'nullable|url',
            'pix_qr_code'   => 'nullable|string',
            'pix_payload'   => 'nullable|string',
            'error'         => 'nullable|boolean',
            'message'       => 'nullable|string',
        ]);
        if ($validator->fails()) {
            throw new \InvalidArgumentException('Os dados fornecidos para a função showThankYou são inválidos e não podem ser exibidos na view "thank-you".');
        }        

        return view('thank-you', $data);
    }


}
