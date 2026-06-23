<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$c = require __DIR__ . '/../bootstrap.php';
$c['setting']->schedules();
