<!-- resources/views/checkout.blade.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout de Pagamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Processamento de Pagamento</h3>
                </div>
                <div class="card-body">
                    <form id="paymentForm" action="{{ route('checkout.process') }}" method="POST">
                        @csrf

                        <!-- Dados do Cliente -->
                        <div class="mb-4">
                            <h5 class="mb-3">Dados Pessoais</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customerName" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="customerName" name="customer[name]" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="customerCpf" class="form-label">CPF/CNPJ</label>
                                    <input type="text" class="form-control" id="customerCpf" name="customer[cpfCnpj]" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="customerEmail" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="customerEmail" name="customer[email]" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="customerPhone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="customerPhone" name="customer[phone]" required>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Pagamento -->
                        <div class="mb-4">
                            <h5 class="mb-3">Informações do Pagamento</h5>
                            <div class="row g-3">
                                <div class="col-md12">
                                    <label for="paymentValue" class="form-label">Valor</label>
                                    <input type="number" step="0.01" class="form-control" id="paymentValue" name="value" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="description" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Método de Pagamento -->
                        <div class="mb-4">
                            <h5 class="mb-3">Método de Pagamento</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="billingType" id="creditCard" value="CREDIT_CARD" checked>
                                <label class="form-check-label" for="creditCard">
                                    Cartão de Crédito
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="billingType" id="boleto" value="BOLETO">
                                <label class="form-check-label" for="boleto">
                                    Boleto Bancário
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="billingType" id="pix" value="PIX">
                                <label class="form-check-label" for="pix">
                                    PIX
                                </label>
                            </div>
                        </div>

                        <!-- Seção Dinâmica para Cartão de Crédito -->
                        <div id="creditCardSection">
                            <div class="mb-4">
                                <h5 class="mb-3">Dados do Cartão</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="cardHolderName" class="form-label">Nome no Cartão</label>
                                        <input type="text" class="form-control" id="cardHolderName" name="creditCard[holderName]">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="cardNumber" class="form-label">Número do Cartão</label>
                                        <input type="text" class="form-control" id="cardNumber" name="creditCard[number]">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cardExpiryMonth" class="form-label">Mês</label>
                                        <input type="text" class="form-control" id="cardExpiryMonth" name="creditCard[expiryMonth]" placeholder="MM">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cardExpiryYear" class="form-label">Ano</label>
                                        <input type="text" class="form-control" id="cardExpiryYear" name="creditCard[expiryYear]" placeholder="YYYY">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="cardCvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cardCvv" name="creditCard[ccv]">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="mb-3">Dados do Titular</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="holderName" class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" id="holderName" name="creditCardHolderInfo[name]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="holderEmail" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="holderEmail" name="creditCardHolderInfo[email]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="holderCpf" class="form-label">CPF/CNPJ</label>
                                        <input type="text" class="form-control" id="holderCpf" name="creditCardHolderInfo[cpfCnpj]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="holderPostalCode" class="form-label">CEP</label>
                                        <input type="text" class="form-control" id="holderPostalCode" name="creditCardHolderInfo[postalCode]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="holderAddressNumber" class="form-label">Número</label>
                                        <input type="text" class="form-control" id="holderAddressNumber" name="creditCardHolderInfo[addressNumber]">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="holderPhone" class="form-label">Telefone</label>
                                        <input type="text" class="form-control" id="holderPhone" name="creditCardHolderInfo[phone]">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Finalizar Pagamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mostrar/ocultar seção do cartão de crédito
    document.querySelectorAll('input[name="billingType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const creditCardSection = document.getElementById('creditCardSection');
            creditCardSection.style.display = this.value === 'CREDIT_CARD' ? 'block' : 'none';

            // Tornar campos obrigatórios/opcionais conforme o método
            const creditCardInputs = creditCardSection.querySelectorAll('input, textarea, select');
            creditCardInputs.forEach(input => {
                input.required = this.value === 'CREDIT_CARD';
            });
        });
    });

    // Disparar evento change no carregamento para configurar estado inicial
    document.querySelector('input[name="billingType"]:checked').dispatchEvent(new Event('change'));
</script>
</body>
</html>
