<!-- src/html/index.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>20:20 FC - FINEDICA</h1>
                <p>Expert Financial Coaching</p>
            </div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="questionnaire.php">Questionnaire</a></li>
                <li><a href="ethics_policy.php">AI Ethics Policy</a></li>
                <li><a href="../generate_avatar/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="logout.php" style="font-size: 18px; color: Yellow">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Welcome to FINEDICA</h2>
                <p>Expert financial coaching for a clearer tomorrow</p>
            </div>
            
            <div class="auth-container">
                <div class="tab-container">
                    <button class="btn-primary" onclick="showLogin()">Already a Member?<br><br> Please Login Here</button>
                    <button class="btn-secondary" onclick="showSignup()">New User?<br><br> Let's Create an Account</button>
                </div>

                <!-- Login Form -->
                <div id="login-form" class="form-container active">
                    <h2>Login</h2>
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="login-email" required>

                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="login-password" required>
                        </div>
                        <button type="submit">Login</button>
                    </form>
                </div>

                <!-- Signup Form -->
                <div id="signup-form" class="form-container">
                    <h2>Sign Up</h2>
                    <form id="signupForm" action="signup.php" method="POST">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" required>

                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required>

                            <label for="dateOfBirth">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" required>

                            <label for="employment">Present / Expected Employment</label>
                            <input type="text" id="employment" name="employment" required>

                            <label for="gender">Gender</label>
                            <input type="radio" id="gender-male" name="gender" value="Male" required> <label for="gender-male" style="display:inline;">Male</label>
                            <input type="radio" id="gender-female" name="gender" value="Female" required> <label for="gender-female" style="display:inline;">Female</label>
                            <input type="radio" id="gender-other" name="gender" value="Other" required> <label for="gender-other" style="display:inline;">Other / Prefer not to say</label>

                            <label for="signup-email">Email</label>
                            <input type="email" id="signup-email" name="signup-email" required>

                            <label for="signup-password">Password</label>
                            <input type="password" id="signup-password" name="signup-password" required>

                            <label for="signup-confirm-password">Confirm Password</label>
                            <input type="password" id="signup-confirm-password" name="signup-confirm-password" required>
                        </div>
                        <button type="submit">Create Account</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script src="../js/main.js"></script>
    <script src="../js/auth.js"></script>
</body>
</html>