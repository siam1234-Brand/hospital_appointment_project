<?php
include_once(__DIR__ . "/../receptionist/ReceptionistModel.php");

class AdminModel extends ReceptionistModel {

    public function addSpecialization($name, $description) {
        return $this->execute("INSERT INTO specializations (name, description) VALUES (?, ?)", "ss", [$name, $description]);
    }

    public function updateSpecialization($id, $name, $description) {
        return $this->execute("UPDATE specializations SET name=?, description=? WHERE id=?", "ssi", [$name, $description, $id]);
    }

    public function deleteSpecialization($id) {
        return $this->execute("DELETE FROM specializations WHERE id=?", "i", [$id]);
    }

    public function addAnnouncement($author_id, $title, $body, $target) {
        return $this->execute("INSERT INTO announcements (author_id, title, body, target_role) VALUES (?, ?, ?, ?)", "isss", [$author_id, $title, $body, $target]);
    }

    public function deleteAnnouncement($id) {
        return $this->execute("DELETE FROM announcements WHERE id=?", "i", [$id]);
    }

    public function createUserOnly($name, $email, $password, $phone, $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->execute("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)", "sssss", [$name, $email, $hash, $phone, $role]);
        return $this->getLastId();
    }

    public function adminCreateDoctor($name, $email, $password, $phone, $specialization_id, $bio, $fee, $license, $experience) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->execute("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'doctor')", "ssss", [$name, $email, $hash, $phone]);
        $user_id = $this->getLastId();

        return $this->execute("INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, license_number, experience_years, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)", "iisdsi", [$user_id, $specialization_id, $bio, $fee, $license, $experience]);
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

    public function adminUpdateDoctor($doctor_id, $name, $email, $phone, $specialization_id, $bio, $fee, $license, $experience, $approved) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) return false;

        $this->execute("UPDATE users SET name=?, email=?, phone=? WHERE id=?", "sssi", [$name, $email, $phone, $doctor['user_id']]);
        return $this->execute("UPDATE doctors SET specialization_id=?, bio=?, consultation_fee=?, license_number=?, experience_years=?, is_approved=? WHERE id=?", "isdsiii", [$specialization_id, $bio, $fee, $license, $experience, $approved, $doctor_id]);
    }

    public function deactivateDoctor($doctor_id) {
        $doctor = $this->fetchOne("SELECT * FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) return false;
        return $this->execute("UPDATE users SET is_active=0 WHERE id=?", "i", [$doctor['user_id']]);
    }

    public function getStaff($role) {
        return $this->fetchAll("SELECT * FROM users WHERE role=? ORDER BY id DESC", "s", [$role]);
    }

    public function createStaff($name, $email, $password, $phone, $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->execute("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)", "sssss", [$name, $email, $hash, $phone, $role]);
    }

    public function updateStaff($id, $name, $email, $phone, $active) {
        return $this->execute("UPDATE users SET name=?, email=?, phone=?, is_active=? WHERE id=?", "sssii", [$name, $email, $phone, $active, $id]);
    }

    public function deactivatePatient($patient_id) {
        $p = $this->fetchOne("SELECT * FROM patients WHERE id=?", "i", [$patient_id]);
        if ($p == null) return false;
        return $this->execute("UPDATE users SET is_active=0 WHERE id=?", "i", [$p['user_id']]);
    }

    public function getSettings() {
        return $this->fetchAll("SELECT * FROM hospital_settings ORDER BY setting_name");
    }

    public function updateSetting($name, $value) {
        return $this->execute("UPDATE hospital_settings SET setting_value=? WHERE setting_name=?", "ss", [$value, $name]);
    }

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
            'todays_appointments' => $today['total'],
            'total_patients' => $patients['total'],
            'active_doctors' => $active_doctors,
            'pending_bills' => $pending['total']
        ];
    }

    public function getRevenueReport() {
        $bills = $this->fetchAll("SELECT * FROM billing WHERE payment_status='paid' ORDER BY paid_at DESC");
        $report = [];

        foreach ($bills as $bill) {
            $appointment = $this->getAppointment($bill['appointment_id']);
            if ($appointment == null) continue;

            $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
            if ($doctor == null) continue;

            $paid_date = substr($bill['paid_at'], 0, 10);
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
        $appointments = $this->fetchAll("SELECT * FROM appointments ORDER BY appointment_date DESC");
        $report = [];

        foreach ($appointments as $appointment) {
            $doctor = $this->getDoctorUserRow($appointment['doctor_id']);
            if ($doctor == null) continue;

            $day = date('l', strtotime($appointment['appointment_date']));
            $key = $doctor['id'] . "_" . $day;

            if (!isset($report[$key])) {
                $report[$key] = [
                    'doctor_name' => $doctor['name'],
                    'specialization' => $doctor['specialization'],
                    'day_name' => $day,
                    'total' => 0
                ];
            }

            $report[$key]['total'] = $report[$key]['total'] + 1;
        }

        return array_values($report);
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
                if ($appointment['status'] == 'completed') $total_consultation = $total_consultation + 1;
                if ($appointment['status'] == 'no_show') $no_show_count = $no_show_count + 1;
            }

            $sum = 0;
            foreach ($reviews as $review) {
                $sum = $sum + intval($review['rating']);
            }
            $avg = count($reviews) > 0 ? $sum / count($reviews) : 0;

            $report[] = [
                'doctor_name' => $doctor['name'],
                'total_consultation' => $total_consultation,
                'average_rating' => $avg,
                'no_show_count' => $no_show_count
            ];
        }

        return $report;
    }

    public function getBillingDashboard() {
        $bills = $this->fetchAll("SELECT * FROM billing ORDER BY id DESC");
        $data = [
            'total_paid_count' => 0,
            'total_pending_count' => 0,
            'paid_amount' => 0,
            'pending_amount' => 0
        ];

        foreach ($bills as $bill) {
            if ($bill['payment_status'] == 'paid') {
                $data['total_paid_count'] = $data['total_paid_count'] + 1;
                $data['paid_amount'] = $data['paid_amount'] + floatval($bill['amount']);
            }
            if ($bill['payment_status'] == 'pending') {
                $data['total_pending_count'] = $data['total_pending_count'] + 1;
                $data['pending_amount'] = $data['pending_amount'] + floatval($bill['amount']);
            }
        }

        return $data;
    }

    public function getComplaints() {
        $complaints = $this->fetchAll("SELECT * FROM complaints ORDER BY id DESC");
        $list = [];

        foreach ($complaints as $complaint) {
            $patient = $this->getPatientUserRow($complaint['patient_id']);
            $complaint['patient_name'] = $patient != null ? $patient['patient_name'] : '';
            $list[] = $complaint;
        }

        return $list;
    }

    public function resolveComplaint($id, $response) {
        return $this->execute("UPDATE complaints SET status='resolved', admin_response=? WHERE id=?", "si", [$response, $id]);
    }
}
?>
