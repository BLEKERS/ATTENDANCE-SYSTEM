<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$database = "student_registration";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Path to the text file
$file_path = "D:/Attendance-System/ATTENDANCE-SYSTEM/Reader/serial.txt";

// Check if the text file exists and is readable
if (!file_exists($file_path)) {
    echo json_encode(["status" => "error", "message" => "RFID text file not found."]);
    exit;
}

// Read the RFID serial number from the file
$rfid_serial = trim(file_get_contents($file_path));

// If the file is empty, return no data
if (empty($rfid_serial)) {
    echo json_encode(["status" => "empty", "message" => "No RFID detected."]);
    exit;
}

// Query the database to find the matching student
$sql = "SELECT student_name, course_year, school_year, photo_path, rfid_serial FROM students WHERE rfid_serial = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rfid_serial);
$stmt->execute();
$result = $stmt->get_result();

// Check if a match is found
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();

    // Clear the RFID from the file
    file_put_contents($file_path, "");

    // Return the student data, including the rfid_serial for comparison
    echo json_encode(["status" => "success", "data" => $student]);
} else {
    // If RFID is not recognized, erase it from the file
    file_put_contents($file_path, "");

    echo json_encode(["status" => "not_found", "message" => "RFID not recognized."]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
