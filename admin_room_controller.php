<?php
session_start();

require_once "config.php";
require_once "models.php";

$connect = sqlConnect();

if (@trim($_POST['login']) && @trim($_POST['password'])) {
    if ($user = checkUser($_POST['login'], $_POST['password'])) {
        $_SESSION['authorization'] = $user;
    }
} elseif (@$_POST['reset']) {
    unset($_SESSION['authorization']);
    session_destroy();
} elseif (isset($_SESSION['authorization'])) {
    unset($_POST['form_name']);
    $courses = getCourses();
    $certificates = getCertificates();
}

if (!isset($_SESSION['authorization']) && isset($_POST['login'])) {
    header("HTTP/1.0 404 Not Found");
    exit("404 Not Found");
}