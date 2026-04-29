<?php
    declare (strict_types = 1);

    require_once __DIR__ . '/../models/BookingModel.php';
    require_once __DIR__ . '/../utils/response.php';

    class BookingController
    {
    private BookingModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new BookingModel($db);
    }

    public function createBooking(int $user_id): void
    {
        $body    = json_decode(file_get_contents('php://input'), true);
        $room_id = isset($body['room_id']) ? (int) $body['room_id'] : 0;
        $message = trim($body['message'] ?? '');

        if ($room_id <= 0) {
            send_response(false, null, 'Invalid room.', 422);
        }

        if ($this->model->hasExistingBooking($user_id, $room_id)) {
            send_response(false, null, 'You already have a pending booking for this room.', 409);
        }

        try {
            $id = $this->model->createBooking($user_id, $room_id, $message);
            send_response(true, ['id' => $id], 'Booking created successfully.', 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to create booking.', 500);
        }
    }

    public function getUserBookings(int $user_id): void
    {
        try {
            $bookings = $this->model->getUserBookings($user_id);
            send_response(true, $bookings, 'Bookings fetched successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to fetch bookings.', 500);
        }
    }

    public function getAllBookings(): void
    {
        try {
            $bookings = $this->model->getAllBookings();
            send_response(true, $bookings, 'All bookings fetched successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to fetch bookings.', 500);
        }
    }

    public function updateBookingStatus(int $id): void
    {
        $body   = json_decode(file_get_contents('php://input'), true);
        $status = trim($body['status'] ?? '');

        if (! in_array($status, ['approved', 'rejected'], true)) {
            send_response(false, null, 'Invalid status.', 422);
        }

        // Get the room ID before updating status
        $room_id = $this->model->getRoomIdByBooking($id);
        if (! $room_id) {
            send_response(false, null, 'Booking not found.', 404);
        }

        try {
            $updated = $this->model->updateBookingStatus($id, $status);
            if (! $updated) {
                send_response(false, null, 'Booking not found.', 404);
            }

            // If approved — mark room as unavailable
            // If rejected — mark room as available again
            if ($status === 'approved') {
                $this->model->setRoomAvailability($room_id, 0);
            } elseif ($status === 'rejected') {
                $this->model->setRoomAvailability($room_id, 1);
            }

            send_response(true, null, 'Booking status updated successfully.', 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Failed to update booking status.', 500);
        }
    }
}