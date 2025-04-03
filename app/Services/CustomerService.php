<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\AsaasPaymentService;
use Exception;

class CustomerService
{
    /**
     * Serviço de integração com o Asaas.
     *
     * @var AsaasPaymentService
     */
    protected $asaasPaymentService;

    /**
     * Construtor da classe CustomerService.
     *
     * @param AsaasPaymentService $asaasPaymentService Serviço de integração com o Asaas.
     */
    public function __construct(AsaasPaymentService $asaasPaymentService)
    {
        $this->asaasPaymentService = $asaasPaymentService;
    }

    /**
     * Obtém ou cria um cliente com base nos dados fornecidos.
     *
     * @param array $customerData Dados do cliente (nome, CPF/CNPJ, email, telefone, etc.).
     * @return Customer Cliente criado ou atualizado.
     * @throws Exception Caso ocorra algum erro ao criar ou atualizar o cliente.
     */
    public function getOrCreateCustomer($customerData)
    {
        try {
            $customer = Customer::where('cpf_cnpj', $customerData['cpfCnpj'])->first();

            if ($customer && $customer->asaas_id) {
                // Atualiza os dados básicos do cliente
                $customer->update([
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'],
                ]);
            } else {
                // Criar cliente no Asaas
                $asaasResponse = $this->asaasPaymentService->createCustomer($customerData);

                if (!empty($asaasResponse['id'])) {
                    $customer = Customer::updateOrCreate(
                        ['asaas_id' => $asaasResponse['id']],
                        [
                            'name' => $asaasResponse['name'],
                            'cpf_cnpj' => $asaasResponse['cpfCnpj'],
                            'email' => $asaasResponse['email'],
                            'phone' => $asaasResponse['phone']
                        ]
                    );
                }
            }

            if (!$customer) {
                throw new Exception('Não foi possível salvar o cliente no banco de dados.');
            }

            return $customer;
        } catch (Exception $e) {
            throw new Exception('Erro ao criar ou atualizar o cliente: ' . $e->getMessage());
        }
    }

    /**
     * Obtém um cliente com base no ID externo (ID do Asaas).
     *
     * @param string $customerAsaasId ID do cliente no Asaas.
     * @return Customer Cliente encontrado.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Caso o cliente não seja encontrado.
     */
    public function getCustomerByExternalId($customerAsaasId)
    {
        return Customer::where('asaas_id', $customerAsaasId)->firstOrFail();
    }
}
