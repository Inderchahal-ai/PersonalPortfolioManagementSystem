
<?php
session_start();

// 1. Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$admin_username = $_SESSION["admin_username"] ?? "Admin";

// 2. Create CSRF protection token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// 3. Delete a message when the admin submits the delete form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_message"])) {

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        die("Invalid request. Please refresh the page and try again.");
    }

    $message_id = filter_input(INPUT_POST, "message_id", FILTER_VALIDATE_INT);

    if ($message_id) {
        $delete_stmt = $conn->prepare(
            "DELETE FROM contact_messages WHERE id = ?"
        );

        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $message_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    // Prevent the same delete request from being submitted again
    header("Location: dashboard.php");
    exit;
}

// 4. Fetch all visitor messages
$result = $conn->query(
    "SELECT id, sender_name, sender_email, subject, message, submitted_at
     FROM contact_messages
     ORDER BY submitted_at DESC"
);

if (!$result) {
    die("Unable to load contact messages. Please check the database.");
}

$total_messages = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Personal Portfolio</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #080B12;
            color: #D1D5E0;
            min-height: 100vh;
            padding: 35px 20px;
        }

        .dashboard {
            max-width: 1100px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        h1 {
            color: #B8C4FF;
            font-size: 28px;
        }

        .welcome {
            color: #AAB0C5;
            margin-top: 8px;
        }

        .logout {
            display: inline-block;
            padding: 10px 20px;
            background: #9B7BFF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .logout:hover {
            background: #8060E8;
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(184, 196, 255, 0.2);
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 30px;
        }

        .summary-card h2 {
            color: #B8C4FF;
            font-size: 17px;
            font-weight: 500;
        }

        .total {
            color: #9B7BFF;
            font-size: 32px;
            font-weight: 700;
            margin-top: 8px;
        }

        .section-title {
            color: #B8C4FF;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .message-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(184, 196, 255, 0.18);
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 18px;
            overflow-wrap: anywhere;
        }

        .message-card h3 {
            color: #C5B5FF;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .message-info {
            color: #B8C4D8;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .message-content {
            color: #E0E3EF;
            line-height: 1.7;
            margin-top: 15px;
            white-space: pre-wrap;
        }

        .delete-form {
            margin-top: 20px;
        }

        .delete-btn {
            padding: 9px 17px;
            border: none;
            border-radius: 7px;
            background: #B83E58;
            color: white;
            font-family: inherit;
            cursor: pointer;
            transition: 0.3s;
        }

        .delete-btn:hover {
            background: #922D43;
        }

        .empty-message {
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(184, 196, 255, 0.18);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.04);
            color: #B8C4D8;
        }

        @media (max-width: 600px) {
            body {
                padding: 22px 14px;
            }

            h1 {
                font-size: 23px;
            }

            .message-card {
                padding: 17px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <div class="header">

        <div>
            <h1>Admin Dashboard</h1>

            <p class="welcome">
                Welcome, <?php echo htmlspecialchars($admin_username); ?>!
            </p>
        </div>

        <a href="logout.php" class="logout">Logout</a>

    </div>

    <div class="summary-card">

        <h2>Total Contact Messages</h2>

        <div class="total">
            <?php echo $total_messages; ?>
        </div>

    </div>

    <h2 class="section-title">Visitor Messages</h2>

    <?php if ($total_messages > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <div class="message-card">

                <h3>
                    <?php echo htmlspecialchars($row["sender_name"]); ?>
                </h3>

                <p class="message-info">
                    <strong>Email:</strong>
                    <?php echo htmlspecialchars($row["sender_email"]); ?>
                </p>

                <p class="message-info">
                    <strong>Subject:</strong>
                    <?php echo htmlspecialchars($row["subject"] ?? "No subject"); ?>
                </p>

                <p class="message-info">
                    <strong>Date:</strong>
                    <?php echo htmlspecialchars($row["submitted_at"]); ?>
                </p>

                <div class="message-content">
                    <?php echo htmlspecialchars($row["message"]); ?>
                </div>

                <form method="POST" action="dashboard.php" class="delete-form"
                      onsubmit="return confirm('Are you sure you want to delete this message?');">

                    <input type="hidden" name="message_id"
                           value="<?php echo (int)$row["id"]; ?>">

                    <input type="hidden" name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                    <button type="submit" name="delete_message" class="delete-btn">
                        Delete Message
                    </button>

                </form>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty-message">
            No contact messages have been received yet.
        </div>

    <?php endif; ?>

</div>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
