-- risasi_one MySQL database schema and sample data
DROP DATABASE IF EXISTS risasi_ones;
CREATE DATABASE risasi_ones CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE risasi_ones;

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    contact_type ENUM('WhatsApp','SMS/USSD') NOT NULL DEFAULT 'WhatsApp',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE electricity_rotation (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    current_tenant_id INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE water_bills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_month VARCHAR(20) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    control_number VARCHAR(80) NOT NULL,
    tenant_count INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE message_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT NULL,
    tenant_name VARCHAR(120) NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    message_type ENUM('electricity','water','announcement') NOT NULL,
    message_body TEXT NOT NULL,
    delivery_channel VARCHAR(255) NOT NULL,
    provider VARCHAR(80) DEFAULT NULL,
    status VARCHAR(40) DEFAULT NULL,
    provider_response TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO admins (full_name, email, password) VALUES
('Site Administrator', 'admin@risasi.one', '$2y$10$BNV8dPA8li0mTdJBtBvj6ucweB0yJaPaFxFCMRhQ80S.sWfn0wFkW');

INSERT INTO tenants (full_name, phone_number, room_number, contact_type, status) VALUES
('Amina Mwandu', '+255712345678', 'A101', 'WhatsApp', 'active'),
('John Komba', '+255788112233', 'A102', 'SMS/USSD', 'active'),
('Fatima Hassan', '+255754998877', 'B201', 'WhatsApp', 'active'),
('Emmanuel Mwakyoma', '+255765443322', 'B202', 'SMS/USSD', 'inactive'),
('Rashid Selemani', '+255678223344', 'C301', 'WhatsApp', 'active');

INSERT INTO electricity_rotation (current_tenant_id) VALUES (1);
