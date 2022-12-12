<?php

return [
    // 请求域名
    'domain' => env('AERO_INVOICE_DOMAIN', ''),

    // 请求端口
    'port' => env('AERO_INVOICE_PORT', ''),

    // 项目名
    'program' => env('AERO_INVOICE_PROGRAM','eisp-zk'),

    // 是否加密校验
    'verified' => env('AERO_INVOICE_VERIFIED', 0),

    // 校验码
    'secretKey' => env('AERO_INVOICE_SECRET_KEY', '')
];