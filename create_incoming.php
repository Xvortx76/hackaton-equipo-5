<?php
require_once __DIR__.'/env.php';
require_once __DIR__.'/ilp_client.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$in  = $raw ? json_decode($raw, true) : $_POST;

$amount   = isset($in['amount']) ? floatval($in['amount']) : 0;
$currency = $in['currency'] ?? ASSET_CODE;
$ref      = trim($in['ref'] ?? '');

if ($amount <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'amount']); exit; }
if ($currency !== ASSET_CODE) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'asset_mismatch']); exit; }

try {
  // 1) Discovery del Wallet Address del comercio
  $wa = discover_wallet_address(MERCHANT_PAYMENT_POINTER);
  $walletAddressId = $wa['wallet_address_id'];
  $resourceBase    = MERCHANT_RESOURCE_BASE ?: ($wa['resource_base'] ?? '');

  // 2) Token del MERCHANT
  $token = oauth_token(MERCHANT_AUTH_ISSUER, MERCHANT_CLIENT_ID, MERCHANT_CLIENT_SECRET);

  // 3) Crear Incoming Payment
  $incomingUrl = rtrim($walletAddressId, '/').'/incoming-payments';
  $body = [
    'incomingAmount' => [
      'value'      => (string) intval(round($amount * (10 ** ASSET_SCALE))), // subunidades
      'assetCode'  => ASSET_CODE,
      'assetScale' => ASSET_SCALE
    ],
    'description' => $ref ?: 'K’ab’ Pay charge'
  ];

  [$status, $json] = http_json('POST', $incomingUrl, [
    'Authorization: Bearer '.$token
  ], $body);

  if ($status < 200 || $status >= 300) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'incoming_failed','detail'=>$json]);
    exit;
  }

  $incomingPaymentId = $json['id']; // ← esto va al QR

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
