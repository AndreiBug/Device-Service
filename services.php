<?php
session_start();
if (!isset($_SESSION['id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Services</title>
</head>

<body>
    <div class="nav-spacer">
        <div class="nav-bar">
            <div class="nav-left">
                <button onclick="location.href='index.php'">Home</button>
                <button onclick="location.href='services.php'">Services</button>
                <button onclick="location.href='about.php'">About</button>
            </div>
            <h1 class="nav-title">TechFix</h1>
            <div class="nav-right">
                <?php if (isset($_SESSION['id'])): ?>
                    <button onclick="location.href='profile.php'">Profile</button>
                    <button onclick="location.href='logout.php'">Logout</button>
                <?php else: ?>
                    <button onclick="location.href='login.php'">Login</button>
                    <button onclick="location.href='register.php'">Register</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="web-container">
        <h2 class="services-title">Our Services</h2>
        <p class="services-intro">
            At TechFix, we provide a wide range of services to keep your devices running smoothly. Explore what we offer below!
        </p>

        <div class="services-list">
            <div class="service-item">
                <h3>Device Repairs</h3>
                <p>Quick fixes for cracked screens, faulty hardware, and more.</p>
            </div>
            <div class="service-item">
                <h3>Software Solutions</h3>
                <p>Eliminate viruses, recover lost data, and optimize performance.</p>
            </div>
            <div class="service-item">
                <h3>Maintenance Plans</h3>
                <p>Keep your devices in top shape with regular check-ups and upgrades.</p>
            </div>
            <div class="service-item">
                <h3>Corporate Services</h3>
                <p>Tailored solutions for managing fleets of devices in businesses.</p>
            </div>
        </div>

        <div class="cta-section">
            <p>Book an appointment to get your problem resolved efficiently.</p><br>
            <button onclick="location.href='reservation.html'">Book an appointment</button>
        </div>
    </div>

    <footer>
        123 Iuliu Maniu Avenue &nbsp•&nbsp service.dispozitive@gmail.com &nbsp•&nbsp 071-234-567
    </footer>
</body>

</html>
