<?php

namespace Sotbit\Auth\Person\User\AdminInterface;

use Bitrix\Main\Localization\Loc;
use DigitalWand\AdminHelper\Helper\AdminInterface;
use DigitalWand\AdminHelper\Widget\DateTimeWidget;
use DigitalWand\AdminHelper\Widget\NumberWidget;
use DigitalWand\AdminHelper\Widget\StringWidget;
use DigitalWand\AdminHelper\Widget\UserWidget;

class EditAdminInterface extends AdminInterface
{
	public function fields()
	{
		return [
			'MAIN' => [
				'NAME' => Loc::getMessage('TAB_TITLE'),
				'FIELDS' => [
					'ID' => [
						'WIDGET' => new NumberWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_ID'),
						'READONLY' => true,
						'HEADER' => true,
					],
					'ID_USER' => [
						'WIDGET' => new UserWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_USER_ID'),
						'READONLY' => true,
						'HEADER' => true,
						'STYLE' => 'height: 200px;'
					],
					'EMAIL' => [
						'WIDGET' => new StringWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_EMAIL'),
						'READONLY' => true,
						'HEADER' => true,
					],
					'FIELDS' => [
						'WIDGET' => new Widget\OrgWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_FIELDS'),
						'READONLY' => true,
						'HEADER' => true,
						'FILTER' => false,
					],
					'DATE_CREATE' => [
						'WIDGET' => new DateTimeWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_DATE_CREATE'),
						'HEADER' => true,
						'READONLY' => true,
					],
					'DATE_UPDATE' => [
						'WIDGET' => new DateTimeWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_DATE_UPDATE'),
						'HEADER' => true,
						'READONLY' => true,
						'FILTER' => true,
					],
					'STATUS' => [
						'WIDGET' => new Widget\StatusWidget(),
						'TITLE' => Loc::getMessage('SOTBIT_AUTH_STATUS'),
						'READONLY' => true,
						'HEADER' => true,
						'READONLY' => false,
					],
				]
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function helpers()
	{
		return [
			'\Sotbit\Auth\Person\User\AdminInterface\ListHelper' => [
				'BUTTONS' => [
					'LIST_CREATE_NEW' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_ADD_NEWS'),
					],
					'LIST_CREATE_NEW_SECTION' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_ADD_CATEGORY'),
					]
				]
			],
			'\Sotbit\Auth\Person\User\AdminInterface\EditHelper' => [
				'BUTTONS' => [
					'ADD_ELEMENT' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_ADD_NEWS')
					],
					'DELETE_ELEMENT' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_DELETE')
					],
					'CONFIRM_ELEMENT' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_CONFIRM_ELEMENT')
					],
					'UNCONFIRM_ELEMENT' => [
						'TEXT' => Loc::getMessage('DEMO_AH_NEWS_BUTTON_UNCONFIRM_ELEMENT')
					]
				]
			]
		];
	}
}