<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>

<?if(!empty($arResult['COMPANIES'])):?>
    <div class="auth-company-choose" onclick="window.addEventOpenPopup();">
        <div class="icon-block">
            <img class="company-choose-icon" src="<?=$component->__template->GetFolder()?>/img/icon_organizations.svg" alt="">
        </div>
        <div class="auth-company-choose__text-container">
            <p class="auth-company-choose__title-header"><?=GetMessage("KIT_AUTH_COMPANY_CHOOSE_HEADER_TITLE")?></p>
            <span class="current-company"><?= $arResult['CURRENT_COMPANY']?></span>
        </div>
    </div>
    <div class="popup-company-choose-wrap">
        <div class="popup-company-choose">
            <button class="popup-company-choose__close-btn">
                <svg class="popup-company-choose__close-icon" width="25" height="26">
                    <g >
                        <line  x1="6.01022" y1="19.0104" x2="18.031" y2="6.98958" />
                        <line  x1="18.0312" y1="19.0104" x2="6.0104" y2="6.9896" />
                    </g>
                </svg>
            </button>
            <p class="company-choose__title-form"><?=GetMessage("KIT_AUTH_COMPANY_CHOOSE_FORM_TITLE")?></p>
            <div class="company-choose__input-wrapper">
                <input type="text" class="company-choose__search" placeholder="�����">
                <div class="company-choose__block-companies">
                    <?if($_SESSION['AUTH_COMPANY_CURRENT_ID']):?>
                        <div class="company-item" data-current-company="Y"
                             data-company-id="<?=$arResult["COMPANIES"][$_SESSION['AUTH_COMPANY_CURRENT_ID']]["ID_COMPANY"]?>"
                             title="<?=htmlspecialcharsbx($arResult["COMPANIES"][$_SESSION['AUTH_COMPANY_CURRENT_ID']]["COMPANY_NAME"])?>">
                            <div class="company-item__name-company">
                                <span>
                                    <?=$arResult["COMPANIES"][$_SESSION['AUTH_COMPANY_CURRENT_ID']]["COMPANY_NAME"]?>
                                </span>
                            </div>
                            <div>
                                <span class="company-item__hint current-company__hint"><?=GetMessage("KIT_AUTH_COMPANY_CHOOSE_FORM_HINT_CURRENT_COMPANY")?></span>
                            </div>
                        </div>
                    <?endif;?>
                    <?foreach ($arResult['COMPANIES'] as $company):?>
                        <?if($company["ID_COMPANY"] == $_SESSION['AUTH_COMPANY_CURRENT_ID']) continue;?>
                        <div class="company-item" data-company-id="<?=$company["ID_COMPANY"]?>" title="<?=htmlspecialcharsbx($company["COMPANY_NAME"])?>">
                            <div class="company-item__name-company">
                                <span><?=$company["COMPANY_NAME"]?></span>
                            </div>
                            <div>
                                <span class="company-item__hint"><?=GetMessage("KIT_AUTH_COMPANY_CHOOSE_FORM_HINT_SELECT_COMPANY")?></span>
                            </div>
                        </div>
                    <?endforeach;?>
                </div>
            </div>
        </div>
    </div>
<?endif;?>