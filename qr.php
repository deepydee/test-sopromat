<?php
  require_once __DIR__ . '/phpqrcode/qrlib.php';
  $link = $_REQUEST['link'];
  QRcode::png($link, false, 'H', 6, 2, false);
?>