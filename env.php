<?php // env.php para K'ab' Pay con Open Payments (Interledger Test)

// === Activo (moneda) ===
define('ASSET_CODE',  'MXN');
define('ASSET_SCALE', 2); // subunidades (centavos)

// === MERCHANT (caleb) ===
define('MERCHANT_PAYMENT_POINTER', '$ilp.interledger-test.dev/caleb');
define('MERCHANT_KEY_ID',  '548df86f-ee42-47eb-a637-8a2038f9bc5b');
define('MERCHANT_PUBLIC_KEY',  'MCowBQYDK2VwAyEAmX25PIY5IKvsCZFcJ91o8cIz4Wt2R3gMnJo1vs3TmZc=');
// ⚠️ necesitas también la PRIVATE KEY para firmar DPoP
define('MERCHANT_PRIVATE_KEY', '--- PON AQUÍ LA PRIVATE KEY GENERADA ---');

// === PAYER (carlosz) ===
define('PAYER_PAYMENT_POINTER', '$ilp.interledger-test.dev/carlosz');
define('PAYER_KEY_ID',  'c7865ac0-3194-4ccf-8367-0b29f7a99a3f');
define('PAYER_PUBLIC_KEY',  'MCowBQYDK2VwAyEAx9jVLuSU7QMQZqoegF/+XUpvTj40BHjJR6rUwIvkgOc=');
// ⚠️ igual necesitas la PRIVATE KEY
define('PAYER_PRIVATE_KEY', '--- PON AQUÍ LA PRIVATE KEY GENERADA ---');

// === Servidores de testnet ===
// En el sandbox de Interledger Testnet suele ser:
define('AUTH_SERVER_URL', 'https://auth.ilp.interledger-test.dev'); 
define('RESOURCE_SERVER_URL', 'https://openpayments.ilp.interledger-test.dev');

// === Opciones comunes ===
define('HTTP_TIMEOUT', 15);
