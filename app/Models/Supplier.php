<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Supplier
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
             FROM suppliers
             ORDER BY name ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function active(): array
    {
        $stmt = $this->db->query(
            'SELECT *
             FROM suppliers
             WHERE is_active = 1
             ORDER BY name ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM suppliers
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

        return $supplier ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO suppliers (
                supplier_code,
                name,
                contact_person,
                phone,
                email,
                address,
                notes
            ) VALUES (
                :supplier_code,
                :name,
                :contact_person,
                :phone,
                :email,
                :address,
                :notes
            )'
        );

        $stmt->execute([
            'supplier_code' => $data['supplier_code'] ?: null,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE suppliers
             SET
                supplier_code = :supplier_code,
                name = :name,
                contact_person = :contact_person,
                phone = :phone,
                email = :email,
                address = :address,
                notes = :notes
             WHERE id = :id'
        );

        return $stmt->execute([
            'supplier_code' => $data['supplier_code'] ?: null,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
            'notes' => $data['notes'] ?: null,
            'id' => $id,
        ]);
    }

    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE suppliers
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            'is_active' => $active ? 1 : 0,
            'id' => $id,
        ]);
    }
}
