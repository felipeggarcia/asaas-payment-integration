<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\CustomerService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

/**
 * Classe CheckoutController
 * 
 * Controlador responsável por gerenciar o processo de checkout, incluindo a criação de clientes,
 * processamento de pagamentos e exibição de páginas relacionadas ao checkout.
 */
class CheckoutController extends Controller
{
    /**
     * Serviço de gerenciamento de clientes.
     * 
     * @var CustomerService
     */
    protected $customerService;

    /**
     * Serviço de processamento de pagamentos.
     * 
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * Construtor da classe.
     * 
     * @param CustomerService $customerService Serviço de gerenciamento de clientes.
     * @param PaymentService $paymentService Serviço de processamento de pagamentos.
     */
    public function __construct(CustomerService $customerService, PaymentService $paymentService)
    {
        $this->customerService = $customerService;
        $this->paymentService = $paymentService;
    }

    /**
     * Processa o checkout do cliente.
     * 
     * @param PaymentRequest $request Requisição contendo os dados de pagamento.
     * @return RedirectResponse Retorna a página de agradecimento com os dados do pagamento.
     */
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

    /**
     * Exibe o formulário de checkout.
     * 
     * @return \Illuminate\View\View Retorna a view do formulário de checkout.
     */
    public function showCheckoutForm()
    {
        return view('checkout');
    }

    /**
     * Exibe a página de agradecimento com os dados do pagamento.
     * 
     * @param array $data Dados do pagamento ou erro.
     * @return \Illuminate\View\View Retorna a view de agradecimento.
     * 
     * @throws \InvalidArgumentException Caso os dados fornecidos sejam inválidos.
     */
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
