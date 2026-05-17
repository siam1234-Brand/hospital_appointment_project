Hospital Appointment Booking System - Basic PHP Project

Run Steps:
1. Copy this folder to xampp/htdocs/
2. Open phpMyAdmin and import database.sql
3. Visit: http://localhost/hospital_appointment_basic/

Demo Password for all users: 123456
Admin: admin@hospital.com
Patient: patient@hospital.com
Doctor: doctor@hospital.com
Receptionist: reception@hospital.com

Code Style:
- Simple PHP, HTML, CSS
- MVC folders: model, view, controller
- mysqli prepared statements used in model
- PHP session login and role access check
- XMLHttpRequest AJAX examples:
  Patient: api/patient_slots.php
  Doctor: api/doctor_checkin.php
  Receptionist: api/receptionist_slots.php
  Admin: api/admin_stats.php

Git Command Example:
git checkout -b feature/patient-module
git add .
git commit -m "patient module added"
git push origin feature/patient-module
Then create Pull Request on GitHub.


MODEL STRUCTURE
- model/BaseModel.php contains database connection/helper query methods.
- model/user/UserModel.php contains login/register/common user methods.
- model/patient/PatientModel.php contains patient profile, dependents, booking, billing and review methods.
- model/doctor/DoctorModel.php contains doctor availability, consultation, notes, reports and message methods.
- model/receptionist/ReceptionistModel.php contains receptionist appointment, queue, bill and daily summary methods.
- model/admin/AdminModel.php contains admin doctor/staff/patient/specialization/settings/report/complaint methods.
- model/HospitalModel.php is kept only as a compatibility wrapper.
