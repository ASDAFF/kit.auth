<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="company-join-wrap">
    <?if($arResult["ITEMS"]):?>
        <div class="joinCompany__form-title">
            <?=GetMessage("KIT_COMPANY_JOIN_FORM_TITLE")?>
        </div>
        <div class="joinCompany__error-block"></div>
        <div class="joinCompany__success-block">
            <p><?=GetMessage("KIT_COMPANY_JOIN_FORM_TITLE_SUCCESS")?></p>
            <button type="button" class="btn btn-light btn_b2b btn_ok">
                <?=GetMessage("KIT_COMPANY_JOIN_FORM_BTN_SUCCESS")?>
            </button>
        </div>
        <form name="joinCompany" enctype="multipart/form-data">
            <input type="text" class="form-control join__search-company" placeholder="<?=GetMessage("KIT_COMPANY_JOIN_FORM_INPUT_PLACEHOLDER")?>"/>
            <div class="company-join__select-block">
                <?foreach ($arResult["SELECT_ITEMS"] as $idCompany=>$company):?>
                    <div class="select__company-item" data-id="<?=$idCompany?>"><?=$company?><i class="select__company-icon icon-checkmark3"></i></div>
                <?endforeach;?>
            </div>
            <div class="join-row">
                <input type="reset" name="company-join-reset" class="btn btn-light"
                       value="<?=GetMessage("KIT_COMPANY_JOIN_FORM_BTN_RESET")?>"/>
                <input type="button" name="company-join-send" class="btn btn-light btn_b2b"
                       value="<?=GetMessage("KIT_COMPANY_JOIN_FORM_BTN_SUBMIT")?>"/>
            </div>
        </form>
    <?else:?>
        <div class="joinCompany__form-no-item">
            <?=GetMessage("KIT_COMPANY_JOIN_FORM_TITLE_NO_COMPANIES")?>
        </div>
    <?endif;?>
</div>
