<?php

declare(strict_types=1);

return [
    // Mettre à true pour observer la CSP sans la faire appliquer (en-tête Content-Security-Policy-Report-Only).
    'csp_report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', false),

    // HSTS émis par l'application uniquement sur les requêtes HTTPS (Caddy l'émet aussi : même valeur).
    'hsts' => (bool) env('SECURITY_HSTS', true),
];
