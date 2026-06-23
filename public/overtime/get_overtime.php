<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

require_method('GET');

$userId         = require_id_param('user_id');
[$year, $month] = parse_year_month();

$c = require __DIR__ . '/../bootstrap.php';
$c['overtime']->month($userId, $year, $month);
