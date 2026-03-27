<?php

return [
    'contact_verification_token_ttl_minutes' => (int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10),
    'password_reset_token_ttl_minutes' => (int) env('PASSWORD_RESET_TOKEN_TTL_MINUTES', 30),
    'fixed_testing_token' => env('AUTH_SECURITY_FIXED_TOKEN'),
];
