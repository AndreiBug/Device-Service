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
    <title>About Us</title>
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

    <div class="about">
        <div class="web-container">
            <h2>About TechFix</h2>
            <p>
                Welcome to TechFix, your trusted partner for all device repair needs. Founded in 2010, we have been
                dedicated to providing top-notch repair services for smartphones, laptops, PCs, and peripherals.
                Our mission is to deliver reliable and affordable solutions while ensuring our customers receive
                exceptional support and care.
            </p>

            <br>
            <h3>Our Story</h3>
            <p>
                TechFix began as a small family-owned business with a passion for technology and a commitment to helping
                people keep their devices running smoothly. Over the years, we have grown into a trusted name
                in the device repair industry, servicing thousands of customers and building long-lasting relationships
                with our clients.
            </p>

            <br>
            <h3>Why Choose Us?</h3>
            <br>
            <ul>
                <li><strong>Expert Technicians:</strong> Our team of certified professionals has extensive experience in
                    diagnosing and fixing a wide range of device issues.</li>
                <li><strong>Fast Turnaround:</strong> We understand how important your devices are to you, so we work
                    diligently to provide quick and efficient service.</li>
                <li><strong>Affordable Pricing:</strong> We offer transparent pricing with no hidden fees, ensuring you
                    get great value for your money.</li>
                <li><strong>Customer-Centric Approach:</strong> Your satisfaction is our priority, and we go above and
                    beyond to make your experience hassle-free.</li>
            </ul>

            <br>
            <h3>Our Services</h3>
            <p>
                Whether you need a simple fix or a comprehensive solution, we've got you covered. At TechFix, we
                specialize in repairing a variety of devices, including but not limited to:
            <ul>
                <li>Smartphone screen replacements and battery issues</li>
                <li>Laptop hardware repairs and software troubleshooting</li>
                <li>PC component upgrades and diagnostics</li>
                <li>Peripheral repairs (keyboards, printers, etc.)</li>
            </ul>
            </p>

            <br>
            <h3>Our Vision</h3>
            <p>
                We envision a future where technology is seamless and stress-free. By providing exceptional repair
                services, we aim to empower individuals and businesses to stay connected, productive, and efficient.
            </p>

            <br>
            <h3>Get in Touch</h3>
            <p>
                Do you have a device in need of repair? Reach out to us today! We're here to help and ensure your
                technology works as it should.
            </p>
        </div>
    </div>
    <footer>
        123 Iuliu Maniu Avenue &nbsp•&nbsp service.dispozitive@gmail.com &nbsp•&nbsp 071-234-567
    </footer>
</body>

</html>