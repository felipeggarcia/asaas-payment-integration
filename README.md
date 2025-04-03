<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

#  Projeto Laravel – Instalação e Configuração  

## Pré-requisitos

Certifique-se de ter os seguintes softwares instalados:

- **PHP**: 8.2.28 (CLI)
- **MariaDB**: 10.4.32
- **Laravel**: 12.5.0
- **Composer**: Instalado globalmente para gerenciar dependências do Laravel
- **XAMPP**: Usado como servidor SQL para o banco de dados

Eu usei basicamente um servidor de SQL pelo XAMPP e o Laravel foi instalado através do Composer.

---

##  **Passos para Configuração**  

### 1 -  Clone o repositório  
```bash
git clone https://github.com/felipeggarcia/asaas-payment-integration.git
cd asaas-payment-integration
```

### 2 - Instale as dependências do PHP  
```bash
composer install
```

### 3️ - Configure o ambiente  
Crie o arquivo **.env** com base no modelo:  
```bash
cp .env.example .env
```
Edite o arquivo `.env` e configure as credenciais do banco de dados:  
```

ASAAS_API_KEY= sua_chave
ASAAS_BASE_URL=https://sandbox.asaas.com/api/v3/

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=asaas_integration
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 4 - Gere a chave da aplicação  
```bash
php artisan key:generate
```

### 5 - Execute as migrations e seeders  
```bash
php artisan migrate --seed
```

### 6 - Suba o servidor  
```bash
php artisan serve
```
O projeto estará disponível em **http://127.0.0.1:8000** 

---
### Possíveis problemas com HTTPS (cacert.pem)

Se encontrar erros como `cURL error 60: SSL certificate problem`, pode ser necessário configurar um certificado CA no PHP. 

Eu coloquei no meu projeto, mas só é necessario para aquivos que vão para produção

1. Baixe o arquivo [cacert.pem do cURL](https://curl.se/ca/cacert.pem).
2. Copie o arquivo para um local seguro, como `C:\cacert.pem` (Windows) ou `/usr/local/share/cacert.pem` (Linux/macOS).
3. Edite o `php.ini` e adicione ou modifique esta linha:
```bash
curl.cainfo="C:\caminho\para\cacert.pem"
```


##  **Possíveis Erros e Soluções**  

| Erro | Solução |
|------|---------|
| `SQLSTATE[HY000] [1045] Access denied` | Verifique as credenciais do banco no `.env` |
| `No application encryption key has been specified.` | Execute `php artisan key:generate` |
| `Class not found` ao rodar migrations | Rode `composer dump-autoload` e tente novamente |

---


##  **Licença**  
Este projeto está sob a licença MIT.  

