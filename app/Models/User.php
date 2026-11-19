<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use InvalidArgumentException;

final class User
{
    private PDO $db;

    private const ROLES = [
        'owner',
        'admin',
        'cashier',
        'inventory_staff',
    ];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT
                u.id,
                u.employee_id,
                u.name,
                u.username,
                u.email,
                u.role,
                u.is_active,
                u.last_login_at,
                u.created_at,
                e.employee_code
             FROM users u
             LEFT JOIN employees e ON e.id = u.employee_id
             ORDER BY u.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                employee_id,
                name,
                username,
                email,
                role,
                is_active
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM users
             WHERE username = :username
             LIMIT 1'
        );

        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $this->validateRole($data['role']);

        $stmt = $this->db->prepare(
            'INSERT INTO users (
                employee_id,
                name,
                username,
                email,
                password,
                role,
                is_active
            ) VALUES (
                :employee_id,
                :name,
                :username,
                :email,
                :password,
                :role,
                :is_active
            )'
        );

        $stmt->execute([
            'employee_id' => $data['employee_id'] ?: null,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'password' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            ),
            'role' => $data['role'],
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->validateRole($data['role']);

        $stmt = $this->db->prepare(
            'UPDATE users SET
                employee_id = :employee_id,
                name = :name,
                username = :username,
                email = :email,
                role = :role,
                is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'employee_id' => $data['employee_id'] ?: null,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'role' => $data['role'],
            'is_active' => $data['is_active'],
        ]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET password = :password
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET last_login_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
    }

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'is_active' => (int) $active,
        ]);
    }

    private function validateRole(string $role): void
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Invalid user role.');
        }
    }

public function countActiveOwners(): int
{
    $stmt = $this->db->query(
"SELECT
    u.id,
    u.employee_id,
    u.name,
    u.username,
    u.email,
    u.role,
    u.is_active,
    u.last_login_at,
    u.created_at,
    e.employee_code,
    e.name AS employee_name
FROM users u
LEFT JOIN employees e ON e.id = u.employee_id
ORDER BY u.created_at DESC"
    );

    return (int) $stmt->fetchColumn();
}

}
