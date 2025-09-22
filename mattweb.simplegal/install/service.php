<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

class ServiceActions{
    
    public static function ImportIBlockFromXML($xmlFile, $iblockCode, $iblockType, $siteID, $permissions = [])
    {
        if (!Loader::IncludeModule("iblock"))
            return false;

        $rsIBlock = CIBlock::GetList([], ["CODE" => $iblockCode, "TYPE" => $iblockType, "SITE_ID"=>$siteID]);
        if ($arIBlock = $rsIBlock->Fetch())
            return $arIBlock['ID'];
        if (!is_array($siteID))
            $siteID = [$siteID];

        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/classes/".strtolower($GLOBALS["DB"]->type)."/cml2.php");
        ImportXMLFile($xmlFile, $iblockType, $siteID, $section_action = "N", $element_action = "N");
        
        $iblockID = false;
        $rsIBlock = CIBlock::GetList([], ["CODE" => $iblockCode, "TYPE" => $iblockType, "SITE_ID"=>$siteID]);

        if ($arIBlock = $rsIBlock->Fetch())
        {
            $iblockID = $arIBlock["ID"];
            if (empty($permissions))
                $permissions = [1 => "X", 2 => "R"];
            CIBlock::SetPermission($iblockID, $permissions);
        }

        return $iblockID;
    }

    public static function ReplaceMacros($filePath, $arReplace)
    {
        $skipSharp = false;
        clearstatcache();
        if ((!is_file($filePath)) || !is_writable($filePath) || !is_array($arReplace))
            return;

        @chmod($filePath, BX_FILE_PERMISSIONS);
        if (!$handleFile = @fopen($filePath, "rb"))
            return;
        $content = @fread($handleFile, filesize($filePath));
        @fclose($handleFile);
        $handleFile = false;
        if (!$handleFile = @fopen($filePath, "wb"))
            return;
        if (flock($handleFile, LOCK_EX))
        {
            $arSearch = [];
            $arValue = [];
            foreach ($arReplace as $search => $replace)
            {
                if ($skipSharp)
                    $arSearch[] = $search;
                else
                    $arSearch[] = "#".$search."#";
                $arValue[] = $replace;
            }
            $content = str_replace($arSearch, $arValue, $content);
            @fwrite($handleFile, $content);
            @flock($handleFile, LOCK_UN);
        }
        @fclose($handleFile);
    }
}