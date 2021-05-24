<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

if(strlen($arResult["ID"])>0) {
    $APPLICATION->AddChainItem(Loc::getMessage('SPPD_EDIT_PROFILE', array('#ID#'=>$arResult['ID'])));
}

$this->addExternalCss('/local/templates/b2bcabinet/assets/css/components.min.css');
?>
<div class="row companies-wrapper">
    <div class="col-md-12">
        <div class="card">
            <?if(strlen($arResult["ID"])>0):?>
            <div class="card-header header-elements-inline">
                <h6 class="card-title"><?= Loc::getMessage('SPPD_PROFILE_NO', array("#ID#" => $arResult["ID"]))?></h6>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane show active" id="basic-tab1">
                        <div>
                            <?
                            ShowError($arResult["ERROR_MESSAGE"]);
                                ?>
                                <div class="form-group">
                                    <div class="row profile-types__wrapper">
                                        <label class="col-lg-12 col-form-label"><?=Loc::getMessage('SALE_PERS_TYPE')?>:</label>
                                        <div class="col-lg-12">
                                            <input readonly="" type="text" class="form-control" placeholder="<?=$arResult["PERSON_TYPE_NAME"]?>" value="<?=$arResult["PERSON_TYPE_NAME"]?>">
                                        </div>
                                    </div>
                                    <div class="row profile-types__wrapper">
                                        <label class="col-lg-12 col-form-label"><?=Loc::getMessage('SALE_PNAME')?>:</label>
                                        <div class="col-lg-12">
                                            <input readonly="" type="text" name="NAME" class="form-control" placeholder="<?=htmlspecialcharsbx($arResult["NAME"])?>" value="<?=htmlspecialcharsbx($arResult["NAME"])?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <?foreach($arResult["ORDER_PROPS"] as $block)
                                    {
                                        if (!empty($block["PROPS"]))
                                        {
                                            ?>
                                            <div class="col-md-12 ñol-lg-6 col-xl-6 my-2">
                                                <div class="card card-bitrix-cabinet">
                                                    <div class="card-header header-elements-inline">
                                                        <h5 class="card-title"><?= $block["NAME"]?></h5>
                                                        <div class="header-elements">
                                                            <div class="list-icons">
                                                                <a class="list-icons-item" data-action="collapse"></a>
                                                                <!--                                        <a class="list-icons-item" data-action="reload"></a>-->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <?
                                                        foreach($block["PROPS"] as $property)
                                                        {
                                                            $key = (int)$property["ID"];
                                                            $name = "ORDER_PROP_".$key;
                                                            $currentValue = $property['VALUE'];
                                                            $alignTop = ($property["TYPE"] === "LOCATION" && $arParams['USE_AJAX_LOCATIONS'] === 'Y') ? "vertical-align-top" : "";
                                                            ?>
                                                            <div class="form-group form-group-float">
                                                                <label class="text-muted" for="sppd-property-<?=$key?>">
                                                                    <?= $property["NAME"]?>:
                                                                    <? if ($property["REQUIRED"] == "Y") {
                                                                        ?>
                                                                        <span class="req">*</span>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </label>
                                                                <?
                                                                if ($property["TYPE"] == "CHECKBOX")
                                                                {
                                                                    ?>
                                                                    <input
                                                                            class="sale-personal-profile-detail-form-checkbox"
                                                                            id="sppd-property-<?=$key?>"
                                                                            type="checkbox"
                                                                            name="<?=$name?>"
                                                                            value="Y"
                                                                        <?if ($currentValue == "Y" || !isset($currentValue) && $property["DEFAULT_VALUE"] == "Y") echo " checked";?>/>
                                                                    <?
                                                                }
                                                                elseif ($property["TYPE"] == "TEXT")
                                                                {
                                                                    if ($property["MULTIPLE"] === 'Y')
                                                                    {
                                                                        if (empty($currentValue) || !is_array($currentValue))
                                                                            $currentValue = array('');
                                                                        foreach ($currentValue as $elementValue)
                                                                        {
                                                                            ?>
                                                                            <input
                                                                                    class="form-control"
                                                                                    type="text" name="<?=$name?>[]"
                                                                                    maxlength="50"
                                                                                    id="sppd-property-<?=$key?>"
                                                                                    value="<?=$elementValue?>"/>
                                                                            <?
                                                                        }
                                                                        ?>
                                                                        <span class="btn-themes btn-default btn-md btn input-add-multiple"
                                                                              data-add-type=<?=$property["TYPE"]?>
                                                                              data-add-name="<?=$name?>[]"><?=Loc::getMessage('SPPD_ADD')?></span>
                                                                        <?
                                                                    }
                                                                    else
                                                                    {
                                                                        ?>
                                                                        <input
                                                                                class="form-control"
                                                                                type="text" name="<?=$name?>"
                                                                                maxlength="50"
                                                                                id="sppd-property-<?=$key?>"
                                                                                value="<?=htmlspecialcharsbx($currentValue)?>" disabled/>
                                                                        <?
                                                                    }
                                                                }
                                                                elseif ($property["TYPE"] == "SELECT")
                                                                {
                                                                    ?>
                                                                    <select
                                                                            class="form-control"
                                                                            name="<?=$name?>"
                                                                            id="sppd-property-<?=$key?>"
                                                                            size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:1; ?>"
                                                                            disabled
                                                                    >
                                                                        <?
                                                                        foreach ($property["VALUES"] as $value)
                                                                        {
                                                                            ?>
                                                                            <option value="<?= $value["VALUE"]?>" <?if ($value["VALUE"] == $currentValue || !isset($currentValue) && $value["VALUE"]==$property["DEFAULT_VALUE"]) echo " selected"?>>
                                                                                <?= $value["NAME"]?>
                                                                            </option>
                                                                            <?
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                    <?
                                                                }
                                                                elseif ($property["TYPE"] == "MULTISELECT")
                                                                {
                                                                    ?>
                                                                    <select
                                                                            class="form-control"
                                                                            id="sppd-property-<?=$key?>"
                                                                            multiple name="<?=$name?>[]"
                                                                            size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:5; ?>"
                                                                            disabled
                                                                    >
                                                                        <?
                                                                        $arCurVal = array();
                                                                        $arCurVal = explode(",", $currentValue);
                                                                        for ($i = 0, $cnt = count($arCurVal); $i < $cnt; $i++)
                                                                            $arCurVal[$i] = trim($arCurVal[$i]);
                                                                        $arDefVal = explode(",", $property["DEFAULT_VALUE"]);
                                                                        for ($i = 0, $cnt = count($arDefVal); $i < $cnt; $i++)
                                                                            $arDefVal[$i] = trim($arDefVal[$i]);
                                                                        foreach($property["VALUES"] as $value)
                                                                        {
                                                                            ?>
                                                                            <option value="<?= $value["VALUE"]?>"<?if (in_array($value["VALUE"], $arCurVal) || !isset($currentValue) && in_array($value["VALUE"], $arDefVal)) echo" selected"?>>
                                                                                <?= $value["NAME"]?>
                                                                            </option>
                                                                            <?
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                    <?
                                                                }
                                                                elseif ($property["TYPE"] == "ENUM")
                                                                {
                                                                    $propValue = explode(',', $property["VALUE"]);
                                                                    ?>
                                                                        <select
                                                                                class="form-control"
                                                                                name="<?= $name ?><?=$property['COMPANY_PROPERTY_MULTIPLE'] == "Y" ? "[]" : ""?>"
                                                                                id="sppd-property-<?= $key ?>"
                                                                            <?=$property['COMPANY_PROPERTY_MULTIPLE'] == "Y" ? "multiple" : ""?>
                                                                            <?=$property['COMPANY_PROPERTY_REQUIRED'] == 'Y' ? 'required' : ''?>
                                                                                disabled
                                                                        >
                                                                            <?foreach ($property["VARIANTS"] as $variant):?>
                                                                                <option
                                                                                        value="<?=$variant["ID"]?>"
                                                                                    <?
                                                                                    if(in_array($variant["ID"], $propValue)){
                                                                                        echo "selected";
                                                                                    }
                                                                                    ?>
                                                                                >
                                                                                    <?=$variant["NAME"]?>
                                                                                </option>
                                                                            <?endforeach;?>
                                                                        </select>
                                                                    <?
                                                                }
                                                                elseif ($property["TYPE"] == "TEXTAREA")
                                                                {
                                                                    ?>
                                                                    <textarea
                                                                            class="form-control"
                                                                            id="sppd-property-<?=$key?>"
                                                                            rows="<?echo ((int)($property["SIZE2"])>0)?$property["SIZE2"]:4; ?>"
                                                                            cols="<?echo ((int)($property["SIZE1"])>0)?$property["SIZE1"]:40; ?>"
                                                                            name="<?=$name?>"
                                                                            disabled><?= (isset($currentValue)) ? $currentValue : $property["DEFAULT_VALUE"];?>
                                                            </textarea>
                                                                    <?
                                                                }
                                                                elseif ($property["TYPE"] == "LOCATION")
                                                                {
                                                                    $locationTemplate = ($arParams['USE_AJAX_LOCATIONS'] !== 'Y') ? "popup" : "";
                                                                    $locationClassName = 'location-block-wrapper';
                                                                    if ($arParams['USE_AJAX_LOCATIONS'] === 'Y')
                                                                    {
                                                                        $locationClassName .= ' location-block-wrapper-delimeter';
                                                                    }
                                                                    if ($property["MULTIPLE"] === 'Y')
                                                                    {
                                                                        if (empty($currentValue) || !is_array($currentValue))
                                                                            $currentValue = array($property["DEFAULT_VALUE"]);

                                                                        foreach ($currentValue as $code => $elementValue)
                                                                        {
                                                                            $locationValue = intval($elementValue) ? $elementValue : $property["DEFAULT_VALUE"];
                                                                            CSaleLocation::proxySaleAjaxLocationsComponent(
                                                                                array(
                                                                                    "ID" => "propertyLocation".$name."[$code]",
                                                                                    "AJAX_CALL" => "N",
                                                                                    'CITY_OUT_LOCATION' => 'Y',
                                                                                    'COUNTRY_INPUT_NAME' => $name.'_COUNTRY',
                                                                                    'CITY_INPUT_NAME' => $name."[$code]",
                                                                                    'LOCATION_VALUE' => $locationValue,
                                                                                ),
                                                                                array(
                                                                                ),
                                                                                $locationTemplate,
                                                                                true,
                                                                                $locationClassName
                                                                            );
                                                                        }
                                                                        ?>
                                                                        <span class="btn-themes btn-default btn-md btn input-add-multiple"
                                                                              data-add-type=<?=$property["TYPE"]?>
                                                                              data-add-name="<?=$name?>"
                                                                              data-add-last-key="<?=$code?>"
                                                                              data-add-template="<?=$locationTemplate?>"><?=Loc::getMessage('SPPD_ADD')?></span>
                                                                        <?
                                                                    }
                                                                    else
                                                                    {
                                                                        $locationValue = (int)($currentValue) ? (int)$currentValue : $property["DEFAULT_VALUE"];

                                                                        CSaleLocation::proxySaleAjaxLocationsComponent(
                                                                            array(
                                                                                "AJAX_CALL" => "N",
                                                                                'CITY_OUT_LOCATION' => 'Y',
                                                                                'COUNTRY_INPUT_NAME' => $name.'_COUNTRY',
                                                                                'CITY_INPUT_NAME' => $name,
                                                                                'LOCATION_VALUE' => $locationValue,
                                                                            ),
                                                                            array(
                                                                            ),
                                                                            $locationTemplate,
                                                                            true,
                                                                            'location-block-wrapper'
                                                                        );
                                                                    }
                                                                }
                                                                elseif ($property["TYPE"] == "RADIO")
                                                                {
                                                                    foreach($property["VALUES"] as $value)
                                                                    {
                                                                        ?>
                                                                        <div class="radio">
                                                                            <input
                                                                                    type="radio"
                                                                                    id="sppd-property-<?=$key?>"
                                                                                    name="<?=$name?>"
                                                                                    value="<?= $value["VALUE"]?>"
                                                                                <?if ($value["VALUE"] == $currentValue || !isset($currentValue) && $value["VALUE"] == $property["DEFAULT_VALUE"]) echo " checked"?>>
                                                                            <?= $value["NAME"]?>
                                                                        </div>
                                                                        <?
                                                                    }
                                                                }
                                                                elseif ($property["TYPE"] == "FILE")
                                                                {
                                                                    $multiple = ($property["MULTIPLE"] === "Y") ? "multiple" : '';
                                                                    $profileFiles = is_array($currentValue) ? $currentValue : array($currentValue);
                                                                    if (count($currentValue) > 0)
                                                                    {
                                                                        ?>
                                                                        <input type="hidden" name="<?=$name?>_del" class="profile-property-input-delete-file">
                                                                        <?
                                                                        foreach ($profileFiles as $file)
                                                                        {
                                                                            ?>
                                                                            <div class="sale-personal-profile-detail-form-file">
                                                                                <?
                                                                                $fileId = $file['ID'];
                                                                                if (CFile::IsImage($file['FILE_NAME']))
                                                                                {
                                                                                    ?>
                                                                                    <div class="sale-personal-profile-detail-prop-img">
                                                                                        <?=CFile::ShowImage($fileId, 150, 150, "border=0", "", true)?>
                                                                                    </div>
                                                                                    <?
                                                                                }
                                                                                else
                                                                                {
                                                                                    ?>
                                                                                    <a download="<?=$file["ORIGINAL_NAME"]?>" href="<?=CFile::GetFileSRC($file)?>">
                                                                                        <?=Loc::getMessage('SPPD_DOWNLOAD_FILE', array("#FILE_NAME#" => $file["ORIGINAL_NAME"]))?>
                                                                                    </a>
                                                                                    <?
                                                                                }
                                                                                ?>
                                                                                <input type="checkbox" value="<?=$fileId?>" class="profile-property-check-file" id="profile-property-check-file-<?=$fileId?>">
                                                                                <label for="profile-property-check-file-<?=$fileId?>"><?=Loc::getMessage('SPPD_DELETE_FILE')?></label>
                                                                            </div>
                                                                            <?
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <label>
                                                                <span class="btn-themes btn-default btn-md btn">
                                                                    <?=Loc::getMessage('SPPD_SELECT')?>
                                                                </span>
                                                                        <span class="sale-personal-profile-detail-load-file-info">
                                                                    <?=Loc::getMessage('SPPD_FILE_NOT_SELECTED')?>
                                                                </span>
                                                                        <?=CFile::InputFile($name."[]", 20, null, false, 0, "IMAGE", "class='btn sale-personal-profile-detail-input-file' ".$multiple)?>
                                                                    </label>
                                                                    <span class="sale-personal-profile-detail-load-file-cancel sale-personal-profile-hide"></span>
                                                                    <?
                                                                }

                                                                if (strlen($property["DESCRIPTION"]) > 0)
                                                                {
                                                                    ?>
                                                                    <br /><small><?= $property["DESCRIPTION"] ?></small>
                                                                    <?
                                                                }
                                                                ?>
                                                            </div>
                                                            <?
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?
                                        }
                                    }
                                    ?>
                                </div>
                            <div class="clearfix"></div>

                        </div>
                    </div>
                </div>
            </div>
            <?else:
                ShowError($arResult["ERROR_MESSAGE"]);
            endif;?>
        </div>

    </div>
</div>

<!--<script>
    $('.b2b_detail_order__nav_ul__block a').click(function (e)
    {
        e.preventDefault();
        $(this).tab('show');
    })
</script>-->

