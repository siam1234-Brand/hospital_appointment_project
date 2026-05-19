<?php
include_once(__DIR__ . "/../user/UserModel.php");

class ReceptionistModel extends UserModel {

    public function receptionistRegisterPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone) {
        return $this->registerPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone);
    }

    public function getPatientUserRow($patient_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) {
            return null;
        }

        $user = $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$patient['user_id']]);
        if ($user == null) {
            return null;
        }

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

    public function getSpecializationName($specialization_id) {
        $row = $this->fetchOne("SELECT name FROM specializations WHERE id=?", "i", [$specialization_id]);
        if ($row == null) {
            return "";
        }
        return $row['name'];
    }

    public function getDoctorAverageRating($doctor_id) {
        $reviews = $this->fetchAll("SELECT rating FROM doctor_reviews WHERE doctor_id=?", "i", [$doctor_id]);
        if (count($reviews) == 0) {
            return 0;
        }

        $total = 0;
        foreach ($reviews as $review) {
            $total = $total + intval($review['rating']);
        }

        return $total / count($reviews);
    }

    public function getDoctorUserRow($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) {
            return null;
        }

        $user = $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$doctor['user_id']]);
        if ($user == null) {
            return null;
        }

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

    public function searchPatients($keyword = "") {
        $patients = $this->fetchAll("SELECT * FROM patients ORDER BY id DESC");
        $list = [];

        foreach ($patients as $patient) {
            $row = $this->getPatientUserRow($patient['id']);

            if ($row == null) {
                continue;
            }

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

    public function doctorWorksOnDay($doctor_id, $day) {
        $row = $this->fetchOne(
            "SELECT id FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1",
            "is",
            [$doctor_id, $day]
        );

        return $row != null;
    }

    public function getApprovedDoctors($search = "", $specialization_id = "", $min_fee = "", $max_fee = "", $day = "") {
        $doctors = $this->fetchAll("SELECT * FROM doctors WHERE is_approved=1 ORDER BY id DESC");
        $list = [];

        foreach ($doctors as $doctor) {
            $row = $this->getDoctorUserRow($doctor['id']);

            if ($row == null) {
                continue;
            }

            if ($row['is_active'] != 1) {
                continue;
            }

            if ($search != "") {
                $text = strtolower($row['name'] . " " . $row['specialization']);
                if (strpos($text, strtolower($search)) === false) {
                    continue;
                }
            }

            if ($specialization_id != "" && $row['specialization_id'] != $specialization_id) {
                continue;
            }

            if ($min_fee != "" && $row['consultation_fee'] < $min_fee) {
                continue;
            }

            if ($max_fee != "" && $row['consultation_fee'] > $max_fee) {
                continue;
            }

            if ($day != "" && !$this->doctorWorksOnDay($row['id'], $day)) {
                continue;
            }

            $list[] = $row;
        }

        return $list;
    }

    public function getAvailableSlots($doctor_id, $date) {
        if ($doctor_id == "" || $date == "") {
            return [];
        }

        $leave = $this->fetchOne("SELECT id FROM leave_dates WHERE doctor_id=? AND leave_date=?", "is", [$doctor_id, $date]);
        if ($leave != null) {
            return [];
        }

        $day = date("l", strtotime($date));

        $availability = $this->fetchOne(
            "SELECT * FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1",
            "is",
            [$doctor_id, $day]
        );

        if ($availability == null) {
            return [];
        }

        $appointments = $this->fetchAll(
            "SELECT appointment_time, status FROM appointments WHERE doctor_id=? AND appointment_date=?",
            "is",
            [$doctor_id, $date]
        );

        $booked = [];
        foreach ($appointments as $appointment) {
            if ($appointment['status'] != 'cancelled' && $appointment['status'] != 'rejected' && $appointment['status'] != 'no_show') {
                $booked[] = substr($appointment['appointment_time'], 0, 5);
            }
        }

        $slots = [];
        $start = strtotime($date . " " . $availability['start_time']);
        $end = strtotime($date . " " . $availability['end_time']);
        $gap = intval($availability['slot_duration_minutes']) * 60;

        while (($start + $gap) <= $end) {
            $slot = date("H:i", $start);

            if (!in_array($slot, $booked)) {
                $slots[] = $slot;
            }

            $start = $start + $gap;
        }

        return $slots;
    }

    public function bookAppointment($patient_id, $dependent_id, $doctor_id, $date, $time, $reason, $booked_by) {
        if ($dependent_id == "" || $dependent_id == 0) {
            $dependent_id = null;
        }

        $patient = $this->fetchOne("SELECT id FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) {
            return false;
        }

        $doctor = $this->fetchOne("SELECT id FROM doctors WHERE id=? AND is_approved=1", "i", [$doctor_id]);
        if ($doctor == null) {
            return false;
        }

        $available_slots = $this->getAvailableSlots($doctor_id, $date);
        if (!in_array(substr($time, 0, 5), $available_slots)) {
            return false;
        }

        return $this->execute(
            "INSERT INTO appointments (patient_id, dependent_id, doctor_id, appointment_date, appointment_time, reason, booked_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            "iiissss",
            [$patient_id, $dependent_id, $doctor_id, $date, $time, $reason, $booked_by]
        );
    }

    public function updateAppointmentStatus($id, $status) {
        $allowed = ["pending", "confirmed", "checked_in", "completed", "cancelled", "no_show", "rejected"];

        if (!in_array($status, $allowed)) {
            return false;
        }

        return $this->execute("UPDATE appointments SET status=? WHERE id=?", "si", [$status, $id]);
    }

    public function getAppointment($id) {
        $appointment = $this->fetchOne("SELECT * FROM appointments WHERE id=?", "i", [$id]);
        if ($appointment == null) {
            return null;
        }

        $patient = $this->getPatientUserRow($appointment['patient_id']);
        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);

        $appointment['patient_user_id'] = $patient != null ? $patient['user_id'] : 0;
        $appointment['patient_name'] = $patient != null ? $patient['patient_name'] : "";
        $appointment['phone'] = $patient != null ? $patient['phone'] : "";
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : "";
        $appointment['specialization'] = $doctor != null ? $doctor['specialization'] : "";
        $appointment['specialization_name'] = $doctor != null ? $doctor['specialization'] : "";
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;

        return $appointment;
    }

    public function addFullDataToAppointment($appointment) {
        $patient = $this->getPatientUserRow($appointment['patient_id']);
        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);

        $appointment['patient_name'] = $patient != null ? $patient['patient_name'] : "";
        $appointment['phone'] = $patient != null ? $patient['phone'] : "";
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : "";
        $appointment['specialization'] = $doctor != null ? $doctor['specialization'] : "";
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;

        return $appointment;
    }

    public function getAllAppointments($date = "", $status = "", $doctor_id = "", $booked_by = "") {
        $rows = $this->fetchAll("SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time");
        $list = [];

        foreach ($rows as $row) {
            if ($date != "" && $row['appointment_date'] != $date) continue;
            if ($status != "" && $row['status'] != $status) continue;
            if ($doctor_id != "" && $row['doctor_id'] != $doctor_id) continue;
            if ($booked_by != "" && $row['booked_by'] != $booked_by) continue;

            $list[] = $this->addFullDataToAppointment($row);
        }

        return $list;
    }

    public function getWaitingQueue() {
        $rows = $this->fetchAll("SELECT * FROM appointments WHERE appointment_date=CURDATE() AND status='checked_in' ORDER BY appointment_time");
        $list = [];

        foreach ($rows as $row) {
            $list[] = $this->addFullDataToAppointment($row);
        }

        return $list;
    }

    public function getPendingBills() {
        $bills = $this->fetchAll("SELECT * FROM billing WHERE payment_status='pending' ORDER BY id DESC");
        $list = [];

        foreach ($bills as $bill) {
            $appointment = $this->getAppointment($bill['appointment_id']);

            $bill['patient_name'] = $appointment != null ? $appointment['patient_name'] : "";
            $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : "";
            $bill['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : "";
            $bill['appointment_time'] = $appointment != null ? $appointment['appointment_time'] : "";

            $list[] = $bill;
        }

        return $list;
    }

    public function getBill($bill_id) {
        $bill = $this->fetchOne("SELECT * FROM billing WHERE id=?", "i", [$bill_id]);
        if ($bill == null) {
            return null;
        }

        $appointment = $this->getAppointment($bill['appointment_id']);

        $bill['patient_name'] = $appointment != null ? $appointment['patient_name'] : "";
        $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : "";
        $bill['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : "";
        $bill['appointment_time'] = $appointment != null ? $appointment['appointment_time'] : "";

        return $bill;
    }

    public function getDailySummary($date) {
        $appointments = $this->fetchAll("SELECT * FROM appointments WHERE appointment_date=?", "s", [$date]);
        $summary = [
            "total_appointments" => 0,
            "check_ins" => 0,
            "completed" => 0,
            "cancelled" => 0,
            "revenue" => 0
        ];

        foreach ($appointments as $appointment) {
            $summary['total_appointments']++;

            if ($appointment['status'] == "checked_in") $summary['check_ins']++;
            if ($appointment['status'] == "completed") $summary['completed']++;
            if ($appointment['status'] == "cancelled") $summary['cancelled']++;
        }

        $paid_at = $date . "%";
        $bills = $this->fetchAll("SELECT amount FROM billing WHERE payment_status='paid' AND paid_at LIKE ?", "s", [$paid_at]);

        foreach ($bills as $bill) {
            $summary['revenue'] = $summary['revenue'] + floatval($bill['amount']);
        }

        return $summary;
    }

    public function markBillPaid($bill_id, $method) {
        $bill = $this->fetchOne("SELECT id FROM billing WHERE id=? AND payment_status='pending'", "i", [$bill_id]);
        if ($bill == null) {
            return false;
        }

        return $this->execute(
            "UPDATE billing SET payment_method=?, payment_status='paid', paid_at=NOW() WHERE id=?",
            "si",
            [$method, $bill_id]
        );
    }
}
?>
