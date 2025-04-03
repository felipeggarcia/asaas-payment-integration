<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Pagamento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.6/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                @if(isset($error) && $error)
                    {{-- Caso ocorra um erro --}}
                    <h2 class="text-danger">Ops! Algo deu errado.</h2>
                    <p>{{ $message }}</p>
                    <a href="/" class="btn btn-default">Voltar</a>
                @elseif($billingType == 'BOLETO')
                    {{-- Pagamento via Boleto --}}
                    <h2 class="text-success">Boleto gerado com sucesso!</h2>
                    <p>Agora é só pagar o boleto e aguardar a confirmação.</p>
                    <a href="{{ $bank_slip_url }}" class="btn btn-primary" target="_blank">Visualizar Boleto</a>
                @elseif($billingType == 'PIX')
                    {{-- Pagamento via PIX --}}
                    <h2 class="text-success">Pagamento via PIX gerado!</h2>
                    <p>Escaneie o QR Code ou copie o código abaixo:</p>
                    <img src="data:image/png;base64,{{ $pix_qr_code }}" alt="QR Code PIX" class="img-thumbnail" style="max-width: 300px;">
                    <div class="input-group" style="margin-top: 10px;">
                        <input type="text" id="pix-code" class="form-control" value="{{ $pix_payload }}" readonly>
                        <span class="input-group-btn">
                            <button class="btn btn-default" onclick="copyPixCode()">Copiar Código PIX</button>
                        </span>
                    </div>
                @elseif($billingType == 'CREDIT_CARD')
                    {{-- Pagamento via Cartão --}}
                    <h2 class="text-success">Pagamento aprovado!</h2>
                    <p>Seu pagamento foi processado com sucesso.</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyPixCode() {
            var copyText = document.getElementById("pix-code");
            copyText.select();
            document.execCommand("copy");
            alert("Código PIX copiado para a área de transferência!");
        }
    </script>
</body>
</html>
