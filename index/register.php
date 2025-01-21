<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "student_registration");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $student_name = $_POST['student_name'];
    $legal_guardian = $_POST['legal_guardian'];
    $id_number = $_POST['id_number'];
    $rfid_serial = $_POST['rfid_serial'];
    $course_year = $_POST['course_year'];
    $school_year = $_POST['school_year'];
    $guardian_contact = $_POST['guardian_contact'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $photo_path = $upload_dir . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO students (student_name, legal_guardian, id_number, rfid_serial, course_year, school_year, guardian_contact, address, password, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $student_name, $legal_guardian, $id_number, $rfid_serial, $course_year, $school_year, $guardian_contact, $address, $password, $photo_path);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
