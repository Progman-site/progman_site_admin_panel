<?php
session_start();

require_once "config.php";
require_once "models.php";
require_once 'ImageSigner/image_signer.inc.php';


if (!isset($_SESSION['authorization'])) {
    header("HTTP/1.0 404 Not Found");
    exit("404 Not Found");
}
$connect = sqlConnect();

try {
    if (@$_POST['form_name'] == 'updateCertificates') {
        unset($_POST['form_name']);
        updateCertificate();
        exit();
    }
    elseif (@$_POST['form_name'] == 'userSearch') {
        unset($_POST['form_name']);
        printResult(getUserByFieldName($_POST['field'], $_POST['value']));
    }
    elseif (@$_POST['form_name'] == 'delCertificate') {
        unset($_POST['form_name']);
        delCertificate((int) $_POST['id']);
    }
    elseif (@$_POST['form_name'] == 'downloadCertificate') {
        unset($_POST['form_name']);
        printResult(downloadCertificate($_POST['id']));
    }
//    ?form_name=getCertificateImg&id=1&language=en
} catch (Throwable $e) {
    printError([
        'message' => $e->getMessage(),
        'string' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTrace(),
        ]);
}