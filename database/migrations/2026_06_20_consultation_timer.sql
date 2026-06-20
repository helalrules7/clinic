-- Consultation timer: accumulated active consultation time per appointment.
-- consultation_seconds  = active on-screen seconds the doctor spent (pause/resume aware, client-accumulated, monotonic).
-- consultation_timer_status = 'running' | 'paused' | 'done' (NULL = a timer was never started for this appointment).
ALTER TABLE appointments
    ADD COLUMN consultation_seconds INT NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN consultation_timer_status VARCHAR(20) NULL DEFAULT NULL AFTER consultation_seconds;
