<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';

function getMyProjects() {
    global $conn;
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] :0;
    
    $query = "SELECT p.id AS project_id, p.name AS project_title, 
       t.id AS task_id, t.title AS task_title
    FROM projects p
    LEFT JOIN tasks t ON p.id = t.project_id AND t.assigned_to = ? AND t.status = 'In Progress'
    WHERE p.status = 'active'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $projects = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        if (!isset($projects[$row['project_id']])) {
            $projects[$row['project_id']] = [
                'project_title' => $row['project_title'],
                'tasks' => [],
                'tasks_exist' => false, 
            ];
        }

        
        if ($row['task_id'] !== null) {
            $projects[$row['project_id']]['tasks'][] = [
                'task_title' => $row['task_title'],
                'task_id' => $row['task_id']
            ];
            
            $projects[$row['project_id']]['tasks_exist'] = true;
        }
    }
    
    return array_values($projects); 
}



$getProject = getMyProjects();
echo $twig->render('dashboard.twig', [
    "projects" => $getProject,

]);
?>