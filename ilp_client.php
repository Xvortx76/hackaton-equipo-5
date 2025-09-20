<?php // ilp_client.php
require_once __DIR__.'/env.php';

function _apply_curl_network_opts($ch) {
  // DNS públicos (opcional)
  if (defined('CURL_DNS_SERVERS')) {
    $dns = constant('CURL_DNS_SERVERS');
    if (is_string($dns) && $dns !== '') {
      @curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
    }
  }
  // Resolver host->IP manual (opcional)
  if (defined('CURL_RESOLVE')) {
    $resolve = constant('CURL_RESOLVE');
    if (is_array($resolve) && !empty($resolve)) {
      @curl_setopt($ch, CURLOPT_RESOLVE, $resolve);
    }
  }
  // CA bundle custom (opcional, Windows/XAMPP)
  if (defined('CURL_CA_BUNDLE')) {
    $cab = constant('CURL_CA_BUNDLE');
    if (is_string($cab) && $cab !== '' && file_exists($cab)) {
      @curl_setopt($ch, CURLOPT_CAINFO, $cab);
    }
  }
}

function http_json($method, $url, $headers = [], $body = null) {
  $ch = curl_init($url);

  // --- Opciones de red seguras (no rompen si no defines las constantes) ---
  if (defined('CURL_DNS_SERVERS')) {
    $dns = constant('CURL_DNS_SERVERS');
    if (is_string($dns) && $dns !== '') {
      @curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
    }
  }
  if (defined('CURL_RESOLVE')) {
    $resolve = constant('CURL_RESOLVE'); // debe ser array ej: ['host:443:1.2.3.4']
    if (is_array($resolve) && !empty($resolve)) {
      @curl_setopt($ch, CURLOPT_RESOLVE, $resolve);
    }
  }
  if (defined('CURL_CA_BUNDLE')) {
    $cab = constant('CURL_CA_BUNDLE');   // ruta a cacert.pem si la usas
    if (is_string($cab) && $cab !== '' && file_exists($cab)) {
      @curl_setopt($ch, CURLOPT_CAINFO, $cab);
    }
  }
  // ------------------------------------------------------------------------

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, HTTP_TIMEOUT);
  curl_setopt($ch, CURLOPT_TIMEOUT, HTTP_TIMEOUT);

  $h = array_merge(['Accept: application/json'], $headers);
  if ($body !== null) {
    $h[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
  }
  curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

  $res = curl_exec($ch);
  if ($res === false) {
    $err = curl_error($ch);
    curl_close($ch);
    throw new Exception('cURL error: '.$err);
  }
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $json = $res !== '' ? json_decode($res, true) : null;
  return [$status, $json, $res];
}


function oauth_token($issuer, $clientId, $clientSecret, $scope='') {
  $url = rtrim($issuer, '/').'/token';
  $headers = ['Content-Type: application/x-www-form-urlencoded'];
  $fields  = 'grant_type=client_credentials';
  if ($scope) $fields .= '&scope='.urlencode($scope);

  $ch = curl_init($url);

  // --- Opciones de red seguras (no fallan si no defines las constantes) ---
  if (defined('CURL_DNS_SERVERS')) {
    $dns = constant('CURL_DNS_SERVERS');                // ej: '8.8.8.8,1.1.1.1'
    if (is_string($dns) && $dns !== '') {
      @curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
    }
  }
  if (defined('CURL_RESOLVE')) {
    $resolve = constant('CURL_RESOLVE');                // ej: ['host:443:1.2.3.4']
    if (is_array($resolve) && !empty($resolve)) {
      @curl_setopt($ch, CURLOPT_RESOLVE, $resolve);
    }
  }
  if (defined('CURL_CA_BUNDLE')) {
    $cab = constant('CURL_CA_BUNDLE');                  // ej: __DIR__.'/cacert.pem'
    if (is_string($cab) && $cab !== '' && file_exists($cab)) {
      @curl_setopt($ch, CURLOPT_CAINFO, $cab);
    }
  }
  // ------------------------------------------------------------------------

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $fields,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_USERPWD        => $clientId.':'.$clientSecret,
    CURLOPT_CONNECTTIMEOUT => HTTP_TIMEOUT,
    CURLOPT_TIMEOUT        => HTTP_TIMEOUT,
  ]);

  $res = curl_exec($ch);
  if ($res === false) {
    $err = curl_error($ch);
    curl_close($ch);
    throw new Exception('OAuth cURL error: '.$err);
  }
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($status < 200 || $status >= 300) {
    throw new Exception('OAuth http '.$status.': '.$res);
  }

  $json = json_decode($res, true);
  if (empty($json['access_token'])) {
    throw new Exception('OAuth sin access_token');
  }
  return $json['access_token'];
}


/**
 * Discovery de Payment Pointer (Open Payments)
 * Estrategia:
 *  1) GET https://host/.well-known/open-payments?paymentPointer=$host/alice
 *     Accept: application/openpayments+json
 *  2) Si falla, intenta GET directo al pointer (https://host/alice) con ese Accept
 * Devuelve:
 *  ['wallet_address_id' => 'https://.../wallet-addresses/{id}',
 *   'resource_base'     => 'https://...']  // opcional si el server lo declara
 */
function discover_wallet_address($paymentPointer) {
  // normaliza: $host/alice -> host (sin $) + path
  if ($paymentPointer[0] !== '$') {
    throw new Exception('Payment pointer inválido (debe empezar con $)');
  }
  $pp = substr($paymentPointer, 1);
  $slash = strpos($pp, '/');
  $host = $slash === false ? $pp : substr($pp, 0, $slash);
  $path = $slash === false ? ''  : substr($pp, $slash); // ej: /alice

  $urls = [
    "https://{$host}/.well-known/open-payments?paymentPointer=$paymentPointer",
    "https://{$host}{$path}"
  ];
  $accept = ['Accept: application/openpayments+json'];

  foreach ($urls as $u) {
    [$status, $json, $raw] = http_json('GET', $u, $accept, null);
    if ($status >= 200 && $status < 300 && is_array($json)) {
      // Algunos servidores devuelven directamente la Wallet Address con "id"
      if (!empty($json['id'])) {
        $waId = $json['id'];
        // Si exponen resource base, intenta leerlo; si no, lo deducimos por URL base.
        $base = '';
        if (!empty($json['resourceServer'])) $base = rtrim($json['resourceServer'], '/');
        if (!$base) {
          // deduce base del id de wallet address (hasta /wallet-addresses)
          $parts = parse_url($waId);
          $scheme = $parts['scheme'].'://'.$parts['host'].(isset($parts['port'])?':'.$parts['port']:'');
          $base = $scheme;
        }
        return ['wallet_address_id' => $waId, 'resource_base' => $base];
      }
    }
  }
  throw new Exception('No se pudo descubrir la Wallet Address del payment pointer');
}
