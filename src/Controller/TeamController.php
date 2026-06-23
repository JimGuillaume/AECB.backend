<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\GetTeamPrestationsForMonth;
use App\UseCase\ListUsersByTeams;

final class TeamController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private ListUsersByTeams $listUsersByTeams,
        private GetTeamPrestationsForMonth $getTeamPrestationsForMonth,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function users(array $teamIds): void
    {
        $users = $this->listUsersByTeams->execute($teamIds);
        $this->respond($this->serializeUsers($users));
    }

    public function attendance(array $teamIds, int $year, int $month): void
    {
        $prestations = $this->getTeamPrestationsForMonth->execute($teamIds, $year, $month);
        $this->respond([
            'period'      => ['year' => $year, 'month' => $month],
            'prestations' => $this->serializePrestations($prestations),
        ]);
    }
}
