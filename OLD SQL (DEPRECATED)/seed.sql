-- Seed data for Roaya Clinic
-- Insert after running schema.sql

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Insert users
INSERT INTO users (name, username, email, phone, password_hash, role, is_active) VALUES
('Dr. Ahmed Abo AlKassem', 'dr_ahmed', 'dr.ahmed@roayaclinic.com', '+201234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', TRUE),
('Dr. Ahmed AlFaramawy', 'dr_faramawy', 'dr.faramawy@roayaclinic.com', '+201234567891', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', TRUE),
('Roya Sec', 'sec', 'sec@roayaclinic.com', '+201234567892', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary', TRUE),
('System Admin', 'admin', 'admin@roayaclinic.com', '+201234567893', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);

-- Insert doctors
INSERT INTO doctors (user_id, display_name, specialty, license_number) VALUES
(1, 'Dr. Ahmed Abo AlKassem', 'Ophthalmology', 'EGY-OPH-001'),
(2, 'Dr. Ahmed AlFaramawy', 'Ophthalmology', 'EGY-OPH-002');

-- Insert doctor schedules
-- All doctors work all days except Friday (Friday is always off)
-- Dr. Ahmed: All days except Friday
INSERT INTO doctor_schedule (doctor_id, weekday, is_working, work_start, work_end) VALUES
(1, 0, TRUE, '14:00:00', '23:00:00'), -- Sunday
(1, 1, TRUE, '14:00:00', '23:00:00'), -- Monday
(1, 2, TRUE, '14:00:00', '23:00:00'), -- Tuesday
(1, 3, TRUE, '14:00:00', '23:00:00'), -- Wednesday
(1, 4, TRUE, '14:00:00', '23:00:00'), -- Thursday
(1, 5, FALSE, '00:00:00', '00:00:00'), -- Friday (OFF)
(1, 6, TRUE, '14:00:00', '23:00:00'); -- Saturday

-- Dr. Ahmed AlFaramawy: All days except Friday
INSERT INTO doctor_schedule (doctor_id, weekday, is_working, work_start, work_end) VALUES
(2, 0, TRUE, '14:00:00', '23:00:00'), -- Sunday
(2, 1, TRUE, '14:00:00', '23:00:00'), -- Monday
(2, 2, TRUE, '14:00:00', '23:00:00'), -- Tuesday
(2, 3, TRUE, '14:00:00', '23:00:00'), -- Wednesday
(2, 4, TRUE, '14:00:00', '23:00:00'), -- Thursday
(2, 5, FALSE, '00:00:00', '00:00:00'), -- Friday (OFF)
(2, 6, TRUE, '14:00:00', '23:00:00'); -- Saturday

-- Insert sample patients
INSERT INTO patients (first_name, last_name, dob, gender, address, phone, alt_phone, national_id) VALUES
('Mohammed', 'Ali', '1985-03-15', 'Male', '123 Main St, Cairo', '+201234567894', '+201234567895', '12345678901234'),
('Aisha', 'Hassan', '1990-07-22', 'Female', '456 Oak Ave, Giza', '+201234567896', '+201234567897', '12345678901235'),
('Omar', 'Mahmoud', '1978-11-08', 'Male', '789 Pine Rd, Alexandria', '+201234567898', '+201234567899', '12345678901236'),
('Fatima', 'Ahmed', '1995-01-30', 'Female', '321 Elm St, Cairo', '+201234567900', '+201234567901', '12345678901237'),
('Ahmed', 'Saleh', '1982-09-14', 'Male', '654 Maple Dr, Giza', '+201234567902', '+201234567903', '12345678901238'),
('Nour', 'Ibrahim', '1988-12-03', 'Female', '987 Cedar Ln, Alexandria', '+201234567904', '+201234567905', '12345678901239');

-- Insert medical history for patients
INSERT INTO medical_history (patient_id, allergies, medications, systemic_history, ocular_history, prior_surgeries, family_history) VALUES
(1, 'Penicillin', 'None currently', 'Hypertension', 'Myopia since childhood', 'None', 'Father has diabetes'),
(2, 'None', 'Contact lens solution', 'None', 'Astigmatism', 'None', 'Mother has glaucoma'),
(3, 'Sulfa drugs', 'Blood pressure medication', 'Diabetes, Hypertension', 'Cataract in right eye', 'Cataract surgery 2020', 'Both parents diabetic'),
(4, 'None', 'None', 'None', 'Hyperopia', 'None', 'Sister has myopia'),
(5, 'Latex', 'None', 'None', 'Presbyopia', 'None', 'None'),
(6, 'None', 'None', 'None', 'None', 'None', 'None');

-- Insert sample appointments
INSERT INTO appointments (patient_id, doctor_id, booked_by, source, date, start_time, end_time, status, visit_type, notes) VALUES
(1, 1, 3, 'Walk-in', CURRENT_DATE, '14:00:00', '14:15:00', 'Completed', 'New', 'Annual eye checkup'),
(2, 2, 3, 'Phone', CURRENT_DATE, '14:15:00', '14:30:00', 'Completed', 'FollowUp', 'Follow-up for astigmatism'),
(3, 1, 3, 'Walk-in', CURRENT_DATE, '14:30:00', '14:45:00', 'InProgress', 'New', 'Complaining of blurry vision'),
(4, 2, 3, 'Phone', CURRENT_DATE, '14:45:00', '15:00:00', 'Booked', 'New', 'First time visit'),
(5, 1, 3, 'Walk-in', CURRENT_DATE, '15:00:00', '15:15:00', 'Booked', 'FollowUp', 'Glasses adjustment'),
(6, 2, 3, 'Phone', CURRENT_DATE, '15:15:00', '15:30:00', 'Booked', 'New', 'Eye pain complaint');

-- Insert consultation notes
INSERT INTO consultation_notes (appointment_id, chief_complaint, hx_present_illness, visual_acuity_right, visual_acuity_left, refraction_right, refraction_left, IOP_right, IOP_left, slit_lamp, fundus, diagnosis, diagnosis_code, plan, followup_days, created_by) VALUES
(1, 'Annual checkup', 'No complaints', '6/6', '6/6', '-2.00 DS', '-2.25 DS', 16.5, 17.0, 'Normal anterior segment', 'Normal fundus', 'Myopia', 'H52.1', 'Continue current glasses, annual follow-up', 365, 1),
(2, 'Blurry vision', 'Worsening over 6 months', '6/9', '6/12', '-1.50 -0.75 x 90', '-1.75 -0.50 x 85', 18.0, 17.5, 'Normal anterior segment', 'Normal fundus', 'Astigmatism', 'H52.2', 'New glasses prescription, follow-up in 3 months', 90, 2);

-- Insert glasses prescriptions
INSERT INTO glasses_prescriptions (appointment_id, distance_sphere_r, distance_cylinder_r, distance_axis_r, distance_sphere_l, distance_cylinder_l, distance_axis_l, PD_NEAR, PD_DISTANCE, lens_type, comments) VALUES
(1, -2.00, 0.00, 0, -2.25, 0.00, 0, 0.00, 0.00, 62.0, 62.0, 'Single Vision', 'Standard myopia correction'),
(2, -1.50, -0.75, 90, -1.75, -0.50, 85, 0.00, 0.00, 61.5, 61.5, 'Single Vision', 'Astigmatism correction');

-- Insert medication prescriptions
INSERT INTO prescriptions (appointment_id, drug_name, dose, frequency, duration, route, notes) VALUES
(1, 'Artificial tears', '1 drop', '4 times daily', 'As needed', 'Topical', 'For dry eyes'),
(2, 'Lubricating eye drops', '1 drop', '3 times daily', '2 weeks', 'Topical', 'Reduce eye strain');

-- Insert payments
INSERT INTO payments (appointment_id, patient_id, received_by, type, method, amount, currency, discount_amount, discount_reason, is_exempt, exempt_reason, approval_user_id, approval_at) VALUES
(1, 1, 3, 'Consultation', 'Cash', 200.00, 'EGP', 0.00, NULL, FALSE, NULL, 1, NOW()),
(2, 2, 3, 'FollowUp', 'Card', 150.00, 'EGP', 0.00, NULL, FALSE, NULL, 2, NOW()),
(3, 3, 3, 'Consultation', 'Cash', 200.00, 'EGP', 50.00, 'Student discount', FALSE, NULL, NULL, NULL),
(4, 4, 3, 'Booking', 'Cash', 50.00, 'EGP', 0.00, NULL, FALSE, NULL, NULL, NULL),
(5, 5, 3, 'FollowUp', 'Cash', 150.00, 'EGP', 0.00, NULL, FALSE, NULL, NULL, NULL),
(6, 6, 3, 'Booking', 'Card', 50.00, 'EGP', 0.00, NULL, FALSE, NULL, NULL, NULL);

-- Insert timeline events
INSERT INTO timeline_events (patient_id, appointment_id, actor_user_id, event_type, event_summary, event_payload) VALUES
(1, 1, 3, 'Booking', 'Appointment booked for annual checkup', '{"source": "Walk-in", "doctor": "Dr. Ahmed Hassan"}'),
(1, 1, 1, 'Consultation', 'Consultation completed', '{"diagnosis": "Myopia", "plan": "Continue current glasses"}'),
(1, 1, 1, 'Rx', 'Glasses prescription issued', '{"sphere_r": -2.00, "sphere_l": -2.25}'),
(1, 1, 3, 'Payment', 'Payment received for consultation', '{"amount": 200.00, "method": "Cash"}'),
(2, 2, 3, 'Booking', 'Follow-up appointment booked', '{"source": "Phone", "doctor": "Dr. Sara Mahmoud"}'),
(2, 2, 2, 'Consultation', 'Follow-up consultation completed', '{"diagnosis": "Astigmatism", "plan": "New glasses prescription"}'),
(2, 2, 2, 'GlassesRx', 'New glasses prescription issued', '{"cylinder_r": -0.75, "cylinder_l": -0.50}'),
(2, 2, 3, 'Payment', 'Payment received for follow-up', '{"amount": 150.00, "method": "Card"}');

-- Insert invoices
INSERT INTO invoices (invoice_no, patient_id, doctor_id, date, total, paid, status) VALUES
('INV-2024-001', 1, 1, CURRENT_DATE, 200.00, 200.00, 'Paid'),
('INV-2024-002', 2, 2, CURRENT_DATE, 150.00, 150.00, 'Paid'),
('INV-2024-003', 3, 1, CURRENT_DATE, 200.00, 150.00, 'Partial'),
('INV-2024-004', 4, 2, CURRENT_DATE, 50.00, 0.00, 'Unpaid'),
('INV-2024-005', 5, 1, CURRENT_DATE, 150.00, 0.00, 'Unpaid'),
('INV-2024-006', 6, 2, CURRENT_DATE, 50.00, 0.00, 'Unpaid');

-- Insert daily closure for today
INSERT INTO daily_closures (doctor_id, date, closed_by, total_payments, total_appointments, completed_appointments, note) VALUES
(1, CURRENT_DATE, 1, 350.00, 3, 2, 'Good day, 2 consultations completed'),
(2, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 2, 150.00, 3, 1, 'One follow-up completed');

-- Insert audit logs
INSERT INTO audit_logs (user_id, entity_table, entity_id, action, before_json, after_json, reason, ip_address) VALUES
(3, 'appointments', 1, 'CREATE', NULL, '{"patient_id": 1, "doctor_id": 1, "status": "Booked"}', 'New appointment booking', '127.0.0.1'),
(1, 'consultation_notes', 1, 'CREATE', NULL, '{"diagnosis": "Myopia", "plan": "Continue current glasses"}', 'Consultation notes added', '127.0.0.1'),
(3, 'payments', 1, 'CREATE', NULL, '{"amount": 200.00, "type": "Consultation"}', 'Payment received', '127.0.0.1');

-- Note: Password hash used is 'password' for all users
-- In production, use proper password hashing
