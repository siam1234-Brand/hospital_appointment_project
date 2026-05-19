
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    role ENUM('patient','doctor','receptionist','admin') NOT NULL,
    profile_pic VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_of_birth DATE,
    blood_group VARCHAR(10),
    gender VARCHAR(20),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(30),
    medical_history_notes TEXT
);

CREATE TABLE specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization_id INT,
    bio TEXT,
    consultation_fee DECIMAL(10,2) DEFAULT 0,
    photo_path VARCHAR(255),
    license_number VARCHAR(100),
    experience_years INT DEFAULT 0,
    is_approved TINYINT(1) DEFAULT 0
);

CREATE TABLE doctor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week VARCHAR(20),
    start_time TIME,
    end_time TIME,
    slot_duration_minutes INT DEFAULT 30,
    is_available TINYINT(1) DEFAULT 1
);

CREATE TABLE leave_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    leave_date DATE NOT NULL,
    reason TEXT
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    dependent_id INT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT,
    status ENUM('pending','confirmed','checked_in','completed','cancelled','no_show','rejected') DEFAULT 'pending',
    booked_by ENUM('patient','receptionist') DEFAULT 'patient',
    cancel_reason TEXT,
    reschedule_note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultation_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    symptoms TEXT,
    diagnosis TEXT,
    prescription TEXT,
    follow_up_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    paid_at DATETIME NULL
);

CREATE TABLE doctor_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    rating INT,
    review_text TEXT,
    doctor_reply TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dependents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    primary_patient_id INT NOT NULL,
    name VARCHAR(100),
    date_of_birth DATE,
    relationship VARCHAR(50),
    blood_group VARCHAR(10)
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(150),
    body TEXT,
    target_role ENUM('all','patient','doctor') DEFAULT 'all',
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hospital_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) UNIQUE,
    setting_value VARCHAR(100)
);

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    complaint_text TEXT,
    status ENUM('pending','resolved') DEFAULT 'pending',
    admin_response TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE patient_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    doctor_id INT,
    message_text TEXT,
    reply_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (id, name, email, password_hash, phone, role, is_active) VALUES
(1, 'Admin User', 'admin@hospital.com', '$2y$12$JNKepBX/6VuKFyRZJq1nFeCLHfFsnsJDrGf0GZakkLhWwzX9JqXhC', '01000000001', 'admin', 1),
(2, 'Patient User', 'patient@hospital.com', '$2y$12$JNKepBX/6VuKFyRZJq1nFeCLHfFsnsJDrGf0GZakkLhWwzX9JqXhC', '01000000002', 'patient', 1),
(3, 'Dr. Rahman', 'doctor@hospital.com', '$2y$12$JNKepBX/6VuKFyRZJq1nFeCLHfFsnsJDrGf0GZakkLhWwzX9JqXhC', '01000000003', 'doctor', 1),
(4, 'Reception User', 'reception@hospital.com', '$2y$12$JNKepBX/6VuKFyRZJq1nFeCLHfFsnsJDrGf0GZakkLhWwzX9JqXhC', '01000000004', 'receptionist', 1),
(5, 'Dr. Karim', 'doctor2@hospital.com', '$2y$12$JNKepBX/6VuKFyRZJq1nFeCLHfFsnsJDrGf0GZakkLhWwzX9JqXhC', '01000000005', 'doctor', 1);

INSERT INTO patients (id, user_id, date_of_birth, blood_group, gender, address, emergency_contact_name, emergency_contact_phone, medical_history_notes) VALUES
(1, 2, '2001-01-15', 'B+', 'Male', 'Dhaka', 'Father', '01911111111', 'No major history');

INSERT INTO specializations (id, name, description) VALUES
(1, 'Cardiology', 'Heart specialist'),
(2, 'Medicine', 'General medicine'),
(3, 'Dentistry', 'Dental care');

INSERT INTO doctors (id, user_id, specialization_id, bio, consultation_fee, license_number, experience_years, is_approved) VALUES
(1, 3, 1, 'Experienced heart specialist.', 800, 'DOC-1001', 8, 1),
(2, 5, 3, 'Dental surgeon and oral care doctor.', 600, 'DOC-1002', 5, 1);

INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes, is_available) VALUES
(1, 'Monday', '09:00:00', '13:00:00', 30, 1),
(1, 'Tuesday', '10:00:00', '14:00:00', 30, 1),
(1, 'Wednesday', '09:00:00', '12:00:00', 30, 1),
(1, 'Thursday', '09:00:00', '13:00:00', 30, 1),
(2, 'Monday', '15:00:00', '18:00:00', 30, 1),
(2, 'Wednesday', '15:00:00', '18:00:00', 30, 1),
(2, 'Friday', '10:00:00', '12:00:00', 30, 1);

INSERT INTO appointments (id, patient_id, doctor_id, appointment_date, appointment_time, reason, status, booked_by) VALUES
(1, 1, 1, CURDATE(), '09:00:00', 'Chest pain follow up', 'confirmed', 'patient'),
(2, 1, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'Old fever', 'completed', 'patient');

INSERT INTO consultation_notes (appointment_id, doctor_id, patient_id, symptoms, diagnosis, prescription, follow_up_date) VALUES
(2, 1, 1, 'Fever and headache', 'Viral fever', 'Paracetamol 500mg after meal', DATE_ADD(CURDATE(), INTERVAL 7 DAY));

INSERT INTO billing (appointment_id, patient_id, amount, payment_method, payment_status, paid_at) VALUES
(2, 1, 800, 'cash', 'paid', NOW());

INSERT INTO doctor_reviews (appointment_id, patient_id, doctor_id, rating, review_text) VALUES
(2, 1, 1, 5, 'Good doctor and clear explanation');

INSERT INTO announcements (author_id, title, body, target_role) VALUES
(1, 'Hospital Notice', 'Please bring your previous prescription during visit.', 'all'),
(1, 'Patient Notice', 'Online appointment booking is open.', 'patient'),
(1, 'Doctor Notice', 'Please update weekly availability.', 'doctor');

INSERT INTO hospital_settings (setting_name, setting_value) VALUES
('cancel_hours', '6'),
('advance_booking_days', '30'),
('default_consultation_fee', '500');

INSERT INTO complaints (patient_id, complaint_text) VALUES
(1, 'Waiting time was long.');

INSERT INTO patient_messages (patient_id, doctor_id, message_text) VALUES
(1, 1, 'Can I take medicine before breakfast?');
