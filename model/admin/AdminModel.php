<?php
include_once(__DIR__ . "/../user/UserModel.php");

class AdminModel extends UserModel {

    /* ---------- Common helper methods for admin ---------- */

    public function getSpecializations() {
        return $this->fetchAll("SELECT * FROM specializations ORDER BY name");
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

        $sum = 0;
        foreach ($reviews as $review) {
            $sum = $sum + intval($review['rating']);
        }

        return $sum / count($reviews);
    }

    public function getDoctorUserRow($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) {
            return null;
        }

        $user = $this->getUser($doctor['user_id']);
        if ($user == null) {
            return null;
        }

        $doctor['name'] = $user['name'];
        $doctor['email'] = $user['email'];
        $doctor['phone'] = $user['phone'];
        $doctor['profile_pic'] = $user['profile_pic'];
        $doctor['is_active'] = $user['is_active'];
        $doctor['specialization'] = $this->getSpecializationName($doctor['specialization_id']);
        $doctor['avg_rating'] = $this->getDoctorAverageRating($doctor_id);

        return $doctor;
    }

    public function getPatientUserRow($patient_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) {
            return null;
        }

        $user = $this->getUser($patient['user_id']);
        if ($user == null) {
            return null;
        }

        $patient['patient_name'] = $user['name'];
        $patient['name'] = $user['name'];
        $patient['email'] = $user['email'];
        $patient['phone'] = $user['phone'];
        $patient['profile_pic'] = $user['profile_pic'];
        $patient['is_active'] = $user['is_active'];

        return $patient;
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

        return $appointment;
    }

    /* ---------- Specialization management ---------- */

    public function addSpecialization($name, $description) {
        return $this->execute(
            "INSERT INTO specializations (name, description) VALUES (?, ?)",
            "ss",
            [$name, $description]
        );
    }

    public function updateSpecialization($id, $name, $description) {
        return $this->execute(
            "UPDATE specializations SET name=?, description=? WHERE id=?",
            "ssi",
            [$name, $description, $id]
        );
    }

    public function deleteSpecialization($id) {
        return $this->execute("DELETE FROM specializations WHERE id=?", "i", [$id]);
    }

    /* ---------- Announcement management ---------- */

    public function getAnnouncements($role = "all") {
        $rows = $this->fetchAll("SELECT * FROM announcements ORDER BY published_at DESC");
        $list = [];

        foreach ($rows as $row) {
            $author = $this->getUser($row['author_id']);
            $row['author'] = $author != null ? $author['name'] : "";
            $list[] = $row;
        }

        return $list;
    }

    public function addAnnouncement($author_id, $title, $body, $target) {
        return $this->execute(
            "INSERT INTO announcements (author_id, title, body, target_role) VALUES (?, ?, ?, ?)",
            "isss",
            [$author_id, $title, $body, $target]
        );
    }

    public function deleteAnnouncement($id) {
        return $this->execute("DELETE FROM announcements WHERE id=?", "i", [$id]);
    }

    /* ---------- Doctor management ---------- */

    public function adminCreateDoctor($name, $email, $password, $phone, $specialization_id, $bio, $fee, $license, $experience) {
        $old = $this->fetchOne("SELECT id FROM users WHERE email=?", "s", [$email]);
        if ($old != null) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->execute(
            "INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'doctor')",
            "ssss",
            [$name, $email, $hash, $phone]
        );

        $user_id = $this->getLastId();

        return $this->execute(
            "INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, license_number, experience_years, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)",
            "iisdsi",
            [$user_id, $specialization_id, $bio, $fee, $license, $experience]
        );
    }

    public function getAllDoctorsAdmin() {
        $doctors = $this->fetchAll("SELECT * FROM doctors ORDER BY id DESC");
        $list = [];

        foreach ($doctors as $doctor) {
            $row = $this->getDoctorUserRow($doctor['id']);
            if ($row != null) {
                $list[] = $row;
            }
        }

        return $list;
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

    public function doctorWorksOnDay($doctor_id, $day) {
        $row = $this->fetchOne(
            "SELECT id FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1",
            "is",
            [$doctor_id, $day]
        );

        return $row != null;
    }

    public function adminUpdateDoctor($doctor_id, $name, $email, $phone, $specialization_id, $bio, $fee, $license, $experience, $approved) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) {
            return false;
        }

        $this->execute(
            "UPDATE users SET name=?, email=?, phone=? WHERE id=?",
            "sssi",
            [$name, $email, $phone, $doctor['user_id']]
        );

        return $this->execute(
            "UPDATE doctors SET specialization_id=?, bio=?, consultation_fee=?, license_number=?, experience_years=?, is_approved=? WHERE id=?",
            "isdsiii",
            [$specialization_id, $bio, $fee, $license, $experience, $approved, $doctor_id]
        );
    }

    public function deactivateDoctor($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) {
            return false;
        }

        return $this->execute("UPDATE users SET is_active=0 WHERE id=?", "i", [$doctor['user_id']]);
    }

    /* ---------- Staff and patient management ---------- */

    public function getStaff($role) {
        return $this->fetchAll("SELECT * FROM users WHERE role=? ORDER BY id DESC", "s", [$role]);
    }

    public function createStaff($name, $email, $password, $phone, $role) {
        $old = $this->fetchOne("SELECT id FROM users WHERE email=?", "s", [$email]);
        if ($old != null) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->execute(
            "INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)",
            "sssss",
            [$name, $email, $hash, $phone, $role]
        );
    }

    public function updateStaff($id, $name, $email, $phone, $active) {
        return $this->execute(
            "UPDATE users SET name=?, email=?, phone=?, is_active=? WHERE id=?",
            "sssii",
            [$name, $email, $phone, $active, $id]
        );
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
                $text = strtolower($row['name'] . " " . $row['phone'] . " " . $row['email'] . " " . $row['id']);
                if (strpos($text, strtolower($keyword)) === false) {
                    continue;
                }
            }

            $list[] = $row;
        }

        return $list;
    }

    public function deactivatePatient($patient_id) {
        $patient = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($patient == null) {
            return false;
        }

        return $this->execute("UPDATE users SET is_active=0 WHERE id=?", "i", [$patient['user_id']]);
    }

    /* ---------- Hospital settings ---------- */

    public function getSettings() {
        return $this->fetchAll("SELECT * FROM hospital_settings ORDER BY setting_name");
    }

    public function updateSetting($name, $value) {
        return $this->execute(
            "UPDATE hospital_settings SET setting_value=? WHERE setting_name=?",
            "ss",
            [$value, $name]
        );
    }

    /* ---------- Appointments ---------- */

    public function getAllAppointments($date = "", $status = "", $doctor_id = "", $booked_by = "") {
        $appointments = $this->fetchAll("SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time");
        $list = [];

        foreach ($appointments as $appointment) {
            if ($date != "" && $appointment['appointment_date'] != $date) {
                continue;
            }

            if ($status != "" && $appointment['status'] != $status) {
                continue;
            }

            if ($doctor_id != "" && $appointment['doctor_id'] != $doctor_id) {
                continue;
            }

            if ($booked_by != "" && $appointment['booked_by'] != $booked_by) {
                continue;
            }

            $list[] = $this->addFullDataToAppointment($appointment);
        }

        return $list;
    }

    /* ---------- Admin dashboard ---------- */

    public function getAdminStats() {
        $today = $this->fetchOne("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date=CURDATE()");
        $patients = $this->fetchOne("SELECT COUNT(*) AS total FROM patients");
        $pending = $this->fetchOne("SELECT COUNT(*) AS total FROM billing WHERE payment_status='pending'");
        $doctors = $this->getAllDoctorsAdmin();

        $active_doctors = 0;
        foreach ($doctors as $doctor) {
            if ($doctor['is_active'] == 1 && $doctor['is_approved'] == 1) {
                $active_doctors = $active_doctors + 1;
            }
        }

        return [
            'todays_appointments' => $today != null ? $today['total'] : 0,
            'total_patients' => $patients != null ? $patients['total'] : 0,
            'active_doctors' => $active_doctors,
            'pending_bills' => $pending != null ? $pending['total'] : 0
        ];
    }

    /* ---------- Reports ---------- */

    public function getRevenueReport() {
        $bills = $this->fetchAll("SELECT * FROM billing WHERE payment_status='paid' ORDER BY paid_at DESC");
        $report = [];

        foreach ($bills as $bill) {
            $appointment = $this->getAppointment($bill['appointment_id']);
            if ($appointment == null) {
                continue;
            }

            $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
            if ($doctor == null) {
                continue;
            }

            $paid_date = $bill['paid_at'] != null ? substr($bill['paid_at'], 0, 10) : "";
            $key = $paid_date . "_" . $doctor['id'];

            if (!isset($report[$key])) {
                $report[$key] = [
                    'paid_date' => $paid_date,
                    'doctor_name' => $doctor['name'],
                    'specialization' => $doctor['specialization'],
                    'total_revenue' => 0
                ];
            }

            $report[$key]['total_revenue'] = $report[$key]['total_revenue'] + floatval($bill['amount']);
        }

        return array_values($report);
    }

    public function getVolumeReport() {
        $appointments = $this->fetchAll("SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time");

        $doctorCount = [];
        $specializationCount = [];
        $dayCount = [];
        $hourCount = [];

        foreach ($appointments as $appointment) {
            $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
            if ($doctor == null) {
                continue;
            }

            $doctorName = $doctor['name'];
            $specialization = $doctor['specialization'];
            $day = date('l', strtotime($appointment['appointment_date']));
            $hour = date('h A', strtotime($appointment['appointment_time']));

            if (!isset($doctorCount[$doctorName])) {
                $doctorCount[$doctorName] = 0;
            }
            $doctorCount[$doctorName] = $doctorCount[$doctorName] + 1;

            if (!isset($specializationCount[$specialization])) {
                $specializationCount[$specialization] = 0;
            }
            $specializationCount[$specialization] = $specializationCount[$specialization] + 1;

            if (!isset($dayCount[$day])) {
                $dayCount[$day] = 0;
            }
            $dayCount[$day] = $dayCount[$day] + 1;

            if (!isset($hourCount[$hour])) {
                $hourCount[$hour] = 0;
            }
            $hourCount[$hour] = $hourCount[$hour] + 1;
        }

        arsort($doctorCount);
        arsort($specializationCount);
        arsort($dayCount);
        arsort($hourCount);

        return [
            'busiest_doctor' => $this->firstCountRow($doctorCount),
            'most_demanded_specialization' => $this->firstCountRow($specializationCount),
            'peak_day' => $this->firstCountRow($dayCount),
            'peak_hour' => $this->firstCountRow($hourCount),
            'doctor_count' => $doctorCount,
            'specialization_count' => $specializationCount,
            'day_count' => $dayCount,
            'hour_count' => $hourCount
        ];
    }

    private function firstCountRow($items) {
        foreach ($items as $name => $total) {
            return ['name' => $name, 'total' => $total];
        }

        return ['name' => 'No data', 'total' => 0];
    }

    public function getPerformanceReport() {
        $doctors = $this->getAllDoctorsAdmin();
        $report = [];

        foreach ($doctors as $doctor) {
            $appointments = $this->fetchAll("SELECT status FROM appointments WHERE doctor_id=?", "i", [$doctor['id']]);
            $reviews = $this->fetchAll("SELECT rating FROM doctor_reviews WHERE doctor_id=?", "i", [$doctor['id']]);

            $total_consultation = 0;
            $no_show_count = 0;

            foreach ($appointments as $appointment) {
                if ($appointment['status'] == 'completed') {
                    $total_consultation = $total_consultation + 1;
                }

                if ($appointment['status'] == 'no_show') {
                    $no_show_count = $no_show_count + 1;
                }
            }

            $rating_sum = 0;
            foreach ($reviews as $review) {
                $rating_sum = $rating_sum + intval($review['rating']);
            }

            $average_rating = count($reviews) > 0 ? $rating_sum / count($reviews) : 0;

            $report[] = [
                'doctor_name' => $doctor['name'],
                'total_consultation' => $total_consultation,
                'average_rating' => $average_rating,
                'no_show_count' => $no_show_count
            ];
        }

        return $report;
    }

    /* ---------- Billing ---------- */

    public function getBillingDashboard() {
        $bills = $this->fetchAll("SELECT * FROM billing ORDER BY id DESC");

        $total_paid_count = 0;
        $total_pending_count = 0;
        $paid_amount = 0;
        $pending_amount = 0;
        $overdue_count = 0;
        $overdue_amount = 0;

        foreach ($bills as $bill) {
            if ($bill['payment_status'] == 'paid') {
                $total_paid_count = $total_paid_count + 1;
                $paid_amount = $paid_amount + floatval($bill['amount']);
            }

            if ($bill['payment_status'] == 'pending') {
                $total_pending_count = $total_pending_count + 1;
                $pending_amount = $pending_amount + floatval($bill['amount']);

                $appointment = $this->getAppointment($bill['appointment_id']);
                if ($appointment != null) {
                    $appointment_time = strtotime($appointment['appointment_date'] . " " . $appointment['appointment_time']);
                    if ($appointment_time < time()) {
                        $overdue_count = $overdue_count + 1;
                        $overdue_amount = $overdue_amount + floatval($bill['amount']);
                    }
                }
            }
        }

        return [
            'total_paid_count' => $total_paid_count,
            'total_pending_count' => $total_pending_count,
            'paid_amount' => $paid_amount,
            'pending_amount' => $pending_amount,
            'overdue_count' => $overdue_count,
            'overdue_amount' => $overdue_amount
        ];
    }

    public function getPendingBills() {
        $bills = $this->fetchAll("SELECT * FROM billing WHERE payment_status='pending' ORDER BY id DESC");
        $list = [];

        foreach ($bills as $bill) {
            $appointment = $this->getAppointment($bill['appointment_id']);

            $bill['billing_id'] = $bill['id'];
            $bill['patient_name'] = $appointment != null ? $appointment['patient_name'] : "";
            $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : "";
            $bill['appointment_date'] = $appointment != null ? $appointment['appointment_date'] : "";
            $bill['appointment_time'] = $appointment != null ? $appointment['appointment_time'] : "";

            $list[] = $bill;
        }

        return $list;
    }

    public function markBillAsPaid($billing_id, $payment_method) {
        return $this->execute(
            "UPDATE billing SET payment_status='paid', payment_method=?, paid_at=NOW() WHERE id=?",
            "si",
            [$payment_method, $billing_id]
        );
    }

    /* ---------- Complaints ---------- */

    public function getComplaints() {
        $complaints = $this->fetchAll("SELECT * FROM complaints ORDER BY id DESC");
        $list = [];

        foreach ($complaints as $complaint) {
            $patient = $this->getPatientUserRow($complaint['patient_id']);
            $complaint['patient_name'] = $patient != null ? $patient['patient_name'] : "";
            $list[] = $complaint;
        }

        return $list;
    }

    public function resolveComplaint($id, $response) {
        return $this->execute(
            "UPDATE complaints SET status='resolved', admin_response=? WHERE id=?",
            "si",
            [$response, $id]
        );
    }
}
?>

