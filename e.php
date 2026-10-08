<?php
/* ---------- DATABASE CONNECTION ---------- */
$conn = new mysqli("localhost", "root", "", "Design_inside");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* ---------- FETCH FORM DATA ---------- */
$first_name = trim($_POST['first_name']);
$last_name  = trim($_POST['last_name']);
$name       = $first_name . " " . $last_name;

$email      = trim($_POST['email']);
$phone_num  = trim($_POST['phone_num']);
$phone_num2 = trim($_POST['phone_num2']);

$address    = trim($_POST['address']);
$place      = trim($_POST['place']);
$service    = trim($_POST['service']);

$price_range = $_POST['price_range'];
$start_date  = $_POST['start_date'];
$message     = trim($_POST['message']);


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<h3>Invalid Email Format</h3>");
}

if (!preg_match('/^[0-9]{10}$/', $phone_num)) {
    die("<h3>Phone number must be exactly 10 digits</h3>");
}

if (!empty($phone_num2) && !preg_match('/^[0-9]{10}$/', $phone_num2)) {
    die("<h3>Alternate phone must be 10 digits</h3>");
}

/* ---------- CHECK DUPLICATE EMAIL ---------- */
$check = $conn->prepare("SELECT email FROM Customer WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("<h3>This email is already registered</h3>");
}
$check->close();


$sql = "INSERT INTO Customer
        (name, email, phone_num, phone_num2, address, price_range, start_date, place, service, message)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssdssss",
    $name,
    $email,
    $phone_num,
    $phone_num2,
    $address,
    $price_range,
    $start_date,
    $place,
    $service,
    $message
);

if ($stmt->execute()) {
    echo "<h2>Quotation Submitted Successfully</h2>";
    echo "<p>Our team will contact you soon.</p>";
} else {
    echo "<h3>Error submitting quotation</h3>";
}


$stmt->close();
$conn->close();

?>