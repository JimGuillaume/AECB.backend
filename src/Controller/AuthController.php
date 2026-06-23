<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\AuthenticateUser;
use App\UseCase\GetUserById;
use App\UseCase\GetUserPrestationsForMonth;
use App\UseCase\GetUserTeamIds;

final class AuthController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private AuthenticateUser $authenticateUser,
        private GetUserById $getUserById,
        private GetUserPrestationsForMonth $getUserPrestationsForMonth,
        private GetUserTeamIds $getUserTeamIds,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function login(): void
    {
        $payload = $this->readJsonBody();

        $email    = trim((string) ($payload['email']    ?? ''));
        $password =      (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->respond(['message' => 'email and password are required'], 422);
            return;
        }

        $user = $this->authenticateUser->execute($email, $password);

        if ($user === null) {
            $this->respond(['message' => 'Invalid credentials'], 401);
            return;
        }

        $token = $this->jwtService->issue([
            'sub'   => $user->id(),
            'email' => $user->email(),
            'role'  => $user->role(),
        ]);

        $this->setAuthCookie($token);

        $this->respond([
            'message' => 'Login successful',
            'user'    => $this->serializeUser($user),
        ]);
    }

    public function me(int $year, int $month): void
    {
        $claims = $this->getAuthenticatedClaims();

        if ($claims === null) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $userId = isset($claims['sub']) ? (int) $claims['sub'] : null;

        if ($userId === null || $userId <= 0) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getUserById->execute($userId);

        if ($user === null) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $prestations = $this->getUserPrestationsForMonth->execute($userId, $year, $month);
        $teamIds     = array_map('intval', $this->getUserTeamIds->execute($userId));

        $this->respond([
            'message'     => 'Authenticated',
            'user'        => $this->serializeUser($user),
            'period'      => ['year' => $year, 'month' => $month],
            'prestations' => $this->serializePrestations($prestations),
            'team_ids'    => $teamIds,
        ]);
    }

    public function logout(): void
    {
        $this->clearAuthCookie();
        $this->respond(['message' => 'Logged out']);
    }
}
