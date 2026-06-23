<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$teamIds = parse_team_ids();

$c = require __DIR__ . '/../bootstrap.php';
$c['team']->users($teamIds);
