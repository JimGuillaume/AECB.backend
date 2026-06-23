<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\CreateWorkCode;
use App\UseCase\CreateWorkSchedule;
use App\UseCase\DeleteWorkCode;
use App\UseCase\DeleteWorkSchedule;
use App\UseCase\ListWorkCodes;
use App\UseCase\ListWorkSchedules;
use App\UseCase\UpdateWorkCode;
use App\UseCase\UpdateWorkSchedule;

final class SettingController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private ListWorkSchedules $listWorkSchedules,
        private ListWorkCodes $listWorkCodes,
        private CreateWorkCode $createWorkCode,
        private UpdateWorkCode $updateWorkCode,
        private DeleteWorkCode $deleteWorkCode,
        private CreateWorkSchedule $createWorkSchedule,
        private UpdateWorkSchedule $updateWorkSchedule,
        private DeleteWorkSchedule $deleteWorkSchedule,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function schedules(): void
    {
        $list = $this->listWorkSchedules->execute();
        $this->respond(array_map([$this, 'serializeSchedule'], $list));
    }

    public function createSchedule(): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        $payload    = $this->readJsonBody();
        $name       = trim((string) ($payload['name']        ?? ''));
        $fraction   = isset($payload['fraction'])   ? (float) $payload['fraction']   : null;
        $dailyHours = isset($payload['daily_hours']) ? (float) $payload['daily_hours'] : null;

        if ($name === '' || $fraction === null || $dailyHours === null) {
            $this->respond(['message' => 'name, fraction and daily_hours are required'], 422);
            return;
        }

        $record = $this->createWorkSchedule->execute($name, $fraction, $dailyHours);
        $this->respond($this->serializeSchedule($record), 201);
    }

    public function updateSchedule(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        $payload    = $this->readJsonBody();
        $name       = trim((string) ($payload['name']        ?? ''));
        $fraction   = isset($payload['fraction'])   ? (float) $payload['fraction']   : null;
        $dailyHours = isset($payload['daily_hours']) ? (float) $payload['daily_hours'] : null;

        if ($name === '' || $fraction === null || $dailyHours === null) {
            $this->respond(['message' => 'name, fraction and daily_hours are required'], 422);
            return;
        }

        $record = $this->updateWorkSchedule->execute($id, $name, $fraction, $dailyHours);
        if ($record === null) {
            $this->respond(['message' => 'Schedule not found'], 404);
            return;
        }

        $this->respond($this->serializeSchedule($record));
    }

    public function destroySchedule(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        if (!$this->deleteWorkSchedule->execute($id)) {
            $this->respond(['message' => 'Schedule not found'], 404);
            return;
        }

        $this->respond(['message' => 'Schedule deleted']);
    }

    // ── Codes ─────────────────────────────────────────────────────────────────

    public function codes(): void
    {
        $list = $this->listWorkCodes->execute();
        $this->respond(array_map([$this, 'serializeCode'], $list));
    }

    public function createCode(): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        $payload     = $this->readJsonBody();
        $codeName    = trim((string) ($payload['code_name']   ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $isWorked    = isset($payload['worked']) ? (bool) $payload['worked'] : false;

        if ($codeName === '') {
            $this->respond(['message' => 'code_name is required'], 422);
            return;
        }

        $record = $this->createWorkCode->execute($codeName, $description, $isWorked);
        $this->respond($this->serializeCode($record), 201);
    }

    public function updateCode(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        $payload     = $this->readJsonBody();
        $codeName    = trim((string) ($payload['code_name']   ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $isWorked    = isset($payload['worked']) ? (bool) $payload['worked'] : false;

        if ($codeName === '') {
            $this->respond(['message' => 'code_name is required'], 422);
            return;
        }

        $record = $this->updateWorkCode->execute($id, $codeName, $description, $isWorked);
        if ($record === null) {
            $this->respond(['message' => 'Code not found'], 404);
            return;
        }

        $this->respond($this->serializeCode($record));
    }

    public function destroyCode(int $id): void
    {
        if ($this->requireAuth() === null) {
            return;
        }

        if (!$this->deleteWorkCode->execute($id)) {
            $this->respond(['message' => 'Code not found'], 404);
            return;
        }

        $this->respond(['message' => 'Code deleted']);
    }

    // ── Serializers ───────────────────────────────────────────────────────────

    private function serializeSchedule(array $s): array
    {
        return [
            'schedule_id' => (int)   $s['schedule_id'],
            'name'        =>          $s['name'],
            'fraction'    => (float)  $s['fraction'],
            'daily_hours' => (float)  $s['daily_hours'],
        ];
    }

    private function serializeCode(array $c): array
    {
        return [
            'code_id'     => (int)  $c['code_id'],
            'code_name'   =>        $c['code_name'],
            'description' =>        $c['description'],
            'worked'      => (bool) $c['worked'],
        ];
    }
}
