CREATE DATABASE IF NOT EXISTS rmtracker_db;
USE rmtracker_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'petugas_rm', 'perawat') NOT NULL,
    unit_asal VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rm_current_status (
    no_rkm_medis VARCHAR(15) PRIMARY KEY,
    status ENUM('TERSEDIA', 'DIPINJAM', 'HILANG') DEFAULT 'TERSEDIA',
    holder_id INT NULL,
    lokasi_terkini VARCHAR(50) DEFAULT 'RM',
    borrowed_at DATETIME NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (holder_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS rm_tracking_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    no_rkm_medis VARCHAR(15) NOT NULL,
    action ENUM('CHECK_OUT', 'TRANSFER', 'CHECK_IN') NOT NULL,
    from_user_id INT NULL,
    to_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Superadmin (Password: admin123)
INSERT INTO users (nik, password, nama, role, unit_asal) 
VALUES ('admin', '$2y$10$wTf2W48uI4oW8R0.yQpL/.oZpXF4W5C9aB3v0/f5GjM.0Y0d8/V.S', 'Administrator', 'superadmin', 'IT')
ON DUPLICATE KEY UPDATE id=id;
