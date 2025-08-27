<?php
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'mystic_lab',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4'
    ],
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'your-secret-key',
        'expiry' => 60 * 60 * 24 * 7 // 7 days
    ],
    'upload' => [
        'dir' => __DIR__ . '/../../uploads',
        'maxSize' => 5 * 1024 * 1024, // 5MB
        'allowedTypes' => ['image/jpeg', 'image/png', 'image/webp']
    ],
    'email' => [
        'from' => getenv('MAIL_FROM') ?: 'noreply@example.com',
        'smtp' => [
            'host' => getenv('SMTP_HOST') ?: 'localhost',
            'port' => getenv('SMTP_PORT') ?: 587,
            'user' => getenv('SMTP_USER') ?: '',
            'pass' => getenv('SMTP_PASS') ?: '',
            'secure' => getenv('SMTP_SECURE') ?: 'tls'
        ]
    ]
];
