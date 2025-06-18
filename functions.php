<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// === Helper: JSON read/write ===
function readJsonFile($filename) {
    if (!file_exists($filename)) return [];
    $data = file_get_contents($filename);
    return json_decode($data, true) ?: [];
}

function writeJsonFile($filename, $data) {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
}

// === Task Functions ===
function addTask($task_name) {
    $tasks = readJsonFile('tasks.txt');
    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) return;
    }
    $tasks[] = ['id' => uniqid(), 'name' => $task_name, 'completed' => false];
    writeJsonFile('tasks.txt', $tasks);
}

function getAllTasks() {
    return readJsonFile('tasks.txt');
}

function markTaskAsCompleted($task_id, $is_completed) {
    $tasks = readJsonFile('tasks.txt');
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            break;
        }
    }
    writeJsonFile('tasks.txt', $tasks);
}

function deleteTask($task_id) {
    $tasks = readJsonFile('tasks.txt');
    $tasks = array_filter($tasks, fn($task) => $task['id'] !== $task_id);
    writeJsonFile('tasks.txt', array_values($tasks));
}

// === Email Subscription ===
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function subscribeEmail($email) {
    $pending = readJsonFile('pending_subscriptions.txt');
    $code = generateVerificationCode();
    $pending[$email] = ['code' => $code, 'timestamp' => time()];
    writeJsonFile('pending_subscriptions.txt', $pending);

    $link = "http://localhost/functions.php?action=verify&email=" . urlencode($email) . "&code=" . $code;

    $subject = "Verify subscription to Task Planner";
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html\r\n";
    $message = "<p>Click below to verify:</p><a href='$link'>Verify Subscription</a>";

    mail($email, $subject, $message, $headers);
}

function verifySubscription($email, $code) {
    $pending = readJsonFile('pending_subscriptions.txt');
    $subscribers = readJsonFile('subscribers.txt');

    if (isset($pending[$email]) && $pending[$email]['code'] === $code) {
        unset($pending[$email]);
        writeJsonFile('pending_subscriptions.txt', $pending);
        if (!in_array($email, $subscribers)) {
            $subscribers[] = $email;
            writeJsonFile('subscribers.txt', $subscribers);
        }
        return true;
    }
    return false;
}

function unsubscribeEmail($email) {
    $subscribers = readJsonFile('subscribers.txt');
    $updated = array_filter($subscribers, fn($e) => $e !== $email);
    if (count($updated) !== count($subscribers)) {
        writeJsonFile('subscribers.txt', array_values($updated));
        return true;
    }
    return false;
}

function sendTaskReminders() {
    $subscribers = readJsonFile('subscribers.txt');
    $tasks = readJsonFile('tasks.txt');
    $pending_tasks = array_filter($tasks, fn($task) => !$task['completed']);
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending_tasks);
    }
}

function sendTaskEmail($email, $pending_tasks) {
    $subject = "Task Planner - Pending Tasks Reminder";
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html\r\n";
    $taskList = "<ul>";
    foreach ($pending_tasks as $task) {
        $taskList .= "<li>" . htmlspecialchars($task['name']) . "</li>";
    }
    $taskList .= "</ul>";
    $unsubscribe_link = "http://localhost/functions.php?action=unsubscribe&email=" . urlencode($email);
    $message = "<h2>Pending Tasks</h2>$taskList<p><a href='$unsubscribe_link'>Unsubscribe</a></p>";
    mail($email, $subject, $message, $headers);
}

// === Routing for Form Submissions ===
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        addTask($_POST['task-name']);
        header('Location: functions.php'); exit;
    }
    if ($action === 'delete') {
        deleteTask($_POST['task_id']);
        header('Location: functions.php'); exit;
    }
    if ($action === 'toggle') {
        markTaskAsCompleted($_POST['task_id'], $_POST['completed'] === 'true');
        exit;
    }
    if ($action === 'subscribe') {
        subscribeEmail($_POST['email']);
        echo "Verification link sent to email."; exit;
    }
}

if ($action === 'verify') {
    $email = $_GET['email'] ?? '';
    $code = $_GET['code'] ?? '';
    echo verifySubscription($email, $code) ? "✅ Email verified." : "❌ Verification failed.";
    exit;
}

if ($action === 'unsubscribe') {
    $email = $_GET['email'] ?? '';
    echo unsubscribeEmail($email) ? "✅ Unsubscribed successfully." : "❌ Already unsubscribed or invalid.";
    exit;
}

// === HTML OUTPUT ===
$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Scheduler</title>
    <style>
        body { font-family: Arial; background: #f1f1f1; padding: 40px; }
        h1 { text-align: center; }
        form { margin: 20px auto; width: 320px; display: flex; gap: 10px; }
        input, button { padding: 8px; }
        ul { list-style: none; width: 400px; margin: auto; padding: 0; }
        .task-item { background: white; margin-bottom: 10px; padding: 10px; border-radius: 5px; display: flex; justify-content: space-between; }
        .completed { text-decoration: line-through; color: gray; }
        .delete-task { background: red; color: white; border: none; padding: 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Task Planner</h1>

    <form method="POST" action="functions.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="task-name" placeholder="New task" required>
        <button type="submit">Add</button>
    </form>

    <form method="POST" action="functions.php">
        <input type="hidden" name="action" value="subscribe">
        <input type="email" name="email" placeholder="Your Email" required>
        <button type="submit">Subscribe</button>
    </form>

    <ul>
        <?php foreach ($tasks as $task): ?>
        <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
            <span>
                <input type="checkbox" class="task-status" data-id="<?= $task['id'] ?>" <?= $task['completed'] ? 'checked' : '' ?>>
                <?= htmlspecialchars($task['name']) ?>
            </span>
            <form method="POST" action="functions.php" style="margin: 0;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                <button class="delete-task">Delete</button>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>

    <script>
        document.querySelectorAll('.task-status').forEach(cb => {
            cb.addEventListener('change', e => {
                const taskId = e.target.dataset.id;
                fetch('functions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'toggle',
                        task_id: taskId,
                        completed: e.target.checked
                    })
                }).then(() => {
                    e.target.closest('li').classList.toggle('completed', e.target.checked);
                });
            });
        });
    </script>
</body>
</html>
