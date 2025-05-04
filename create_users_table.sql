-- SQL script to create users table for registration

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    security_answer_1 VARCHAR(255) NOT NULL,
    security_answer_2 VARCHAR(255) NOT NULL,
    security_answer_3 VARCHAR(255) NOT NULL,
    security_answer_4 VARCHAR(255) NOT NULL,
    security_answer_5 VARCHAR(255) NOT NULL,
    security_answer_6 VARCHAR(255) NOT NULL,
    security_answer_7 VARCHAR(255) NOT NULL,
    security_answer_8 VARCHAR(255) NOT NULL,
    security_answer_9 VARCHAR(255) NOT NULL,
    security_answer_10 VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time DATETIME NOT NULL,
    ip_address VARCHAR(45),
    success TINYINT(1) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
