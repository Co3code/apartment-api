<?php
declare (strict_types = 1);

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): array | false
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = :email
             AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, 'user')"
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $password,
        ]);
        return (int) $this->db->lastInsertId();
    }
}
