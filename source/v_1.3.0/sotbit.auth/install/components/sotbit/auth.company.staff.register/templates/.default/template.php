<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
if ($arResult["SHOW_SMS_FIELD"] == true) {
    CJSCore::Init('phone_auth');
}
?>


<div class="bx-auth-reg">
<p class="register-form-title"><?= GetMessage("AUTH_REGISTER") ?></p>
    <div class="success-form-add">
        <p class="success-message"></p>
        <button type="button" class="btn btn-light btn_b2b btn_ok">
           <?=GetMessage("COMPANY_REGISTER_CONFIRM_BTN_OK")?>
        </button>
    </div>
    <div class="confirm-form-add">
        <div class="confirm__row confirm__row_first">
            <p><?=GetMessage("COMPANY_REGISTER_CONFIRM_BLOCK_TEXT_1")?></p>
            <p><?=GetMessage("COMPANY_REGISTER_CONFIRM_BLOCK_TEXT_2")?></p>
        </div>
        <div class="confirm__row">
            <button type="button" class="btn btn-light  btn_cancel">
                <?=GetMessage("COMPANY_REGISTER_CONFIRM_BTN_CANCEL")?>
            </button>
            <button type="button" class="btn btn-light btn_b2b btn_confirm">
                <?=GetMessage("COMPANY_REGISTER_CONFIRM_BTN_CONFIRM")?>
            </button>
        </div>
    </div>

    <div class="referral-form">
        <div class="error-block"></div>
        <form id="referralform" name="referralform" enctype="multipart/form-data">
            <table class="staff-register__referal">
                <tbody>
                    <tr>
                        <td class="title-column">
                            <span class="referral__title"><?=GetMessage("REGISTER_REFERRAL_FIELD_EMAIL")?></span>
                        </td>
                        <td>
                            <input class="referral__input-email" type="text" name="REGISTER[EMAIL]"
                                   placeholder="<?= GetMessage("COMPANY_REGISTER_REFERRAL_PLACEHOLDER_EMAIL") ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="title-column">
                            <span class="referral__title"><?=GetMessage("REGISTER_FIELD_STAFF_ROLE")?></span>
                        </td>
                        <td>
                            <?
                            foreach ($arResult["SELECT_STAFF_ROLES"] as $role){
                                $arRoles[$role["CODE"]] = $role["NAME"];
                            }
                            $arr = array(
                                "REFERENCE" =>
                                    array_values($arRoles),
                                "REFERENCE_ID" =>
                                    array_keys($arRoles)
                            );
                            echo SelectBoxMFromArray("REGISTER[STAFF_ROLE][]", $arr, [], "", false, 2, "class =\"form-control\"");
                            ?>
                        </td>
                    </tr>
                    <?if($arResult["SELECT_USER_GROUPS"]):?>
                        <tr>
                            <td class="title-column">
                                <span class="referral__title"><?=GetMessage("REGISTER_FIELD_USER_GROUPS")?></span>
                            </td>
                            <td>
                                <?
                                $arr = array(
                                    "REFERENCE" => // массив заголовков элементов
                                        array_values($arResult["SELECT_USER_GROUPS"]),
                                    "REFERENCE_ID" => // массив значений элементов
                                        array_keys($arResult["SELECT_USER_GROUPS"])
                                );
                                echo SelectBoxMFromArray("REGISTER[USER_GROUPS][]", $arr, [], "", false, 1, "class =\"form-control\"");
                                ?>
                            </td>
                        </tr>
                    <?endif;?>
                </tbody>
            </table>

            <div class="referral__row">
                <input type="button" name="referral_exit_button" class="btn btn-light"
                       value="<?= GetMessage("COMPANY_REGISTER_REFERRAL_BTN_CANCEL") ?>"/>
                <input type="button" name="referral_submit_button" class="btn btn-light  btn_b2b"
                       value="<?= GetMessage("COMPANY_REGISTER_REFERRAL_BTN_SUBMIT")?>"/>
            </div>
        </form>
    </div>
    <form name="regform" enctype="multipart/form-data">
        <p>
            <?=GetMessage("COMPANY_REGISTER_REFERAL_LINK_TITLE_1")?>
            <a class="register-referral-link" href="javascript:void(0)"><?=GetMessage("COMPANY_REGISTER_REFERAL_LINK_TEXT")?></a>
            <?=GetMessage("COMPANY_REGISTER_REFERAL_LINK_TITLE_2")?>
        </p>
        <div class="regform-error"></div>
        <?
        if ($arResult["BACKURL"] <> ''):
            ?>
            <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
        <?
        endif;
        ?>

        <table class="staff-register">
            <tbody>
            <? foreach ($arResult["SHOW_FIELDS"] as $FIELD): ?>
                <? if ($FIELD == "AUTO_TIME_ZONE" && $arResult["TIME_ZONE_ENABLED"] == true): ?>
                    <tr>
                        <td><? echo GetMessage("main_profile_time_zones_auto") ?><? if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"): ?>
                                <span class="starrequired">*</span><? endif ?></td>
                        <td>
                            <select name="REGISTER[AUTO_TIME_ZONE]"
                                    onchange="this.form.elements['REGISTER[TIME_ZONE]'].disabled=(this.value != 'N')">
                                <option value=""><? echo GetMessage("main_profile_time_zones_auto_def") ?></option>
                                <option value="Y"<?= $arResult["VALUES"][$FIELD] == "Y" ? " selected=\"selected\"" : "" ?>><? echo GetMessage("main_profile_time_zones_auto_yes") ?></option>
                                <option value="N"<?= $arResult["VALUES"][$FIELD] == "N" ? " selected=\"selected\"" : "" ?>><? echo GetMessage("main_profile_time_zones_auto_no") ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><? echo GetMessage("main_profile_time_zones_zones") ?></td>
                        <td>
                            <select name="REGISTER[TIME_ZONE]"<? if (!isset($_REQUEST["REGISTER"]["TIME_ZONE"])) echo 'disabled="disabled"' ?>>
                                <? foreach ($arResult["TIME_ZONE_LIST"] as $tz => $tz_name): ?>
                                    <option value="<?= htmlspecialcharsbx($tz) ?>"<?= $arResult["VALUES"]["TIME_ZONE"] == $tz ? " selected=\"selected\"" : "" ?>><?= htmlspecialcharsbx($tz_name) ?></option>
                                <? endforeach ?>
                            </select>
                        </td>
                    </tr>
                <? else: ?>
                    <tr>
                        <td class="register-field-name"><?= GetMessage("REGISTER_FIELD_" . $FIELD) ?>
                            <? if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"): ?><span
                                    class="starrequired">*</span><? endif ?></td>
                        <td><?
                            switch ($FIELD) {
                                case "PASSWORD":
                                    ?><input size="30" type="password" name="REGISTER[<?= $FIELD ?>]"
                                             value="<?= $arResult["VALUES"][$FIELD] ?>" autocomplete="off"
                                             class="form-control" placeholder="<?=GetMessage("COMPANY_REGISTER_PLACEHOLDER_PASSWORD")?>"/>
                                <?
                                if ($arResult["SECURE_AUTH"]): ?>
                                    <span class="bx-auth-secure" id="bx_auth_secure" title="<?
                                    echo GetMessage("AUTH_SECURE_NOTE") ?>" style="display:none">
                                            <div class="bx-auth-secure-icon"></div>
                                        </span>
                                    <noscript>
                                            <span class="bx-auth-secure" title="<?
                                            echo GetMessage("AUTH_NONSECURE_NOTE") ?>">
                                                <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                                            </span>
                                    </noscript>
                                    <script type="text/javascript">
                                        document.getElementById('bx_auth_secure').style.display = 'inline-block';
                                    </script>
                                <?
                                endif ?>
                                    <p class="regform-password-requirements"><?=strripos($arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"])!==false ? $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"] : str_replace('0', '6', $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"])?></p>
                                    <?
                                    break;
                                case "CONFIRM_PASSWORD":
                                    ?><input size="30" type="password" name="REGISTER[<?= $FIELD ?>]"
                                            class="form-control" value="<?= $arResult["VALUES"][$FIELD] ?>" autocomplete="off" placeholder="<?=GetMessage("COMPANY_REGISTER_PLACEHOLDER_CONFIRM_PASSWORD")?>"/><?
                                    break;

                                case "PERSONAL_GENDER":
                                    ?><select name="REGISTER[<?= $FIELD ?>]">
                                    <option value=""><?= GetMessage("USER_DONT_KNOW") ?></option>
                                    <option value="M"<?= $arResult["VALUES"][$FIELD] == "M" ? " selected=\"selected\"" : "" ?>><?= GetMessage("USER_MALE") ?></option>
                                    <option value="F"<?= $arResult["VALUES"][$FIELD] == "F" ? " selected=\"selected\"" : "" ?>><?= GetMessage("USER_FEMALE") ?></option>
                                    </select><?
                                    break;

                                case "PERSONAL_COUNTRY":
                                case "WORK_COUNTRY":
                                    ?><select name="REGISTER[<?= $FIELD ?>]"><?
                                    foreach ($arResult["COUNTRIES"]["reference_id"] as $key => $value) {
                                        ?>
                                        <option value="<?= $value ?>"<?
                                        if ($value == $arResult["VALUES"][$FIELD]):?> selected="selected"<? endif ?>><?= $arResult["COUNTRIES"]["reference"][$key] ?></option>
                                        <?
                                    }
                                    ?></select><?
                                    break;

                                case "PERSONAL_PHOTO":
                                case "WORK_LOGO":
                                    ?><input size="30" type="file" name="REGISTER_FILES_<?= $FIELD ?>" /><?
                                    break;

                                case "PERSONAL_NOTES":
                            case "WORK_NOTES":
                                ?><textarea cols="30" rows="5"
                                            name="REGISTER[<?= $FIELD ?>]"><?= $arResult["VALUES"][$FIELD] ?></textarea><?
                            break;
                            case "USER_GROUPS":
                                $arr = array(
                                    "REFERENCE" => // массив заголовков элементов
                                        array_values($arResult["SELECT_USER_GROUPS"]),
                                    "REFERENCE_ID" => // массив значений элементов
                                        array_keys($arResult["SELECT_USER_GROUPS"])
                                );
                                echo SelectBoxMFromArray("REGISTER[" . $FIELD . "][]", $arr, [], "", false, 1, "class =\"form-control\"");
                                break;
                            case "STAFF_ROLE":
                                foreach ($arResult["SELECT_STAFF_ROLES"] as $role){
                                    $arRoles[$role["CODE"]] = $role["NAME"];
                                }
                                $arr = array(
                                    "REFERENCE" =>
                                        array_values($arRoles),
                                    "REFERENCE_ID" =>
                                        array_keys($arRoles)
                                );
                                echo SelectBoxMFromArray("REGISTER[" . $FIELD . "][]", $arr, [], "", false, 1, "class =\"form-control\"");
                                break;
                            default:
                            if ($FIELD == "PERSONAL_BIRTHDAY"): ?>
                                <small><?= $arResult["DATE_FORMAT"] ?></small><br/><?
                            endif;
                                ?><input size="30" type="text" name="REGISTER[<?= $FIELD ?>]" class="form-control"
                                         value="<?= $arResult["VALUES"][$FIELD] ?>" placeholder="<?=GetMessage("COMPANY_REGISTER_PLACEHOLDER").strtolower(GetMessage("REGISTER_FIELD_" . $FIELD))?>"/><?
                                if ($FIELD == "PERSONAL_BIRTHDAY") {
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.calendar',
                                        '',
                                        array(
                                            'SHOW_INPUT' => 'N',
                                            'FORM_NAME' => 'regform',
                                            'INPUT_NAME' => 'REGISTER[PERSONAL_BIRTHDAY]',
                                            'SHOW_TIME' => 'N'
                                        ),
                                        null,
                                        array("HIDE_ICONS" => "Y")
                                    );
                                }
                                ?><?
                            } ?></td>
                    </tr>
                <? endif ?>
            <? endforeach ?>
            <? // ********************* User properties ***************************************************?>
            <? if ($arResult["USER_PROPERTIES"]["SHOW"] == "Y"): ?>
                <tr>
                    <td colspan="2"><?= strlen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB") ?></td>
                </tr>
                <? foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField): ?>
                    <tr>
                        <td><?= $arUserField["EDIT_FORM_LABEL"] ?>:<? if ($arUserField["MANDATORY"] == "Y"): ?><span
                                    class="starrequired">*</span><? endif; ?></td>
                        <td>
                            <? $APPLICATION->IncludeComponent(
                                "bitrix:system.field.edit",
                                $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                                array(
                                    "bVarsFromForm" => $arResult["bVarsFromForm"],
                                    "arUserField" => $arUserField,
                                    "form_name" => "regform"
                                ), null, array("HIDE_ICONS" => "Y")); ?></td>
                    </tr>
                <? endforeach; ?>
            <? endif; ?>
            <? // ******************** /User properties ***************************************************?>
            <?
            /* CAPTCHA */
            if ($arResult["USE_CAPTCHA"] == "Y") {
                ?>
                <tr>
                    <td colspan="2"><b><?= GetMessage("REGISTER_CAPTCHA_TITLE") ?></b></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                             width="180" height="40" alt="CAPTCHA"/>
                    </td>
                </tr>
                <tr>
                    <td><?= GetMessage("REGISTER_CAPTCHA_PROMT") ?>:<span class="starrequired">*</span></td>
                    <td><input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off"/></td>
                </tr>
                <?
            }
            /* !CAPTCHA */
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <input type="reset" name="register_reset-form" class="btn btn-light"
                           value="<?= GetMessage("COMPANY_REGISTER_RESET_BUTTON_TEXT") ?>"/>
                    <input type="button" name="register_submit_button" class="btn btn-light btn_b2b"
                           value="<?= GetMessage("COMPANY_REGISTER_SUBMIT_BUTTON_TEXT") ?>"/>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<script>
    window.arParams = "<?=$this->__component->getSignedParameters();?>";
</script>