<?php
include_once(__DIR__ . "/../doctor/DoctorModel.php");

class ReceptionistModel extends DoctorModel {

    public function receptionistRegisterPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone) {
        return $this->registerPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone);
    }

    public function addFullDataToAppointment($appointment) {
        $patient = $this->getPatientUserRow($appointment['patient_id']);
        $doctor = $this->getDoctorUserRow($appointment['doctor_id']);

        $appointment['patient_name'] = $patient != null ? $patient['patient_name'] : '';
        $appointment['phone'] = $patient != null ? $patient['phone'] : '';
        $appointment['doctor_name'] = $doctor != null ? $doctor['name'] : '';
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
            $bill['patient_name'] = $appointment != null ? $appointment['patient_name'] : '';
            $bill['doctor_name'] = $appointment != null ? $appointment['doctor_name'] : '';
            $list[] = $bill;
        }

        return $list;
    }

    public function getDailySummary($date) {
        $appointments = $this->fetchAll("SELECT * FROM appointments WHERE appointment_date=?", "s", [$date]);
        $summary = [
            'total_appointments' => 0,
            'check_ins' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'revenue' => 0
        ];

        foreach ($appointments as $appointment) {
            $summary['total_appointments'] = $summary['total_appointments'] + 1;
            if ($appointment['status'] == 'checked_in') $summary['check_ins'] = $summary['check_ins'] + 1;
            if ($appointment['status'] == 'completed') $summary['completed'] = $summary['completed'] + 1;
            if ($appointment['status'] == 'cancelled') $summary['cancelled'] = $summary['cancelled'] + 1;
        }

        $paid_at = $date . "%";
        $bills = $this->fetchAll("SELECT amount FROM billing WHERE payment_status='paid' AND paid_at LIKE ?", "s", [$paid_at]);
        foreach ($bills as $bill) {
            $summary['revenue'] = $summary['revenue'] + floatval($bill['amount']);
        }

        return $summary;
    }

    public function markBillPaid($bill_id, $method) {
        return $this->execute("UPDATE billing SET payment_method=?, payment_status='paid', paid_at=NOW() WHERE id=?", "si", [$method, $bill_id]);
    }
}
?>
