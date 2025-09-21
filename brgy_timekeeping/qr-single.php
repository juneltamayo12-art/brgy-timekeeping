<?php
// qr-single.php
$sid = isset($_GET['sid']) ? trim($_GET['sid']) : '2042609'; // sample/default
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>QR for <?= htmlspecialchars($sid) ?></title>
<style>
  body{font-family:system-ui,arial;margin:20px}
  .card{width:300px;border:1px solid #ddd;border-radius:12px;padding:16px;text-align:center}
  .sid{margin-top:10px;font-weight:700;font-size:18px;letter-spacing:1px}
  @media print {.noprint{display:none}}
</style>
</head>
<body>
<button class="noprint" onclick="window.print()">🖨️ Print</button>
<div class="card">
  <div id="qrcode"></div>
  <div class="sid"><?= htmlspecialchars($sid) ?></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
  const sid = <?= json_encode($sid) ?>;
  new QRCode(document.getElementById('qrcode'), {
    text: sid,
    width: 240,
    height: 240,
    correctLevel: QRCode.CorrectLevel.M
  });
</script>
</body>
</html>
