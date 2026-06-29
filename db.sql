CREATE DATABASE IF NOT EXISTS lab3
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lab3;

CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(120) NOT NULL,

    birth_date DATE NOT NULL,

    gender ENUM('male', 'female') NOT NULL,

    biography TEXT,

    agreement TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE languages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE application_language (
    application_id INT UNSIGNED NOT NULL,
    language_id INT UNSIGNED NOT NULL,

    PRIMARY KEY(application_id, language_id),

    FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE,

    FOREIGN KEY (language_id)
        REFERENCES languages(id)
        ON DELETE CASCADE
);

INSERT INTO languages (title) VALUES
('Pascal'),
('C'),
('C++'),
('JavaScript'),
('PHP'),
('Python'),
('Java'),
('Haskell'),
('Clojure'),
('Prolog'),
('Scala'),
('Go');