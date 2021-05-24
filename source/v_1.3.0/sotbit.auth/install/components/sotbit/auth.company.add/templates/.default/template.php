<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
ShowError($arResult["ERROR_MESSAGE"]);?>
<div class="buyer_profile">
	<div class="wrap_content">
		<div class="row">
			<div class="col-sm-22 col-md-20 col-lg-16 sm-padding-right-no">
				<?
				if($_REQUEST['confirm'] == 'Y')
				{
					?>
					<div class="confirm-buyer"><?=Loc::getMessage('SOA_CONFIRM_BUYER')?></div>
					<?
				}
				else
				{
				?>
					<form method="post" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" id="add-org">
						<?=bitrix_sessid_post()?>

						<div class="row">
							<div class="col-sm-10 col-md-8 sm-padding-right-no">
								<label><?=Loc::getMessage('SOA_SALE_PERS_TYPE')?></label>
							</div>
							<div class="col-sm-14 col-md-16">
								<select name="PERSON_TYPE" id="person-type">
									<?php
									foreach($arResult['PERSON_TYPES'] as $id => $name)
									{
										?>
										<option <?=($id==$arResult['PERSON_TYPE']['ID']?'selected':'')?> value="<?=$id?>"><?=$name?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<input type="hidden" name="NAME" id="NAME" value="<?=htmlspecialcharsbx($arResult["NAME"])?>" />
						<?
						foreach($arResult["ORDER_PROPS"] as $block)
						{
							if (!empty($block["PROPS"]))
							{
								?>
								<div class="wrap_block">
								<h3 class="block_title"><?php echo $block['NAME'];?></h3>
								<?php
								foreach($block["PROPS"] as $key => $property)
								{
									if($property['CODE'] == 'EQ_POST')
									{
										continue;
									}
									$name = "ORDER_PROP_".$property["ID"];
									$currentValue = $arResult["ORDER_PROPS_VALUES"][$name];
									$alignTop = ($property["TYPE"] === "LOCATION" && $arParams['USE_AJAX_LOCATIONS'] === 'Y') ? "vertical-align-top" : "";
									?>
									<div class="row">
										<div class="col-sm-10 col-md-8 sm-padding-right-no">
											<label><?php echo $property["NAME"];
												if($property['REQUIED'] == 'Y')
												{
													?>
													<span class="req">*</span>
													<?php
												}?>
											</label>
										</div>
										<div class="col-sm-14 col-md-16">
											<?php
											if($property["TYPE"] == 'TEXT')
											{
												?>
												<input id="<?php echo $property['CODE'];?>" type="text" name="<?=$name?>" maxlength="50" value="<?=$currentValue?>">
												<?php
											}
											elseif ($property["TYPE"] == "CHECKBOX")
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
											elseif ($property["TYPE"] == "SELECT")
											{
												?>
												<select
													class="form-control"
													name="<?=$name?>"
													id="sppd-property-<?=$key?>"
													size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:1; ?>">
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
													size="<?echo (intval($property["SIZE1"])>0)?$property["SIZE1"]:5; ?>">
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
															<option value="<?= $value["VALUE"]?>"<?if (in_array($value["VALUE"], $arCurVal) || !isset($currentValue) && in_array($value["VALUE"], $arDefVal)) echo" selected"?>><?echo $value["NAME"]?></option>
															<?
														}
														?>
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
													name="<?=$name?>"><?= (isset($currentValue)) ? $currentValue : $property["DEFAULT_VALUE"];?></textarea>
												<?
											}
											elseif ($property["TYPE"] == "LOCATION")
											{
												$locationTemplate = ($arParams['USE_AJAX_LOCATIONS'] !== 'Y') ? "popup" : "";

												$locationValue = intval($currentValue) ? $currentValue : $property["DEFAULT_VALUE"];
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
											elseif ($property["TYPE"] == "RADIO")
											{
												foreach($property["VALUES"] as $value)
												{
													?>
													<input
														class="form-control"
														type="radio"
														id="sppd-property-<?=$key?>"
														name="<?=$name?>"
														value="<?echo $value["VALUE"]?>"
														<?if ($value["VALUE"] == $currentValue || !isset($currentValue) && $value["VALUE"] == $property["DEFAULT_VALUE"]) echo " checked"?>>
													<?= $value["NAME"]?><br />
													<?
												}
											}
											elseif ($property["TYPE"] == "FILE")
											{
												$multiple = ($property["MULTIPLE"] === "Y") ? "multiple" : '';
												?>
												<label>
													<span class="btn-themes btn-default btn-md btn">
														<?=Loc::getMessage('SOA_SALE_SELECT')?>
													</span>
													<span class="sale-personal-profile-detail-load-file-info">
														<?=Loc::getMessage('SOA_SALE_FILE_NOT_SELECTED')?>
													</span>
													<?=CFile::InputFile($name."[]", 20, null, false, 0, "IMAGE", "class='btn sale-personal-profile-detail-input-file' ".$multiple)?>
												</label>
												<span class="sale-personal-profile-detail-load-file-cancel sale-personal-profile-hide"></span>
												<?
												if (count($currentValue) > 0)
												{
													?>
													<input type="hidden" name="<?=$name?>_del" class="profile-property-input-delete-file">
													<?
													$profileFiles = unserialize(htmlspecialchars_decode($currentValue));
													if (!is_array($profileFiles))
													{
														$profileFiles = array($profileFiles);
													}
													foreach ($profileFiles as $file)
													{
														?>
														<div class="sale-personal-profile-detail-form-file">
															<input type="checkbox" value="<?=$file?>" class="profile-property-check-file" id="profile-property-check-file-<?=$file?>">
															<label for="profile-property-check-file-<?=$file?>"><?=Loc::getMessage('SOA_SALE_DELETE_FILE')?></label>
															<?
															$fileInfo = CFile::GetByID($file);
															$fileInfoArray = $fileInfo->Fetch();
															if (CFile::IsImage($fileInfoArray['FILE_NAME']))
															{
																?>
																<p>
																	<?=CFile::ShowImage($file, 150, 150, "border=0", "", true)?>
																</p>
																<?
															}
															else
															{
																?>
																<a download="<?=$fileInfoArray["ORIGINAL_NAME"]?>" href="<?=CFile::GetFileSRC($fileInfoArray)?>">
																	<?=Loc::getMessage('SOA_SALE_DOWNLOAD_FILE', array("#FILE_NAME#" => $fileInfoArray["ORIGINAL_NAME"]))?>
																</a>
																<?
															}
															?>
														</div>
														<?
													}
												}
											}
											?>
										</div>
									</div>
									<?
								}
								?>
								</div>
								<?php
							}
						}
						?>
						<div class="row">
							<div class="col-sm-8">
								<input type="submit" class="btn btn-themes btn-default btn-md" name="save" value="<?echo GetMessage("SOA_SALE_SAVE") ?>">
								&nbsp;
							</div>
							<div class="col-sm-8">
								<input type="submit" class="btn btn-themes btn-default btn-md"  name="apply" value="<?=GetMessage("SOA_SALE_APPLY")?>">
								&nbsp;
							</div>
							<div class="col-sm-8">
								<input type="submit" class="btn-reset"  name="reset" value="<?echo GetMessage("SOA_SALE_RESET")?>">
							</div>
						</div>
					</form>
				<?}?>
			</div>
		</div>
	</div>
</div>