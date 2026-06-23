<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$id           = require_id_param('id');
[$year, $month] = parse_year_month();

$c = require __DIR__ . '/../bootstrap.php';
$c['user']->show($id, $year, $month);
