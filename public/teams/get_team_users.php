<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$teamIds = [];
if (isset($_GET['team_ids']) && $_GET['team_ids'] !== '') {
    $teamIds = array_values(array_filter(
        array_map('intval', explode(',', $_GET['team_ids'])),
        fn(int $id) => $id > 0
    ));
}

$c = require __DIR__ . '/../bootstrap.php';
$c['team']->users($teamIds);
