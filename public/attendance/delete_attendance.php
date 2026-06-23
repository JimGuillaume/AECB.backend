<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$id = require_id_param('id');

$c = require __DIR__ . '/../bootstrap.php';
$c['attendance']->destroy($id);
