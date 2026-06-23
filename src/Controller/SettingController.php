<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\ListWorkSchedules;

final class SettingController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private ListWorkSchedules $listWorkSchedules,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function schedules(): void
    {
        $list = $this->listWorkSchedules->execute();
        $this->respond(array_map(static function (array $row): array {
            return [
                'schedule_id' => (int)   $row['schedule_id'],
                'name'        =>          $row['name'],
                'fraction'    => (float)  $row['fraction'],
                'daily_hours' => (float)  $row['daily_hours'],
            ];
        }, $list));
    }
}
