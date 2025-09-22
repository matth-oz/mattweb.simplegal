<?php
use \Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}

$arComponentDescription = [
    'NAME' => Loc::GetMessage('MW_SGS_NAME'),
    'DESCRIPTION' => Loc::GetMessage('MW_SGS_DESCRIPTION'),
    "ICON" => "/images/icon.gif",
	"SORT" => 10,
    'PATH' => [
        'ID' => 'mattweb',
        'NAME' => Loc::GetMessage('MW_NAME'),
    ],
];
?>