<?php
require_once __DIR__.'/env.php';
require_once __DIR__.'/ilp_client.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$in  = $raw ? json_decode($raw, true) : $_POST;

$wallet   = trim($in['wallet'] ?? '');
$amount   = isset($in['amount']) ? floatval($in['amount']) : 0;
$currency = strtoupper(trim($in['currency'] ?? ASSET_CODE));
$ref      = trim($in['ref'] ?? '');

if ($wallet === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'missing_wallet']); exit; }
if ($amount <= 0)   { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'amount']); exit; }
if ($currency !== ASSET_CODE) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'asset_mismatch']); exit; }

try {
  // 1) Resolver Wallet Address a partir de Payment Pointer o URL directa
  if ($wallet[0] === '$') {
    $wa = discover_wallet_address($wallet);
    $walletAddressId = $wa['wallet_address_id'] ?? ($wa['id'] ?? '');
    if (!$walletAddressId) {
      http_response_code(500);
      echo json_encode(['ok'=>false,'error'=>'discovery_failed','detail'=>$wa]);
      exit;
    }
  } elseif (stripos($wallet, 'http://') === 0 || stripos($wallet, 'https://') === 0) {
    $walletAddressId = rtrim($wallet, '/');
  } else {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'wallet_format','detail'=>'Usa $host/usuario o https://…/wallet-addresses/{id}']);
    exit;
  }

  // 2) Token del MERCHANT (client_credentials)
  $token = oauth_token(MERCHANT_AUTH_ISSUER, MERCHANT_CLIENT_ID, MERCHANT_CLIENT_SECRET);

  // 3) Crear Incoming Payment
  $incomingUrl = $walletAddressId.'/incoming-payments';
  $body = [
    'incomingAmount' => [
      'value'      => (string) intval(round($amount * (10 ** ASSET_SCALE))),
      'assetCode'  => ASSET_CODE,
      'assetScale' => ASSET_SCALE
    ],
    'description' => $ref ?: 'K’ab’ Pay charge'
  ];

  [$status, $json, $rawRes] = http_json('POST', $incomingUrl, [
    'Authorization: Bearer '.$token,
    'Accept: application/openpayments+json',
    'Content-Type: application/openpayments+json'
  ], $body);

  if ($status < 200 || $status >= 300 || empty($json['id'])) {
    http_response_code(500);
    echo json_encode([
      'ok'    => false,
      'error' => 'incoming_failed',
      'status'=> $status,
      'detail'=> $json ?: $rawRes
    ]);
    exit;
  }

  $incomingPaymentId = $json['id']; // URL para el QR

  echo json_encode([
    'ok' => true,
    'incomingPayment' => $incomingPaymentId,
    'display' => [
      'amount'   => number_format($amount, 2),
      'currency' => ASSET_CODE,
      'ref'      => $ref
    ],
    'qr' => $incomingPaymentId
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'exception','message'=>$e->getMessage()]);
}
