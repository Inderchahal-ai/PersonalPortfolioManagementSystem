 
<?php

require_once "config/db.php";

// Allow only form submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html#contact");
    exit;
}

// Get form data
$name = trim($_POST["sender_name"] ?? "");
$email = trim($_POST["sender_email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

// Validate input
if (
    $name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $message === ""
) {
    die("Invalid details. Please go back and fill the form correctly.");
}

// Insert message into database
$stmt = $conn->prepare(
    "INSERT INTO contact_messages
    (sender_name, sender_email, subject, message)
    VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    die("Unable to process your message. Please try again.");
}

$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Message Sent</title>
    </head>
    <body>
        <h2>Thank You!</h2>
        <p>Your message has been sent successfully.</p>
        <a href='index.html'>Return to Portfolio</a>
    </body>
    </html>
    ";
} else {
    echo "Sorry, your message could not be sent. Please try again.";
}

$stmt->close();
$conn->close();

?>
