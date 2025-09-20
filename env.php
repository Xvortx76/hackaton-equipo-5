<?php // env.php

// === Activo (moneda) ===
define('ASSET_CODE',  'MXN');
define('ASSET_SCALE', 2); // subunidades (centavos)

// === MERCHANT (quien cobra) ===
// Payment Pointer del comercio (estilo $host/alice)
define('MERCHANT_PAYMENT_POINTER', '$wallet.merchant.example/alice');

// Servidores del MERCHANT (proveedor Open Payments del comercio)
define('MERCHANT_AUTH_ISSUER',   'https://auth.merchant.example');     // OAuth issuer
define('MERCHANT_CLIENT_ID',     'YOUR_MERCHANT_CLIENT_ID');
define('MERCHANT_CLIENT_SECRET', 'YOUR_MERCHANT_CLIENT_SECRET');

// Si tu proveedor expone resource base fijo, ponlo; si no, se resuelve por discovery:
define('MERCHANT_RESOURCE_BASE', ''); // ej: 'https://api.merchant.example' o vacío para usar discovery

// === PAYER (quien paga) ===
define('PAYER_AUTH_ISSUER',   'https://auth.payer.example');
define('PAYER_CLIENT_ID',     'YOUR_PAYER_CLIENT_ID');
define('PAYER_CLIENT_SECRET', 'YOUR_PAYER_CLIENT_SECRET');
// Base del wallet del pagador (su Resource Server Open Payments)
define('PAYER_RESOURCE_BASE', 'https://api.payer.example');

// === Opcional: tiempos de espera HTTP ===
define('HTTP_TIMEOUT', 15);
