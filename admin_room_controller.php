<?php
session_start();

require_once "init.php";
require_once "models.php";

$connect = sqlConnect();

if (@trim($_POST['login']) && @trim($_POST['password'])) {
    if ($user = checkUser($_POST['login'], $_POST['password'])) {
        $_SESSION['authorization'] = $user;
    }
} elseif (@$_POST['reset']) {
    unset($_SESSION['authorization']);
    session_destroy();
}

try {
    if (isset($_SESSION['authorization'])) {
        if (isset($_POST['form_name'])) {
            $formName = $_POST['form_name'];
            unset($_POST['form_name']);

            switch ($formName) {
                case 'edit':
                    updateSiteInfo();
                    break;
                case '_______':
                    printResult(getUserByFieldName($_POST['field'], $_POST['value']));
                    break;
            }
        }
        @$_GET['navigation'] = $_GET['navigation'] ?: "certificates";
        $courses = getCourses();
        $certificates = getCertificates();
        $siteInfo = getSiteInfo();
//        echo '<pre>';
//        print_r($siteInfo);
//        die();
    }
} catch (Throwable $e) {
    printError([
        'message' => $e->getMessage(),
        'string' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTrace(),
    ]);
}

if (!isset($_SESSION['authorization']) && isset($_POST['login'])) {
    header("HTTP/1.0 404 Not Found");
    exit("404 Not Found");
}