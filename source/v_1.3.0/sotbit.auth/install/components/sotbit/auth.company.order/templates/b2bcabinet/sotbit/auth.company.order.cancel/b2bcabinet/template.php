<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if(!Loader::includeModule('sotbit.b2bcabinet'))
{
	return false;
}
?>
<div class="index_order_cancel">
	<div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">
                        <?=Loc::getMessage('SALE_CANCEL_ORDER_TITLE')?>
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-header header-elements-inline payer_type-title">
                    <h6 class="card-title">
                        <span><?=GetMessage("SALE_CANCEL_ORDER1") ?></span>
                        <a href="<?=$arResult["URL_TO_DETAIL"]?>">
                            <?=GetMessage("SALE_CANCEL_ORDER2")?> #<?=$arResult["ACCOUNT_NUMBER"]?>
                        </a>
                        <span> ? <b>
                                <?= GetMessage("SALE_CANCEL_ORDER3") ?>
                            </b>
                        </span>
                    </h6>
                </div>
                <?if(strlen($arResult["ERROR_MESSAGE"])<=0):?>
                    <div class="card-body index_order_cancel_form">
                        <form method="post" action="<?=POST_FORM_ACTION_URI?>">
                            <input type="hidden" name="CANCEL" value="Y">
                            <?=bitrix_sessid_post()?>
                            <input type="hidden" name="ID" value="<?=$arResult["ID"]?>">
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">
                                    <?=GetMessage("SALE_CANCEL_ORDER4")?>:
                                </label>
                                <div class="col-lg-9">
                                    <textarea rows="5" cols="5" class="form-control" name="REASON_CANCELED" maxlength="250"></textarea>
                                </div>
                            </div>
                            <button class="btn btn-light index_order_cancel-button" type="submit" name="action" value="<?=GetMessage("SALE_CANCEL_ORDER_BTN")?>"><?=GetMessage("SALE_CANCEL_ORDER_BTN")?></button>
                        </form>
                    </div>
                <?else:?>
                    <?=ShowError($arResult["ERROR_MESSAGE"]);?>
                <?endif;?>
            </div>
        </div>
	</div>
</div>