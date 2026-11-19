<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Core\Application;

$app = new Application();

require_once dirname(__DIR__) . '/routes/web.php';

$app->run();
