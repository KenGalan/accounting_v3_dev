<?php
ob_start();

$url = "http://odoodev.teamglac.com:8069/web?db=AUG_29_ODOO_DB";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
curl_close($ch);

header("X-Frame-Options: ALLOWALL");
header("Content-Security-Policy: frame-ancestors *");

echo $response;

ob_end_flush();
?>