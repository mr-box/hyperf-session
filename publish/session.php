<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */


return [
    'handler' => \Hf\Session\Handler\FileHandler::class,
    'options' => [
        'redis' => 'default',
        'save_path' => BASE_PATH . '/runtime/session',
        'gc_maxlifetime' => 1200,
    ],
];
