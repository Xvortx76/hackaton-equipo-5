<?php // env.php

// === Activo (moneda) ===
define('ASSET_CODE',  'MXN');
define('ASSET_SCALE', 2); // subunidades (centavos)

// === MERCHANT (quien cobra) ===
// Payment Pointer del comercio (estilo $host/alice)
define('MERCHANT_PAYMENT_POINTER', '$ilp.interledger-test.dev/caleb');

// Servidores del MERCHANT (proveedor Open Payments del comercio)
define('MERCHANT_AUTH_ISSUER',   '$ilp.interledger-test.dev/caleb');     // OAuth issuer
define('MERCHANT_CLIENT_ID',     'MCowBQYDK2VwAyEAmX25PIY5IKvsCZFcJ91o8cIz4Wt2R3gMnJo1vs3TmZc=');
define('MERCHANT_CLIENT_SECRET', '548df86f-ee42-47eb-a637-8a2038f9bc5b');

// Si tu proveedor expone resource base fijo, ponlo; si no, se resuelve por discovery:
define('MERCHANT_RESOURCE_BASE', ''); // ej: 'https://api.merchant.example' o vacío para usar discovery

// === PAYER (quien paga) ===
define('PAYER_AUTH_ISSUER',   '$ilp.interledger-test.dev/carlosz');
define('PAYER_CLIENT_ID',     'MCowBQYDK2VwAyEAx9jVLuSU7QMQZqoegF/+XUpvTj40BHjJR6rUwIvkgOc=');
define('PAYER_CLIENT_SECRET', 'c7865ac0-3194-4ccf-8367-0b29f7a99a3f');
// Base del wallet del pagador (su Resource Server Open Payments)
define('PAYER_RESOURCE_BASE', '$ilp.interledger-test.dev/carlosz');

// === Opcional: tiempos de espera HTTP ===
define('HTTP_TIMEOUT', 15);
