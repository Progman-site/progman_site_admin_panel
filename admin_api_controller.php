<?php
session_start();

require_once "init.php";
require_once "functions.php";
require_once 'ImageSigner/image_signer.inc.php';
require_once 'libs/QRcode.php';


if (!isset($_SESSION['authorization'])) {
    header("HTTP/1.0 404 Not Found");
    exit("404 Not Found");
}
$connect = sqlConnect();

try {
    $formName = $_POST['form_name'];
    unset($_POST['form_name']);

    switch($formName) {
        case 'updateCertificates':
            unset($_POST['form_name']);
            printResult(updateCertificate($connect));
            break;
        case 'userSearch':
            printResult(getUserByFieldName($_POST['field'], $_POST['value']));
            break;
        case 'delCertificate':
            printResult(delCertificate($connect, (int)$_POST['id']));
            break;
        case 'downloadCertificate':
            printResult(downloadCertificate($_POST['id']));
            break;
        case 'updateSiteInfo':
            printResult(updateSiteInfo($connect));
            break;
        case 'adviserSearch':
            printResult(getAdviseList($_POST['table'], $_POST['field'], $_POST['value']));
            break;
        case 'getCourseTechnologies':
            printResult(getCourseTechnologies($_POST['course_id']));
            break;
        case 'updateCourse':
            printResult(updateCourse($connect, $_POST));
            break;
        case 'delCourse':
            printResult(delCourse($connect, $_POST['id']));
            break;
        case 'removeTechnology':
            printResult(removeTechnology($connect, (int) $_POST['id']));
            break;
        case 'updateCoupon':
            printResult(updateCoupon($connect, $_POST));
            break;
        case 'delCoupon':
            printResult(deleteCoupon($connect, (int) $_POST['id']));
            break;
        case 'checkCouponSerialNumber':
            printResult(isCouponSerialNumberExists($_POST['serial_number']));
            break;
        case 'downloadCoupon':
            printResult(downloadCoupon($_POST['id']));
            break;
        case 'getRequest':
            $requests = getRequestsByFieldName($_POST['field'], $_POST['value']);
            if (isset($requests['created_at'])) {
                $requests['created_at'] = date('m/d/Y H:i:s', strtotime($requests['created_at']));
            }
            $requests['total_price'] = countPriceByCoupon($requests['current_product_price'], $requests['coupon_value'], $requests['coupon_formula'], $requests['quantity']);
            printResult($requests);
            break;
        case 'updatePurchase':
            if (isset($_POST['id'])) {
                printResult(updatePurchase($connect, $_POST));
            } else {
                printResult(registerPurchase($connect, $_POST));
            }
            break;
        case 'delPurchase':
            printResult(delPurchase($connect, (int) $_POST['id']));
            break;
    }
} catch (Throwable $e) {
    printError([
        'message' => $e->getMessage(),
        'string' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTrace(),
    ]);
}
