<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$userController = require __DIR__ . '/../bootstrap.php';

$userController->storeAttendance();
