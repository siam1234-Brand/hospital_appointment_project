<?php
include_once(__DIR__ . "/../db/db_conn.php");

class HospitalModel {
    private $conn = null;

    function __construct() {
        $dbConnObj = new DBConnection();
        $this->conn = $dbConnObj->connect();
    }

    private function makeStatement($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("SQL Error: " . $this->conn->error);
        }
        if ($types !== "") {
            $refs = [];
            $refs[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $refs[] = &$params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }
        return $stmt;
    }

    public function fetchAll($sql, $types = "", $params = []) {
        $stmt = $this->makeStatement($sql, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function fetchOne($sql, $types = "", $params = []) {
        $rows = $this->fetchAll($sql, $types, $params);
        if (count($rows) > 0) {
            return $rows[0];
        }
        return null;
    }

    public function execute($sql, $types = "", $params = []) {
        $stmt = $this->makeStatement($sql, $types, $params);
        return $stmt->execute();
    }

    public function getLastId() {
        return $this->conn->insert_id;
    }

    public function login($email, $password) {
        $user = $this->fetchOne("SELECT * FROM users WHERE email=? AND is_active=1", "s", [$email]);
        if ($user != null && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        $_SESSION['error']['login'] = "Invalid email or password";
        return false;
    }

    public function registerPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone) {
        $old = $this->fetchOne("SELECT id FROM users WHERE email=?", "s", [$email]);
        if ($old != null) {
            return false;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->execute("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'patient')", "ssss", [$name, $email, $hash, $phone]);
        $user_id = $this->getLastId();
        return $this->execute("INSERT INTO patients (user_id, date_of_birth, blood_group, gender, address, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?)", "issssss", [$user_id, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone]);
    }

    public function getUser($id) {
        return $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$id]);
    }

    public function updateUserBasic($id, $name, $email, $phone, $profile_pic = "") {
        if ($profile_pic != "") {
            return $this->execute("UPDATE users SET name=?, email=?, phone=?, profile_pic=? WHERE id=?", "ssssi", [$name, $email, $phone, $profile_pic, $id]);
        }
        return $this->execute("UPDATE users SET name=?, email=?, phone=? WHERE id=?", "sssi", [$name, $email, $phone, $id]);
    }

    public function changePassword($user_id, $oldPassword, $newPassword) {
        $user = $this->getUser($user_id);
        if ($user != null && password_verify($oldPassword, $user['password_hash'])) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->execute("UPDATE users SET password_hash=? WHERE id=?", "si", [$hash, $user_id]);
        }
        return false;
    }

    public function getPatientByUser($user_id) {
        return $this->fetchOne("SELECT p.*, u.name, u.email, u.phone, u.profile_pic FROM patients p JOIN users u ON p.user_id=u.id WHERE p.user_id=?", "i", [$user_id]);
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

    public function addSpecialization($name, $description) {
        return $this->execute("INSERT INTO specializations (name, description) VALUES (?, ?)", "ss", [$name, $description]);
    }

    public function updateSpecialization($id, $name, $description) {
        return $this->execute("UPDATE specializations SET name=?, description=? WHERE id=?", "ssi", [$name, $description, $id]);
    }

    public function deleteSpecialization($id) {
        return $this->execute("DELETE FROM specializations WHERE id=?", "i", [$id]);
    }

    public function getApprovedDoctors($search = "", $specialization_id = "", $min_fee = "", $max_fee = "", $day = "") {
        $sql = "SELECT d.*, u.name, u.email, u.phone, u.profile_pic, s.name AS specialization,
                IFNULL(AVG(r.rating),0) AS avg_rating
                FROM doctors d
                JOIN users u ON d.user_id=u.id
                LEFT JOIN specializations s ON d.specialization_id=s.id
                LEFT JOIN doctor_reviews r ON d.id=r.doctor_id
                WHERE d.is_approved=1 AND u.is_active=1";
        $types = "";
        $params = [];
        if ($search != "") {
            $sql .= " AND (u.name LIKE ? OR s.name LIKE ?)";
            $like = "%" . $search . "%";
            $types .= "ss";
            $params[] = $like;
            $params[] = $like;
        }
        if ($specialization_id != "") {
            $sql .= " AND d.specialization_id=?";
            $types .= "i";
            $params[] = $specialization_id;
        }
        if ($min_fee != "") {
            $sql .= " AND d.consultation_fee >= ?";
            $types .= "d";
            $params[] = $min_fee;
        }
        if ($max_fee != "") {
            $sql .= " AND d.consultation_fee <= ?";
            $types .= "d";
            $params[] = $max_fee;
        }
        if ($day != "") {
            $sql .= " AND d.id IN (SELECT doctor_id FROM doctor_availability WHERE day_of_week=? AND is_available=1)";
            $types .= "s";
            $params[] = $day;
        }
        $sql .= " GROUP BY d.id ORDER BY u.name";
        return $this->fetchAll($sql, $types, $params);
    }

    public function getDoctorDetail($doctor_id) {
        return $this->fetchOne("SELECT d.*, u.name, u.email, u.phone, u.profile_pic, s.name AS specialization,
                IFNULL(AVG(r.rating),0) AS avg_rating
                FROM doctors d JOIN users u ON d.user_id=u.id
                LEFT JOIN specializations s ON d.specialization_id=s.id
                LEFT JOIN doctor_reviews r ON d.id=r.doctor_id
                WHERE d.id=? GROUP BY d.id", "i", [$doctor_id]);
    }

    public function getDoctorIdByUser($user_id) {
        $row = $this->fetchOne("SELECT id FROM doctors WHERE user_id=?", "i", [$user_id]);
        if ($row == null) return 0;
        return $row['id'];
    }

    public function getDoctorAvailability($doctor_id) {
        return $this->fetchAll("SELECT * FROM doctor_availability WHERE doctor_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')", "i", [$doctor_id]);
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

    public function getAvailableSlots($doctor_id, $date) {
        $leave = $this->fetchOne("SELECT id FROM leave_dates WHERE doctor_id=? AND leave_date=?", "is", [$doctor_id, $date]);
        if ($leave != null) {
            return [];
        }
        $day = date("l", strtotime($date));
        $av = $this->fetchOne("SELECT * FROM doctor_availability WHERE doctor_id=? AND day_of_week=? AND is_available=1", "is", [$doctor_id, $day]);
        if ($av == null) {
            return [];
        }
        $bookedRows = $this->fetchAll("SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=? AND status NOT IN ('cancelled','rejected','no_show')", "is", [$doctor_id, $date]);
        $booked = [];
        foreach ($bookedRows as $b) {
            $booked[] = substr($b['appointment_time'], 0, 5);
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
        $check = $this->fetchOne("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND status NOT IN ('cancelled','rejected','no_show')", "iss", [$doctor_id, $date, $time]);
        if ($check != null) return false;
        return $this->execute("INSERT INTO appointments (patient_id, dependent_id, doctor_id, appointment_date, appointment_time, reason, booked_by) VALUES (?, ?, ?, ?, ?, ?, ?)", "iiissss", [$patient_id, $dependent_id, $doctor_id, $date, $time, $reason, $booked_by]);
    }

    public function getUpcomingAppointments($patient_id) {
        return $this->fetchAll("SELECT a.*, u.name AS doctor_name, s.name AS specialization, d.consultation_fee
                FROM appointments a JOIN doctors d ON a.doctor_id=d.id JOIN users u ON d.user_id=u.id
                LEFT JOIN specializations s ON d.specialization_id=s.id
                WHERE a.patient_id=? AND a.status IN ('pending','confirmed','checked_in')
                ORDER BY a.appointment_date, a.appointment_time", "i", [$patient_id]);
    }

    public function getPastAppointments($patient_id) {
        return $this->fetchAll("SELECT a.*, u.name AS doctor_name, s.name AS specialization
                FROM appointments a JOIN doctors d ON a.doctor_id=d.id JOIN users u ON d.user_id=u.id
                LEFT JOIN specializations s ON d.specialization_id=s.id
                WHERE a.patient_id=? AND a.status IN ('completed','cancelled','no_show','rejected')
                ORDER BY a.appointment_date DESC, a.appointment_time DESC", "i", [$patient_id]);
    }

    public function getAppointment($id) {
        return $this->fetchOne("SELECT a.*, p.user_id AS patient_user_id, pu.name AS patient_name, du.name AS doctor_name, d.consultation_fee
            FROM appointments a
            JOIN patients p ON a.patient_id=p.id
            JOIN users pu ON p.user_id=pu.id
            JOIN doctors d ON a.doctor_id=d.id
            JOIN users du ON d.user_id=du.id
            WHERE a.id=?", "i", [$id]);
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

    public function getDoctorScheduleToday($doctor_id) {
        return $this->fetchAll("SELECT a.*, u.name AS patient_name, u.phone
            FROM appointments a JOIN patients p ON a.patient_id=p.id JOIN users u ON p.user_id=u.id
            WHERE a.doctor_id=? AND a.appointment_date=CURDATE()
            ORDER BY a.appointment_time", "i", [$doctor_id]);
    }

    public function getDoctorWeeklyCalendar($doctor_id) {
        return $this->fetchAll("SELECT a.*, u.name AS patient_name
            FROM appointments a JOIN patients p ON a.patient_id=p.id JOIN users u ON p.user_id=u.id
            WHERE a.doctor_id=? AND a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY a.appointment_date, a.appointment_time", "i", [$doctor_id]);
    }

    public function updateAppointmentStatus($id, $status) {
        return $this->execute("UPDATE appointments SET status=? WHERE id=?", "si", [$status, $id]);
    }

    public function completeAppointment($appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $followup) {
        $old = $this->fetchOne("SELECT id FROM consultation_notes WHERE appointment_id=?", "i", [$appointment_id]);
        if ($old == null) {
            $this->execute("INSERT INTO consultation_notes (appointment_id, doctor_id, patient_id, symptoms, diagnosis, prescription, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)", "iiissss", [$appointment_id, $doctor_id, $patient_id, $symptoms, $diagnosis, $prescription, $followup]);
        } else {
            $this->execute("UPDATE consultation_notes SET symptoms=?, diagnosis=?, prescription=?, follow_up_date=? WHERE appointment_id=?", "ssssi", [$symptoms, $diagnosis, $prescription, $followup, $appointment_id]);
        }
        $this->execute("UPDATE appointments SET status='completed' WHERE id=?", "i", [$appointment_id]);
        $app = $this->getAppointment($appointment_id);
        $bill = $this->fetchOne("SELECT id FROM billing WHERE appointment_id=?", "i", [$appointment_id]);
        if ($bill == null && $app != null) {
            $this->execute("INSERT INTO billing (appointment_id, patient_id, amount, payment_status) VALUES (?, ?, ?, 'pending')", "iid", [$appointment_id, $patient_id, $app['consultation_fee']]);
        }
        return true;
    }

    public function getConsultationNoteByAppointment($appointment_id) {
        return $this->fetchOne("SELECT n.*, du.name AS doctor_name, pu.name AS patient_name FROM consultation_notes n
            JOIN doctors d ON n.doctor_id=d.id JOIN users du ON d.user_id=du.id
            JOIN patients p ON n.patient_id=p.id JOIN users pu ON p.user_id=pu.id
            WHERE n.appointment_id=?", "i", [$appointment_id]);
    }

    public function getDoctorPatientNotes($doctor_id, $patient_id) {
        return $this->fetchAll("SELECT n.*, a.appointment_date FROM consultation_notes n JOIN appointments a ON n.appointment_id=a.id WHERE n.doctor_id=? AND n.patient_id=? ORDER BY n.created_at DESC", "ii", [$doctor_id, $patient_id]);
    }

    public function getBillingByPatient($patient_id) {
        return $this->fetchAll("SELECT b.*, a.appointment_date, du.name AS doctor_name FROM billing b
            JOIN appointments a ON b.appointment_id=a.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            WHERE b.patient_id=? ORDER BY b.id DESC", "i", [$patient_id]);
    }

    public function submitPaymentIntent($bill_id, $patient_id, $method) {
        return $this->execute("UPDATE billing SET payment_method=? WHERE id=? AND patient_id=? AND payment_status='pending'", "sii", [$method, $bill_id, $patient_id]);
    }

    public function markBillPaid($bill_id, $method) {
        return $this->execute("UPDATE billing SET payment_method=?, payment_status='paid', paid_at=NOW() WHERE id=?", "si", [$method, $bill_id]);
    }

    public function getBill($bill_id) {
        return $this->fetchOne("SELECT b.*, a.appointment_date, a.appointment_time, pu.name AS patient_name, du.name AS doctor_name
            FROM billing b JOIN appointments a ON b.appointment_id=a.id
            JOIN patients p ON b.patient_id=p.id JOIN users pu ON p.user_id=pu.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            WHERE b.id=?", "i", [$bill_id]);
    }

    public function getOwnReviews($patient_id) {
        return $this->fetchAll("SELECT r.*, u.name AS doctor_name FROM doctor_reviews r JOIN doctors d ON r.doctor_id=d.id JOIN users u ON d.user_id=u.id WHERE r.patient_id=? ORDER BY r.id DESC", "i", [$patient_id]);
    }

    public function getDoctorReviews($doctor_id) {
        return $this->fetchAll("SELECT r.*, u.name AS patient_name FROM doctor_reviews r JOIN patients p ON r.patient_id=p.id JOIN users u ON p.user_id=u.id WHERE r.doctor_id=? ORDER BY r.created_at DESC", "i", [$doctor_id]);
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

    public function replyReview($id, $doctor_id, $reply) {
        return $this->execute("UPDATE doctor_reviews SET doctor_reply=? WHERE id=? AND doctor_id=?", "sii", [$reply, $id, $doctor_id]);
    }

    public function getAnnouncements($role) {
        return $this->fetchAll("SELECT a.*, u.name AS author FROM announcements a JOIN users u ON a.author_id=u.id WHERE a.target_role='all' OR a.target_role=? ORDER BY a.published_at DESC", "s", [$role]);
    }

    public function addAnnouncement($author_id, $title, $body, $target) {
        return $this->execute("INSERT INTO announcements (author_id, title, body, target_role) VALUES (?, ?, ?, ?)", "isss", [$author_id, $title, $body, $target]);
    }

    public function deleteAnnouncement($id) {
        return $this->execute("DELETE FROM announcements WHERE id=?", "i", [$id]);
    }

    public function updateDoctorProfile($doctor_id, $bio, $specialization_id, $fee, $license, $experience, $photo = "") {
        if ($photo != "") {
            return $this->execute("UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=?, photo_path=? WHERE id=?", "sidsssi", [$bio, $specialization_id, $fee, $license, $experience, $photo, $doctor_id]);
        }
        return $this->execute("UPDATE doctors SET bio=?, specialization_id=?, consultation_fee=?, license_number=?, experience_years=? WHERE id=?", "sidsii", [$bio, $specialization_id, $fee, $license, $experience, $doctor_id]);
    }

    public function getDoctorEarnings($doctor_id) {
        return $this->fetchAll("SELECT a.appointment_date, COUNT(*) AS completed_count, SUM(b.amount) AS total_earning
            FROM appointments a JOIN billing b ON a.id=b.appointment_id
            WHERE a.doctor_id=? AND a.status='completed'
            GROUP BY a.appointment_date ORDER BY a.appointment_date DESC", "i", [$doctor_id]);
    }

    public function getDoctorStats($doctor_id) {
        return $this->fetchOne("SELECT
            SUM(status='completed') AS completed,
            SUM(status='cancelled') AS cancelled,
            SUM(status='no_show') AS no_show,
            COUNT(*) AS total
            FROM appointments WHERE doctor_id=?", "i", [$doctor_id]);
    }

    public function getFollowUps($doctor_id) {
        return $this->fetchAll("SELECT n.*, u.name AS patient_name FROM consultation_notes n
            JOIN patients p ON n.patient_id=p.id JOIN users u ON p.user_id=u.id
            WHERE n.doctor_id=? AND n.follow_up_date IS NOT NULL AND n.follow_up_date >= CURDATE()
            ORDER BY n.follow_up_date", "i", [$doctor_id]);
    }

    public function getDoctorMessages($doctor_id) {
        return $this->fetchAll("SELECT m.*, u.name AS patient_name FROM patient_messages m JOIN patients p ON m.patient_id=p.id JOIN users u ON p.user_id=u.id WHERE m.doctor_id=? ORDER BY m.created_at DESC", "i", [$doctor_id]);
    }

    public function replyMessage($message_id, $doctor_id, $reply) {
        return $this->execute("UPDATE patient_messages SET reply_text=? WHERE id=? AND doctor_id=?", "sii", [$reply, $message_id, $doctor_id]);
    }

    public function searchPatients($keyword = "") {
        $sql = "SELECT p.*, u.name, u.email, u.phone, u.is_active FROM patients p JOIN users u ON p.user_id=u.id WHERE 1=1";
        $types = "";
        $params = [];
        if ($keyword != "") {
            $sql .= " AND (u.name LIKE ? OR u.phone LIKE ? OR p.id=?)";
            $like = "%" . $keyword . "%";
            $pid = intval($keyword);
            $types = "ssi";
            $params = [$like, $like, $pid];
        }
        $sql .= " ORDER BY p.id DESC";
        return $this->fetchAll($sql, $types, $params);
    }

    public function receptionistRegisterPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone) {
        return $this->registerPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone);
    }

    public function getAllAppointments($date = "", $status = "", $doctor_id = "", $booked_by = "") {
        $sql = "SELECT a.*, pu.name AS patient_name, pu.phone, du.name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id=p.id JOIN users pu ON p.user_id=pu.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id WHERE 1=1";
        $types = "";
        $params = [];
        if ($date != "") { $sql .= " AND a.appointment_date=?"; $types .= "s"; $params[] = $date; }
        if ($status != "") { $sql .= " AND a.status=?"; $types .= "s"; $params[] = $status; }
        if ($doctor_id != "") { $sql .= " AND a.doctor_id=?"; $types .= "i"; $params[] = $doctor_id; }
        if ($booked_by != "") { $sql .= " AND a.booked_by=?"; $types .= "s"; $params[] = $booked_by; }
        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time";
        return $this->fetchAll($sql, $types, $params);
    }

    public function getWaitingQueue() {
        return $this->fetchAll("SELECT a.*, pu.name AS patient_name, du.name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id=p.id JOIN users pu ON p.user_id=pu.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            WHERE a.appointment_date=CURDATE() AND a.status='checked_in'
            ORDER BY du.name, a.appointment_time");
    }

    public function getPendingBills() {
        return $this->fetchAll("SELECT b.*, pu.name AS patient_name, du.name AS doctor_name
            FROM billing b JOIN appointments a ON b.appointment_id=a.id
            JOIN patients p ON b.patient_id=p.id JOIN users pu ON p.user_id=pu.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            WHERE b.payment_status='pending' ORDER BY b.id DESC");
    }

    public function getDailySummary($date) {
        return $this->fetchOne("SELECT
            COUNT(*) AS total_appointments,
            SUM(status='checked_in') AS check_ins,
            SUM(status='completed') AS completed,
            SUM(status='cancelled') AS cancelled,
            (SELECT IFNULL(SUM(amount),0) FROM billing WHERE payment_status='paid' AND DATE(paid_at)=?) AS revenue
            FROM appointments WHERE appointment_date=?", "ss", [$date, $date]);
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
        return $this->fetchAll("SELECT d.*, u.name, u.email, u.phone, u.is_active, s.name AS specialization FROM doctors d JOIN users u ON d.user_id=u.id LEFT JOIN specializations s ON d.specialization_id=s.id ORDER BY d.id DESC");
    }

    public function adminUpdateDoctor($doctor_id, $name, $email, $phone, $specialization_id, $bio, $fee, $license, $experience, $approved) {
        $doctor = $this->fetchOne("SELECT user_id FROM doctors WHERE id=?", "i", [$doctor_id]);
        if ($doctor == null) return false;
        $this->execute("UPDATE users SET name=?, email=?, phone=? WHERE id=?", "sssi", [$name, $email, $phone, $doctor['user_id']]);
        return $this->execute("UPDATE doctors SET specialization_id=?, bio=?, consultation_fee=?, license_number=?, experience_years=?, is_approved=? WHERE id=?", "isdsiii", [$specialization_id, $bio, $fee, $license, $experience, $approved, $doctor_id]);
    }

    public function deactivateDoctor($doctor_id) {
        $doctor = $this->fetchOne("SELECT user_id FROM doctors WHERE id=?", "i", [$doctor_id]);
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
        $p = $this->fetchOne("SELECT user_id FROM patients WHERE id=?", "i", [$patient_id]);
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
        return $this->fetchOne("SELECT
            (SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE()) AS todays_appointments,
            (SELECT COUNT(*) FROM patients) AS total_patients,
            (SELECT COUNT(*) FROM doctors d JOIN users u ON d.user_id=u.id WHERE u.is_active=1 AND d.is_approved=1) AS active_doctors,
            (SELECT COUNT(*) FROM billing WHERE payment_status='pending') AS pending_bills");
    }

    public function getRevenueReport() {
        return $this->fetchAll("SELECT DATE(b.paid_at) AS paid_date, du.name AS doctor_name, s.name AS specialization, SUM(b.amount) AS total_revenue
            FROM billing b JOIN appointments a ON b.appointment_id=a.id
            JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            LEFT JOIN specializations s ON d.specialization_id=s.id
            WHERE b.payment_status='paid'
            GROUP BY DATE(b.paid_at), d.id, s.id ORDER BY paid_date DESC");
    }

    public function getVolumeReport() {
        return $this->fetchAll("SELECT du.name AS doctor_name, s.name AS specialization, DAYNAME(a.appointment_date) AS day_name, COUNT(*) AS total
            FROM appointments a JOIN doctors d ON a.doctor_id=d.id JOIN users du ON d.user_id=du.id
            LEFT JOIN specializations s ON d.specialization_id=s.id
            GROUP BY d.id, s.id, DAYNAME(a.appointment_date) ORDER BY total DESC");
    }

    public function getPerformanceReport() {
        return $this->fetchAll("SELECT du.name AS doctor_name,
            COUNT(a.id) AS total_consultation,
            IFNULL(AVG(r.rating),0) AS average_rating,
            SUM(a.status='no_show') AS no_show_count
            FROM doctors d JOIN users du ON d.user_id=du.id
            LEFT JOIN appointments a ON d.id=a.doctor_id
            LEFT JOIN doctor_reviews r ON d.id=r.doctor_id
            GROUP BY d.id ORDER BY total_consultation DESC");
    }

    public function getBillingDashboard() {
        return $this->fetchOne("SELECT
            SUM(payment_status='paid') AS total_paid_count,
            SUM(payment_status='pending') AS total_pending_count,
            IFNULL(SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END),0) AS paid_amount,
            IFNULL(SUM(CASE WHEN payment_status='pending' THEN amount ELSE 0 END),0) AS pending_amount
            FROM billing");
    }

    public function getComplaints() {
        return $this->fetchAll("SELECT c.*, u.name AS patient_name FROM complaints c LEFT JOIN patients p ON c.patient_id=p.id LEFT JOIN users u ON p.user_id=u.id ORDER BY c.id DESC");
    }

    public function resolveComplaint($id, $response) {
        return $this->execute("UPDATE complaints SET status='resolved', admin_response=? WHERE id=?", "si", [$response, $id]);
    }
}
?>
