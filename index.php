<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/public/db_connection.php';
require_once __DIR__ . '/public/session.php';

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] != true) {
    if ($_GET['page'] != 'login') {  
        header('Location: index.php?page=login');
        exit();
    }
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);
$page = $_GET['page'] ?? 'dashboard';

//Obsługa dynamicznych ścieżek
if (preg_match('/^client\/(\d+)$/', $page, $matches)) {
    $_GET['client_id'] = $matches[1]; // Pobieramy ID klienta
    $page = 'client'; // Przekierowujemy na klienta
}

$allowed_pages = ['dashboard','projectList','project','task','login','addProject'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

$file = __DIR__ . "/pages/{$page}.php";

if (file_exists($file)) {
    require $file;
} else {
    echo $twig->render('layout.twig', [
        'content' => '<h2>Strona nie istnieje</h2>',
    ]);
}
