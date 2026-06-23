<?php
declare(strict_types=1);

namespace App\Controller;

use App\Domain\User;
use App\Infrastructure\Security\JwtService;

abstract class BaseController
{
    public function __construct(
        protected JwtService $jwtService,
        protected int $jwtTtlSeconds,
    ) {
    }

    protected function respond(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function readJsonBody(): array
    {
        $body = file_get_contents('php://input');

        if ($body === false || $body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function getAuthenticatedClaims(): ?array
    {
        $token = $_COOKIE['aecb_jwt'] ?? '';

        if ($token === '') {
            return null;
        }

        return $this->jwtService->verify($token);
    }

    protected function requireAuth(): ?array
    {
        $claims = $this->getAuthenticatedClaims();
        if ($claims === null) {
            $this->respond(['message' => 'Unauthorized'], 401);
        }
        return $claims;
    }

    protected function setAuthCookie(string $token): void
    {
        setcookie('aecb_jwt', $token, [
            'expires'  => time() + $this->jwtTtlSeconds,
            'path'     => '/',
            'secure'   => $this->isHttpsRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    protected function clearAuthCookie(): void
    {
        setcookie('aecb_jwt', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $this->isHttpsRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    protected function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id'         => $user->id(),
            'first_name' => $user->first_name(),
            'last_name'  => $user->last_name(),
            'email'      => $user->email(),
            'role'       => $user->role(),
        ];
    }

    protected function serializeUsers(array $users): array
    {
        return array_map([$this, 'serializeUser'], $users);
    }

    protected function serializePrestation(array $p): array
    {
        return [
            'attendance_id'   => isset($p['attendance_id'])  ? (int)   $p['attendance_id']  : null,
            'user_id'         => isset($p['user_id'])         ? (int)   $p['user_id']         : null,
            'team_id'         => isset($p['team_id'])         ? (int)   $p['team_id']         : null,
            'attendance_date' => $p['attendance_date'] ?? null,
            'code_id'         => isset($p['code_id'])         ? (int)   $p['code_id']         : null,
            'code_key'        => $p['code_key']  ?? null,
            'hours_value'     => isset($p['hours_value'])     ? (float) $p['hours_value']     : null,
            'notes'           => $p['notes']      ?? null,
            'created_by'      => isset($p['created_by'])      ? (int)   $p['created_by']      : null,
            'created_at'      => $p['created_at'] ?? null,
            'updated_at'      => $p['updated_at'] ?? null,
        ];
    }

    protected function serializePrestations(array $prestations): array
    {
        return array_map([$this, 'serializePrestation'], $prestations);
    }
}
