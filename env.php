<?php // env.php - K'ab' Pay (Open Payments testnet)

/** Activo */
define('ASSET_CODE',  'MXN');
define('ASSET_SCALE', 2);

/** MERCHANT (caleb) */
define('MERCHANT_PAYMENT_POINTER', '$ilp.interledger-test.dev/caleb');
define('MERCHANT_KEY_ID',          '6a9a15fe-8251-46a0-b395-2b994afe7be3');
define('MERCHANT_PUBLIC_KEY_PEM',  <<<PEM
-----BEGIN PUBLIC KEY-----
MCowBQYDK2VwAyEACY/VnPSG0PM8ek54H+qdDKKEUjzeO7mvj2wl3takL+4=
-----END PUBLIC KEY-----
PEM
);
define('MERCHANT_PRIVATE_KEY_PEM', <<<PEM
-----BEGIN PRIVATE KEY-----
MC4CAQAwBQYDK2VwBCIEIJmmbmcTbbQuEdPZFWKp7mi4q3oBVKz9miBTy/TdAn9L
-----END PRIVATE KEY-----
PEM
);

/** PAYER (carlosz) */
define('PAYER_PAYMENT_POINTER', '$ilp.interledger-test.dev/carlosz');
define('PAYER_KEY_ID',          '0b3fe03a-4375-4ce3-843b-d0a98c85bb2b');
define('PAYER_PUBLIC_KEY_PEM',  <<<PEM
-----BEGIN PUBLIC KEY-----
MCowBQYDK2VwAyEAlI+JvGtP4zU70nhfPcdvYayGojPiujKJIlDk24nAKZU=
-----END PUBLIC KEY-----
PEM
);
define('PAYER_PRIVATE_KEY_PEM', <<<PEM
-----BEGIN PRIVATE KEY-----
MC4CAQAwBQYDK2VwBCIEIPSrxVXq8QOHr1k8ZFV3rppG2WY50PGsuS5KC1pgn53K
-----END PRIVATE KEY-----
PEM
);

/** Servidores (testnet) */
define('AUTH_SERVER_URL',     'https://auth.ilp.interledger-test.dev');
define('RESOURCE_SERVER_URL', 'https://openpayments.ilp.interledger-test.dev');

/** HTTP */
define('HTTP_TIMEOUT', 15);

// ============================
// Aliases para que los scripts no marquen undefined
// ============================
if (!defined('MERCHANT_AUTH_ISSUER'))   define('MERCHANT_AUTH_ISSUER',   AUTH_SERVER_URL);
if (!defined('MERCHANT_RESOURCE_BASE')) define('MERCHANT_RESOURCE_BASE', RESOURCE_SERVER_URL);
if (!defined('PAYER_AUTH_ISSUER'))      define('PAYER_AUTH_ISSUER',      AUTH_SERVER_URL);
if (!defined('PAYER_RESOURCE_BASE'))    define('PAYER_RESOURCE_BASE',    RESOURCE_SERVER_URL);

// ============================
// Credenciales OAuth (client_credentials)
// Reemplaza con tus IDs reales de testnet
// ============================
if (!defined('MERCHANT_CLIENT_ID'))     define('MERCHANT_CLIENT_ID',     'caleb-client-id');
if (!defined('MERCHANT_CLIENT_SECRET')) define('MERCHANT_CLIENT_SECRET', 'caleb-client-secret');

if (!defined('PAYER_CLIENT_ID'))        define('PAYER_CLIENT_ID',        'carlosz-client-id');
if (!defined('PAYER_CLIENT_SECRET'))    define('PAYER_CLIENT_SECRET',    'carlosz-client-secret');
