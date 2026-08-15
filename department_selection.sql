-- ============================================================
-- DATABASE: department_selection
-- Debre Markos University - Department Selection System
-- ============================================================

CREATE DATABASE IF NOT EXISTS department_selection
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

USE department_selection;

-- ============================================================
-- TABLE: settings
-- System-wide configuration (single row, id=1)
-- ============================================================

CREATE TABLE IF NOT EXISTS settings (
    id               INT          NOT NULL AUTO_INCREMENT,
    university_name  VARCHAR(200) NOT NULL DEFAULT 'Debre Markos University',
    system_name      VARCHAR(200) NOT NULL DEFAULT 'Department Selection and Placement System',
    email            VARCHAR(150) NOT NULL DEFAULT '',
    phone            VARCHAR(50)  NOT NULL DEFAULT '',
    address          TEXT,
    website          VARCHAR(200) NOT NULL DEFAULT '',
    created_at       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed default settings row
INSERT INTO settings (id, university_name, system_name, email, phone, address, website)
VALUES (1,
        'Debre Markos University',
        'Department Selection and Placement System',
        'info@dmu.edu.et',
        '+251 058 771 1570',
        'Debre Markos, Amhara Region, Ethiopia',
        'https://www.dmu.edu.et')
ON DUPLICATE KEY UPDATE id = id;

-- ============================================================
-- TABLE: colleges
-- Colleges / Schools within the university
-- ============================================================

CREATE TABLE IF NOT EXISTS colleges (
    id           INT          NOT NULL AUTO_INCREMENT,
    college_name VARCHAR(200) NOT NULL,
    college_code VARCHAR(20)  NOT NULL UNIQUE,
    created_at   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: academic_years
-- Academic year records (only one can be Active at a time)
-- ============================================================

CREATE TABLE IF NOT EXISTS academic_years (
    id         INT         NOT NULL AUTO_INCREMENT,
    year_name  VARCHAR(50) NOT NULL UNIQUE,
    status     ENUM('Active','Inactive') NOT NULL DEFAULT 'Inactive',
    created_at TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: departments
-- University departments available for student selection
-- ============================================================

CREATE TABLE IF NOT EXISTS departments (
    id              INT          NOT NULL AUTO_INCREMENT,
    department_name VARCHAR(200) NOT NULL,
    department_code VARCHAR(20)  NOT NULL UNIQUE,
    capacity        INT          NOT NULL DEFAULT 0,
    status          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    created_at      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: users
-- All system users: students, registrars, admins, dept heads
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id            INT          NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(200) NOT NULL,
    student_id    VARCHAR(50)  NOT NULL DEFAULT '',
    gender        ENUM('Male','Female','Other','') NOT NULL DEFAULT '',
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(50)  DEFAULT NULL,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('student','registrar','admin','department_head') NOT NULL DEFAULT 'student',
    status        ENUM('Active','Blocked','Pending') NOT NULL DEFAULT 'Active',
    photo         VARCHAR(255) DEFAULT NULL,
    cgpa          DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    college_id    INT          DEFAULT NULL,
    department_id INT          DEFAULT NULL,
    entry_year    VARCHAR(20)  DEFAULT NULL,
    created_at    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_role   (role),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: students
-- Extended student profile (linked to users)
-- ============================================================

CREATE TABLE IF NOT EXISTS students (
    id           INT          NOT NULL AUTO_INCREMENT,
    user_id      INT          NOT NULL UNIQUE,
    first_name   VARCHAR(100) DEFAULT NULL,
    middle_name  VARCHAR(100) DEFAULT NULL,
    last_name    VARCHAR(100) DEFAULT NULL,
    college_id   INT          DEFAULT NULL,
    cgpa         DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    entry_year   VARCHAR(20)  DEFAULT NULL,
    status       ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    created_at   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: quotas
-- Department seat quotas per academic year
-- ============================================================

CREATE TABLE IF NOT EXISTS quotas (
    id               INT       NOT NULL AUTO_INCREMENT,
    department_id    INT       NOT NULL,
    academic_year_id INT       NOT NULL,
    total_seat       INT       NOT NULL DEFAULT 0,
    available_seat   INT       NOT NULL DEFAULT 0,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_dept_year (department_id, academic_year_id),
    FOREIGN KEY (department_id)    REFERENCES departments(id)    ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: student_choices
-- Student department preferences (up to 3 choices)
-- ============================================================

CREATE TABLE IF NOT EXISTS student_choices (
    id             INT       NOT NULL AUTO_INCREMENT,
    student_id     INT       NOT NULL UNIQUE,
    first_choice   INT       NOT NULL,
    second_choice  INT       NOT NULL,
    third_choice   INT       NOT NULL,
    submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id)    REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (first_choice)  REFERENCES departments(id)  ON DELETE CASCADE,
    FOREIGN KEY (second_choice) REFERENCES departments(id)  ON DELETE CASCADE,
    FOREIGN KEY (third_choice)  REFERENCES departments(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: placements
-- Student placement results after running the algorithm
-- ============================================================

CREATE TABLE IF NOT EXISTS placements (
    id               INT       NOT NULL AUTO_INCREMENT,
    student_id       INT       NOT NULL,
    department_id    INT       DEFAULT NULL,
    academic_year_id INT       NOT NULL,
    status           ENUM('Placed','Not Placed') NOT NULL DEFAULT 'Not Placed',
    published        ENUM('Yes','No')            NOT NULL DEFAULT 'No',
    placed_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_year (student_id, academic_year_id),
    FOREIGN KEY (student_id)       REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (department_id)    REFERENCES departments(id)  ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- TABLE: notifications
-- System notifications sent to students or broadcast to all
-- ============================================================

CREATE TABLE IF NOT EXISTS notifications (
    id         INT          NOT NULL AUTO_INCREMENT,
    user_id    INT          DEFAULT NULL,   -- NULL = broadcast to all students
    title      VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================================
-- DEFAULT ADMIN ACCOUNT
-- Username: admin  |  Password: admin1234
-- CHANGE THIS PASSWORD immediately after first login!
-- ============================================================

INSERT INTO users (full_name, student_id, gender, email, phone, username, password, role, status)
VALUES (
    'System Administrator',
    '',
    '',
    'admin@dmu.edu.et',
    '',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'Active'
)
ON DUPLICATE KEY UPDATE id = id;

-- ============================================================
-- SAMPLE DATA (optional - remove in production)
-- ============================================================

-- Sample Colleges
INSERT IGNORE INTO colleges (college_name, college_code) VALUES
('College of Computing and Informatics',      'CCI'),
('College of Engineering and Technology',     'CET'),
('College of Natural and Computational Science', 'CNCS'),
('College of Business and Economics',         'CBE'),
('College of Social Science and Humanities',  'CSSH');

-- Sample Academic Year
INSERT IGNORE INTO academic_years (year_name, status) VALUES
('2024/25', 'Inactive'),
('2025/26', 'Active');

-- Sample Departments
INSERT IGNORE INTO departments (department_name, department_code, capacity, status) VALUES
('Computer Science',               'CS',   60, 'Active'),
('Information Technology',         'IT',   55, 'Active'),
('Software Engineering',           'SE',   50, 'Active'),
('Electrical Engineering',         'EE',   45, 'Active'),
('Civil Engineering',               'CE',   50, 'Active'),
('Mechanical Engineering',         'ME',   45, 'Active'),
('Mathematics',                    'MATH', 40, 'Active'),
('Physics',                        'PHY',  35, 'Active'),
('Chemistry',                      'CHEM', 35, 'Active'),
('Accounting and Finance',         'AF',   60, 'Active'),
('Management',                     'MGT',  55, 'Active'),
('Economics',                      'ECON', 50, 'Active');

-- ============================================================
-- TABLE: contact_messages
-- Messages submitted via the public contact form
-- ============================================================

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT          NOT NULL AUTO_INCREMENT,
    name       VARCHAR(200) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
