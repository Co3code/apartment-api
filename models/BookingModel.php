<?php
declare(strict_types=1);

class BookingModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createBooking(int $user_id, int $room_id, string $message): int {
        $stmt = $this->db->prepare(
            "INSERT INTO bookings (user_id, room_id, message)
             VALUES (:user_id, :room_id, :message)"
        );
        $stmt->execute([
            ':user_id' => $user_id,
            ':room_id' => $room_id,
            ':message' => $message
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getUserBookings(int $user_id): array {
        $stmt = $this->db->prepare(
            "SELECT b.id, b.status, b.message, b.created_at,
                    r.title AS room_title, r.price, r.location
             FROM bookings b
             JOIN rooms r ON b.room_id = r.id
             WHERE b.user_id = :user_id
             AND b.deleted_at IS NULL
             ORDER BY b.created_at DESC"
        );
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function getAllBookings(): array {
        $stmt = $this->db->prepare(
            "SELECT b.id, b.status, b.message, b.created_at,
                    r.title AS room_title, r.price, r.location,
                    u.name AS user_name, u.email AS user_email
             FROM bookings b
             JOIN rooms r ON b.room_id = r.id
             JOIN users u ON b.user_id = u.id
             WHERE b.deleted_at IS NULL
             ORDER BY b.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateBookingStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare(
            "UPDATE bookings
             SET status = :status
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function hasExistingBooking(int $user_id, int $room_id): bool {
        $stmt = $this->db->prepare(
            "SELECT id FROM bookings
             WHERE user_id = :user_id
             AND room_id = :room_id
             AND status = 'pending'
             AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([
            ':user_id' => $user_id,
            ':room_id' => $room_id
        ]);
        return (bool) $stmt->fetch();
    }
}