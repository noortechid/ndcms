<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Employee
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT *
             FROM employees
             ORDER BY created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM employees
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO employees (
                employee_code,
                name,
                phone,
                email,
                address,
                position,
                hire_date,
                employment_status
            ) VALUES (
                :employee_code,
                :name,
                :phone,
                :email,
                :address,
                :position,
                :hire_date,
                :employment_status
            )'
        );

        $stmt->execute([
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'position' => $data['position'],
            'hire_date' => $data['hire_date'] ?: null,
            'employment_status' => $data['employment_status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE employees SET
                employee_code = :employee_code,
                name = :name,
                phone = :phone,
                email = :email,
                address = :address,
                position = :position,
                hire_date = :hire_date,
                employment_status = :employment_status
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'employee_code' => $data['employee_code'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'position' => $data['position'],
            'hire_date' => $data['hire_date'] ?: null,
            'employment_status' => $data['employment_status'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM employees WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db
            ->query('SELECT COUNT(*) FROM employees')
            ->fetchColumn();
    }


public function setStatus(int $id, string $status): bool
{
    if (!in_array($status, ['active', 'inactive'], true)) {
        return false;
    }

    $stmt = $this->db->prepare(
        'UPDATE employees
         SET employment_status = :status
         WHERE id = :id'
    );

    return $stmt->execute([
        'id' => $id,
        'status' => $status,
    ]);
}

public function availableForUser(?int $userId = null): array
{
    $sql = '
        SELECT e.id, e.employee_code, e.name, e.position
        FROM employees e
        LEFT JOIN users u ON u.employee_id = e.id
        WHERE e.employment_status = "active"
        AND (u.id IS NULL OR u.id = :user_id)
        ORDER BY e.name ASC
    ';

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll();
}

}
