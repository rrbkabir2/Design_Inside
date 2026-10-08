<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}

/* ---------- DB Connection ---------- */
$conn = new mysqli("localhost", "root", "", "Design_inside");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

/* ---------- DELETE ---------- */
if (isset($_GET['delete'], $_GET['email'])) {
    $stmt = $conn->prepare("DELETE FROM Customer WHERE email=?");
    $stmt->bind_param("s", $_GET['email']);
    $stmt->execute();
    header("Location: customer.php");
    exit;
}

/* ---------- COMPLETED BUTTON ---------- */
if (isset($_GET['completed'], $_GET['email'])) {
    // Fetch customer details
    $stmt = $conn->prepare("SELECT * FROM Customer WHERE email=?");
    $stmt->bind_param("s", $_GET['email']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();

    if ($customer) {
        // Insert into completed table (Compro) with default extra fields (Profit_price, comp_pro_price)
        $stmt = $conn->prepare("
            INSERT INTO Compro 
            (name,email,phone_num,phone_num2,address,Profit_price,start_date,ending_date,place,service,message,comp_pro_price)
            VALUES (?,?,?,?,?,0,?,?,?, ?,?,0)
        ");
        $ending_date = NULL; // Can be updated by admin later in completed.php
        $stmt->bind_param(
            "ssssssssss",
            $customer['name'],
            $customer['email'],
            $customer['phone_num'],
            $customer['phone_num2'],
            $customer['address'],
            $customer['start_date'],
            $ending_date,
            $customer['place'],
            $customer['service'],
            $customer['message']
        );
        $stmt->execute();

        // Delete from Customer table after moving
        $stmt = $conn->prepare("DELETE FROM Customer WHERE email=?");
        $stmt->bind_param("s", $_GET['email']);
        $stmt->execute();
    }
    header("Location: completed.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name        = $_POST['name'];
    $email       = $_POST['email'];
    $phone_num   = $_POST['phone_num'];
    $phone_num2  = $_POST['phone_num2'] ?? '';
    $address     = $_POST['address'];
    $price_range = $_POST['price_range'];
    $start_date  = $_POST['start_date'];
    $place       = $_POST['place'];
    $service     = $_POST['service'];
    $message     = $_POST['message'];
    $status_c    = $_POST['status_c'];

    /* ---------- VALIDATION ---------- */
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone_num)) {
        echo "<script>alert('Phone number must be 10 digits and start with 6-9');history.back();</script>";
        exit;
    }

    if (!empty($phone_num2) && !preg_match('/^[6-9][0-9]{9}$/', $phone_num2)) {
        echo "<script>alert('Second phone number must be 10 digits and start with 6-9');history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');history.back();</script>";
        exit;
    }

    /* ---------- UPDATE ---------- */
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $stmt = $conn->prepare("UPDATE Customer SET name=?, email=?, phone_num=?, phone_num2=?, address=?, price_range=?, start_date=?, place=?, service=?, message=?, status_c=? WHERE email=?");
        $stmt->bind_param(
            "ssssssssssss",
            $name, $email, $phone_num, $phone_num2, $address, $price_range, $start_date, $place, $service, $message, $status_c, $_POST['old_email']
        );
        $stmt->execute();
    } 
    /* ---------- INSERT ---------- */
    else {
        $stmt = $conn->prepare("INSERT INTO Customer (name,email,phone_num,phone_num2,address,price_range,start_date,place,service,message,status_c) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "sssssssssss",
            $name, $email, $phone_num, $phone_num2, $address, $price_range, $start_date, $place, $service, $message, $status_c
        );

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('This email already exists!');history.back();</script>";
                exit;
            } else throw $e;
        }
    }
    header("Location: customer.php");
    exit;   
}

/* ---------- FETCH FOR EDIT ---------- */
$edit_data = null;
if (isset($_GET['edit'], $_GET['email'])) {
    $stmt = $conn->prepare("SELECT * FROM Customer WHERE email=?");
    $stmt->bind_param("s", $_GET['email']);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

/* ---------- FETCH WITH FILTER ---------- */
$filter_sql = "SELECT * FROM Customer WHERE 1=1";
$params = []; $types = '';
$filter_fields = ['name','email','service','status_c','place','price_range'];
foreach($filter_fields as $f){
    if(!empty($_GET[$f])){
        if($f == 'price_range'){
            if($_GET[$f] == '100000') $filter_sql .= " AND price_range <= 100000";
            elseif($_GET[$f] == '500000') $filter_sql .= " AND price_range <= 500000";
            elseif($_GET[$f] == '500001') $filter_sql .= " AND price_range > 500000";
        } else {
            $filter_sql .= " AND $f LIKE ?";
            $params[] = "%".$_GET[$f]."%";
            $types .= 's';
        }
    }
}

$filter_sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($filter_sql);
if($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}
body{
    background:#f5f5f5
}

nav{
    /* background:#004d4d; */
    background:#69a3a3;
    height: 100px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 30px;
    color:#fff;
}
nav .logo{
    font-size:24px;
    font-weight:700
}
nav a{
    color:#fff;
    text-decoration:none;
    margin-left:18px;
    font-weight:600
}
nav a:hover{
    color:#ffd700
}

/* ================= NAVBAR RESPONSIVE ================= */

.logo{
    height: 80px;
    width: 150px;
    background-image: url(Assets/Images/Logo.png);
    background-size: 115%;
    background-position-x: center;
    background-position-y: 45%;
}

.hamburger{
    display:none;
    font-size:28px;
    cursor:pointer;
}

/* Desktop */
.menus{
    display:flex;
    align-items:center;
}

/* Tablet */
@media (max-width: 992px){
    nav{
        padding:0 20px;
    }
}

/* Mobile */
@media (max-width: 768px){
    .hamburger{
        display:block;
    }

    .menus{
        position:absolute;
        top:80px;
        left:0;
        width:100%;
        background:#004d4d;
        flex-direction:column;
        align-items:center;
        display:none;
        padding:20px 0;
        z-index:999;
    }

    .menus a{
        margin:12px 0;
        font-size:18px;
    }

    .menus.active{
        display:flex;
    }
}

.filter-form{
    margin:20px auto;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    width:95%
}
.filter-form input, .filter-form select, .filter-form textarea, .filter-form button{
    padding:8px 10px;
    border:1px solid #ccc;
    border-radius:5px;
    font-size:14px
}
.filter-form button{
    background:#004d4d;
    color:#fff;
    border:none;
    cursor:pointer
}
.filter-form button:hover{
    background:#006666
}
.table-container{
    width:95%;
    margin:20px auto;
    background:#fff;
    padding:15px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
    overflow-x:auto
}

table{
    width:100%;
    border-collapse:collapse
}
th,td{
    padding:8px;
    border:1px solid #ddd;
    font-size:13px
}
th{
    background:#004d4d;
    color:#fff
}
tr:nth-child(even){
    background:#f9f9f9
}

.approve{
    background:#28a745;
    color:#fff;
    border:none;
    padding:6px 10px;
    border-radius:5px
}

.reject{
    background:#dc3545;
    color:#fff;
    border:none;
    padding:6px 10px;
    border-radius:5px
}

.completed{
    background:#007bff;
    color:#fff;
    border:none;
    padding:6px 10px;
    border-radius:5px
}

@media(max-width:768px){
    table,thead,tbody,tr,td,th{
        display:block
        }
    thead{
        display:none
    }
    tr{
        margin-bottom:10px
    }
    td{
        padding-left:45%;
        position:relative;
    }
    td::before{
        content:attr(data-label);
        position:absolute;
        left:10px;
        font-weight:600;
        color:#004d4d
    }
}

</style>
</head>
<body>
    <!-- <nav>
        <div class="logo">Design_Inside</div>
        <div>
            <a href="dashboard.php">Home</a>
            <a href="customer.php">Customer</a>
            <a href="completed.php">Completed</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav> -->
    <nav>
    <a href="http://localhost/Design_Inside/">
        <div class="logo"></div>
    </a>

    <!-- Hamburger -->
    <div class="hamburger" onclick="toggleMenu()">☰</div>

    <div class="menus">
        <a class="submenus underline" href="dashboard.php">DASHBOARD</a>
        <a class="submenus underline" href="customer.php">CUSTOMER</a>
        <a class="submenus underline" href="completed.php">COMPLETED</a>
        <a class="submenus underline" href="logout.php">LOGOUT</a>
    </div>
</nav>

<h2 style="margin:20px"><?= $edit_data ? "Update Customer" : "Add New Customer" ?></h2>

<form class="filter-form" method="post">
    <input type="hidden" name="action" value="<?= $edit_data ? 'update' : 'add' ?>">
    <?php if($edit_data): ?>
        <input type="hidden" name="old_email" value="<?= $edit_data['email'] ?>">
    <?php endif; ?>

    <input type="text" name="name" placeholder="Name" value="<?= $edit_data['name'] ?? '' ?>" required>
    <input type="email" name="email" placeholder="example@domain.com" pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" value="<?= $edit_data['email'] ?? '' ?>" required>
    <input type="tel" name="phone_num" placeholder="10-digit phone" pattern="[6-9][0-9]{9}" maxlength="10" value="<?= $edit_data['phone_num'] ?? '' ?>" required>
    <input type="tel" name="phone_num2" placeholder="10-digit phone (optional)" pattern="[6-9][0-9]{9}" maxlength="10" value="<?= $edit_data['phone_num2'] ?? '' ?>">
    <input type="text" name="address" placeholder="Address" value="<?= $edit_data['address'] ?? '' ?>" required>
    <input type="number" name="price_range" placeholder="Price Range" value="<?= $edit_data['price_range'] ?? '' ?>" required>
    <input type="date" name="start_date" value="<?= $edit_data['start_date'] ?? '' ?>" required>
    <input type="text" name="place" placeholder="Place" value="<?= $edit_data['place'] ?? '' ?>" required>
    <input type="text" name="service" placeholder="Service" value="<?= $edit_data['service'] ?? '' ?>" required>
    <textarea name="message" placeholder="Message"><?= $edit_data['message'] ?? '' ?></textarea>
    <select name="status_c" required>
        <option value="not selected" <?= ($edit_data['status_c'] ?? '')=='not selected'?'selected':'' ?>>Not Selected</option>
        <option value="selected" <?= ($edit_data['status_c'] ?? '')=='selected'?'selected':'' ?>>Selected</option>
    </select>
    <button type="submit"><?= $edit_data ? "Update Customer" : "Add Customer" ?></button>
</form>

<h2 style="margin:20px">Customer List</h2>

<form class="filter-form" method="get">
    <input type="text" name="name" placeholder="Name" value="<?= $_GET['name'] ?? '' ?>">
    <input type="text" name="email" placeholder="Email" value="<?= $_GET['email'] ?? '' ?>">
    <input type="text" name="service" placeholder="Service" value="<?= $_GET['service'] ?? '' ?>">
    <input type="text" name="place" placeholder="Place" value="<?= $_GET['place'] ?? '' ?>">
    <select name="status_c">
        <option value="">All Status</option>
        <option value="not selected" <?= ($_GET['status_c'] ?? '')=='not selected'?'selected':'' ?>>Not Selected</option>
        <option value="selected" <?= ($_GET['status_c'] ?? '')=='selected'?'selected':'' ?>>Selected</option>
    </select>
    <select name="price_range">
        <option value="">All Price</option>
        <option value="100000" <?= ($_GET['price_range'] ?? '')=='100000'?'selected':'' ?>>Under 100000</option>
        <option value="500000" <?= ($_GET['price_range'] ?? '')=='500000'?'selected':'' ?>>Under 500000</option>
        <option value="500001" <?= ($_GET['price_range'] ?? '')=='500001'?'selected':'' ?>>More than 500000</option>
    </select>
    <button type="submit">Filter</button>
</form>

<div class="table-container">
<table>
<thead>
<tr>
<th>Name</th><th>Email</th><th>Phone</th><th>Phone 2</th><th>Address</th><th>Price</th><th>Service</th><th>Start Date</th><th>Place</th><th>Message</th><th>Status</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td data-label="Name"><?= $row['name'] ?></td>
<td data-label="Email"><?= $row['email'] ?></td>
<td data-label="Phone"><?= $row['phone_num'] ?></td>
<td data-label="Phone2"><?= $row['phone_num2'] ?></td>
<td data-label="Address"><?= $row['address'] ?></td>
<td data-label="Price"><?= $row['price_range'] ?></td>
<td data-label="Service"><?= $row['service'] ?></td>
<td data-label="Start Date"><?= $row['start_date'] ?></td>
<td data-label="Place"><?= $row['place'] ?></td>
<td data-label="Message"><?= $row['message'] ?></td>
<td data-label="Status"><?= $row['status_c'] ?></td>
<td data-label="Actions">
    <a href="?edit=1&email=<?= $row['email'] ?>"><button class="approve">Edit</button></a>
    <a href="?completed=1&email=<?= $row['email'] ?>"><button class="completed">Completed</button></a>
    <a href="?delete=1&email=<?= $row['email'] ?>" onclick="return confirm('Delete?')"><button class="reject">Delete</button></a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<script>
    function toggleMenu(){
        document.querySelector('.menus').classList.toggle('active');
    }
</script>

</body>
</html>