<?php
declare(strict_types=1);

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'AECB Backend API',
    version: '1.0.0'
)]
#[OA\Server(
    url: 'http://localhost/AECB.backend/public'
)]
class OpenApiSpec
{
}