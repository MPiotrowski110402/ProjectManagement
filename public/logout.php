<?php
    include 'session.php';

    if(isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true){
        $_SESSION['zalogowany'] = false;
        $_SESSION = [];
        session_destroy();
    }
    header('Location: ../index.php');
    exit();
?>


