<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';

    function getProjectDescription(){
        global $conn;
        $project_id = $_GET['project_id'];
        $sql = "SELECT *FROM projects WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $table = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'user_id' => $row['user_id'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
        return $table;
    }



    //przycisk zakończenia projektu
    if(isset($_POST['end_project'])){
        $project_id = isset($_GET['project_id']) ? (int)$_POST['project_id']:0;
        $sql = "UPDATE projects SET status = 'Finished' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $project_id);
        mysqli_stmt_execute($stmt);
        header('Location: index.php?page=projectList');
    }
    
    function TaskInProgress(){
        global $conn;
        $project_id = $_GET['project_id'];
        $sql = "SELECT tasks.*, users.username
        FROM tasks
        JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.project_id = ? AND tasks.status = 'In Progress'
        ";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tasks = [];
        while($row = mysqli_fetch_assoc($result)){
            $tasks[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'assigned_to' => $row['assigned_to'],
                'username' => $row['username'],
            ];
        }
        return $tasks;
    }
    function TaskCompleted(){
        global $conn;
        $project_id = $_GET['project_id'];
        $sql = "SELECT tasks.*, users.username
        FROM tasks
        JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.project_id = ? AND tasks.status = 'Completed'
        ";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tasks = [];
        while($row = mysqli_fetch_assoc($result)){
            $tasks[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'assigned_to' => $row['assigned_to'],
                'username' => $row['username'],
            ];
        }
        return $tasks;
    }
        function TaskStarted(){
        global $conn;
        $project_id = $_GET['project_id'];
        $sql = "SELECT tasks.*, users.username
        FROM tasks
        JOIN users ON tasks.assigned_to = users.id
        WHERE tasks.project_id = ? AND tasks.status = 'Not Started'
        ";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $project_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tasks = [];
        while($row = mysqli_fetch_assoc($result)){
            $tasks[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'assigned_to' => $row['assigned_to'],
                'username' => $row['username'],
            ];
        }
        return $tasks;
    }
    if(isset($_POST['reserved_btn'])){
        $id = $_POST['task_id'];
        $status = "In Progress";
        $user_id = $_SESSION['user_id'];
        $sql = "UPDATE tasks SET status = ?, assigned_to = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $status, $user_id, $id);
        mysqli_stmt_execute($stmt);
        header('Location: index.php?page=project&project_id='.$_GET['project_id']);
        exit();
    }
    if (isset($_POST['add_task_btn'])) {
        
        $title = $_POST['title'];
        $description = $_POST['description'];
        $project_id = $_GET['project_id'];
        $user_id = $_SESSION['user_id'];
        $status = "Not Started";
        $date = $_POST['due_date'];
    
        
        if (empty($title) || empty($description) || empty($date)) {
            echo "Proszę wypełnić wszystkie pola!";
            exit();
        }
    
        $sql = "INSERT INTO tasks (title, description, project_id, assigned_to, status, due_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
    
        if ($stmt === false) {
            die('Błąd przygotowania zapytania SQL: ' . mysqli_error($conn));
        }
    
        mysqli_stmt_bind_param($stmt, "sssiss", $title, $description, $project_id, $user_id, $status, $date);

        if (mysqli_stmt_execute($stmt)) {

            header('Location: index.php?page=project&project_id=' . $_GET['project_id']);
            exit();
        } else {
            echo "Wystąpił błąd przy dodawaniu zadania!";
        }
    }
    

$taskInProgress = TaskInProgress();
$projectDescription = getProjectDescription();
$taskCompleted = TaskCompleted();
$taskStarted = TaskStarted();
echo $twig->render('project.twig', [
        'project' => $projectDescription,
        'tasksInProgress' => $taskInProgress,
        'tasksCompleted' => $taskCompleted,
        'tasksStarted' => $taskStarted,
        

]);
?>