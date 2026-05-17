<?php
include_once(__DIR__ . "/../patient/PatientModel.php");

class DoctorModel extends PatientModel {

    public function getDoctorIdByUser($user_id) {
        $row = $this->fetchOne("SELECT id FROM doctors WHERE user_id=?", "i", [$user_id]);
        if ($row == null) return 0;
        return $row['id'];
    }

    public function saveAvailability($doctor_id, $day, $start, $end, $duration, $available) {
        $old = $this->fetchOne("SELECT id FROM doctor_availability WHERE doctor_id=? AND day_of_week=?", "is", [$doctor_id, $day]);
        if ($old == null) {
            return $this->execute("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes, is_available) VALUES (?, ?, ?, ?, ?, ?)", "isssii", [$doctor_id, $day, $start, $end, $duration, $available]);
        }
        return $this->execute("UPDATE doctor_availability SET start_time=?, end_time=?, slot_duration_minutes=?, is_available=? WHERE doctor_id=? AND day_of_week=?", "ssiiis", [$start, $end, $duration, $available, $doctor_id, $day]);
    }

    public function getLeaveDates($doctor_id) {
        return $this->fetchAll("SELECT * FROM leave_dates WHERE doctor_id=? ORDER BY leave_date DESC", "i", [$doctor_id]);
    }

    public function addLeaveDate($doctor_id, $date, $reason) {
        return $this->execute("INSERT INTO leave_dates (doctor_id, leave_date, reason) VALUES (?, ?, ?)", "iss", [$doctor_id, $date, $reason]);
    }

    public function removeLeaveDate($id, $doctor_id) {
        return $this->execute("DELETE FROM leave_dates WHERE id=? AND doctor_id=?", "ii", [$id, $doctor_id]);
    }

    public function addPatientDataToAppointment($appointment) {
        $patient = $this->getPatientUserRow($appointment['patient_id']);
        $appointment['patient_name'] = $patient != null ? $patient['patient_name'] : '';
        $appointment['phone'] = $patient != null ? $patient['phone'] : '';
        return $appointment;
    }

    public function getTodayAppointments($doctor_id) {
        $rows = $this->fetchAll("SELECT * FROM appointments WHERE doctor_id=? AND appointment_date=CURDATE() ORDER BY appointment_time", "i", [$doctor_id]);
        $list = [];

        foreach ($rows as $row) {
            $list[] = $this->addPatientDataToAppointment($row);
        }

        return $list;
    }

    public function getWeeklyAppointments($doctor_id) {
        $rows = $this->fetchAll("SELECT * FROM appointments WHERE doctor_id=? ORDER BY appointment_date, appointment_time", "i", [$doctor_id]);
        $list = [];
        $today = strtotime(date('Y-m-d'));
        $last = strtotime('+7 days', $today);

        foreach ($rows as $row) {
            $date = strtotime($row['appointment_date']);
            if ($date >= $today && $date <= $last) {
                $list[] = $this->addPatientDataToAppointment($row);
            }
        }

        return $list;
    }

    public function updateAppointmentStatus($id, $status) {
        return $this->execute("UPDATE appointments SET status=? WHERE id=?", "si", [$status, $id]);
    }

    public function completeAppointment($appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $follow_up) {
        $old = $this->fetchOne("SELECT id FROM consultation_notes WHERE appointment_id=?", "i", [$appointment_id]);

        if ($old == null) {
            $this->execute("INSERT INTO consultation_notes (appointment_id, doctor_id, patient_id, symptoms, diagnosis, prescription, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)", "iiissss", [$appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $follow_up]);
        } else {
            $this->execute("UPDATE consultation_notes SET symptoms=?, diagnosis=?, prescription=?, follow_up_date=? WHERE appointment_id=?", "ssssi", [$symptoms, $diagnosis, $prescription, $follow_up, $appointment_id]);
        }

        $this->execute("UPDATE appointments SET status='completed' WHERE id=?", "i", [$appointment_id]);

        $bill = $this->fetchOne("SELECT id FROM billing WHERE appointment_id=?", "i", [$appointment_id]);
        if ($bill == null) {
            $appointment = $this->getAppointment($appointment_id);
            $amount = $appointment != null ? $appointment['consultation_fee'] : 0;
            $this->execute("INSERT INTO billing (appointment_id, patient_id, amount, payment_status) VALUES (?, ?, ?, 'pending')", "iid", [$appointment_id, $patient_id, $amount]);
        }

        return true;
    }

    public function getPatientNotes($doctor_id, $patient_id) {
        return $this->fetchAll("SELECT * FROM consultation_notes WHERE doctor_id=? AND patient_id=? ORDER BY created_at DESC", "ii", [$doctor_id, $patient_id]);
    }

    public function replyReview($review_id, $doctor_id, $reply) {
        return $this->execute("UPDATE doctor_reviews SET doctor_reply=? WHERE id=? AND doctor_id=?", "sii", [$reply, $review_id, $doctor_id]);
    }

    public function updateDoctorProfile($doctor_id, $bio, $specialization_id, $fee, $license, $experience, $photo = "") {
        if ($photo != "") {
            return $this->execute("UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=?, photo_path=? WHERE id=?", "sidsssi", [$bio, $specialization_id, $fee, $license, $experience, $photo, $doctor_id]);
        }
        return $this->execute("UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=? WHERE id=?", "sidsii", [$bio, $specialization_id, $fee, $license, $experience, $doctor_id]);
    }

    public function getEarningsReport($doctor_id) {
        $appointments = $this->fetchAll("SELECT * FROM appointments WHERE doctor_id=? AND status='completed' ORDER BY appointment_date DESC", "i", [$doctor_id]);
        $report = [];

        foreach ($appointments as $appointment) {
            $bill = $this->fetchOne("SELECT * FROM billing WHERE appointment_id=?", "i", [$appointment['id']]);
            $date = $appointment['appointment_date'];

            if (!isset($report[$date])) {
                $report[$date] = [
                    'appointment_date' => $date,
                    'completed_count' => 0,
                    'total_earning' => 0
                ];
            }

            $report[$date]['completed_count'] = $report[$date]['completed_count'] + 1;
            if ($bill != null) {
                $report[$date]['total_earning'] = $report[$date]['total_earning'] + floatval($bill['amount']);
            }
        }

        return array_values($report);
    }

    public function getDoctorStats($doctor_id) {
        $appointments = $this->fetchAll("SELECT status FROM appointments WHERE doctor_id=?", "i", [$doctor_id]);
        $stats = [
            'completed' => 0,
            'cancelled' => 0,
            'no_show' => 0,
            'total' => 0
        ];

        foreach ($appointments as $appointment) {
            $stats['total'] = $stats['total'] + 1;
            if ($appointment['status'] == 'completed') $stats['completed'] = $stats['completed'] + 1;
            if ($appointment['status'] == 'cancelled') $stats['cancelled'] = $stats['cancelled'] + 1;
            if ($appointment['status'] == 'no_show') $stats['no_show'] = $stats['no_show'] + 1;
        }

        return $stats;
    }

    public function getUpcomingFollowUps($doctor_id) {
        $notes = $this->fetchAll("SELECT * FROM consultation_notes WHERE doctor_id=? ORDER BY follow_up_date", "i", [$doctor_id]);
        $list = [];
        $today = date('Y-m-d');

        foreach ($notes as $note) {
            if ($note['follow_up_date'] != null && $note['follow_up_date'] >= $today) {
                $patient = $this->getPatientUserRow($note['patient_id']);
                $note['patient_name'] = $patient != null ? $patient['patient_name'] : '';
                $list[] = $note;
            }
        }

        return $list;
    }

    public function getMessages($doctor_id) {
        $messages = $this->fetchAll("SELECT * FROM patient_messages WHERE doctor_id=? ORDER BY created_at DESC", "i", [$doctor_id]);
        $list = [];

        foreach ($messages as $message) {
            $patient = $this->getPatientUserRow($message['patient_id']);
            $message['patient_name'] = $patient != null ? $patient['patient_name'] : '';
            $list[] = $message;
        }

        return $list;
    }

    public function replyMessage($id, $doctor_id, $reply) {
        return $this->execute("UPDATE patient_messages SET reply_text=? WHERE id=? AND doctor_id=?", "sii", [$reply, $id, $doctor_id]);
    }
    public function getDoctorScheduleToday($doctor_id) {
        return $this->getTodayAppointments($doctor_id);
    }

    public function getDoctorWeeklyCalendar($doctor_id) {
        return $this->getWeeklyAppointments($doctor_id);
    }

    public function getDoctorPatientNotes($doctor_id, $patient_id) {
        return $this->getPatientNotes($doctor_id, $patient_id);
    }

    public function getDoctorEarnings($doctor_id) {
        return $this->getEarningsReport($doctor_id);
    }

    public function getFollowUps($doctor_id) {
        return $this->getUpcomingFollowUps($doctor_id);
    }

    public function getDoctorMessages($doctor_id) {
        return $this->getMessages($doctor_id);
    }

}
?>
