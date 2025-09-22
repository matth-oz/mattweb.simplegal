<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle("Галерея");
    $APPLICATION->AddHeadString('<meta name="robots" content="nofollow, noindex" />');
?><?$APPLICATION->IncludeComponent(
	"mattweb:simple.gallery.slider", 
	".default", 
	[
		"CACHE_TIME" => "300",
		"CACHE_TYPE" => "A",
		"DIRECTION_TYPE" => "ltr",
		"FOCUS_AT_POSITION" => "0",
		"GAP_SIZE" => "10",
		"IBLOCK_ID" => "#GALLERY_IBLOCK_ID#",
		"IBLOCK_TYPE" => "gallery",
		"MOVEMENT_TYPE" => "slider",
		"SLIDES_PER_VIEW" => "1",
		"USE_AUTOPLAY" => "",
		"COMPONENT_TEMPLATE" => ".default",
		"USE_NAV_CONTROLS" => "Y",
		"USE_NAV_POINTS" => "Y",
		"USE_ADDITIONAL_CSS" => "N",
		"SORT_BY" => "ID",
		"SORT_ORDER" => "DESC",
		"SECTION_ID" => "#SLIDER_SECT_ID#",
		"SECTION_CODE" => ""
	],
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>