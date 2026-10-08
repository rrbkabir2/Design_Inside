<?php
session_start();

$conn = new mysqli("localhost", "root", "", "design_inside");
if ($conn->connect_error) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $name       = $first_name . " " . $last_name;
    $email   = trim($_POST['email']);
    $phone1  = trim($_POST['phone_num']);
    $phone2  = trim($_POST['phone_num2']);
    $address = trim($_POST['address']);
    $place   = trim($_POST['place']);
    $service = trim($_POST['service']);
    $price   = trim($_POST['price_range']);
    $date    = trim($_POST['start_date']);
    $msg     = trim($_POST['message']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert'] = "Invalid email address!";
    }
    elseif (!preg_match("/^[0-9]{10}$/", $phone1)) {
    $_SESSION['alert'] = "Phone number must contain only digits and be exactly 10 digits long!";
    }
elseif (!empty($phone2) && !preg_match("/^[0-9]{10}$/", $phone2)) {
    $_SESSION['alert'] = "Alternate phone number must contain only digits and be exactly 10 digits long!";
    }
    else {

        $stmt = $conn->prepare(
    "INSERT INTO Customer
    (name, email, phone_num, phone_num2, address, price_range, start_date, place, service, message)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);


$stmt->bind_param(
    "sssssdssss",
    $name,
    $email,
    $phone1,
    $phone2,
    $address,
    $price,
    $date,  
    $place,
    $service,
    $msg
);


        if ($stmt->execute()) {
            $_SESSION['alert'] = "Quotation submitted successfully!";
        } else {
            $_SESSION['alert'] = "Something went wrong. Please try again!";
        }

        $stmt->close();
    }

    header("Location: quotation_form.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Form</title>
    <link rel="stylesheet" href="quotation_form.css">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
</head>

<body>

    <nav>
        <a href="index.html"><div class="logo"></div></a>

        <!-- Hamburger -->
        <div class="hamburger" onclick="toggleMenu()">☰</div>

        <div class="menus">
            <a class="submenus underline" href="index.html">HOME</a>
            <a class="submenus underline" href="service.html">SERVICES</a>
            <a class="submenus underline" href="project.html">PROJECTS</a>
            <a class="submenus underline" href="about_us.html">ABOUT US</a>
        </div>
    </nav>

    <div class="quote-wrapper">
        <div class="quote-card">
            <h1>Get a Quote</h1>
            <p>Tell us about your project and we’ll get back to you</p>

            <form action="quotation_form.php" method="POST">

                <div class="grid-2">
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>

                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>

                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_num" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                    </div>

                    <div class="field">
                        <label>Alternate Phone</label>
                        <input type="tel" name="phone_num2" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>
                </div>

                <div class="field">
                    <label>Address</label>
                    <input type="text" name="address">
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Place</label>
                        <input type="text" name="place">
                    </div>

                    <div class="field">
                        <label>Service</label>
                        <select name="service" required>
                            <option value="">Select service</option>
                            <option>Interior Design</option>
                            <option>Renovation</option>
                            <option>Modular Kitchen</option>
                            <option>Full Home Design</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label>Price Range</label>
                        <select name="price_range">
                            <option value="">Select budget</option>
                            <option value="500000">Below ₹5 Lakhs</option>
                            <option value="1000000">₹5–10 Lakhs</option>
                            <option value="2000000">₹10–20 Lakhs</option>
                            <option value="2500000">Above ₹20 Lakhs</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Start Date</label>
                        <input type="date" name="start_date" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="field">
                    <label>Project Details</label>
                    <textarea rows="4" name="message"></textarea>
                </div>

                <button class="quote-btn">Get a Quote</button>
            </form>
        </div>
    </div>

    <?php
if (isset($_SESSION['alert'])) {
    echo "<script>alert('{$_SESSION['alert']}');</script>";
    unset($_SESSION['alert']);
}
?>

    <footer>
        <div class="foot">
            <div class="foot1">
                <div class="footLogo">
                    <a href="full_admin/index.php"><div class="logo"></div></a>
                </div>
            </div>
            <div class="foot2">
                <div class="footMenus">
                    <span><a href="service.html">SERVICES</a></span>
                    <span><a href="project.html">PROJECTS</a></span>
                    <span><a href="about_us.html">ABOUT US</a></span>
                </div>
                <div class="address">
                    <div class="addTitle">HEAD OFFICE</div>
                    <div class="addressInfo">203- Roshan towers, gulam Ali nagar, handewadi road, Pune-28</div>
                    <div class="mobGmail">123-456-7890 info@mysite.com</div>
                </div>
                <div class="socials">
                    <span class="socialHead">SOCIALS</span>
                    <span><a href="https://www.facebook.com/profile.php?id=61574788605174" target="_blank">Facebook</a></span>
                    <span><a href="https://www.instagram.com/design_insidee?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank">Instagram</a></span>
                    <span><a href="https://www.linkedin.com/company/designinsideee/" target="_blank">LinkedIn</a></span>
                </div>
                <div class="inquire">
                    <span>INQUIRIES</span>
                    <a class="submenus fifthButt" href="quotation_form.php">
                        <button class="butt">
                            GET A QUOTE
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="credit">©2025 by Design Inside. Developed by Tanmay Bhogekar & Kabir Bundele</div>
    </footer>

    <script src="script.js"></script>
</body>
</html>