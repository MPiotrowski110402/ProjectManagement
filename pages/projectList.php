<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';

function projectlist() {
    global $conn;
    $query = "SELECT * FROM projects WHERE status= 'active'";
    $result = mysqli_query($conn, $query);
    $projects = [];
    while($row = mysqli_fetch_assoc($result)) {
        $projects[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'user_id' => $row['user_id'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    return $projects;
}
$projectList = projectlist();
echo $twig->render('projects.twig', [
        "projects" => $projectList,
]);
?>