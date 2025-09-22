<?php

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <!-- обязательное получение сессии -->
    <?= bitrix_sessid_post() ?>   
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="mattweb.simplegal">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <!-- чекбокс для определния параметра -->
    <p><?= Loc::getMessage("MATTWEB_SIMPLEGAL_INSTALL") ?></p>
    <p>
        <input type="checkbox" name="add_data" id="add_data" value="Y" checked>
        <label for="add_data"><?= Loc::getMessage("MATTWEB_SIMPLEGAL_ADD_DATA_BUTTON") ?></label>
    </p>    
    <input type="submit" name="" value="<?= Loc::getMessage("MATTWEB_SIMPLEGAL_MOD_INSTALL") ?>">
</form>
