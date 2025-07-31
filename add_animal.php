<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['name'])) {
    // Get farmer's phone from the database
    $farmer_name = $_SESSION['name'];
    $sql = "SELECT phone FROM farmers WHERE name = '$farmer_name' LIMIT 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $farmer_phone = $row['phone'];

    $animal_type = $conn->real_escape_string($_POST['animal_type']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $age = (int)$_POST['age'];
    $health_status = $conn->real_escape_string($_POST['health_status']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $sql = "INSERT INTO animals (farmer_phone, animal_type, breed, age, health_status, notes)
            VALUES ('$farmer_phone', '$animal_type', '$breed', $age, '$health_status', '$notes')";
    $conn->query($sql);
}
header("Location: dashboard.php");
exit();
?>