<?php
include 'db_connect.php';

$county = $_POST['county'] ?? '';
$animal_type = $_POST['animal_type'] ?? '';

// You can add animal_type to the WHERE clause if your vets table supports it
$sql = "SELECT name, phone FROM vets WHERE county = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $county);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'name' => $row['name'], 'phone' => $row['phone']]);
} else {
    echo json_encode(['success' => false, 'message' => 'No vet found in this county.']);
}
?>