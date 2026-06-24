<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\OvertimeRepository;

final class GetUserOvertimeForYear
{
    public function __construct(private OvertimeRepository $overtime)
    {
    }

    public function execute(int $userId, int $year): array
    {
        $rows = $this->overtime->findByUserAndYear($userId, $year);

        // Indexe les lignes par mois pour pouvoir remplir les mois manquants en O(1)
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['month']] = $row;
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            // Les mois sans enregistrement en base sont retournés avec des zéros pour garder un tableau de 12 entrées
            $result[] = $indexed[$m] ?? [
                'overtime_id'   => null,
                'user_id'       => $userId,
                'month'         => $m,
                'year'          => $year,
                'hours_earned'  => 0.0,
                'hours_used'    => 0.0,
                'balance'       => 0.0,
                'calculated_at' => null,
                'updated_at'    => null,
            ];
        }

        return $result;
    }
}
