<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}

/* ---------- DB CONNECTION ---------- */
$conn = new mysqli("localhost", "root", "", "Design_inside");
if ($conn->connect_error) {
    die("Database connection failed. Start MySQL in XAMPP.");
}

/* ---------- APPROVE / REJECT ---------- */
if (isset($_GET['action'], $_GET['email'])) {
    $email = $_GET['email'];

    if ($_GET['action'] === 'approve') {
        $stmt = $conn->prepare("UPDATE Customer SET status_c='selected' WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
    }

    if ($_GET['action'] === 'reject') {
        $stmt = $conn->prepare("DELETE FROM Customer WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
    }

    header("Location: dashboard.php");
    exit;
}

/* ---------- DASHBOARD COUNTS ---------- */
$total_customer = $conn->query("SELECT COUNT(*) c FROM Customer")->fetch_assoc()['c'];
$selected_customer = $conn->query("SELECT COUNT(*) c FROM Customer WHERE status_c='selected'")->fetch_assoc()['c'];
$not_selected_customer = $conn->query("SELECT COUNT(*) c FROM Customer WHERE status_c='not selected'")->fetch_assoc()['c'];
$completed_project = $conn->query("SELECT COUNT(*) c FROM Compro")->fetch_assoc()['c'];

/* ---------- FILTER VALUES ---------- */
$status  = $_GET['status']  ?? '';
$name    = $_GET['name']    ?? '';
$email   = $_GET['email']   ?? '';
$phone   = $_GET['phone']   ?? '';
$service = $_GET['service'] ?? '';
$price   = $_GET['price']   ?? '';

/* ---------- FILTER QUERY ---------- */
$sql = "SELECT * FROM Customer WHERE 1";
$params = [];
$types = "";

if ($status !== '') {
    $sql .= " AND status_c=?";
    $params[] = $status;
    $types .= "s";
}
if ($name !== '') {
    $sql .= " AND name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}
if ($email !== '') {
    $sql .= " AND email LIKE ?";
    $params[] = "%$email%";
    $types .= "s";
}
if ($phone !== '') {
    $sql .= " AND phone_num LIKE ?";
    $params[] = "%$phone%";
    $types .= "s";
}
if ($service !== '') {
    $sql .= " AND service LIKE ?";
    $params[] = "%$service%";
    $types .= "s";
}
if ($price !== '') {
    if ($price === '100000') $sql .= " AND price_range <= 100000";
    if ($price === '500000') $sql .= " AND price_range <= 500000";
    if ($price === '500001') $sql .= " AND price_range > 500000";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI,sans-serif;
}
body{
    background:#f4f6f8;
}

/* NAV */
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

/* CARDS */
.dashboard{
    width:95%;
    margin:20px auto;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px
}
.card{
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,.1)
}
.card h3{
    color:#666;
    margin-bottom:10px
}
.card .count{
    font-size:32px;
    font-weight:800;
    color:#004d4d
}

/* FILTER */
.filter-form{
    width:95%;
    margin:20px auto;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.filter-form input,.filter-form select,.filter-form button{
    padding:8px 12px;
    border-radius:5px;
    border:1px solid #ccc;
}
.filter-form button{
    background:#004d4d;
    color:#fff;
    border:none;
    cursor:pointer;
}

/* TABLE */
.table-container{
    width:95%;
    margin:20px auto;
    background:#fff;
    padding:15px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:8px;
    border:1px solid #ddd;
    font-size:13px;
}
th{
    background:#004d4d;
    color:#fff
}
.approve{
    background:#28a745;
    color:#fff;
    border:none;
    padding:5px 8px;
    border-radius:5px;
}
.reject{
    background:#dc3545;
    color:#fff;
    border:none;
    padding:5px 8px;
    border-radius:5px;
}

</style>
</head>

<body>
    <!-- <nav>
        <div class="logo">Design_Inside</div>
        <div>
            <a href="dashboard.php">Dashboard</a>
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

    <div class="dashboard">
        <div class="card"><h3>Total Customers</h3><div class="count"><?= $total_customer ?></div></div>
        <div class="card"><h3>Selected</h3><div class="count"><?= $selected_customer ?></div></div>
        <div class="card"><h3>Not Selected</h3><div class="count"><?= $not_selected_customer ?></div></div>
        <div class="card"><h3>Completed</h3><div class="count"><?= $completed_project ?></div></div>
    </div>

    <form class="filter-form" method="get">
        <select name="status">
            <option value="">All Status</option>
            <option value="selected">Selected</option>
            <option value="not selected">Not Selected</option>
        </select>
        <input type="text" name="name" placeholder="Name">
        <input type="text" name="email" placeholder="Email">
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="service" placeholder="Service">
        <select name="price">
            <option value="">All Price</option>
            <option value="100000">Under 100000</option>
            <option value="500000">Under 500000</option>
            <option value="500001">Above 500000</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Phone</th><th>Address</th>
                    <th>Price</th><th>Service</th><th>Message</th>
                    <th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row=$result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone_num']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['price_range']) ?></td>
                    <td><?= htmlspecialchars($row['service']) ?></td>
                    <td><?= htmlspecialchars($row['message']) ?></td>
                    <td><?= htmlspecialchars($row['status_c']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <?php if($row['status_c']=='not selected'): ?>
                        <a href="?action=approve&email=<?= $row['email'] ?>"><button class="approve">Approve</button></a>
                        <a href="?action=reject&email=<?= $row['email'] ?>" onclick="return confirm('Delete this record?')">
                        <button class="reject">Reject</button></a>
                        <?php else: ?>
                        Approved
                        <?php endif; ?>
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