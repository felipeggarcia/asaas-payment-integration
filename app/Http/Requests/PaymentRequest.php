<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages(): array
    {
        return [
            'billingType.in' => 'O tipo de pagamento deve ser CREDIT_CARD, BOLETO ou PIX',
            'creditCard.ccv.min' => 'O CVV deve ter pelo menos 3 dígitos',
            'creditCard.ccv.max' => 'O CVV deve ter no máximo 4 dígitos',
            'required' => 'O campo :attribute é obrigatório',
            'numeric' => 'O campo :attribute deve ser um número',
        ];
    }
}
