<?php
/**
 * Database Setup & Migration Script
 * Engineering Department Letter Management System
 * Database: engineering_db
 */

$host = "127.0.0.1";
$username = "root";
$password = "";

try {
    // 1. Connect to MySQL server
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS engineering_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✅ Database 'engineering_db' checked/created successfully.\n";

    // 3. Connect to engineering_db
    $conn->exec("USE engineering_db");

    // 4. Create 'users' table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) DEFAULT '',
        role VARCHAR(20) DEFAULT 'admin'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // Ensure 'email' column exists if users table was created previously without it
    $checkEmailCol = $conn->query("SHOW COLUMNS FROM users LIKE 'email'")->fetch();
    if (!$checkEmailCol) {
        $conn->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER username");
        echo "  - Added 'email' column to users table.\n";
    }

    // 5. Create 'letters' table
    $sql = "CREATE TABLE IF NOT EXISTS letters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial_no VARCHAR(50) NOT NULL UNIQUE,
        letter_no VARCHAR(100) DEFAULT NULL,
        sent_date DATE DEFAULT NULL,
        received_date DATETIME DEFAULT NULL,
        sender_name VARCHAR(150) DEFAULT NULL,
        sender_designation VARCHAR(150) DEFAULT NULL,
        sender_institute VARCHAR(150) DEFAULT NULL,
        sender_address VARCHAR(255) DEFAULT NULL,
        subject TEXT DEFAULT NULL,
        mail_type VARCHAR(50) DEFAULT NULL,
        added_by INT DEFAULT NULL,
        added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'Unresolved',
        KEY idx_added_by (added_by),
        CONSTRAINT letters_fk_users FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 6. Create 'section_heads' table
    $sql = "CREATE TABLE IF NOT EXISTS section_heads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 7. Create 'primary_assignments' table
    $sql = "CREATE TABLE IF NOT EXISTS primary_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        letter_id INT NOT NULL,
        serial_no VARCHAR(50) DEFAULT NULL,
        letter_no VARCHAR(100) DEFAULT NULL,
        received_date VARCHAR(50) DEFAULT NULL,
        subject TEXT DEFAULT NULL,
        sender_name VARCHAR(150) DEFAULT NULL,
        sender_designation VARCHAR(150) DEFAULT NULL,
        sender_institute VARCHAR(150) DEFAULT NULL,
        sender_address VARCHAR(255) DEFAULT NULL,
        mail_type VARCHAR(50) DEFAULT NULL,
        assignment_date DATE DEFAULT NULL,
        section_head VARCHAR(100) DEFAULT NULL,
        task VARCHAR(150) DEFAULT NULL,
        primary_notes TEXT DEFAULT NULL,
        KEY idx_pa_letter_id (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 8. Create 'secondary_assignments' table
    $sql = "CREATE TABLE IF NOT EXISTS secondary_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        letter_id INT NOT NULL,
        serial_no VARCHAR(50) DEFAULT NULL,
        letter_no VARCHAR(100) DEFAULT NULL,
        subject TEXT DEFAULT NULL,
        sender_details TEXT DEFAULT NULL,
        primary_assignment VARCHAR(255) DEFAULT NULL,
        primary_date VARCHAR(50) DEFAULT NULL,
        primary_note TEXT DEFAULT NULL,
        section_head VARCHAR(100) DEFAULT NULL,
        secondary_date DATE DEFAULT NULL,
        secondary_notes TEXT DEFAULT NULL,
        KEY idx_sa_letter_id (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 9. Create 'institute_contacts' table
    $sql = "CREATE TABLE IF NOT EXISTS institute_contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        institute_name VARCHAR(150) NOT NULL UNIQUE,
        person_name VARCHAR(150) DEFAULT NULL,
        designation VARCHAR(150) DEFAULT NULL,
        email VARCHAR(150) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        address VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 10. Create 'officers' table
    $sql = "CREATE TABLE IF NOT EXISTS officers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        section VARCHAR(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 11. Create 'tasks' table
    $sql = "CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_name VARCHAR(150) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    // 12. Create 'assignments' table
    $sql = "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        letter_id INT NOT NULL,
        section_head_id INT DEFAULT NULL,
        task_id INT DEFAULT NULL,
        primary_notes TEXT DEFAULT NULL,
        assignment_date DATE DEFAULT NULL,
        KEY idx_ass_letter_id (letter_id),
        KEY idx_ass_sh_id (section_head_id),
        KEY idx_ass_task_id (task_id),
        CONSTRAINT assignments_ibfk_1 FOREIGN KEY (letter_id) REFERENCES letters(id) ON DELETE CASCADE,
        CONSTRAINT assignments_ibfk_2 FOREIGN KEY (section_head_id) REFERENCES officers(id) ON DELETE SET NULL,
        CONSTRAINT assignments_ibfk_3 FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->exec($sql);

    echo "✅ All database tables verified and created.\n";

    // 13. Seed default section heads if table is empty
    $shCount = $conn->query("SELECT COUNT(*) FROM section_heads")->fetchColumn();
    if ($shCount == 0) {
        $defaultSectionHeads = [
            'AD- Additional director',
            'AO- Administrative officer',
            'Chief Accountant',
            'Electric Engineer',
            'Chief Architecture',
            'Chief engineer (Edu)',
            'Chief Engineer(Health)',
            'Chief Engineer (Other)',
            'QS-Quanty serviyor'
        ];
        $shStmt = $conn->prepare("INSERT IGNORE INTO section_heads (name) VALUES (:name)");
        foreach ($defaultSectionHeads as $name) {
            $shStmt->execute(['name' => $name]);
        }
        echo "✅ Seeded " . count($defaultSectionHeads) . " default Section Heads.\n";
    }

    // 14. Seed institute_contacts if table is empty
    $icCount = $conn->query("SELECT COUNT(*) FROM institute_contacts")->fetchColumn();
    if ($icCount == 0) {
        $defaultInstitutes = [
            [
                'institute_name' => 'NWP Chief Secretariat',
                'person_name' => 'P. B. Wijerathne',
                'designation' => 'Chief Secretary',
                'email' => 'chiefsec@nwp.gov.lk',
                'phone' => '037-2222123',
                'address' => 'Chief Secretarial Office, Kurunegala'
            ],
            [
                'institute_name' => 'Ministry of Education NWP',
                'person_name' => 'K. M. Fernando',
                'designation' => 'Secretary - Education',
                'email' => 'edu@nwp.gov.lk',
                'phone' => '037-2223456',
                'address' => 'Provincial Education Office, Kurunegala'
            ],
            [
                'institute_name' => 'Ministry of Health NWP',
                'person_name' => 'Dr. S. K. Jayasinghe',
                'designation' => 'Provincial Director of Health Services',
                'email' => 'health@nwp.gov.lk',
                'phone' => '037-2224567',
                'address' => 'Provincial Health Dept, Kurunegala'
            ],
            [
                'institute_name' => 'Road Development Authority (RDA)',
                'person_name' => 'Eng. R. M. Perera',
                'designation' => 'Executive Engineer',
                'email' => 'rda_nwp@rda.gov.lk',
                'phone' => '037-2227890',
                'address' => 'RDA Provincial Office, Kurunegala'
            ],
            [
                'institute_name' => 'Ceylon Electricity Board (CEB)',
                'person_name' => 'Eng. T. Wickramasinghe',
                'designation' => 'Area Electrical Engineer',
                'email' => 'ceb_kurunegala@ceb.lk',
                'phone' => '037-2225678',
                'address' => 'CEB Regional Office, Kurunegala'
            ],
            [
                'institute_name' => 'National Water Supply & Drainage Board',
                'person_name' => 'Eng. D. Bandara',
                'designation' => 'Regional Manager',
                'email' => 'waterboard_nw@waterboard.lk',
                'phone' => '037-2226789',
                'address' => 'NWSDB Regional Office, Kurunegala'
            ]
        ];

        $icStmt = $conn->prepare("INSERT IGNORE INTO institute_contacts (institute_name, person_name, designation, email, phone, address) 
                                  VALUES (:inst, :pname, :desig, :email, :phone, :addr)");
        foreach ($defaultInstitutes as $inst) {
            $icStmt->execute([
                ':inst' => $inst['institute_name'],
                ':pname' => $inst['person_name'],
                ':desig' => $inst['designation'],
                ':email' => $inst['email'],
                ':phone' => $inst['phone'],
                ':addr' => $inst['address']
            ]);
        }
        echo "✅ Seeded default Institute Contacts.\n";
    }

    // 15. Create or verify default Admin user (admin / admin123)
    $password_hash = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $insert = $conn->prepare("INSERT INTO users (username, email, password, name, role) VALUES ('admin', 'admin@engineering.gov.lk', :pass, 'Administrator', 'admin')");
        $insert->execute(['pass' => $password_hash]);
        echo "✅ Default admin user created (Username: admin, Password: admin123).\n";
    } else {
        $update = $conn->prepare("UPDATE users SET password = :pass, role = 'admin' WHERE username = 'admin'");
        $update->execute(['pass' => $password_hash]);
        echo "✅ Default admin user verified / password updated (Username: admin, Password: admin123).\n";
    }

    echo "\n🎉 Database setup and schema updates completed successfully!\n";

} catch(PDOException $e) {
    echo "❌ Database Setup Error: " . $e->getMessage() . "\n";
}
?>
