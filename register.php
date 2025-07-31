<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $county = $conn->real_escape_string($_POST['county']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO farmers (name, phone, county, password) VALUES ('$name', '$phone', '$county', '$password')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['name'] = $name;
        $_SESSION['county'] = $county; // Add this line
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Registration failed: " . $conn->error;
    }
    $conn->close();
}
?>