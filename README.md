What is the problem?
Indigenous artisans in Mexico and Latin America are excluded from the financial system: they have no access to digital payments, face high fees and cultural barriers, which forces them to depend on cash and limits the growth of their businesses.

What technology will you use?
Our platform uses simple, scalable, and secure tools adapted to indigenous artisans: GitHub for collaboration and version control. PHP for accessible, low-cost, and maintainable web development. Payments API (Open Payments) for secure transactions with low fees. Translation API (Cloud Translate) to provide the platform in indigenous languages, removing cultural barriers.

What is the solution?
Option 1:We developed an inclusive financial platform for indigenous artisans in Mexico and Latin America that removes economic, cultural, and technological barriers. Accessible: progressive web app (PWA) version and support from community agents. Low cost: digital payments and blockchain with fees under 2%, no hidden charges. Culturally inclusive: interfaces in indigenous languages, adapted iconography, and local promoters with financial education. From cash to digital: integration of QR payments for everyday operations.
With this solution, artisans will be able to commercialize their products in the digital economy, increase their income, and actively participate in global markets.

Option 2: An inclusive financial platform for indigenous artisans that offers low-cost digital payments, interfaces in native languages, and simple tools such as QR and PWA, removing cultural and economic barriers so they can participate in the digital economy.

What are the benefits?

Low fees: digital payments with costs under 2%, more money for artisans.
Greater reach: access to the digital economy and new markets without depending solely on cash.
Cultural inclusion: platform in indigenous languages and adapted iconography.
Trust and local support: community promoters who accompany and train.
Scalability: sustainable technological solution, easy to expand to more regions.

What is your simple architecture/stack? A lightweight web architecture (HTML/JS + PHP) that only generates and consumes Open Payments resources. The “financial core” is in the Interledger wallet provider (we do not store or move money ourselves).

What functions are essential? Generate QR codes, request payments, and complete payments.

Who will be responsible for building each part? Payment API implementation: Caleb Franco and main page and QR code integration: Antonio Ruvalcaba and Cinthia Arreola

---------------------------------------------------------------------------------------------

K’ab’ Pay – Open Payments MVP
Inclusive payments platform for indigenous artisans in Mexico and Latin America.Built with PHP, Open Payments API, and QR codes for digital transactions.

Requirements
- Windows 10/11
- Visual Studio Code
- XAMPP (for PHP + Apache server)
- Git for Windows (optional, for version control)
- Internet access to Open Payments test servers

Project Structure
HACKATON-EQUIPO-5-2/
 │
├── index.php              # Main UI (charge/pay screens, QR generation/scan)
├── create_incoming.php    # Creates Incoming Payments on merchant’s wallet
├── pay_openpayments.php   # Handles quotes and outgoing payments
├── env.php                # Configuration (keys, payment pointers, API endpoints)
├── ilp_client.php         # HTTP helpers, OAuth2, and Open Payments discovery
├── assets/                # Static assets (logo, images)
└── README.md              # Technical documentation

Configuration
Edit env.php with your own credentials:
// === Asset ===
define('ASSET_CODE',  'MXN');
define('ASSET_SCALE', 2);

// === Merchant (receiver) ===
define('MERCHANT_PAYMENT_POINTER', '$ilp.interledger-test.dev/caleb');
define('MERCHANT_AUTH_ISSUER',   'https://auth.merchant.example');
define('MERCHANT_CLIENT_ID',     'YOUR_MERCHANT_CLIENT_ID');
define('MERCHANT_CLIENT_SECRET', 'YOUR_MERCHANT_CLIENT_SECRET');
define('MERCHANT_RESOURCE_BASE', '');

// === Payer (sender) ===
define('PAYER_AUTH_ISSUER',   'https://auth.payer.example');
define('PAYER_CLIENT_ID',     'YOUR_PAYER_CLIENT_ID');
define('PAYER_CLIENT_SECRET', 'YOUR_PAYER_CLIENT_SECRET');
define('PAYER_RESOURCE_BASE', 'https://api.payer.example');

// === HTTP Timeout ===
define('HTTP_TIMEOUT', 15);

Setup (Windows + VS Code)
1. Install XAMPP
    - Download from XAMPP
    - Install and start Apache from the XAMPP Control Panel
2. Clone / Copy ProjectPlace the project folder inside:C:\xampp\htdocs\kabpay
3. Open in Visual Studio Code
    - Launch VS Code
    - Open the folder: File → Open Folder → C:\xampp\htdocs\kabpay
4. Run Local ServerOpen a browser and go to:http://localhost/kabpay/index.php

Usage
Merchant Flow (Cobrar)
- Enter amount, currency, and optional concept
- Calls create_incoming.php → creates Incoming Payment
- Displays a QR code with payment URL
Payer Flow (Pagar)
- Scan QR with camera
- App fetches payment details
- On confirm, calls pay_openpayments.php:
    1. Gets payer token
    2. Creates Quote
    3. Creates Outgoing Payment
- Displays payment status

Key Components:
- ilp_client.php
    - http_json → Wrapper for cURL JSON
    - oauth_token → Fetches OAuth2 token
    - discover_wallet_address → Resolves payment pointer
- create_incoming.php
    - Creates Incoming Payment on merchant wallet
    - Returns JSON and QR payload
- pay_openpayments.php
    - Creates Quote + Outgoing Payment
    - Returns result to frontend

Dependencies (Browser-side)
- qrcode.js → QR generation
- html5-qrcode → Camera-based QR scanning
Loaded via CDN in index.php.

Contributors
- Payment API Implementation: Caleb Franco
- Frontend + QR Integration: Antonio Ruvalcaba, Cinthia Arreola
