<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';
global $conn;
if(isset($_POST['add_project']) && isset($_POST['project_name']) && isset($_POST['project_description'])) {
    $project_name = htmlspecialchars(trim($_POST['project_name']));
    $project_description = htmlspecialchars(trim($_POST['project_description']));
    $user_id = (int)$_SESSION['user_id'];

    $sql = "INSERT INTO projects (name, description,user_id, created_at, updated_at,status)
    VALUES (?, ?,?,NOW(),NOW(), 'active')";
    $stmt = $conn ->prepare($sql);
    $stmt -> bind_param("ssi", $project_name, $project_description, $user_id);
    $stmt -> execute();
    header('Location: index.php?projectList');
}






echo $twig->render('addProject.twig', [

]);
?>