<?php

namespace DigitalWand\AdminHelper\Widget;

use Bitrix\Main\UserTable;
use Sotbit\Auth\User\Profile;
use Bitrix\Main\Localization\Loc;

\CModule::IncludeModule('sale');

class UserWidget extends NumberWidget
{
	/**
	 * @inheritdoc
	 */
	public function getEditHtml()
	{
		$style = $this->getSettings('STYLE');
		$size = $this->getSettings('SIZE');
		$userId = $this->getValue();
		$htmlUser = '';

		if (!empty($userId) && $userId != 0) {
			$rsUser = UserTable::getById($userId);
			$user = $rsUser->fetch();

			$htmlUser = '[<a href="user_edit.php?lang=ru&ID=' . $user['ID'] . '">' . $user['ID'] . '</a>] ('
				. $user['EMAIL'] . ') ' . $user['NAME'] . '&nbsp;' . $user['LAST_NAME'];
		}

		return '<input type="text"
					   name="' . $this->getEditInputName() . '"
					   value="' . static::prepareToTagAttr($this->getValue()) . '"
					   size="' . $size . '"
					   style="' . $style . '"/>' . $htmlUser;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueReadonly()
	{
		$userId = $this->getValue();
		$htmlUser = '';

		if (!empty($userId) && $userId != 0) {
			$rsUser = UserTable::getById($userId);
			$user = $rsUser->fetch();

			$htmlUser = '[<a href="user_edit.php?lang=ru&ID=' . $user['ID'] . '">' . $user['ID'] . '</a>]';

			if ($user['EMAIL']) {
				$htmlUser .= ' (' . $user['EMAIL'] . ')';
			}

			$htmlUser .= ' ' . static::prepareToOutput($user['NAME'])
				. '&nbsp;' . static::prepareToOutput($user['LAST_NAME']);
		}

		return $htmlUser;
	}

	/**
	 * @inheritdoc
	 */
	public function generateRow(&$row, $data)
	{
        $userId = $this->getValue();
        $strUser = '';

        if (!empty($userId) && $userId != 0) {

            $rsUser = UserTable::getById($userId);
            $user = $rsUser->fetch();
            $strUser = '[<a href="user_edit.php?lang=ru&ID=' . $user['ID'] . '">'.Loc::getMessage( "PROFILE" ).'</a>]';

            $res = Profile::GetList(array(), array('USER_ID'=>$user['ID']));
            $profile = $res->Fetch();
            $profileId = $profile['ID'];
            if($profileId > 0)
			{
				$strUser .= ' [<a href="sale_buyers_profile_edit.php?id='.$profileId.'&lang='.LANG.'">'.Loc::getMessage(
						"BUYER" ).'</a>]';
			}

            if ($user['EMAIL']) {
                $strUser .= ' (' . $user['EMAIL'] . ')';
            }


            $strUser .= ' ' . static::prepareToOutput($user['NAME'])
                . '&nbsp;' . static::prepareToOutput($user['LAST_NAME']);
        }


        if ($strUser) {
            $row->AddViewField($this->getCode(), $strUser);
        } else {
            $row->AddViewField($this->getCode(), '');
        }
	}
}