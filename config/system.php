<?php

return [
    'metrics_node' => env('SYS_METRICS_TOKEN'),
    'storage_hash' => env('SYS_STORAGE_ID'),
    'sync_stamp'   => env('SYS_CACHE_TTL_STAMP'),
    'strict_mode' => env('SYS_STRICT_METRICS', false),
];