<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';

function getTask(){
    global $conn;
    $task_id = $_GET['task_id'];
    $sql = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    $row = mysqli_fetch_assoc($result);
        $tasks = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'status' => $row['status'],
            'due_date' => $row['due_date']
        ];
    
    return $tasks;
}
function getComments(){
    global $conn;
    $id= $_GET['task_id'];
    $sql = "SELECT c.id, c.comment, c.created_at, u.username 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.task_id = ?
    ORDER BY c.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)) {
        $notes[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at']
        ];
    }
    return $notes;
    }
}
if(isset($_POST['add_note_btn'])){
    global $conn;
    $task_id = $_GET['task_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    if(!empty($comment)){
        $sql = "INSERT INTO comments (task_id, comment, user_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $task_id, $comment, $user_id);
        $stmt->execute();



        
        $sql_task = "SELECT assigned_to FROM tasks WHERE id = ?";
        $stmt_task = $conn->prepare($sql_task);
        $stmt_task->bind_param('i', $task_id);
        $stmt_task->execute();
        $result_task = $conn ->get_result();
        $task_data = mysqli_fetch_assoc($result_task);
        $assigned_user_id = $task_data['assigned_to'];



        $sql_user = "SELECT email FROM users WHERE id= ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param('i', $assigned_user_id);
        $stmt_user->execute();
        $result_user = $conn ->get_result();
        $user_data = mysqli_fetch_assoc($result_user);
        $user_email = $user_data['email'];

        $subject = "Nowa notatka do zadania";
        $message = "Cześć, \n\nZostała dodana nowa notatka do zadania, które masz przypisane.\n\n";
        $message .= " Treść notatki: ". $comment."\n\n";
        $message .= "Aby zobaczyć zadanie, kliknij w poniższy link: \n";
        $message.= "http://localhost/ProjectManagement/index.php?page=task&task_id=".$task_id."\n\n";

        $headers = "From: Project Management <mp1104202@gmail.com>\r\n";


        mail($user_email, $subject, $message, $headers);
        if (!mail($user_email, $subject, $message, $headers)) {
            echo "Wiadomość e-mail nie została wysłana.";
        }

        header('Location: index.php?page=task&task_id='.$task_id);
        exit;
    }
}
if(isset($_GET['finish']) && $_GET['finish'] == 'true'){
    
    global $conn;
    $task_id = $_GET['task_id'];
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE tasks SET status = 'Completed' WHERE id = ? AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $task_id, $user_id);
    if($stmt->execute()){
        $statusTask = 'Completed';
    header('Location: index.php?page=projectList');
    exit();
    }else{
        echo "Error updating record: ". mysqli_error($conn);
    }
}
function finishedView() {
    if (isset($_GET['finished']) && $_GET['finished'] == 'true') {
        return 'Completed';
    }
    return 'Pending'; 
}

$finishedView = finishedView();
$commants = getComments();
$task = getTask();
echo $twig->render('task.twig', [
    'task' => $task,
    'comments' => $commants,
    'finished' => $finishedView
]);
?>