<?php
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class mattweb_simplegal extends CModule{

    function __construct(){
        $arModuleVersion = array();
        include(__DIR__."/version.php");

         $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );

        $this->MODULE_ID = 'mattweb.simplegal';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('MATTWEB_SIMPLEGAL_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MATTWEB_SIMPLEGAL_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('MATTWEB_SIMPLEGAL_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('MATTWEB_SIMPLEGAL_PARTNER_URI');

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function DoInstall(){
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

         global $APPLICATION;
         if ($request["step"] < 2) {
            // подключаем скрипт с административным прологом и эпилогом
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('INSTALL_TITLE_STEP_1'),
                __DIR__ . '/step1.php'
            );
        }

        if ($request["step"] == 2) {

            if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/local/components"))
                mkdir($_SERVER["DOCUMENT_ROOT"]."/local/components", 0777, true);

	        CopyDirFiles(__DIR__ .'/components/',
			$_SERVER['DOCUMENT_ROOT'].'/local/components/', true, true);
			
			CopyDirFiles(__DIR__ .'/js/',
			$_SERVER['DOCUMENT_ROOT'].'/local/js/', true, true);
			

            // регистрируем модуль в системе
            ModuleManager::RegisterModule("mattweb.simplegal");
           
            // проверяим ответ формы введеный пользователем на первом шаге
            if ($request["add_data"] == "Y") {
                include(__DIR__."/service.php");

                Loader::IncludeModule('iblock');

                $sliderSectId = 0;
                $carouselSectId = 0;

                $iblockID = $this->AddTestData();

                $rsSection = CIBlockSection::GetList(
                    $arOrder  = ["SORT" => "ASC"],
                    $arFilter = [
                        "ACTIVE" => "Y",
                        "IBLOCK_ID" => $iblockID
                        
                    ],
                    false,
                    $arSelect = ["ID", "NAME", "IBLOCK_ID", "CODE"],
                    false
                );

                $arSectTmp = [];

                while($arSection = $rsSection->fetch()) {

                    $arSectTmp[$arSection['CODE']] = $arSection['ID'];
                }

                if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/simple_gallery"))
                    mkdir($_SERVER["DOCUMENT_ROOT"]."/simple_gallery", 0777, true);

                CopyDirFiles(__DIR__ .'/public/simple_gallery/',
                $_SERVER['DOCUMENT_ROOT'].'/simple_gallery/', true, true);

                if(array_key_exists('slideshow', $arSectTmp) || array_key_exists('carousel', $arSectTmp)){
                    ServiceActions::ReplaceMacros($_SERVER['DOCUMENT_ROOT'].'/simple_gallery/carousel_comp.php', ['GALLERY_IBLOCK_ID' => $iblockID, 'CAROUSEL_SECT_ID' => $arSectTmp['carousel']]);
                    ServiceActions::ReplaceMacros($_SERVER['DOCUMENT_ROOT'].'/simple_gallery/gallery_comp.php', ['GALLERY_IBLOCK_ID' => $iblockID, 'SLIDER_SECT_ID' => $arSectTmp['slideshow']]);
                }
            }

            // подключаем скрипт с административным прологом и эпилогом
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('INSTALL_TITLE_STEP_2'),
                __DIR__ . '/step2.php'
            );
        }

        return true;
    }


    function DoUninstall(){
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
  
        global $APPLICATION;

        DeleteDirFilesEx("/local/js/glidejs");
        DeleteDirFilesEx("/local/components/mattweb");

        if(is_dir($_SERVER["DOCUMENT_ROOT"]."/simple_gallery")){
            DeleteDirFilesEx("/simple_gallery");
        }

        // удаляем регистрацию модуля в системе
        ModuleManager::UnRegisterModule("mattweb.simplegal");
        
        // подключаем скрипт с административным прологом и эпилогом
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('DEINSTALL_TITLE_2'),
            __DIR__ . '/unstep.php'
        );

        return true;
    }

    function AddTestData(){
        global $DB;

        $iblockXMLFile = __DIR__ ."/public/xml/gallery.xml";
        $iblockCode = "pictures";
        $iblockType = "gallery";

        $arIblockTypes = [];
        $dbIblockType = CIBlockType::GetList();

        while($arIblockType = $dbIblockType->Fetch())
        {
            if($arIBType = CIBlockType::GetByIDLang($arIblockType["ID"], LANG))
            {
                $arIblockTypes[] = $arIBType['IBLOCK_TYPE_ID'];
            }
        }

        if(!in_array($iblockType, $arIblockTypes)){
            $arFields = [
                'ID' => $iblockType,
                'SECTIONS'=>'Y',
                'IN_RSS'=>'N',
                'SORT'=>500,
                'LANG' => [
                    'en' =>[
                        'NAME'=>'Gallery',
                        'SECTION_NAME'=>'Sections',
                        'ELEMENT_NAME'=>'Pictures'
                    ],
                    'ru' =>[
                        'NAME'=>'Фотогалерея',
                        'SECTION_NAME'=>'Разделы',
                        'ELEMENT_NAME'=>'Фото'
                    ],
                ],
            ];

            $obBlocktype = new CIBlockType;
            $DB->StartTransaction();
            $res = $obBlocktype->Add($arFields);
            if(!$res)
            {
                $DB->Rollback();
                echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
            }
            else
                $DB->Commit();


        }

        $rsIBlock = CIBlock::GetList(array(), array("CODE" => $iblockCode, "TYPE" => $iblockType));
        $iblockID = false;

        if ($arIBlock = $rsIBlock->Fetch())
        {
            $iblockID = $arIBlock["ID"];
        }

        if($iblockID == false){
            $permissions = ["1" => "X", "2" => "R"];

            $currentSiteID = SITE_ID;
            if (defined("ADMIN_SECTION"))
            {
                $obSite = CSite::GetList($by = "def", $order = "desc", ["ACTIVE" => "Y"]);
                if ($arSite = $obSite->Fetch())
                    $currentSiteID = $arSite["LID"];
            }

            $iblockID = ServiceActions::ImportIBlockFromXML(
                $iblockXMLFile,
                $iblockCode,
                $iblockType,
                $currentSiteID,
                $permissions
            );

            if ($iblockID < 1) return;

            //IBlock fields
            $iblock = new CIBlock;
            
            $arFields = [
                "ACTIVE" => "Y",
                "LIST_MODE" => "C",
                "FIELDS" => [
                    'IBLOCK_SECTION' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'ACTIVE' => ['IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y'],
                    'ACTIVE_FROM' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '=today'],
                    'ACTIVE_TO' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'SORT' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'NAME' => ['IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => ''],
                    'PREVIEW_PICTURE' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ['FROM_DETAIL' => 'N', 'SCALE' => 'Y', 'WIDTH' => '350', 'HEIGHT' => '215', 'IGNORE_ERRORS' => 'N']],
                    'PREVIEW_TEXT_TYPE' => ['IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text'],
                    'PREVIEW_TEXT' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'DETAIL_PICTURE' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ['SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N']],
                    'DETAIL_TEXT_TYPE' => ['IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text'],
                    'DETAIL_TEXT' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'XML_ID' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'CODE' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => ''],
                    'TAGS' => ['IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '']
                ],
                "CODE" => $iblockCode,
                "XML_ID" => $iblockCode,
                "NAME" => $iblock->GetArrayByID($iblockID, "NAME"),
            ];
            
            $iblock->Update($iblockID, $arFields);
        }

        return $iblockID;
    }


}