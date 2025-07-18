-- Create the users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    last_login DATETIME
);

-- Insert the data
INSERT INTO users (user_id, username, password, email, role, created_at, first_name, last_name, phone, last_login)
VALUES 
(1, 'bobogodwill', '$2y$10$0BX5HIhZfXO.zWIs1ZWi7O.g3xi5i6X5X3pcVlKKDAT...', 'ryan@gmail.com', 'patient', '2025-07-03 20:17:13', NULL, NULL, NULL, NULL),
(2, 'staff', '$2y$10$gQr4r/DOvTo5vFRUoKzRPOp0Bp428ndSh4tJkwk0Ey5...', 'staff@gmail.com', 'staff', '2025-07-03 22:43:26', 'anyanwu', 'godwill', '672223454', NULL),
(3, 'newstaff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2...', 'newstaff@example.com', 'staff', '2025-07-05 00:18:34', 'Njo', 'Hanson', '1234567890', '2025-07-05 17:53:30'),
(4, 'nchangmarry', '$2y$10$b7qXBX/CwpMiqWzYniWJGO.ARkB8PJdHyut5WnWXTOS...', 'mary@gmail.com', 'patient', '2025-07-05 16:25:59', NULL, NULL, NULL, NULL);

-- If you want the auto-increment to continue from the highest user_id
ALTER TABLE users AUTO_INCREMENT = 5;

-- Create the reminders table
CREATE TABLE reminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    reminder_type VARCHAR(50) NOT NULL,
    reminder_text VARCHAR(255) NOT NULL,
    reminder_date DATETIME NOT NULL,
    reminder_message VARCHAR(255),
    due_date DATETIME,
    status VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    created_by INT
);

-- Insert the data
INSERT INTO reminders (reminder_id, patient_id, reminder_type, reminder_text, reminder_date, reminder_message, due_date, status, created_at, created_by)
VALUES 
(1, 1, 'followup', 'reminder', '2025-07-05 12:00:00', NULL, '2025-07-05 12:21:02', 'pending', '2025-07-05 11:45:21', NULL);

-- Set auto-increment to continue from the highest reminder_id
ALTER TABLE reminders AUTO_INCREMENT = 2;

-- Create the providers table
CREATE TABLE providers (
    provider_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    specialty VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

-- Insert the data
INSERT INTO providers (provider_id, first_name, last_name, specialty, phone, email, is_active)
VALUES 
(1, 'Sarah', 'Johnson', 'Cardiology', '555-123-4567', 's.johnson@example.com', 1),
(2, 'Michael', 'Chen', 'Pediatrics', '555-234-5678', 'm.chen@example.com', 1),
(3, 'Emily', 'Rodriguez', 'Neurology', '555-345-6789', 'e.rodriguez@example.com', 1),
(4, 'David', 'Wilson', 'Orthopedics', '555-456-7890', 'd.wilson@example.com', 1);

-- Set auto-increment to continue from the highest provider_id
ALTER TABLE providers AUTO_INCREMENT = 5;

-- Create the patients table
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    created_at DATETIME NOT NULL
);

-- Insert the data
INSERT INTO patients (patient_id, first_name, last_name, email, phone, dob, created_at)
VALUES 
(1, 'Bobo', 'godwill', 'ryan@gmail.com', '672392144', '2006-10-03', '2025-07-03 20:17:13'),
(2, 'Nchang', 'marry', 'mary@gmail.com', '672392144', '2005-10-03', '2025-07-05 16:25:59');

-- Set auto-increment to continue from the highest patient_id
ALTER TABLE patients AUTO_INCREMENT = 3;

-- Create the medical_records table
CREATE TABLE medical_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    record_type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    date_recorded DATETIME NOT NULL,
    recorded_by INT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id)
);

-- Example insert (adjust values as needed)
INSERT INTO medical_records 
(record_id, patient_id, record_type, title, description, file_path, date_recorded, recorded_by, created_at)
VALUES
(1, 1, 'lab_result', 'Blood Test 2025', 'Complete blood count results', '/records/patient1/blood_test_2025.pdf', '2025-07-10 14:30:00', 2, '2025-07-10 14:35:00');

-- Set auto-increment to continue from the highest record_id
ALTER TABLE medical_records AUTO_INCREMENT = 2;

-- Create the feedback table
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    feedback_text TEXT NOT NULL COLLATE utf8mb4_0900_ai_ci,
    rating INT,
    feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'resolved') COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
    INDEX (patient_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- Example insert statements
INSERT INTO feedback (patient_id, feedback_text, rating, status)
VALUES 
(1, 'The doctor was very attentive and explained everything clearly.', 5, 'reviewed'),
(2, 'Long waiting time for the appointment.', 3, 'pending'),
(1, 'Excellent service from the nursing staff.', 4, 'resolved');

-- Set auto-increment to continue from the highest feedback_id
ALTER TABLE feedback AUTO_INCREMENT = 4;

-- Create the appointment_types table
CREATE TABLE appointment_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    description TEXT
) COMMENT 'Table storing different types of medical appointments';

-- Insert the appointment types
INSERT INTO appointment_types (type_id, name, duration, description)
VALUES
(1, 'General Consultation', 30, 'Routine health check or consultation'),
(2, 'Follow-up Visit', 20, 'Follow-up appointment for existing condition'),
(3, 'Vaccination', 15, 'Immunization appointment'),
(4, 'Procedure', 60, 'Medical procedure or treatment'),
(5, 'Lab Test', 30, 'Laboratory testing appointment');

-- Set auto-increment to continue from the highest type_id
ALTER TABLE appointment_types AUTO_INCREMENT = 6;

-- Create a junction table for provider-appointment type relationships
CREATE TABLE provider_appointment_types (
    provider_id INT NOT NULL,
    type_id INT NOT NULL,
    PRIMARY KEY (provider_id, type_id),
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id),
    FOREIGN KEY (type_id) REFERENCES appointment_types(type_id)
);

-- Create the appointments table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    appointment_type VARCHAR(50) NOT NULL COLLATE utf8mb4_0900_ai_ci,
    appointment_date DATETIME NOT NULL,
    duration INT NOT NULL DEFAULT 30,
    status ENUM('scheduled', 'completed', 'canceled', 'no-show') COLLATE utf8mb4_0900_ai_ci DEFAULT 'scheduled',
    notes TEXT COLLATE utf8mb4_0900_ai_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (patient_id),
    INDEX (provider_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Example insert statements
INSERT INTO appointments (
    patient_id, 
    provider_id, 
    appointment_type, 
    appointment_date, 
    duration, 
    status, 
    notes
) VALUES
(1, 1, 'General Consultation', '2025-07-15 09:00:00', 30, 'scheduled', 'Annual checkup'),
(2, 2, 'Vaccination', '2025-07-15 10:30:00', 15, 'scheduled', 'Flu shot required'),
(1, 3, 'Follow-up Visit', '2025-07-16 14:00:00', 20, 'scheduled', 'Review lab results');

-- Set auto-increment to continue from the highest appointment_id
ALTER TABLE appointments AUTO_INCREMENT = 4;

-- First make sure the referenced tables exist with proper structure
CREATE TABLE IF NOT EXISTS patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    -- other patient fields...
    created_at DATETIME
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS providers (
    provider_id INT AUTO_INCREMENT PRIMARY KEY,
    -- other provider fields...
    is_active TINYINT(1)
) ENGINE=InnoDB;

-- Then create the appointments table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    appointment_type VARCHAR(50) NOT NULL COLLATE utf8mb4_0900_ai_ci,
    appointment_date DATETIME NOT NULL,
    duration INT NOT NULL DEFAULT 30,
    status ENUM('scheduled', 'completed', 'canceled', 'no-show') COLLATE utf8mb4_0900_ai_ci DEFAULT 'scheduled',
    notes TEXT COLLATE utf8mb4_0900_ai_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (patient_id),
    INDEX (provider_id),
    CONSTRAINT fk_appointment_patient FOREIGN KEY (patient_id) 
        REFERENCES patients(patient_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_appointment_provider FOREIGN KEY (provider_id) 
        REFERENCES providers(provider_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



const ACCOUNT_SID = 'ACa9eda75befdbffae465556f13a2e66e8';
    const AUTH_TOKEN = 'e63e47eb7ad379bf0ef8e80e06180449';
    const FROM_NUMBER = '+17722764245'; 

    Add message_sid column to reminders table
ALTER TABLE reminders ADD COLUMN message_sid VARCHAR(50) NULL;
ALTER TABLE reminders ADD COLUMN sent_at TIMESTAMP NULL;

-- Create SMS logs table
CREATE TABLE sms_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT,
    patient_id INT,
    phone_number VARCHAR(20),
    message TEXT,
    status ENUM('sent', 'failed', 'delivered', 'undelivered') DEFAULT 'sent',
    message_sid VARCHAR(50),
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES reminders(reminder_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
);

-- Add phone_number column to patients table if not exists
ALTER TABLE patients ADD COLUMN phone_number VARCHAR(20) NULL;