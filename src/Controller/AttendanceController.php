<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\CreateAttendance;
use App\UseCase\DeleteAttendance;
use App\UseCase\ListWorkCodes;
use App\UseCase\UpdateAttendance;

final class AttendanceController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private CreateAttendance $createAttendance,
        private UpdateAttendance $updateAttendance,
        private DeleteAttendance $deleteAttendance,
        private ListWorkCodes $listWorkCodes,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function store(): void
    {
        if (($claims = $this->requireAuth()) === null) {
            return;
        }

        $payload   = $this->readJsonBody();
        $userId    = isset($payload['user_id'])        ? (int)   $payload['user_id']        : null;
        $teamId    = isset($payload['team_id'])         ? (int)   $payload['team_id']         : null;
        $date      = trim((string) ($payload['attendance_date'] ?? ''));
        $codeId    = isset($payload['code_id'])         ? (int)   $payload['code_id']         : null;
        $hours     = isset($payload['hours_value'])     ? (float) $payload['hours_value']     : null;
        $notes     = isset($payload['notes'])           ? trim((string) $payload['notes'])    : null;
        $createdBy = isset($claims['sub'])              ? (int)   $claims['sub']              : null;

        if ($userId === null || $teamId === null || $date === '' || $codeId === null || $hours === null) {
            $this->respond(['message' => 'user_id, team_id, attendance_date, code_id, hours_value are required'], 422);
            return;
        }

        $record = $this->createAttendance->execute($userId, $teamId, $date, $codeId, $hours, $notes ?: null, $createdBy);
        $this->respond($this->serializePrestation($record), 201);
    }

    public function update(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        $payload = $this->readJsonBody();
        $codeId  = isset($payload['code_id'])     ? (int)   $payload['code_id']              : null;
        $hours   = isset($payload['hours_value']) ? (float) $payload['hours_value']           : null;
        $notes   = isset($payload['notes'])       ? trim((string) $payload['notes'])          : null;

        if ($codeId === null || $hours === null) {
            $this->respond(['message' => 'code_id and hours_value are required'], 422);
            return;
        }

        $record = $this->updateAttendance->execute($id, $codeId, $hours, $notes ?: null);
        if ($record === null) {
            $this->respond(['message' => 'Attendance record not found'], 404);
            return;
        }

        $this->respond($this->serializePrestation($record));
    }

    public function destroy(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        if (!$this->deleteAttendance->execute($id)) {
            $this->respond(['message' => 'Attendance record not found'], 404);
            return;
        }

        $this->respond(['message' => 'Attendance record deleted']);
    }

    public function codes(): void
    {
        $list = $this->listWorkCodes->execute();
        $this->respond(array_map(static function (array $row): array {
            return [
                'code_id'     => (int)  $row['code_id'],
                'code_name'   =>        $row['code_name'],
                'description' =>        $row['description'],
                'worked'      => (bool) $row['worked'],
            ];
        }, $list));
    }
}
