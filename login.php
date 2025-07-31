<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone']) && isset($_POST['password'])) {
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM farmers WHERE phone = '$phone'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['county'] = $row['county']; // Add this line
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No user found with that phone number.</div>";
    }
    $conn->close();
} else {
    header("Location: index.html");
    exit();
}
?>