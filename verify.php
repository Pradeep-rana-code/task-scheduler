<?php
require_once 'functions.php';

$email = isset($_GET['email']) ? $_GET['email'] : null;
$code = isset($_GET['code']) ? $_GET['code'] : null;

$verified = ($email && $code) ? verifySubscription($email, $code) : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            padding: 40px;
            text-align: center;
        }
        h2 {
            color: <?= $verified ? 'green' : 'red' ?>;
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
        <?php if ($verified): ?>
            <h2>✅ Subscription Verified!</h2>
            <p>Your email <strong><?= htmlspecialchars($email) ?></strong> has been successfully verified.</p>
        <?php else: ?>
            <h2>❌ Verification Failed</h2>
            <p>Invalid or expired verification link.</p>
        <?php endif; ?>
        <p><a href="functions.php">Go back to Task Planner</a></p>
    </div>
</body>
</html>
