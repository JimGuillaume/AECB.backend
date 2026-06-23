<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$teamIds        = parse_team_ids();
[$year, $month] = parse_year_month();

$c = require __DIR__ . '/../bootstrap.php';
$c['team']->attendance($teamIds, $year, $month);
