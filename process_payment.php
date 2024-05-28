<?php
header('Content-Type: application/json');
// Step 1: Require the library from your Composer vendor folder
require_once 'vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Step 2: Set production or sandbox access token
MercadoPagoConfig::setAccessToken("TEST-3797020972438326-052422-ce17294d3464630399bf2bb218a700b9-490976225");
// Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
// In case you want to test in your local machine first, set runtime enviroment to LOCAL
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Step 3: Initialize the API client
$client = new PaymentClient();

$json = file_get_contents('php://input');

// Decodifica os dados JSON para um array associativo
$data = json_decode($json, true);

try {

    // Step 4: Create the request array
    $request = [
        "transaction_amount"    => (float)$data['transaction_amount'],
        "issuer_id"             => $data['issuer_id'],
        "token"                 => $data['token'],
        "description"           => $data['description'],
        "installments"          => $data['installments'],
        "payment_method_id"     => $data['payment_method_id'],
        "payer" => [
            "email"          => $data['payer']['email'],
            "identification" => [
                "type" => $data['payer']['identification']['type'],
                "number" => $data['payer']['identification']['number'],
            ],
        ]
    ];

    // Step 5: Create the request options, setting X-Idempotency-Key
    $request_options = new RequestOptions();
    $idempotency_key = uniqid();
    $request_options->setCustomHeaders(["X-Idempotency-Key: $idempotency_key"]);

    // Step 6: Make the request
    $payment = $client->create($request, $request_options);

    echo json_encode($payment);
    exit;

    // Step 7: Handle exceptions
} catch (MPApiException $e) {
    $array = [
        "status"    => $e->getApiResponse()->getStatusCode(),
        "content"   => $e->getApiResponse()->getContent()
    ];
    echo json_encode($array);
    exit;
} catch (\Exception $e) {
    echo $e->getMessage();
}
