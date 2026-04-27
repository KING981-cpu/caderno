<?php

return [
    'log_level' => getenv('LOG_LEVEL') ?: 'INFO',
    'log_path' => getenv('LOG_PATH') ?: '/var/log/caderno/app.log',
    'log_format' => '[{timestamp}] {level}: {message}',
];
