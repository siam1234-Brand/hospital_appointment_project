<?php
include_once(__DIR__ . "/../user/UserModel.php");

class DoctorModel extends UserModel {

    public function getDoctorIdByUser($user_id) {
        $row = $this->fetchOne("SELECT id FROM doctors WHERE user_id=?", "i", [$user_id]);
        if ($row == null) return 0;
        return $row['id'];
    }

    public function getPatientUserRow($patient_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) return null;

        $user = $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$patient['user_id']]);
        if ($user == null) return null;

        $patient['patient_id'] = $patient['id'];
        $patient['user_id'] = $user['id'];
        $patient['patient_name'] = $user['name'];
        $patient['name'] = $user['name'];
        $patient['email'] = $user['email'];
        $patient['phone'] = $user['phone'];
        $patient['profile_pic'] = $user['profile_pic'];
        $patient['is_active'] = $user['is_active'];

        return $patient;
    }

    public function getSpecializations() {
        return $this->fetchAll("SELECT * FROM specializations ORDER BY name");
    }

    public function getSpecializationName($specialization_id) {
        $row = $this->fetchOne("SELECT name FROM specializations WHERE id=?", "i", [$specialization_id]);
        if ($row == null) return "";
        return $row['name'];
    }

    public function getDoctorAverageRating($doctor_id) {
        $reviews = $this->fetchAll("SELECT rating FROM doctor_reviews WHERE doctor_id=?", "i", [$doctor_id]);
        if (count($reviews) == 0) return 0;

        $total = 0;
        foreach ($reviews as $review) {
            $total = $total + intval($review['rating']);
        }

        return $total / count($reviews);
    }

    public function getDoctorUserRow($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) return null;

        $user = $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$doctor['user_id']]);
        if ($user == null) return null;

        $doctor['doctor_id'] = $doctor['id'];
        $doctor['name'] = $user['name'];
        $doctor['email'] = $user['email'];
        $doctor['phone'] = $user['phone'];
        $doctor['profile_pic'] = $user['profile_pic'];
        $doctor['is_active'] = $user['is_active'];
        $doctor['specialization'] = $this->getSpecializationName($doctor['specialization_id']);
        $doctor['specialization_name'] = $doctor['specialization'];
        $doctor['avg_rating'] = $this->getDoctorAverageRating($doctor_id);

        return $doctor;
    }

    public function getDoctorDetail($doctor_id) {
        return $this->getDoctorUserRow($doctor_id);
    }

    public function getDoctorAvailability($doctor_id) {
        return $this->fetchAll(
            "SELECT * FROM doctor_availability WHERE doctor_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')",
            "i",
            [$doctor_id]
        );
    }

    public function saveAvailability($doctor_id, $day, $start, $end, $duration, $available) {
        $old = $this->fetchOne(
            "SELECT id FROM doctor_availability WHERE doctor_id=? AND day_of_week=?",
            "is",
            [$doctor_id, $day]
        );

        if ($old == null) {
            return $this->execute(
                "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes, is_available) VALUES (?, ?, ?, ?, ?, ?)",
                "isssii",
                [$doctor_id, $day, $start, $end, $duration, $available]
            );
        }

        return $this->execute(
            "UPDATE doctor_availability SET start_time=?, end_time=?, slot_duration_minutes=?, is_available=? WHERE doctor_id=? AND day_of_week=?",
            "ssiiis",
            [$start, $end, $duration, $available, $doctor_id, $day]
        );
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

    public function getAppointment($appointment_id) {
        $appointment = $this->fetchOne("SELECT * FROM appointments WHERE id=?", "i", [$appointment_id]);
        if ($appointment == null) return null;

        $appointment = $this->addPatientDataToAppointment($appointment);

        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : "";
        $appointment['specialization_name'] = $doctor != null ? $doctor['specialization_name'] : "";
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;

        return $appointment;
    }

    public function getAppointmentForDoctor($appointment_id, $doctor_id) {
        $appointment = $this->fetchOne(
            "SELECT * FROM appointments WHERE id=? AND doctor_id=?",
            "ii",
            [$appointment_id, $doctor_id]
        );

        if ($appointment == null) return null;

        $appointment = $this->addPatientDataToAppointment($appointment);
        $doctor = $this->getDoctorUserRow($doctor_id);
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : "";
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;

        return $appointment;
    }

    public function getTodayAppointments($doctor_id) {
        $rows = $this->fetchAll(
            "SELECT * FROM appointments WHERE doctor_id=? AND appointment_date=CURDATE() ORDER BY appointment_time",
            "i",
            [$doctor_id]
        );

        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->addPatientDataToAppointment($row);
        }
        return $list;
    }

        public function getWeeklyAppointments($doctor_id) {
        $rows = $this->fetchAll(
            "SELECT * FROM appointments 
            WHERE doctor_id=? 
            AND appointment_date >= CURDATE() 
            AND appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY appointment_date, appointment_time",
            "i",
            [$doctor_id]
        );

        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->addPatientDataToAppointment($row);
        }

        return $list;
    }

    public function getAllDoctorAppointments($doctor_id) {
        $rows = $this->fetchAll(
            "SELECT * FROM appointments WHERE doctor_id=? ORDER BY appointment_date DESC, appointment_time DESC",
            "i",
            [$doctor_id]
        );

        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->addPatientDataToAppointment($row);
        }

        return $list;
    }

    public function updateAppointmentStatus($id, $status, $doctor_id = 0) {
        if ($status == "rejected") {
            $status = "cancelled";
        }

        $allowed = ["pending", "confirmed", "checked_in", "completed", "cancelled", "no_show"];

        if (!in_array($status, $allowed)) {
            return false;
        }

        if ($doctor_id > 0) {
            return $this->execute(
                "UPDATE appointments SET status=? WHERE id=? AND doctor_id=?",
                "sii",
                [$status, $id, $doctor_id]
            );
        }

        return $this->execute("UPDATE appointments SET status=? WHERE id=?", "si", [$status, $id]);
    }

    public function completeAppointment($appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $follow_up) {
        $appointment = $this->getAppointmentForDoctor($appointment_id, $doctor_id);

        if ($appointment == null || $appointment['patient_id'] != $patient_id) {
            return false;
        }

        $old = $this->fetchOne("SELECT id FROM consultation_notes WHERE appointment_id=?", "i", [$appointment_id]);

        if ($old == null) {
            $this->execute(
                "INSERT INTO consultation_notes (appointment_id, doctor_id, patient_id, symptoms, diagnosis, prescription, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
                "iiissss",
                [$appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $follow_up]
            );
        } else {
            $this->execute(
                "UPDATE consultation_notes SET symptoms=?, diagnosis=?, prescription=?, follow_up_date=? WHERE appointment_id=?",
                "ssssi",
                [$symptoms, $diagnosis, $prescription, $follow_up, $appointment_id]
            );
        }

        $this->execute("UPDATE appointments SET status='completed' WHERE id=? AND doctor_id=?", "ii", [$appointment_id, $doctor_id]);

        $bill = $this->fetchOne("SELECT id FROM billing WHERE appointment_id=?", "i", [$appointment_id]);

        if ($bill == null) {
            $amount = $appointment['consultation_fee'];

            $this->execute(
                "INSERT INTO billing (appointment_id, patient_id, amount, payment_status) VALUES (?, ?, ?, 'pending')",
                "iid",
                [$appointment_id, $patient_id, $amount]
            );
        }

        return true;
    }

    public function searchPatients($keyword = "") {
        $patients = $this->fetchAll("SELECT * FROM patients ORDER BY id DESC");
        $list = [];

        foreach ($patients as $patient) {
            $row = $this->getPatientUserRow($patient['id']);

            if ($row == null) continue;

            if ($keyword != "") {
                $text = strtolower($row['id'] . " " . $row['patient_name'] . " " . $row['phone'] . " " . $row['email']);
                if (strpos($text, strtolower($keyword)) === false) {
                    continue;
                }
            }

            $list[] = $row;
        }

        return $list;
    }

    public function getPatientNotes($doctor_id, $patient_id) {
        $notes = $this->fetchAll(
            "SELECT * FROM consultation_notes WHERE doctor_id=? AND patient_id=? ORDER BY created_at DESC",
            "ii",
            [$doctor_id, $patient_id]
        );

        $list = [];
        foreach ($notes as $note) {
            $appointment = $this->getAppointment($note['appointment_id']);
            $note['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : "";
            $list[] = $note;
        }

        return $list;
    }

    public function getDoctorReviews($doctor_id) {
        $reviews = $this->fetchAll("SELECT * FROM doctor_reviews WHERE doctor_id=? ORDER BY created_at DESC", "i", [$doctor_id]);
        $list = [];

        foreach ($reviews as $review) {
            $patient = $this->getPatientUserRow($review['patient_id']);
            $review['patient_name'] = $patient != null ? $patient['patient_name'] : "";
            if (!isset($review['doctor_reply'])) {
                $review['doctor_reply'] = "";
            }
            $list[] = $review;
        }

        return $list;
    }

    public function replyReview($review_id, $doctor_id, $reply) {
        return $this->execute("UPDATE doctor_reviews SET doctor_reply=? WHERE id=? AND doctor_id=?", "sii", [$reply, $review_id, $doctor_id]);
    }

    public function updateDoctorProfile($doctor_id, $bio, $specialization_id, $fee, $license, $experience, $photo = "") {
        if ($photo != "") {
            return $this->execute(
                "UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=?, photo_path=? WHERE id=?",
                "sidsisi",
                [$bio, $specialization_id, $fee, $license, $experience, $photo, $doctor_id]
            );
        }

        return $this->execute(
            "UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=? WHERE id=?",
            "sidsii",
            [$bio, $specialization_id, $fee, $license, $experience, $doctor_id]
        );
    }

    public function getEarningsReport($doctor_id, $period = 'month') {
    // Build the date range based on the chosen period
    if ($period === 'day') {
        $date_condition = "AND appointment_date = CURDATE()";
        $types = "i";
        $params = [$doctor_id];
    } elseif ($period === 'week') {
        $date_condition = "AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $types = "i";
        $params = [$doctor_id];
    } else {
        // default: month
        $date_condition = "AND MONTH(appointment_date) = MONTH(CURDATE()) AND YEAR(appointment_date) = YEAR(CURDATE())";
        $types = "i";
        $params = [$doctor_id];
    }

    $appointments = $this->fetchAll(
        "SELECT * FROM appointments 
         WHERE doctor_id=? AND status='completed' $date_condition 
         ORDER BY appointment_date DESC",
        $types,
        $params
    );

    $report = [];

    foreach ($appointments as $appointment) {
        $bill = $this->fetchOne(
            "SELECT * FROM billing WHERE appointment_id=?",
            "i",
            [$appointment['id']]
        );
        $date = $appointment['appointment_date'];

        if (!isset($report[$date])) {
            $report[$date] = [
                'appointment_date'  => $date,
                'completed_count'   => 0,
                'total_earning'     => 0
            ];
        }

        $report[$date]['completed_count']++;

        if ($bill != null) {
            $report[$date]['total_earning'] += floatval($bill['amount']);
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
            $stats['total']++;

            if ($appointment['status'] == 'completed') $stats['completed']++;
            if ($appointment['status'] == 'cancelled') $stats['cancelled']++;
            if ($appointment['status'] == 'no_show') $stats['no_show']++;
        }

        return $stats;
    }

        public function getDoctorBusiestTimes($doctor_id) {
        // Busiest days of the week (e.g. Monday had 12 appointments)
        $days = $this->fetchAll(
            "SELECT DAYNAME(appointment_date) AS day_name, COUNT(*) AS total
            FROM appointments
            WHERE doctor_id=? AND status='completed'
            GROUP BY DAYNAME(appointment_date)
            ORDER BY total DESC",
            "i",
            [$doctor_id]
        );

        // Busiest hours (e.g. 10:00 AM had 8 appointments)
        $hours = $this->fetchAll(
            "SELECT HOUR(appointment_time) AS hour, COUNT(*) AS total
            FROM appointments
            WHERE doctor_id=? AND status='completed'
            GROUP BY HOUR(appointment_time)
            ORDER BY total DESC",
            "i",
            [$doctor_id]
        );

        return [
            'busiest_days'  => $days,
            'busiest_hours' => $hours
        ];
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
