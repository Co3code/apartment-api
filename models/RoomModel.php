<?php
declare (strict_types = 1);

class RoomModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllRooms(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, description, price, location, image_url, is_available, created_at
             FROM rooms
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRoomById(int $id): array | false
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, description, price, location, image_url, is_available
             FROM rooms
             WHERE id = :id AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function createRoom(
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $image_url
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO rooms (title, description, price, location, image_url)
             VALUES (:title, :description, :price, :location, :image_url)"
        );
        $stmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':price'       => $price,
            ':location'    => $location,
            ':image_url'   => $image_url,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateRoom(
        int $id,
        string $title,
        string $description,
        float $price,
        string $location,
        ?string $image_url,
        int $is_available
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE rooms
             SET title = :title,
                 description = :description,
                 price = :price,
                 location = :location,
                 image_url = :image_url,
                 is_available = :is_available
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            ':id'           => $id,
            ':title'        => $title,
            ':description'  => $description,
            ':price'        => $price,
            ':location'     => $location,
            ':image_url'    => $image_url,
            ':is_available' => $is_available,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function deleteRoom(int $id): bool
    {
        // Soft delete
        $stmt = $this->db->prepare(
            "UPDATE rooms
             SET deleted_at = NOW()
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
