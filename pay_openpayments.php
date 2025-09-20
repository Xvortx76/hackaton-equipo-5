<?php
require_once __DIR__.'/env.php';
require_once __DIR__.'/ilp_client.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$in  = $raw ? json_decode($raw, true) : $_POST;

$incomingUrl = trim($in['incomingPayment'] ?? '');
if (!$incomingUrl) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'missing_incoming']); exit; }

try {
  // 1) Token del PAYER
  $token = oauth_token(PAYER_AUTH_ISSUER, PAYER_CLIENT_ID, PAYER_CLIENT_SECRET);

  // 2) Crear QUOTE en la wallet del pagador
  $quotesUrl = rtrim(PAYER_RESOURCE_BASE, '/').'/quotes';
  $quoteBody = [
    'method'   => 'ilp',
    'receiver' => $incomingUrl
    // Opcional: 'debitAmount' para limitar gasto máximo
    // 'debitAmount' => ['value'=>'10000','assetCode'=>ASSET_CODE,'assetScale'=>ASSET_SCALE]
  ];

  [$qs, $qj] = http_json('POST', $quotesUrl, [
    'Authorization: Bearer '.$token
  ], $quoteBody);

  if ($qs < 200 || $qs >= 300 || empty($qj['id'])) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'quote_failed','detail'=>$qj]);
    exit;
  }

  $quoteId = $qj['id'];

  // 3) Crear OUTGOING PAYMENT con ese quote
  $outUrl  = rtrim(PAYER_RESOURCE_BASE, '/').'/outgoing-payments';
  $idempotency = bin2hex(random_bytes(12));
  $outBody = [ 'quote' => $quoteId ];

  [$os, $oj] = http_json('POST', $outUrl, [
    'Authorization: Bearer '.$token,
    'Idempotency-Key: '.$idempotency
  ], $outBody);

  if ($os < 200 || $os >= 300) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'outgoing_failed','detail'=>$oj]);
    exit;
  }

  echo json_encode([
    'ok' => true,
    'outgoingPayment' => $oj['id'] ?? null,
    'state'           => $oj['state'] ?? null,
    'quote'           => $quoteId
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'exception','message'=>$e->getMessage()]);
}
