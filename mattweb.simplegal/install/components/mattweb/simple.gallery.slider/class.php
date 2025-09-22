<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Iblock\SectionTable,
    \Bitrix\Iblock\SectionElementTable,
    \Bitrix\Iblock\ElementTable;

Loc::loadMessages(__FILE__);

class SimpleGallerySlider extends CBitrixComponent{

    public function onPrepareComponentParams($arParams)
    {
        $arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);

        $arParams["SORT_BY"] = trim($arParams["SORT_BY"]);
        if($arParams["SORT_BY"] == ''){
            $arParams["SORT_BY"] = "ACTIVE_FROM";
        }

        if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER"])){
            $arParams["SORT_ORDER"]="DESC";
        }
        
        $arParams["SECTION_ID"] = (int) $arParams["SECTION_ID"];
        
        $arParams["MOVEMENT_TYPE"] = (strlen(trim($arParams["MOVEMENT_TYPE"])) > 0) ? trim($arParams["MOVEMENT_TYPE"]) : 'slider';
        
        $arParams["SLIDES_PER_VIEW"] = (int) $arParams["SLIDES_PER_VIEW"] > 0 ? (int) $arParams["SLIDES_PER_VIEW"] : 1;

        $arParams["FOCUS_AT_POSITION"] = (int) $arParams["FOCUS_AT_POSITION"];
       
        $arParams["GAP_SIZE"] = (int) $arParams["GAP_SIZE"] > 0 ? (int) $arParams["GAP_SIZE"] : 10;

        $arParams["USE_AUTOPLAY"] = (int) $arParams["USE_AUTOPLAY"] > 0 ? (int) $arParams["USE_AUTOPLAY"] : false;

        $arParams["DIRECTION_TYPE"] = ($arParams["DIRECTION_TYPE"] == 'rtl') ? $arParams["DIRECTION_TYPE"] : 'ltr';

        return $arParams;
    }


    public function executeComponent()
    {
         try {
            // подключаем метод проверки подключения модуля «Информационные блоки»
            $this->checkModules(['iblock']);

           
            if($this->startResultCache()){
           
                if($this->arParams["IBLOCK_ID"] > 0){

                    $imgOrmClass = \Bitrix\Iblock\Iblock::wakeUp($this->arParams["IBLOCK_ID"])->getEntityDataClass();

                    $arFilter = [
                        'IBLOCK_ID' => $this->arParams["IBLOCK_ID"],
                        'ACTIVE' => 'Y',
                    ];

                    $obResSect = SectionTable::getList([
                        'order' => ['ID' => 'asc'],
                        'select' => ['ID', 'IBLOCK_ID', 'NAME', 'CODE'],
                        'filter' => $arFilter,
                        'cache' => [
                            'ttl' => 3600,
                            'cache_joins' => true
                        ]
                    ]);

                    while($arSect = $obResSect->fetch()){

                        $this->arResult['SECTIONS'][$arSect['ID']] = [
                            'ID' => $arSect['ID'],
                            'NAME' => $arSect['NAME'],
                            'CODE' => $arSect['CODE'],
                        ];
                    }

                    $arSectElem = [];

                    $arSectElemFilter = ['IBLOCK_SECTION_ID'=> array_keys($this->arResult['SECTIONS'])];
                    $obSectElem = SectionElementTable::getList([
                        'order' => ['IBLOCK_SECTION_ID' => 'asc'],
                        'select' => ['IBLOCK_SECTION_ID', 'IBLOCK_ELEMENT_ID'],
                        'filter' => $arSectElemFilter,
                    ]);

                    while($arSE = $obSectElem->fetch()){
                        $arSectElem[$arSE['IBLOCK_ELEMENT_ID']] = $arSE['IBLOCK_SECTION_ID'];
                    }

                    // показывать только элементы, размещенные в корне
                    if($this->arParams["SECTION_ID"] == 0){
                        $arFilter['IN_SECTIONS'] = 'N';
                    }

                    $obRes = $imgOrmClass::getList([
                        'order' => [$this->arParams["SORT_BY"] =>  $this->arParams["SORT_ORDER"]],
                        'select' => ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION', 'NAME', 'CODE', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'IN_SECTIONS'],
                        'filter' => $arFilter,
                    ]);

                    while($arItem = $obRes->fetch()){
                       $skipItem = false;
                       if($this->arParams["SECTION_ID"] > 0){
                            if(array_key_exists($arItem['ID'], $arSectElem)){
                                $sectId = $arSectElem[$arItem['ID']];                                
                                $skipItem = ($sectId != $this->arParams["SECTION_ID"]);                               
                            }
                            elseif($arItem['IN_SECTIONS'] == 'N'){
                                 $skipItem = true;
                            }
                       }else{                        
                            $skipItem = ($arItem['IN_SECTIONS'] != 'N');                           
                       }

                       if($skipItem) continue;

                        $prevPictId = (int) $arItem['PREVIEW_PICTURE'];
                        if($prevPictId > 0){
                            $arPrevPict = CFile::GetFileArray($prevPictId);
                        }
                        
                        $detailPictId = (int) $arItem['DETAIL_PICTURE'];
                        if($detailPictId > 0){
                            $arDetailPict = CFile::GetFileArray($detailPictId);
                        }
                        
                        $this->arResult['ITEMS'][$arItem['ID']] = [
                            'NAME' => $arItem['NAME'],
                            'CODE' => $arItem['CODE'],
                            'PREV_PICT' => $arPrevPict,
                            'DETAIL_PICT' => $arDetailPict,
                            'IN_SECTIONS' => $arItem['IN_SECTIONS'],
                        ];
                    }

                }
                else{
                    $this->arResult["ERRORS"][] = Loc::GetMessage('EMPTY_IBLOCK_ID');
                }

                $this->IncludeComponentTemplate();

            }

        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }

    }

    protected function checkModules(array $arModules):void
    {
        foreach($arModules as $module){
            // если модуль не подключен
            if (!Loader::includeModule($module))
                // выводим сообщение в catch
                throw new SystemException(Loc::getMessage('INC_MODULE_NOT_INSTALLED', ['#MODULE_NAME#'=>$module]));
        }
    }

}


