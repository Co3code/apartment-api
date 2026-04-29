<?php
declare (strict_types = 1);

require_once __DIR__ . '/../models/RoomModel.php';
require_once __DIR__ . '/../utils/response.php';

class AdminRoomController
{
    private RoomModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new RoomModel($db);
    }

    public function addRoom(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $title       = trim($body['title'] ?? '');
        $description = trim($body['description'] ?? '');
        $price       = $body['price'] ?? null;
        $location    = trim($body['location'] ?? '');
        $image_url   = trim($body['image_url'] ?? '');

        if (empty($title) || empty($description) || empty($location) || $price === null) {
            send_response(false, null, 'All fields are required.', 422);
        }

        if (! is_numeric($price) || (float) $price <= 0) {
            send_response(false, null, 'Price must be a positive number.', 422);
        }

        try {
            $id = $this->model->createRoom(
                $title,
                $description,
                (float) $price,
                $location,
                $image_url ?: null
            );
            send_response(true, ['id' => $id], 'Room added successfully.', 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to add room.', 500);
        }
    }

    public function editRoom(int $id): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $title        = trim($body['title'] ?? '');
        $description  = trim($body['description'] ?? '');
        $price        = $body['price'] ?? null;
        $location     = trim($body['location'] ?? '');
        $image_url    = trim($body['image_url'] ?? '');
        $is_available = isset($body['is_available']) ? (int) $body['is_available'] : 1;

        if (empty($title) || empty($description) || empty($location) || $price === null) {
            send_response(false, null, 'All fields are required.', 422);
        }

        if (! is_numeric($price) || (float) $price <= 0) {
            send_response(false, null, 'Price must be a positive number.', 422);
        }

        // Check room exists
        $room = $this->model->getRoomById($id);
        if (! $room) {
            send_response(false, null, 'Room not found.', 404);
        }

        try {
            $updated = $this->model->updateRoom(
                $id,
                $title,
                $description,
                (float) $price,
                $location,
                $image_url ?: null,
                $is_available
            );
            send_response(true, null, 'Room updated successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to update room.', 500);
        }
    }

    public function deleteRoom(int $id): void
    {
        $room = $this->model->getRoomById($id);
        if (! $room) {
            send_response(false, null, 'Room not found.', 404);
        }

        try {
            $this->model->deleteRoom($id);
            send_response(true, null, 'Room deleted successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to delete room.', 500);
        }
    }
}
