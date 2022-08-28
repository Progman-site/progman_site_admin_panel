<?php
require_once 'ImageSigner/image_signer.inc.php';

use ImageSigner\ImageBlank;
use ImageSigner\constants\Fonts;
use ImageSigner\constants\Colors;

$technologies = [
    'HTML 5',
    'CSS 3.0',
    'Java Script',
    'PHP 8.1',
    'My SQL',
    'Relational databases',
    'Hosting and domains',
    'GIT',
    'REST API',
    'JQuery',
    'PDO (php)',
    'Basic OOP (php)',
    'Agile (Scrum & Kanban)',
];

$blank = new ImageBlank(__DIR__ . '/images/pg_cert_blank_en.jpg');
$blank
    ->addString(
        "`WEB full stack programmer` of the JUNIOR level ",
        44,
        round($blank->getXSize() * 0.51),
        round($blank->getYSize() * 0.43),
        Fonts::ARIAL_BLACK
    )
    ->addString(
        "5314997901-333-48EN2022",
        40,
        round($blank->getXSize() * 0.5),
        round($blank->getYSize() * 0.5),
        Fonts::CALIBRI,
        0,
        Colors::BLACK
    )
    ->addString(
        "Andrey Ivanovich Tester",
        50,
        round($blank->getXSize() * 0.62),
        round($blank->getYSize() * 0.58),
        Fonts::ARIAL_REGULAR,
    )
    ->addString(
        "99",
        35,
        round($blank->getXSize() * 0.631),
        round($blank->getYSize() * 0.64),
        Fonts::ARIAL_BLACK,
    )
    ->addStringBlock(
        "- Отработан навык предварительного проэктирования приложения по ТЗ
- Создани и внедрен проект с фронт, бек частью и чат ботом
- Проведено ознакомление с Agile технологиями управления iT команд",
        27,
        round($blank->getXSize() * 0.05),
        round($blank->getYSize() * 0.9),
        Fonts::CALIBRI,
        round($blank->getXSize() * 0.5),
        false,
        0,
        0,
        Colors::WHITE,
        1
    )->addColumnsStringBlock(
        $technologies,
        2,
        round($blank->getXSize() * 0.8),
        round($blank->getYSize() * 0.91),
        Fonts::ARIAL_REGULAR,
        round($blank->getXSize() * 0.16),
        24,
        false,
        Colors::WHITE,
        0,
        0.7,
        5
    )
;



echo "<img style='width: 70%; margin: 0 auto; display: block' src='{$blank->getBase64()}'></div>";
//$blank->getShow();