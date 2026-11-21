
<?php
session_start();
require_once "config/db.php";

$error = "";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password_hash FROM admin WHERE username = ?"
    );

    if ($stmt) {

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin["password_hash"])) {

                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Incorrect username or password.";
            }

        } else {
            $error = "Incorrect username or password.";
        }

        $stmt->close();

    } else {
        $error = "Unable to process login. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Personal Portfolio</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;

            background:
                radial-gradient(circle at top left, rgba(112, 76, 255, 0.25), transparent 40%),
                radial-gradient(circle at bottom right, rgba(76, 110, 255, 0.18), transparent 40%),
                #080B12;

            color: #D1D5E0;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 40px 35px;

            background: rgba(255, 255, 255, 0.055);
            border: 1px solid rgba(184, 196, 255, 0.2);
            border-radius: 20px;

            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);

            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        .login-container h1 {
            text-align: center;
            color: #B8C4FF;
            font-size: 27px;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #AAB0C5;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #D1D5E0;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 15px;

            background: #11152A;
            color: #FFFFFF;

            border: 1px solid rgba(184, 196, 255, 0.3);
            border-radius: 10px;

            font-family: inherit;
            font-size: 14px;
            outline: none;

            transition: 0.3s ease;
        }

        .form-group input:focus {
            border-color: #9B7BFF;
            box-shadow: 0 0 12px rgba(155, 123, 255, 0.2);
        }

        .form-group input::placeholder {
            color: #858BA3;
        }

        .login-btn {
            width: 100%;
            padding: 13px;

            border: none;
            border-radius: 10px;

            background: linear-gradient(135deg, #8060E8, #9B7BFF);
            color: #FFFFFF;

            font-family: inherit;
            font-size: 15px;
            font-weight: 600;

            cursor: pointer;
            transition: 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(155, 123, 255, 0.3);
        }

        .error {
            background: rgba(220, 53, 69, 0.12);
            border: 1px solid rgba(220, 53, 69, 0.35);
            color: #FF929D;

            padding: 11px;
            border-radius: 8px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;

            color: #B8C4FF;
            text-decoration: none;
            font-size: 13px;
        }

        .back-link:hover {
            color: #FFFFFF;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 23px;
            }

            .login-container h1 {
                font-size: 23px;
            }
        }

    </style>
</head>

<body>

    <div class="login-container">

        <h1>Admin Login</h1>

        <p class="subtitle">
            Personal Portfolio Management System
        </p>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">

                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    required
                    autocomplete="username"
                >

            </div>

            <div class="form-group">

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >

            </div>

            <button type="submit" class="login-btn">
                Login
            </button>

        </form>

        <a href="index.html" class="back-link">
            ← Back to Portfolio
        </a>

    </div>

</body>
</html>
