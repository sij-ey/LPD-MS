<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['name'])) {
    $user_name = $conn->real_escape_string($_SESSION['name']);
    $animal_type = $conn->real_escape_string($_POST['animal_type']);
    $disease = $conn->real_escape_string($_POST['disease']);
    $county = $conn->real_escape_string($_POST['county']);

    $sql = "INSERT INTO animal_history (user_name, animal_type, disease, county) 
            VALUES ('$user_name', '$animal_type', '$disease', '$county')";
    $conn->query($sql);
}
header("Location: dashboard.php");
exit();
?>