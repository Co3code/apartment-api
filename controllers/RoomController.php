<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/RoomModel.php';
require_once __DIR__ . '/../utils/response.php';

class RoomController {
    private RoomModel $model;

    public function __construct(PDO $db) {
        $this->model = new RoomModel($db);
    }

    public function getRooms(): void {
        try {
            $rooms = $this->model->getAllRooms();
            send_response(true, $rooms, 'Rooms fetched successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to fetch rooms.', 500);
        }
    }
}