<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$appDir = realpath(__DIR__);
$configDir = $appDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

$app = new \MakiseCo\Application($appDir, $configDir);

$code = $app->run($argv);
exit($code);
