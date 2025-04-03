<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Classe PaymentRequest
 *
 * Esta classe lida com as regras de validação para requisições de pagamento.
 * Ela garante que os dados de entrada estejam no formato e valores esperados.
 *
 * @package App\Http\Requests
 */
class PaymentRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool Retorna true se a requisição for autorizada, false caso contrário.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array Um array com as regras de validação.
     */
    public function rules(): array
    {
        $rules = [
            'customer.name' => 'required|string|max:255',
            'customer.cpfCnpj' => 'required|string|max:20',
            'customer.email' => 'required|email|max:255',
            'customer.phone' => 'required|string|max:20',
            
            'billingType' => ['required', Rule::in(['CREDIT_CARD', 'BOLETO', 'PIX'])],
            'value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ];

        if ($this->input('billingType') === 'CREDIT_CARD') {
            $rules = array_merge($rules, [
                'creditCard.holderName' => 'required|string|max:255',
                'creditCard.number' => 'required|string|size:16',
                'creditCard.expiryMonth' => 'required|string|size:2',
                'creditCard.expiryYear' => 'required|string|size:4',
                'creditCard.ccv' => 'required|string|min:3|max:4',

                'creditCardHolderInfo.name' => 'required|string|max:255',
                'creditCardHolderInfo.cpfCnpj' => 'required|string|max:20',
                'creditCardHolderInfo.email' => 'required|email|max:255',
                'creditCardHolderInfo.postalCode' => 'required|string',
                'creditCardHolderInfo.addressNumber' => 'required|string|max:10',
                'creditCardHolderInfo.phone' => 'required|string|max:20',
            ]);
        }

        return $rules;
    }

    /**
     * Obtém as mensagens de validação personalizadas para a requisição.
     *
     * @return array Um array com as mensagens de validação personalizadas.
     */
    public function messages(): array
    {
        return [
            'billingType.in' => 'O tipo de pagamento deve ser CREDIT_CARD, BOLETO ou PIX',
            'creditCard.ccv.min' => 'O código de segurança (CCV) deve ter no mínimo 3 dígitos',
        ];
    }
}
