<!-- Added for pull request testing -->

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'functions.php';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        addTask($_POST['task-name']);
    }

    if (isset($_POST['email'])) {
        saveEmail($_POST['email']); // optional: implement in functions.php
    }
}

$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            margin: 20px auto;
            width: 300px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        input[type="text"],
        input[type="email"] {
            padding: 8px;
            width: 180px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .tasks-list {
            list-style: none;
            padding: 0;
            width: 400px;
            margin: 30px auto;
        }

        .task-item {
            background-color: white;
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 5px;
        }

        .task-item.completed {
            text-decoration: line-through;
            color: gray;
        }

        .task-status {
            margin-right: 10px;
        }

        .delete-task {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 8px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <p style="color:green;">✨ Task Scheduler Final PR Submission</p>
    <h1>Task Planner</h1>

    <!-- Task Input -->
    <form method="POST" action="">
        <input type="text" name="task-name" placeholder="Enter new task" required>
        <button type="submit">Add</button>
    </form>

    <!-- Email Input -->
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your email" required />
        <button type="submit">Save Email</button>
    </form>

    <!-- Tasks List -->
    <ul class="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                <span>
                    <input type="checkbox" class="task-status" <?= $task['completed'] ? 'checked' : '' ?>>
                    <?= htmlspecialchars($task['name']) ?>
                </span>
                <form method="POST" action="delete.php" style="display:inline;">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <button type="submit" class="delete-task">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        // JS to handle checkbox toggle UI (no backend toggle)
        document.querySelectorAll('.task-status').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const li = e.target.closest('.task-item');
                if (e.target.checked) {
                    li.classList.add('completed');
                } else {
                    li.classList.remove('completed');
                }
            });
        });
    </script>
</body>
</html>
