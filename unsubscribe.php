<?php
require_once 'functions.php';

$email = isset($_GET['email']) ? $_GET['email'] : null;
$success = $email ? unsubscribeEmail($email) : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            padding: 40px;
            text-align: center;
        }
        h2 {
            color: <?= $success ? 'green' : 'red' ?>;
        }
        a {
            text-decoration: none;
            color: #007BFF;
        }
        a:hover {
            text-decoration: underline;
        }
        .box {
            background: white;
            padding: 30px;
            max-width: 400px;
            margin: 60px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="box">
        <?php if ($success): ?>
            <h2>✅ Successfully Unsubscribed</h2>
            <p>You will no longer receive task reminders at <strong><?= htmlspecialchars($email) ?></strong>.</p>
        <?php else: ?>
            <h2>❌ Unsubscribe Failed</h2>
            <p>Invalid or already removed email address.</p>
        <?php endif; ?>
        <p><a href="functions.php">Return to Task Planner</a></p>
    </div>
</body>
</html>
