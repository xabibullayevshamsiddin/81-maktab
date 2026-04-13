<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$apiKey = $_ENV['GEMINI_API_KEY'];
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo $response;
