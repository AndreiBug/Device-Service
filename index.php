<?php
session_start();
if (!isset($_SESSION['id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <title>Home</title>
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
        <p>Welcome to our device service website!</p>
    </div>

    <div class="web-container">
        <div class="card-align">
            <div class="card-wrapper">
                <div class="card-top">
                    <img class="image" src=photos/telefon.jpg>
                </div>
                <div class="card-bottom">
                    <span class="top-text">Phone Repairs</span>
                    <br><br>
                    <span class="bottom-text">For phone repairs click here</span>
                    <br><br>
                    <button class="button"><a href="services.php"> Click here </a></button>
                </div>
            </div>

            <div class="card-wrapper">
                <div class="card-top">
                    <img class="image" src=photos/pc.jpg>
                </div>
                <div class="card-bottom">
                    <span class="top-text">PC / Laptop Repairs</span>
                    <br><br>
                    <span class="bottom-text">For PC or laptop repairs click here</span>
                    <br><br>
                    <button class="button"> <a href="services.php"> Click here</a></button>
                </div>
            </div>

            <div class="card-wrapper">
                <div class="card-top">
                    <img class="image" src=photos/periferice.jpg>
                </div>
                <div class="card-bottom">
                    <span class="top-text">Peripheral Repairs</span>
                    <br><br>
                    <span class="bottom-text">For peripheral repairs click here</span>
                    <br><br>
                    <button class="button"><a href="services.php"> Click here </a></button>
                </div>
            </div>
        </div>
    </div>

    <header>
        <div class="web-container">
            <div class="review_box">
                <div class="web-container-left">
                    <h1>Read what customers say about us</h1>
                    <hr><br>
                    <p>
                        A company trusted by over 20 million people across the world.
                    </p>
                    <p>
                        We have helped companies increase their customer base and generate
                        multifold revenue with our service.
                    </p>
                    <p>
                        "Partnering with us has transformed the way businesses connect with their customers. Our
                        expertise has helped companies enhance user experiences, attract more customers, and achieve
                        exponential revenue growth. From startups to industry leaders, our clients rely on us to drive
                        results that matter."
                    </p>
                    <p>
                        Why Companies Choose Us:
                    <ul>
                        <li>
                            Proven Results: Clients have seen a significant boost in customer acquisition and loyalty;
                        </li>
                        <li>
                            Diverse Expertise: Serving industries from tech and retail to healthcare and beyond;
                        </li>
                        <li>
                            Scalable Growth: Our strategies empower companies to achieve sustainable, multi-fold revenue
                            increases.
                        </li>
                    </ul>
                    </p>
                    <div class="centrare">
                        <p>Share your review:</p>
                        <div class="rating">
                            <span id="rating">0</span>/5
                        </div>
                        <div class="stars" id="stars">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                        <textarea id="review" placeholder="Write your review here">
        </textarea>
                        <button id="submit">Submit</button>
                        <div class="reviews" id="reviews">
                        </div>
                    </div>
                </div>
                <div class="web-container-right">
                    <div class="card">
                        <img src="photos/pic1.jpg" alt="user" />
                        <div class="card-content">
                            <span><i class="ri-double-quotes-l"></i></span>
                            <div class="card-details">
                                <p>
                                    We had a great time collaboraring with the TechFix team. They
                                    have my high recommendation!
                                </p>
                                <h4>- John Pork</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <img src="photos/pic2.jpg" alt="user" />
                        <div class="card-content">
                            <span><i class="ri-double-quotes-l"></i></span>
                            <div class="card-details">
                                <p>
                                    The team drastically improved our product's speed and capability. I recommend Ahmed
                                    Baljeet for repairing your devices.
                                </p>
                                <h4>- Flying Headman</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <img src="photos/pic3.jpg" alt="user" />
                        <div class="card-content">
                            <span><i class="ri-double-quotes-l"></i></span>
                            <div class="card-details">
                                <p>
                                    I absolutely loved how fast my problem was solved. Complete
                                    experts at what they do!
                                </p>
                                <h4>- Freak Bob</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <footer>
        123 Iuliu Maniu Avenue &nbsp•&nbsp service.dispozitive@gmail.com &nbsp•&nbsp 071-234-567
    </footer>
    <script src="script.js"></script>
</body>

</html>