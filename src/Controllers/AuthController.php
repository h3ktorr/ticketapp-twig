<?php
namespace Kelvin\TwigApp\Controllers;

class AuthController {

    private static function getUsersFile(): string {
        // Use /tmp for writable storage on Railway
        $tmpFile = sys_get_temp_dir() . '/users.json';

        // Ensure the file exists
        if (!file_exists($tmpFile)) {
            file_put_contents($tmpFile, json_encode([]));
        }

        return $tmpFile;
    }

    private static function readUsers(): array {
        $file = self::getUsersFile();
        return json_decode(file_get_contents($file), true) ?? [];
    }

    private static function writeUsers(array $users): void {
        $file = self::getUsersFile();
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
    }

    public static function currentUser(): ?array {
        return $_SESSION['ticketapp_session']['user'] ?? null;
    }

    public static function login(array $data): ?array {
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($email === '' || $password === '') return null;

        $users = self::readUsers();

        foreach ($users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $session = [
                    'token' => 'mock-token-' . $user['id'],
                    'user' => $user,
                    'expiresAt' => time() + 24 * 3600
                ];
                $_SESSION['ticketapp_session'] = $session;
                return $user;
            }
        }

        return null;
    }

    public static function signup(array $data): array {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            throw new \Exception("All fields are required");
        }

        $users = self::readUsers();

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                throw new \Exception("Email already registered");
            }
        }

        $newUser = [
            'id' => time(),
            'name' => $name,
            'email' => $email,
            'password' => $password
        ];

        $users[] = $newUser;
        self::writeUsers($users);

        $session = [
            'token' => 'mock-token-' . $newUser['id'],
            'user' => $newUser,
            'expiresAt' => time() + 24 * 3600
        ];
        $_SESSION['ticketapp_session'] = $session;

        return $newUser;
    }

    public static function logout(): void {
        unset($_SESSION['ticketapp_session']);
        session_destroy();
    }
}
