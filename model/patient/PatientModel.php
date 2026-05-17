<?php
include_once(__DIR__ . "/../user/UserModel.php");

class PatientModel extends UserModel {

    public function getPatientByUser($user_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE user_id=?", "i", [$user_id]);
        $user = $this->getUser($user_id);

        if ($patient == null || $user == null) {
            return null;
        }

        $patient['name'] = $user['name'];
        $patient['email'] = $user['email'];
        $patient['phone'] = $user['phone'];
        $patient['profile_pic'] = $user['profile_pic'];
        return $patient;
    }

    public function getPatientIdByUser($user_id) {
        $row = $this->fetchOne("SELECT id FROM patients WHERE user_id=?", "i", [$user_id]);
        if ($row == null) return 0;
        return $row['id'];
    }

    public function updatePatientProfile($user_id, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone) {
        return $this->execute("UPDATE patients SET date_of_birth=?, blood_group=?, gender=?, address=?, emergency_contact_name=?, emergency_contact_phone=? WHERE user_id=?", "ssssssi", [$dob, $blood, $gender, $address, $emergencyName, $emergencyPhone, $user_id]);
    }

    public function updateMedicalHistory($patient_id, $notes) {
        return $this->execute("UPDATE patients SET medical_history_notes=? WHERE id=?", "si", [$notes, $patient_id]);
    }

    public function getDependents($patient_id) {
        return $this->fetchAll("SELECT * FROM dependents WHERE primary_patient_id=? ORDER BY id DESC", "i", [$patient_id]);
    }

    public function addDependent($patient_id, $name, $dob, $relationship, $blood) {
        return $this->execute("INSERT INTO dependents (primary_patient_id, name, date_of_birth, relationship, blood_group) VALUES (?, ?, ?, ?, ?)", "issss", [$patient_id, $name, $dob, $relationship, $blood]);
    }

    public function updateDependent($id, $patient_id, $name, $dob, $relationship, $blood) {
        return $this->execute("UPDATE dependents SET name=?, date_of_birth=?, relationship=?, blood_group=? WHERE id=? AND primary_patient_id=?", "ssssii", [$name, $dob, $relationship, $blood, $id, $patient_id]);
    }

    public function deleteDependent($id, $patient_id) {
        return $this->execute("DELETE FROM dependents WHERE id=? AND primary_patient_id=?", "ii", [$id, $patient_id]);
    }

    public function getSpecializations() {
        return $this->fetchAll("SELECT * FROM specializations ORDER BY name");
    }

    public function getSpecializationName($specialization_id) {
        $row = $this->fetchOne("SELECT name FROM specializations WHERE id=?", "i", [$specialization_id]);
        return $row != null ? $row['name'] : '';
    }

    public function getDoctorAverageRating($doctor_id) {
        $reviews = $this->fetchAll("SELECT rating FROM doctor_reviews WHERE doctor_id=?", "i", [$doctor_id]);
        if (count($reviews) == 0) return 0;

        $sum = 0;
        foreach ($reviews as $r) {
            $sum = $sum + intval($r['rating']);
        }
        return $sum / count($reviews);
    }

    public function doctorWorksOnDay($doctor_id, $day) {
        $row = $this->fetchOne("SELECT id FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1", "is", [$doctor_id, $day]);
        return $row != null;
    }

    public function getDoctorUserRow($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) return null;

        $user = $this->getUser($doctor['user_id']);
        if ($user == null) return null;

        $doctor['name'] = $user['name'];
        $doctor['email'] = $user['email'];
        $doctor['phone'] = $user['phone'];
        $doctor['profile_pic'] = $user['profile_pic'];
        $doctor['is_active'] = $user['is_active'];
        $doctor['specialization'] = $this->getSpecializationName($doctor['specialization_id']);
        $doctor['avg_rating'] = $this->getDoctorAverageRating($doctor_id);
        return $doctor;
    }

    public function getApprovedDoctors($search = "", $specialization_id = "", $min_fee = "", $max_fee = "", $day = "") {
        $doctors = $this->fetchAll("SELECT * FROM doctors WHERE is_approved=1 ORDER BY id DESC");
        $list = [];

        foreach ($doctors as $doctor) {
            $row = $this->getDoctorUserRow($doctor['id']);
            if ($row == null || $row['is_active'] != 1) continue;

            if ($search != "") {
                $text = strtolower($row['name'] . " " . $row['specialization']);
                if (strpos($text, strtolower($search)) === false) continue;
            }

            if ($specialization_id != "" && $row['specialization_id'] != $specialization_id) continue;
            if ($min_fee != "" && $row['consultation_fee'] < $min_fee) continue;
            if ($max_fee != "" && $row['consultation_fee'] > $max_fee) continue;
            if ($day != "" && !$this->doctorWorksOnDay($row['id'], $day)) continue;

            $list[] = $row;
        }

        return $list;
    }

    public function getDoctorDetail($doctor_id) {
        return $this->getDoctorUserRow($doctor_id);
    }

    public function getDoctorAvailability($doctor_id) {
        return $this->fetchAll("SELECT * FROM doctor_availability WHERE doctor_id=? ORDER BY id", "i", [$doctor_id]);
    }

    public function getAvailableSlots($doctor_id, $date) {
        $leave = $this->fetchOne("SELECT id FROM leave_dates WHERE doctor_id=? AND leave_date=?", "is", [$doctor_id, $date]);
        if ($leave != null) return [];

        $day = date("l", strtotime($date));
        $av = $this->fetchOne("SELECT * FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1", "is", [$doctor_id, $day]);
        if ($av == null) return [];

        $appointments = $this->fetchAll("SELECT appointment_time, status FROM appointments WHERE doctor_id=? AND appointment_date=?", "is", [$doctor_id, $date]);
        $booked = [];
        foreach ($appointments as $a) {
            if ($a['status'] != 'cancelled' && $a['status'] != 'rejected' && $a['status'] != 'no_show') {
                $booked[] = substr($a['appointment_time'], 0, 5);
            }
        }

        $slots = [];
        $start = strtotime($date . " " . $av['start_time']);
        $end = strtotime($date . " " . $av['end_time']);
        $gap = intval($av['slot_duration_minutes']) * 60;

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
        $appointments = $this->fetchAll("SELECT id, status FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=?", "iss", [$doctor_id, $date, $time]);
        foreach ($appointments as $a) {
            if ($a['status'] != 'cancelled' && $a['status'] != 'rejected' && $a['status'] != 'no_show') {
                return false;
            }
        }

        return $this->execute("INSERT INTO appointments (patient_id, dependent_id, doctor_id, appointment_date, appointment_time, reason, booked_by) VALUES (?, ?, ?, ?, ?, ?, ?)", "iiissss", [$patient_id, $dependent_id, $doctor_id, $date, $time, $reason, $booked_by]);
    }

    public function addDoctorDataToAppointment($appointment) {
        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : '';
        $appointment['specialization'] = $doctor != null ? $doctor['specialization'] : '';
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;
        return $appointment;
    }

    public function getUpcomingAppointments($patient_id) {
        $rows = $this->fetchAll("SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date, appointment_time", "i", [$patient_id]);
        $list = [];

        foreach ($rows as $row) {
            if ($row['status'] == 'pending' || $row['status'] == 'confirmed' || $row['status'] == 'checked_in') {
                $list[] = $this->addDoctorDataToAppointment($row);
            }
        }

        return $list;
    }

    public function getPastAppointments($patient_id) {
        $rows = $this->fetchAll("SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date DESC, appointment_time DESC", "i", [$patient_id]);
        $list = [];

        foreach ($rows as $row) {
            if ($row['status'] == 'completed' || $row['status'] == 'cancelled' || $row['status'] == 'no_show' || $row['status'] == 'rejected') {
                $list[] = $this->addDoctorDataToAppointment($row);
            }
        }

        return $list;
    }

    public function getPatientUserRow($patient_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) return null;

        $user = $this->getUser($patient['user_id']);
        if ($user != null) {
            $patient['patient_name'] = $user['name'];
            $patient['name'] = $user['name'];
            $patient['email'] = $user['email'];
            $patient['phone'] = $user['phone'];
            $patient['is_active'] = $user['is_active'];
        }
        return $patient;
    }

    public function getAppointment($id) {
        $appointment = $this->fetchOne("SELECT * FROM appointments WHERE id=?", "i", [$id]);
        if ($appointment == null) return null;

        $patient = $this->getPatientUserRow($appointment['patient_id']);
        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);

        $appointment['patient_user_id'] = $patient != null ? $patient['user_id'] : 0;
        $appointment['patient_name'] = $patient != null ? $patient['patient_name'] : '';
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : '';
        $appointment['consultation_fee'] = $doctor != null ? $doctor['consultation_fee'] : 0;
        return $appointment;
    }

    public function cancelPatientAppointment($id, $patient_id, $reason) {
        $setting = $this->fetchOne("SELECT setting_value FROM hospital_settings WHERE setting_name='cancel_hours'");
        $hours = 6;
        if ($setting != null) $hours = intval($setting['setting_value']);

        $app = $this->fetchOne("SELECT * FROM appointments WHERE id=? AND patient_id=?", "ii", [$id, $patient_id]);
        if ($app == null) return false;

        $appTime = strtotime($app['appointment_date'] . " " . $app['appointment_time']);
        if (($appTime - time()) < ($hours * 3600)) {
            return false;
        }

        return $this->execute("UPDATE appointments SET status='cancelled', cancel_reason=? WHERE id=? AND patient_id=?", "sii", [$reason, $id, $patient_id]);
    }

    public function rescheduleAppointment($id, $patient_id, $date, $time, $note) {
        return $this->execute("UPDATE appointments SET appointment_date=?, appointment_time=?, status='pending', reschedule_note=? WHERE id=? AND patient_id=?", "sssii", [$date, $time, $note, $id, $patient_id]);
    }

    public function getConsultationNoteByAppointment($appointment_id) {
        $note = $this->fetchOne("SELECT * FROM consultation_notes WHERE appointment_id=?", "i", [$appointment_id]);
        if ($note == null) return null;

        $doctor = $this->getDoctorUserRow($note['doctor_id']);
        $patient = $this->getPatientUserRow($note['patient_id']);

        $note['doctor_name'] = $doctor != null ? $doctor['name'] : '';
        $note['patient_name'] = $patient != null ? $patient['patient_name'] : '';
        return $note;
    }

    public function getBillingByPatient($patient_id) {
        $bills = $this->fetchAll("SELECT * FROM billing WHERE patient_id=? ORDER BY id DESC", "i", [$patient_id]);
        $list = [];

        foreach ($bills as $bill) {
            $appointment = $this->getAppointment($bill['appointment_id']);
            $bill['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : '';
            $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : '';
            $list[] = $bill;
        }

        return $list;
    }

    public function submitPaymentIntent($bill_id, $patient_id, $method) {
        return $this->execute("UPDATE billing SET payment_method=? WHERE id=? AND patient_id=? AND payment_status='pending'", "sii", [$method, $bill_id, $patient_id]);
    }

    public function getBill($bill_id) {
        $bill = $this->fetchOne("SELECT * FROM billing WHERE id=?", "i", [$bill_id]);
        if ($bill == null) return null;

        $appointment = $this->getAppointment($bill['appointment_id']);
        $bill['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : '';
        $bill['appointment_time'] = $appointment != null ? $appointment['appointment_time'] : '';
        $bill['patient_name'] = $appointment != null ? $appointment['patient_name'] : '';
        $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : '';
        return $bill;
    }

    public function getOwnReviews($patient_id) {
        $reviews = $this->fetchAll("SELECT * FROM doctor_reviews WHERE patient_id=? ORDER BY id DESC", "i", [$patient_id]);
        $list = [];

        foreach ($reviews as $review) {
            $doctor = $this->getDoctorUserRow($review['doctor_id']);
            $review['doctor_name'] = $doctor != null ? $doctor['name'] : '';
            $list[] = $review;
        }

        return $list;
    }

    public function getDoctorReviews($doctor_id) {
        $reviews = $this->fetchAll("SELECT * FROM doctor_reviews WHERE doctor_id=? ORDER BY created_at DESC", "i", [$doctor_id]);
        $list = [];

        foreach ($reviews as $review) {
            $patient = $this->getPatientUserRow($review['patient_id']);
            $review['patient_name'] = $patient != null ? $patient['patient_name'] : '';
            $list[] = $review;
        }

        return $list;
    }

    public function addReview($appointment_id, $patient_id, $doctor_id, $rating, $text) {
        $old = $this->fetchOne("SELECT id FROM doctor_reviews WHERE appointment_id=? AND patient_id=?", "ii", [$appointment_id, $patient_id]);
        if ($old != null) return false;

        return $this->execute("INSERT INTO doctor_reviews (appointment_id, patient_id, doctor_id, rating, review_text) VALUES (?, ?, ?, ?, ?)", "iiiis", [$appointment_id, $patient_id, $doctor_id, $rating, $text]);
    }

    public function updateReview($id, $patient_id, $rating, $text) {
        return $this->execute("UPDATE doctor_reviews SET rating=?, review_text=? WHERE id=? AND patient_id=?", "isii", [$rating, $text, $id, $patient_id]);
    }

    public function deleteReview($id, $patient_id) {
        return $this->execute("DELETE FROM doctor_reviews WHERE id=? AND patient_id=?", "ii", [$id, $patient_id]);
    }

    public function searchPatients($keyword = "") {
        $patients = $this->fetchAll("SELECT * FROM patients ORDER BY id DESC");
        $list = [];

        foreach ($patients as $patient) {
            $row = $this->getPatientUserRow($patient['id']);
            if ($row == null) continue;

            if ($keyword != "") {
                $text = strtolower($row['name'] . " " . $row['phone'] . " " . $row['email'] . " " . $row['id']);
                if (strpos($text, strtolower($keyword)) === false) continue;
            }

            $list[] = $row;
        }

        return $list;
    }
}
?>
