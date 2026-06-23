<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

require_method('POST');

$c = require __DIR__ . '/../bootstrap.php';
$c['auth']->login();
