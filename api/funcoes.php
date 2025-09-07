<?php
// Força headers de segurança em TODAS as respostas
function setSecurityHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
}

// Resposta JSON padronizada
function jsonResponse(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Sanitiza CEP (somente números)
function sanitizeCep(string $cep): string {
    return preg_replace('/[^0-9]/', '', $cep);
}

// Valida se o CEP tem 8 dígitos
function validateCep(string $cep): bool {
    return (strlen($cep) === 8);
}
