<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
/** @var array $arCurrentValues */

if (!Loader::includeModule('iblock'))
{
	return;
}

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arTypesEx = CIBlockParameters::GetIBlockTypes();

$arIBlocks = [];
$iblockFilter = [
	'ACTIVE' => 'Y',
];

if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}

$db_iblock = CIBlock::GetList(["SORT"=>"ASC"], $iblockFilter);
while($arRes = $db_iblock->Fetch())
{
	$arIBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];
}


$arSorts = [
	"ASC" => GetMessage("T_IBLOCK_DESC_ASC"),
	"DESC" => GetMessage("T_IBLOCK_DESC_DESC"),
];

$arSortFields = [
	"ID" => GetMessage("T_IBLOCK_DESC_FID"),
	"NAME" => GetMessage("T_IBLOCK_DESC_FNAME"),
	"ACTIVE_FROM" => GetMessage("T_IBLOCK_DESC_FACT"),
	"SORT" => GetMessage("T_IBLOCK_DESC_FSORT"),
	"TIMESTAMP_X" => GetMessage("T_IBLOCK_DESC_FTSAMP"),
];

$arMovementType = [
    'slider' => Loc::GetMessage('MOVEMENT_TYPE_SLIDER'),
    'carousel' => Loc::GetMessage('MOVEMENT_TYPE_CAROUSEL'),
];

$arDirectionType = [
    'ltr' => Loc::GetMessage('DIRECTION_TYPE_LTR'),
    'rtl' => Loc::GetMessage('DIRECTION_TYPE_RTL'),
];

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => Loc::GetMessage("IBLOCK_TYPE_PARAM_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		],
        "IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => Loc::GetMessage("IBLOCK_ID_PARAM_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '',
		],
		'SECTION_ID' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::GetMessage('IBLOCK_SECTION_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '={$_REQUEST["SECTION_ID"]}',
		],
        "SORT_BY" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("T_IBLOCK_DESC_IBORD1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		],
		"SORT_ORDER" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("T_IBLOCK_DESC_IBBY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		],
        "USE_NAV_CONTROLS" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("USE_NAV_CONTROLS_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
        ],
        "USE_NAV_POINTS" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("USE_NAV_POINTS_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
        ],
        "MOVEMENT_TYPE" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("MOVEMENT_TYPE_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arMovementType,
			"DEFAULT" => "slider",
        ],
        "SLIDES_PER_VIEW" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("SLIDES_PER_VIEW_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
        ],
        "FOCUS_AT_POSITION" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("FOCUS_AT_POSITION_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "0",
        ],
        "GAP_SIZE" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("GAP_SIZE_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
        ],
        "USE_AUTOPLAY" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("USE_AUTOPLAY_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
        ],
        "DIRECTION_TYPE" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("DIRECTION_TYPE_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arDirectionType,
			"DEFAULT" => "ltr",
        ],
        "USE_ADDITIONAL_CSS" => [
            "PARENT" => "VISUAL",
			"NAME" => Loc::GetMessage("USE_ADDITIONAL_CSS_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
        ],
        "CACHE_TIME" => ["DEFAULT"=>300],
    ],
];


?>