<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT user_id AS id, first_name, last_name, email, password_hash, role FROM users WHERE user_id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT user_id AS id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT user_id AS id, first_name, last_name, email, password_hash, role FROM users');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function findPrestationsByUserAndMonth(int $userId, int $year, int $month): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ar.attendance_id,
                ar.user_id,
                ar.team_id,
                ar.attendance_date,
                ar.code_id,
                wc.code_name AS code_key,
                ar.hours_value,
                ar.notes,
                ar.created_by,
                ar.created_at,
                ar.updated_at
             FROM attendance_records ar
             INNER JOIN work_codes wc ON wc.code_id = ar.code_id
             WHERE ar.user_id = :user_id
               AND YEAR(ar.attendance_date) = :year
               AND MONTH(ar.attendance_date) = :month
             ORDER BY ar.attendance_date ASC, ar.attendance_id ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ]);

        return $stmt->fetchAll();
    }

    public function findTeamIdsByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT team_id FROM users_teams WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findUsersByTeamIds(array $teamIds): array
    {
        if (empty($teamIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT u.user_id AS id, u.first_name, u.last_name, u.email, u.password_hash, u.role
             FROM users u
             INNER JOIN users_teams ut ON ut.user_id = u.user_id
             WHERE ut.team_id IN ($placeholders)
             ORDER BY u.last_name, u.first_name"
        );
        $stmt->execute($teamIds);
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    public function findTeamPrestationsByMonth(array $teamIds, int $year, int $month): array
    {
        if (empty($teamIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $params = array_merge($teamIds, [$year, $month]);
        $stmt = $this->pdo->prepare(
            "SELECT ar.attendance_id, ar.user_id, ar.team_id, ar.attendance_date, ar.code_id,
                    wc.code_name AS code_key, ar.hours_value, ar.notes, ar.created_by, ar.created_at, ar.updated_at
             FROM attendance_records ar
             INNER JOIN work_codes wc ON wc.code_id = ar.code_id
             WHERE ar.team_id IN ($placeholders)
               AND YEAR(ar.attendance_date) = ?
               AND MONTH(ar.attendance_date) = ?
             ORDER BY ar.attendance_date ASC, ar.user_id ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAttendanceById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ar.attendance_id, ar.user_id, ar.team_id, ar.attendance_date, ar.code_id,
                    wc.code_name AS code_key, ar.hours_value, ar.notes, ar.created_by, ar.created_at, ar.updated_at
             FROM attendance_records ar
             INNER JOIN work_codes wc ON wc.code_id = ar.code_id
             WHERE ar.attendance_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createAttendance(int $userId, int $teamId, string $date, int $codeId, float $hoursValue, ?string $notes, ?int $createdBy): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO attendance_records (user_id, team_id, attendance_date, code_id, hours_value, notes, created_by)
             VALUES (:user_id, :team_id, :date, :code_id, :hours_value, :notes, :created_by)'
        );
        $stmt->execute([
            'user_id'     => $userId,
            'team_id'     => $teamId,
            'date'        => $date,
            'code_id'     => $codeId,
            'hours_value' => $hoursValue,
            'notes'       => $notes,
            'created_by'  => $createdBy,
        ]);
        return $this->findAttendanceById((int) $this->pdo->lastInsertId());
    }

    public function updateAttendance(int $id, int $codeId, float $hoursValue, ?string $notes): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE attendance_records SET code_id = :code_id, hours_value = :hours_value, notes = :notes
             WHERE attendance_id = :id'
        );
        $stmt->execute([
            'id'          => $id,
            'code_id'     => $codeId,
            'hours_value' => $hoursValue,
            'notes'       => $notes,
        ]);
        return $stmt->rowCount() > 0 ? $this->findAttendanceById($id) : null;
    }

    public function deleteAttendance(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM attendance_records WHERE attendance_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function findAllTeams(): array
    {
        $stmt = $this->pdo->query('SELECT team_id AS id, name FROM teams ORDER BY team_id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllCodes(): array
    {
        $stmt = $this->pdo->query('SELECT code_id, code_name, description, is_counted_as_worked AS worked FROM work_codes ORDER BY code_id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllSchedules(): array
    {
        $stmt = $this->pdo->query('SELECT schedule_id, name, fraction, daily_hours FROM work_schedules WHERE active = 1 ORDER BY fraction DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findCodeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT code_id, code_name, description, is_counted_as_worked AS worked FROM work_codes WHERE code_id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createCode(string $codeName, string $description, bool $isWorked): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO work_codes (code_name, description, is_counted_as_worked) VALUES (:code_name, :description, :worked)');
        $stmt->execute(['code_name' => $codeName, 'description' => $description, 'worked' => (int) $isWorked]);
        return $this->findCodeById((int) $this->pdo->lastInsertId());
    }

    public function updateCode(int $id, string $codeName, string $description, bool $isWorked): ?array
    {
        if ($this->findCodeById($id) === null) {
            return null;
        }
        $stmt = $this->pdo->prepare('UPDATE work_codes SET code_name = :code_name, description = :description, is_counted_as_worked = :worked WHERE code_id = :id');
        $stmt->execute(['id' => $id, 'code_name' => $codeName, 'description' => $description, 'worked' => (int) $isWorked]);
        return $this->findCodeById($id);
    }

    public function deleteCode(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM work_codes WHERE code_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function findScheduleById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT schedule_id, name, fraction, daily_hours FROM work_schedules WHERE schedule_id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createSchedule(string $name, float $fraction, float $dailyHours): array
    {
        $stmt = $this->pdo->prepare('INSERT INTO work_schedules (name, fraction, daily_hours, active) VALUES (:name, :fraction, :daily_hours, 1)');
        $stmt->execute(['name' => $name, 'fraction' => $fraction, 'daily_hours' => $dailyHours]);
        return $this->findScheduleById((int) $this->pdo->lastInsertId());
    }

    public function updateSchedule(int $id, string $name, float $fraction, float $dailyHours): ?array
    {
        if ($this->findScheduleById($id) === null) {
            return null;
        }
        $stmt = $this->pdo->prepare('UPDATE work_schedules SET name = :name, fraction = :fraction, daily_hours = :daily_hours WHERE schedule_id = :id');
        $stmt->execute(['id' => $id, 'name' => $name, 'fraction' => $fraction, 'daily_hours' => $dailyHours]);
        return $this->findScheduleById($id);
    }

    public function deleteSchedule(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE work_schedules SET active = 0 WHERE schedule_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function create(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, password_hash, role)
             VALUES (:first_name, :last_name, :email, :password_hash, :role)'
        );

        $stmt->execute([
            'first_name' => $user->first_name(),
            'last_name' => $user->last_name(),
            'email' => $user->email(),
            'password_hash' => $user->password_hash(),
            'role' => $user->role(),
        ]);

        return new User(
            $user->first_name(),
            $user->last_name(),
            $user->email(),
            $user->password_hash(),
            $user->role(),
            (int) $this->pdo->lastInsertId()
        );
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE user_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function update(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET first_name = :first_name,
                 last_name = :last_name,
                 email = :email,
                 password_hash = :password_hash,
                 role = :role
             WHERE user_id = :id'
        );

        $stmt->execute([
            'id' => $user->id(),
            'first_name' => $user->first_name(),
            'last_name' => $user->last_name(),
            'email' => $user->email(),
            'password_hash' => $user->password_hash(),
            'role' => $user->role(),
        ]);

        return $user;
    }

    private function mapRowToUser(array $row): User
    {
        return User::fromRow($row);
    }
}