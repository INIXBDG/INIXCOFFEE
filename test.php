<?php
$ch = curl_init("http://localhost:8000/jurnalakuntansi/data?start_date=2026-07-01&end_date=2026-07-31");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
echo $result;
