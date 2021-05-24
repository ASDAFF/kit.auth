<?
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

if ($arResult["CONFIRM_REGISTRATION"]) {
    ?>
    <p class="success-register-confirm"><? echo Loc::getMessage("CONFIRM_REGISTRATION") ?></p>
    <?php
    return;
}
if ($USER->IsAuthorized()) {
    ?>
    <p>
        <?= Loc::getMessage("MAIN_REGISTER_AUTH"); ?>
    </p>
    <?
    return;
}
?>

<div class="row js_person_type auth-form">
    <div class="card mb-0">
        <div class="success-block__body text-center">
            <h5 class="success-block__title"></h5>
            <a href="/b2bcabinet/" class="btnBlock__authToLink success-block__btn"><?= Loc::getMessage("KIT_AUTH_GO_TO_WEBSITE") ?></a>
        </div>
        <div class="card-body">
            <div class="text-center mb-3">
                <i class="icon-plus3 icon-2x text-success
										 border-success border-3 rounded-round p-3 mb-3 mt-1"></i>
                <h5 class="mb-0"><?= Loc::getMessage("AUTH_REGISTER") ?></h5>
                <span class="d-block text-muted"><?= Loc::getMessage("AUTH_REGISTER_DESCRIPTION") ?></span>
            </div>
            <div class="chouse-company">
                <div class="bitrix-error">
                    <? if (!empty($arParams["~AUTH_RESULT"])):
                        $text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]); ?>

                        <label class="validation-invalid-label <?= ($arParams["~AUTH_RESULT"]["TYPE"] == "OK" ? "alert-success" : "alert-danger") ?>">
                            <?= nl2br(htmlspecialcharsbx($text)) ?>
                        </label>
                    <? endif ?>

                    <? if (!empty($arResult['ERRORS'])) {
                        foreach ($arResult['ERRORS'] as $errorMessage) {
                            if (mb_detect_encoding($errorMessage, 'UTF-8, CP1251') == 'UTF-8') {
                                $errorMessage = mb_convert_encoding($errorMessage, 'CP1251', 'UTF-8');
                            }
                            ShowError($errorMessage);
                        }
                    }
                    ?>

                    <? if ($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && is_array($arParams["AUTH_RESULT"]) && $arParams["AUTH_RESULT"]["TYPE"] === "OK"): ?>
                        <label class="validation-invalid-label"><? echo GetMessage("AUTH_EMAIL_SENT") ?></label>
                    <? elseif ($arResult["USE_EMAIL_CONFIRMATION"] === "Y"): ?>
                        <label class="validation-invalid-label"><? echo GetMessage("AUTH_EMAIL_WILL_BE_SENT") ?></label>
                    <? endif ?>
                </div>
                <label class="d-block font-weight-semibold"><?= Loc::getMessage("AUTH_CHOOSE_USER_TYPE") ?></label>

                <? foreach ($arResult['PERSON_TYPES'] as $key => $group): ?>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <div class="uniform-choice">
                                <input type="radio"
                                       class="js_checkbox_person_type form-check-input-styled REGISTER_WHOLESALER_TYPE"
                                       name="PERSON_TYPE"
                                       value="<?= $group['ID']; ?>"
                                    <?
                                    if (isset($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]))
                                        echo 'checked';
                                    elseif ($key == '0' && is_null($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]))
                                        echo 'checked';
                                    ?>
                                       data-fouc
                                >
                            </div>
                                <?= $group['NAME']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php foreach ($arResult['PERSON_TYPES'] as $key => $group): ?>
            <div class="js_person_type_block js_person_type_<?= $group['ID'] ?>"<? if ($key != 0): ?> style="display: none;"<? endif; ?>
                <? if (isset($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]))
                    echo 'checked';
                elseif ($key == '0' && is_null($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]))
                    echo 'checked';
                ?>
            >
                <form id="company-register" method="post" class="flex-fill" onsubmit="sendForm(); return false;">
                    <input type="hidden" name="REGISTER_WHOLESALER[TYPE]" value="<?= $group['ID'] ?>">
                    <input type="hidden" id="CONFIRM_JOIN" name="CONFIRM_JOIN" value="">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title"><?= GetMessage("AUTH_COMMON_BLOCK_TITLE") ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?if(Option::get("kit.auth", "LOGIN_EQ_EMAIL", "N", SITE_ID) !== 'Y'):?>
                                    <div class="col-md-12">
                                        <label>Login: <span>*</span></label>
                                        <div class="form-group form-group-feedback form-group-feedback-right">
                                            <input required
                                                   type="text"
                                                   name="REGISTER_WHOLESALER_USER[<?= $group['ID'] ?>][LOGIN]"
                                                   class="form-control"
                                                   placeholder="<?= Loc::getMessage('REGISTER_ENTER_LOGIN') ?>"
                                                <?= !empty($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['LOGIN']) ? "value=" . $arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['LOGIN'] . "" : "" ?>
                                            >
                                            <div class="form-control-feedback">
                                                <i class="icon-user-check text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?endif;?>
                                <div class="col-md-12">
                                    <label>E-mail: <span>*</span> </label>
                                    <div class="form-group form-group-feedback form-group-feedback-right">
                                        <input required
                                               type="email"
                                               name="REGISTER_WHOLESALER_USER[<?= $group['ID'] ?>][EMAIL]"
                                               class="form-control"
                                               placeholder="<?= Loc::getMessage('REGISTER_ENTER_EMAIL') ?>"
                                               <?= !empty($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['EMAIL']) ? "value=" . $arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['EMAIL'] . "" : "" ?>
                                        >
                                        <div class="form-control-feedback">
                                            <i class="icon-mention text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label><?= Loc::getMessage("REGISTER_FIELD_NAME") ?>: <span>*</span> </label>
                                    <div class="form-group form-group-feedback form-group-feedback-right">
                                        <input required
                                               type="text"
                                               name="REGISTER_WHOLESALER_USER[<?= $group['ID'] ?>][NAME]"
                                               class="form-control"
                                               placeholder="<?= Loc::getMessage('REGISTER_ENTER_FIRST_NAME') ?>"
                                               <?= !empty($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['NAME']) ? "value=" . $arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['NAME'] . "" : "" ?>
                                        >
                                        <div class="form-control-feedback">
                                            <i class="icon-user-check text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label><?= Loc::getMessage("REGISTER_FIELD_LAST_NAME") ?>: <span>*</span>
                                    </label>
                                    <div class="form-group form-group-feedback form-group-feedback-right">
                                        <input required
                                               type="text"
                                               name="REGISTER_WHOLESALER_USER[<?= $group['ID'] ?>][LAST_NAME]"
                                               class="form-control"
                                               placeholder="<?= Loc::getMessage('REGISTER_ENTER_SECOND_NAME') ?>"
                                               <?= !empty($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['LAST_NAME']) ? "value=" . $arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']]['LAST_NAME'] . "" : "" ?>
                                        >
                                        <div class="form-control-feedback">
                                            <i class="icon-user-check text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                                <?
                                foreach ($arResult["OPT_FIELDS"][$group['ID']] as $FIELD) {
                                    if (in_array($FIELD, ['PASSWORD', 'CONFIRM_PASSWORD', 'EMAIL', 'LOGIN', 'NAME', 'LAST_NAME'])) {
                                        continue;
                                    } else {
                                        ?>
                                        <div class="col-md-12">
                                            <label><?= Loc::getMessage("REGISTER_FIELD_" . $FIELD) ?>:
                                                <?=in_array($FIELD, $arResult['REQUIRED_FIELDS']) ? '<span>*</span>' : ''?></label>
                                            <div class="form-group form-group-feedback form-group-feedback-right">
                                                <input <?=in_array($FIELD, $arResult['REQUIRED_FIELDS']) ? 'required' : ''?>
                                                       type="text" class="form-control"
                                                       name="REGISTER_WHOLESALER_USER[<?=$group['ID']?>][<?=$FIELD ?>]" maxlength="50"
                                                       <?
                                                       $fieldValue = '';
                                                        if (!empty($arResult["VALUES"]['FIELDS'][$FIELD])) {
                                                            $fieldValue = $arResult["VALUES"]['FIELDS'][$FIELD];
                                                        } elseif ($arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']][$FIELD]) {
                                                            $fieldValue = $arResult["VALUES"]['WHOLESALER_FIELDS'][$group['ID']][$FIELD];
                                                        }

                                                    if (!empty($fieldValue)) {
                                                        echo 'value="' . $fieldValue . '"';
                                                    }
                                                    ?>
                                                       autocomplete="off"
                                                >
                                            </div>
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <?if(isset($arResult['OPT_ORDER_FIELDS'][$group['ID']]) && !empty($arResult['OPT_ORDER_FIELDS'][$group['ID']])):?>
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title"><?=Loc::getMessage("AUTH_BLOCK_WHOLESALER_ORDER_TITLE")?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <? foreach ($arResult['OPT_ORDER_FIELDS'][$group['ID']] as $order): ?>
                                        <?if($order['NAME']):?>
                                            <label><?=$order['NAME']; ?>: <?=$order["REQUIRED"] == "Y" ? " <span>*</span>" : ""?></label>
                                            <?if($order["TYPE"] == "ENUM" && $order["VARIANTS"]):?>
                                                <div class="form-group form-group-feedback form-group-feedback-right">
                                                    <select
                                                            class="form-control"
                                                            name="REGISTER_WHOLESALER_OPT[<?= $group['ID']; ?>][<?= $order['CODE'] ?>]<?=$order['MULTIPLE'] == "Y" ? "[]" : ""?>"
                                                            id="WHOLESALER_<?= $order['CODE'] ?>"
                                                        <?=$order['MULTIPLE'] == "Y" ? "multiple" : ""?>
                                                        <?=$order['REQUIRED'] == 'Y' ? 'required' : ''?>
                                                    >
                                                        <?if(!$order["DEFAULT_VALUE"]):?>
                                                            <option disabled selected><?=Loc::getMessage("REGISTER_FIELD_TYPE_ENUM")?></option>
                                                        <?endif;?>
                                                        <?foreach ($order["VARIANTS"] as $variant):?>
                                                            <option
                                                                    value="<?=$variant["ID"]?>"
                                                                <?
                                                                if(($order["MULTIPLE"] == "Y" && in_array($variant["ID"], $order["DEFAULT_VALUE"])) || ($variant["ID"] == $order["DEFAULT_VALUE"])){
                                                                    echo "selected";
                                                                }
                                                                ?>
                                                            ><?=$variant["NAME"]?></option>
                                                        <?endforeach;?>
                                                    </select>
                                                </div>
                                            <?else:?>
                                                <div class="form-group form-group-feedback form-group-feedback-right">
                                                    <input type="text" class="form-control"
                                                           placeholder="<?= $order['NAME']?><?=$order['DESCRIPTION'] ? " ".$order['DESCRIPTION'] : ''?>"
                                                           name="REGISTER_WHOLESALER_OPT[<?= $group['ID']; ?>][<?= $order['CODE'] ?>]"
                                                            <?=$order['REQUIRED'] == 'Y' ? 'required' : ''?>
                                                           maxlength="<?=
                                                           !empty($order['SETTINGS']['MAXLENGTH']) ? $order['SETTINGS']['MAXLENGTH'] :
                                                               ( !empty($order['SETTINGS']['SIZE']) ? $order['SETTINGS']['SIZE'] : 50 )
                                                           ?>"
                                                           minlength="<?=!empty($order['SETTINGS']['MINLENGTH']) ? $order['SETTINGS']['MINLENGTH'] : 0 ?>"
                                                           <?= !empty($arResult["VALUES"]['WHOLESALER_ORDER_FIELDS'][$group['ID']][$order['CODE']]) ? 'value="' . $arResult["VALUES"]['WHOLESALER_ORDER_FIELDS'][$group['ID']][$order['CODE']] . '"' : '' ?>
                                                           <?=$order['SETTINGS']['PATTERN'] ? "pattern='".$order['SETTINGS']['PATTERN']."'" : ""?>
                                                           id="WHOLESALER_<?= $order['CODE'] ?>"
                                                            <?=$order['DESCRIPTION'] ? "title='".$order['DESCRIPTION']."'" : ""?>
                                                    >
                                                </div>
                                            <?endif;?>
                                        <?endif;?>
                                    <? endforeach;?>
                                    <?if($order['CODE']['FILE'] == 'Y'):?>
                                        <?
                                        $APPLICATION->IncludeComponent(
                                            "bitrix:main.file.input",
                                            "auth_drag_n_drop",
                                            [
                                                "INPUT_NAME" => "FILES",
                                                "MULTIPLE" => "Y",
                                                "MODULE_ID" => "main",
                                                "MAX_FILE_SIZE" => "",
                                                "ALLOW_UPLOAD" => "F",
                                                "ALLOW_UPLOAD_EXT" => "",
                                                "TAB_ID" => $group['ID']
                                            ],
                                            false
                                        );
                                        ?>
                                    <?endif;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?endif;?>
                        <div class="card">
                            <div class="card-header header-elements-inline">
                                <h5 class="card-title"><?= Loc::getMessage('AUTH_SAVE_OF_DATA') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <? foreach ($arResult["SHOW_FIELDS"] as $FIELD) {
                                            if ($FIELD == 'PASSWORD' || $FIELD == 'CONFIRM_PASSWORD') {
                                                ?>
                                                <label><?= Loc::getMessage("REGISTER_FIELD_" . $FIELD) ?>:
                                                    <span>*</span> </label>
                                                <div class="form-group form-group-feedback form-group-feedback-right">
                                                    <input required type="password" class="form-control"
                                                           placeholder="<?= Loc::getMessage("REGISTER_PLACEHOLDER_" . $FIELD) ?>"
                                                           name="REGISTER[<?= $FIELD ?>]" maxlength="50"
                                                           value=""
                                                           autocomplete="off">
                                                    <span class="form-text text-muted"><?= Loc::getMessage("REGISTER_NOTE_" . $FIELD) ?></span>
                                                    <div class="form-control-feedback">
                                                        <i class="icon-user-lock text-muted"></i>
                                                    </div>
                                                </div>
                                                <?
                                            }
                                        }
                                        ?>
                                        <? if ($arResult["USE_CAPTCHA"] == "Y"): ?>
                                            <input type="hidden" name="captcha_sid"
                                                   value="<?= $arResult["CAPTCHA_CODE"] ?>"/>

                                            <label>
                                                <?= Loc::getMessage("REGISTER_CAPTCHA_PROMT") ?>: <span>*</span>
                                            </label>

                                            <div class="bx-captcha form-group form-group-feedback form-group-feedback-right">
                                                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                                                     width="180" height="40" alt="CAPTCHA">
                                            </div>

                                            <div class="password_recovery-captcha">
                                                <div class="form-group form-group-feedback form-group-feedback-right password_recovery-captcha_input">
                                                    <input type="text" class="form-control" name="captcha_word"
                                                           maxlength="50" autocomplete="off"
                                                           placeholder="<?= Loc::getMessage("CAPTCHA_REGF_PROMT") ?>">
                                                </div>
                                            </div>

                                        <? endif ?>


                                        <div class="d-flex align-items-center">
                                            <input name="UF_CONFIDENTIAL"
                                                   type="hidden"
                                                   value="Y"/>

                                            <? $APPLICATION->IncludeComponent(
                                                "bitrix:main.userconsent.request",
                                                "",
                                                array(
                                                    "ID" => COption::getOptionString("main", "new_user_agreement", ""),
                                                    "IS_CHECKED" => "Y",
                                                    "AUTO_SAVE" => "Y",
                                                    "IS_LOADED" => "Y",
                                                    "ORIGINATOR_ID" => $arResult["AGREEMENT_ORIGINATOR_ID"],
                                                    "ORIGIN_ID" => $arResult["AGREEMENT_ORIGIN_ID"],
                                                    "INPUT_NAME" => $arResult["AGREEMENT_INPUT_NAME"],
                                                    "REPLACE" => array(
                                                        "button_caption" => GetMessage("AUTH_REGISTER"),
                                                        "fields" => array(
                                                            rtrim(GetMessage("AUTH_NAME"), ":"),
                                                            rtrim(GetMessage("AUTH_LAST_NAME"), ":"),
                                                            rtrim(GetMessage("AUTH_LOGIN_MIN"), ":"),
                                                            rtrim(GetMessage("AUTH_PASSWORD_REQ"), ":"),
                                                            rtrim(GetMessage("AUTH_EMAIL"), ":"),
                                                        )
                                                    ),
                                                )
                                            ); ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


            <input type="hidden" name="kit_auth_register"
                   value="<?= Loc::getMessage('AUTH_REGISTER_WORD') ?>"/>
            <div class="btnBlock">
                <button type="submit" class="btn bg-teal-400 btn-labeled btn-labeled-right"><b><i
                                class="icon-plus3"></i></b><?= Loc::getMessage('AUTH_REGISTER') ?>
                </button>
                <a href="?register=no" class="btnBlock__authToLink"><?= Loc::getMessage('AUTH_AUTH') ?></a>
            </div>

                    </form>
                </div>
            <? endforeach; ?>
        </div>
    </div>
</div>
<script>
    window.arParams = "<?=$this->__component->getSignedParameters();?>";
</script>