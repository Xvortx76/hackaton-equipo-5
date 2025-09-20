<?php /* index.php - K’ab’ Pay (Open Payments MVP) */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>K’ab’ Pay</title>

  <style>
    :root{--bg:#0f1115;--card:#161a22;--fg:#f5f7fb;--muted:#a8b0c0;--border:#232a36;--primary:#ff9f1a}
    *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--fg);font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial}
    .wrap{max-width:980px;margin:0 auto;padding:20px}
    header{display:flex;align-items:center;gap:14px;padding:12px 0}
    header img{height:56px;width:56px;object-fit:contain;border-radius:999px}
    h1{margin:0;font-size:28px;font-weight:800}
    .card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:18px}
    .grid{display:grid;gap:14px} @media(min-width:720px){.grid-2{grid-template-columns:1fr 1fr}}
    .btn{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--border);background:#1a1f2b;color:var(--fg);padding:12px 16px;border-radius:12px;cursor:pointer}
    .btn:hover{background:#22283a} .btn.primary{background:var(--primary);color:#1a1207;border-color:#000}
    label{font-size:12px;color:var(--muted)} input,select{width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);background:#0e131c;color:var(--fg)}
    .row{display:flex;gap:10px;align-items:center} .hidden{display:none} .center{display:grid;place-items:center;text-align:center}
    .muted{color:var(--muted)} .pill{display:inline-block;background:#212737;color:var(--muted);font-size:12px;border-radius:999px;padding:4px 10px}
  </style>

  <!-- Generación de QR (cliente) -->
  <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.js"></script>
  <!-- Lector de QR (cámara) -->
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
  <div class="wrap">
    <header>
      <img src="assets/kabpay-logo.png" alt="K’ab’ Pay" />
      <div>
        <h1>K’ab’ Pay</h1>
        <div class="muted">Inclusive payments for indigenous businesses</div>
      </div>
    </header>

    <div class="card">
      <div class="row" style="justify-content:space-between">
        <h2 style="margin:0;font-size:20px">¿Qué deseas hacer?</h2>
        <span class="pill">Open Payments</span>
      </div>
      <div class="grid grid-2" style="margin-top:10px">
        <button class="btn" onclick="go('charge')">💳 Cobrar (generar QR)</button>
        <button class="btn" onclick="go('pay')">🤝 Pagar (leer QR)</button>
      </div>
    </div>

    <!-- COBRAR -->
    <section id="charge" class="card hidden" style="margin-top:14px">
      <div class="row">
        <button class="btn" onclick="go('home')">⬅ Regresar</button>
        <h3 style="margin:0">Cobrar</h3>
      </div>
      <div class="grid grid-2" style="margin-top:10px">
        <div>
          <label>Moneda</label>
          <select id="currency"><option value="MXN">MXN</option><option value="USD">USD</option></select>
          <div style="margin-top:10px">
            <label>Monto</label>
            <input id="amount" type="number" min="0.01" step="0.01" placeholder="0.00" />
          </div>
          <div style="margin-top:10px">
            <label>Concepto (opcional)</label>
            <input id="concept" type="text" placeholder="Artesanía / Servicio" />
          </div>
          <div class="row" style="margin-top:12px">
            <button class="btn primary" onclick="buildQR()">Generar QR</button>
          </div>
          <p class="muted" id="chargeMsg" style="margin-top:8px"></p>
        </div>
        <div class="center" style="min-height:300px">
          <canvas id="qrCanvas"></canvas>
        </div>
      </div>
    </section>

    <!-- PAGAR -->
    <section id="pay" class="card hidden" style="margin-top:14px">
      <div class="row">
        <button class="btn" onclick="go('home')">⬅ Regresar</button>
        <h3 style="margin:0">Pagar</h3>
      </div>
      <div class="grid grid-2" style="margin-top:10px">
        <div>
          <p class="muted">Escanea el QR (Incoming Payment URL) del comercio.</p>
          <div id="reader" style="width:100%;max-width:420px"></div>
          <p class="muted" id="scanMsg" style="margin-top:8px"></p>
        </div>
        <div>
          <div id="chargeDetail" class="hidden">
            <h4>Detalle del cobro</h4>
            <div class="grid" style="gap:8px">
              <div class="row"><div class="muted" style="width:120px">URL</div><div id="d_url" style="word-break:break-all"></div></div>
              <div class="row"><div class="muted" style="width:120px">Monto</div><div id="d_amount">—</div></div>
              <div class="row"><div class="muted" style="width:120px">Moneda</div><div id="d_curr">—</div></div>
              <div class="row"><div class="muted" style="width:120px">Descripción</div><div id="d_desc">—</div></div>
            </div>
            <button class="btn primary" style="margin-top:12px" onclick="confirmPay()">Confirmar pago</button>
          </div>
          <div id="noScan" class="muted">Aún no se ha escaneado ningún QR.</div>
        </div>
      </div>
    </section>

    <footer class="muted" style="text-align:center;margin:24px 0">
      © <?php echo date('Y'); ?> K’ab’ Pay · MVP Open Payments
    </footer>
  </div>

  <script>
    // Scanner (ámbito global para poder detenerlo)
    let scanner = null;
    let incomingUrl = '';

    function stopScanner() {
      if (scanner) {
        try { scanner.stop(); } catch(_) {}
        try { scanner.clear(); } catch(_) {}
        scanner = null;
      }
    }

    function go(view){
      // Ocultar secciones
      document.getElementById('charge').classList.add('hidden');
      document.getElementById('pay').classList.add('hidden');

      // Detener cámara si salimos de "Pagar"
      if (view !== 'pay') stopScanner();

      // Mostrar la vista solicitada
      if(view==='charge') document.getElementById('charge').classList.remove('hidden');
      if(view==='pay')    { document.getElementById('pay').classList.remove('hidden'); startScanner(); }
    }

    // === COBRAR: crear Incoming Payment y poner su URL en el QR ===
    async function buildQR(){
      const amount   = Number(document.getElementById('amount')?.value);
      const currency = document.getElementById('currency')?.value || 'MXN';
      const ref      = document.getElementById('concept')?.value?.trim() || '';
      const msg = document.getElementById('chargeMsg');
      const canvas = document.getElementById('qrCanvas');
      msg.textContent = '';

      // Validaciones mínimas
      if(!amount || amount <= 0){
        msg.textContent = 'Monto inválido.';
        return;
      }

      // Llamada al backend (wallet sale de env.php)
      let qrText = '';
      try {
        const res = await fetch('create_incoming.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ amount, currency, ref })
        });
        if (!res.ok) {
          const txt = await res.text();
          msg.textContent = 'Error creando el cobro';
          return;
        }
        const data = await res.json();
        if(!data?.ok || !data?.qr){
          msg.textContent = 'Error creando el cobro: ';
          return;
        }
        qrText = String(data.qr);
      } catch (e) {
        msg.textContent = 'Error de red: ' + e.message;
        return;
      }

      // Dibujar QR
      try {
        if (typeof QRCode === 'undefined' || !QRCode?.toCanvas) {
          msg.textContent = 'No se cargó la librería de QR.';
          return;
        }
        const ctx = canvas.getContext('2d');
        ctx?.clearRect(0, 0, canvas.width, canvas.height);
        canvas.width = 256;
        canvas.height = 256;
        await QRCode.toCanvas(canvas, qrText, { width: 256, margin: 2 });
        msg.textContent = 'QR listo.';
      } catch (err) {
        msg.textContent = 'No se pudo generar el QR.';
        console.error(err);
      }
    }

    // === PAGAR: escanear URL del Incoming Payment, obtener detalles y pagar ===
    async function startScanner(){
      if(scanner) return;
      const scanMsg = document.getElementById('scanMsg');
      const readerEl = document.getElementById('reader');
      if (!readerEl) return;

      scanner = new Html5Qrcode("reader");
      const cfg = { fps:10, qrbox:220, aspectRatio:1.0, formatsToSupport:[Html5QrcodeSupportedFormats.QR_CODE] };

      scanner.start({ facingMode:'environment' }, cfg,
        async (text) => {
          if(/^https?:\/\//.test(text)){
            incomingUrl = text;
            try{
              const r = await fetch(text, { headers: { 'Accept': 'application/openpayments+json' }});
              const j = await r.json();
              document.getElementById('noScan').classList.add('hidden');
              document.getElementById('chargeDetail').classList.remove('hidden');
              document.getElementById('d_url').textContent = text;
              if(j?.incomingAmount){
                const v = Number(j.incomingAmount.value)/Math.pow(10, Number(j.incomingAmount.assetScale||2));
                document.getElementById('d_amount').textContent = v.toFixed(Number(j.incomingAmount.assetScale||2));
                document.getElementById('d_curr').textContent   = j.incomingAmount.assetCode || '—';
              } else {
                document.getElementById('d_amount').textContent = '—';
                document.getElementById('d_curr').textContent   = '—';
              }
              document.getElementById('d_desc').textContent = j?.description || '—';
            }catch(e){ scanMsg.textContent = 'No se pudo leer detalles del cobro.'; }
          } else {
            scanMsg.textContent = 'QR no reconocido.';
          }
        },
        (err) => {}
      ).catch(e => { scanMsg.textContent = 'Permiso de cámara denegado.'; });
    }

    async function confirmPay(){
      if(!incomingUrl){ alert('No hay URL de cobro.'); return; }
      const res = await fetch('pay_openpayments.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ incomingPayment: incomingUrl })
      });
      const data = await res.json();
      if(data.ok){ alert('Pago enviado ✅'); location.reload(); }
      else { alert('Error') }
    }
  </script>
</body>
</html>
