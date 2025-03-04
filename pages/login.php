<?php
require_once 'vendor/autoload.php';
require_once 'public/session.php';
require_once 'public/db_connection.php';
global $conn;

if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] == true) {
    header('Location: index.php');
    exit();
}
$error_message = '';

if(isset($_POST['get_login'])){
    if(isset($_POST['login_email']) && $_POST['login_password']){
        $email = $_POST['login_email'];
        $password = $_POST['login_password'];


        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password_hash'])){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['zalogowany'] = true;
                header('Location: index.php');
                exit();
            }else{
                $error_message = "Błędne hasło!";
            }
        }else{
            $error_message = "Nie znaleziono użytkownika z takim email-em!";
        }
    }
}
if(isset($_POST['get_register'])){
    if (isset($_POST['register_name'], $_POST['register_email'], $_POST['register_password'], $_POST['register_confirm_password'])) {
        $name = $_POST['register_name'];
        $email = $_POST['register_email'];
        $password = $_POST['register_password'];
        $confirm_password = $_POST['register_confirm_password'];



        if($password !== $confirm_password){
            $error_message = "Hasła nie są takie same!";
        }else{
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0){
                $error_message = "Taki email jest już zajęty!";
            }else{
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?,?,?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);
                $stmt->execute();
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['zalogowany'] = true;
                header('Location: index.php');
                exit();
            }
        }
    }
}


echo $twig->render('login.twig', [
        'errors' => $error_message,
]);
?>