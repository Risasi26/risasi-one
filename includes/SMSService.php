<?php
// Beem Africa SMS service helper.
// Paste your real API keys below when you are ready to send real SMS messages.
$BEEM_API_KEY = "727813885fb6b966";
$BEEM_SECRET_KEY = "ZDAzODkyODc4ZGFiNWQ0NGE4MmY5OWIzYWU5NzBkZDdjYzdjNzA4OWQ2MWVkMzJkMjBjNWFiZjMyNDEyN2VkNw==";
$BEEM_SENDER_NAME = "RISASIONE";

function normalizePhoneNumber($phone) {
    $digits = preg_replace('/\D+/', '', $phone);

    // Remove a leading zero if present.
    if (strpos($digits, '0') === 0) {
        $digits = substr($digits, 1);
    }

    // Ensure the number is in Tanzanian format.
    if (strpos($digits, '255') !== 0) {
        $digits = '255' . ltrim($digits, '0');
    }

    return $digits;
}

function sendSMS($phone, $message) {
    global $BEEM_API_KEY, $BEEM_SECRET_KEY, $BEEM_SENDER_NAME;

    $normalizedPhone = normalizePhoneNumber($phone);

    if ($BEEM_API_KEY === 'PASTE_API_KEY_HERE' || $BEEM_SECRET_KEY === 'PASTE_SECRET_KEY_HERE') {
        return [
            'status' => 'pending_api_key',
            'provider' => 'Beem',
            'delivery_channel' => 'SMS',
            'provider_response' => 'API keys are not configured. SMS was not sent.',
        ];
    }

    $endpoint = 'https://sms.beem.africa/api/v1/send';
    $payload = [
        'messages' => [
            [
                'to' => $normalizedPhone,
                'from' => $BEEM_SENDER_NAME,
                'body' => $message,
            ],
        ],
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($BEEM_API_KEY . ':' . $BEEM_SECRET_KEY),
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        return [
            'status' => 'failed',
            'provider' => 'Beem',
            'delivery_channel' => 'SMS',
            'provider_response' => 'cURL error: ' . $curlError,
        ];
    }

    $status = 'failed';
    $providerResponse = $response ?: 'Empty response from Beem API.';

    if ($httpCode >= 200 && $httpCode < 300) {
        $decoded = json_decode($response, true);
        if (is_array($decoded) && isset($decoded['status']) && strtolower($decoded['status']) === 'success') {
            $status = 'sent';
        } else {
            $status = 'failed';
        }
    }

    return [
        'status' => $status,
        'provider' => 'Beem',
        'delivery_channel' => 'SMS',
        'provider_response' => $providerResponse,
    ];
}

function logSMSResult($tenant_id, $phone, $message, $status, $response, $message_type = 'announcement') {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }

    $tenantName = '';
    if ($tenant_id) {
        $stmt = $pdo->prepare('SELECT full_name FROM tenants WHERE id = ? LIMIT 1');
        $stmt->execute([$tenant_id]);
        $tenant = $stmt->fetch();
        if ($tenant) {
            $tenantName = $tenant['full_name'];
        }
    }

    $stmt = $pdo->prepare('INSERT INTO message_logs (tenant_id, tenant_name, phone_number, message_type, message_body, delivery_channel, provider, status, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $tenant_id,
        $tenantName,
        $phone,
        $message_type,
        $message,
        'SMS',
        'Beem',
        $status,
        $response,
    ]);
}
