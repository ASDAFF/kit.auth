<?
namespace Sotbit\Auth\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Menu
{
    public static function getAdminMenu(&$arGlobalMenu, &$arModuleMenu)
    {
        $moduleInclude = Loader::includeModule('sotbit.auth');
        global $APPLICATION;

        if ($APPLICATION->GetGroupRight(\SotbitAuth::idModule) != 'D') {
            $Sites = [];
            $result = \Bitrix\Main\SiteTable::getList([
                'select' => [
                    'LID',
                    'NAME'
                ],
                'filter' => [
                    'ACTIVE' => 'Y'
                ]
            ]);
            while ($Site = $result->fetch()) {
                $Sites[] = $Site;
            }
            unset($result);
            unset($Site);

            $Paths = [
                'settings' => '.php'
            ];

            if (count($Sites) == 1) {
                foreach ($Paths as $key => $Path) {
                    $Settings[$key]['text'] = Loc::getMessage(\SotbitAuth::idModule . '_MENU_' . $key . '_SETTINGS_TEXT');
                    $Settings[$key]['title'] = Loc::getMessage(\SotbitAuth::idModule . '_MENU_' . $key . '_SETTINGS_TEXT');
                    $Settings[$key]['items_id'] = 'menu_sotbit.auth_settings_' . $key;
                    $Settings[$key]['items'][0] = [
                        'text' => '[' . $Sites[0]['LID'] . '] ' . $Sites[0]['NAME'],
                        'url' => 'sotbit.auth_' . $key . $Path . '?lang=' . LANGUAGE_ID . '&site=' . $Sites[0]['LID'],
                        'title' => $Sites[0]['NAME']
                    ];
                }
            } else {
                $Items = [];
                foreach ($Paths as $key => $Path) {
                    foreach ($Sites as $Site) {
                        $Items[$key][] = [
                            'text' => '[' . $Site['LID'] . '] ' . $Site['NAME'],
                            'url' => 'sotbit.auth_' . $key . $Path . '?lang=' . LANGUAGE_ID . '&site=' . $Site['LID'],
                            'title' => $Site['NAME']
                        ];
                    }
                }

                foreach ($Paths as $key => $Path)
                    $Settings[$key] = [
                        'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_' . $key . '_SETTINGS_TEXT'),
                        'items_id' => 'menu_sotbit.auth_settings_' . $key,
                        'items' => $Items[$key],
                        'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_' . $key . '_SETTINGS_TEXT')
                    ];
            }
            $Settings['settings']['items'][] = [
                'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_ALL_SETTINGS_TITLE'),
                'url' => 'sotbit.auth_settings.php?lang=' . LANGUAGE_ID,
                'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_ALL_SETTINGS_TITLE'),
            ];

            if (!isset($arGlobalMenu['global_menu_sotbit'])) {
                $arGlobalMenu['global_menu_sotbit'] = [
                    'menu_id'   => 'sotbit',
                    'text'      => Loc::getMessage(\SotbitAuth::idModule .'_GLOBAL_MENU'),
                    'title'     => Loc::getMessage(\SotbitAuth::idModule .'_GLOBAL_MENU'),
                    'sort'      => 1000,
                    'items_id'  => 'global_menu_sotbit_items',
                    "icon"      => "",
                    "page_icon" => "",
                ];
            }

            if($moduleInclude) {
                $menu = [
                    'section' => \SotbitAuth::idModule,
                    'sort' => 700,
                    'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_TEXT'),
                    'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_TITLE'),
                    'icon' => 'sotbit_auth_menu_icon',
                    'page_icon' => 'sotbit_auth_page_icon',
                    'items_id' => 'menu_sotbit.auth',
                    'items' => [
                        [
                            'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_WHOLESALERS_TEXT'),
                            'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_WHOLESALERS_TEXT'),
                            'items_id' => 'menu_sotbit.auth_settings_wholesalers',
                            'items' => [
                                [
                                    'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_USER_CONFIRM_TEXT'),
                                    'url' => 'sotbit_admin_helper_route.php?lang=' . LANGUAGE_ID . '&module=sotbit.auth&view=list&restore_query=Y&entity=person_user',
                                    'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_USER_CONFIRM_TITLE'),
                                    'more_url' => [
                                        'sotbit_admin_helper_route.php?lang=' . LANGUAGE_ID . '&module=sotbit.auth&view=edit&ID='. $_GET['ID'] .'&entity=person_user'
                                    ],
                                ],
                                [
                                    'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_BUYER_CONFIRM_TEXT'),
                                    'url' => 'sotbit_admin_helper_route.php?lang=' . LANGUAGE_ID . '&module=sotbit.auth&view=list&restore_query=Y&entity=person_buyer',
                                    'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_BUYER_CONFIRM_TITLE'),
                                    'more_url' => [
                                        'sotbit_admin_helper_route.php?lang=' . LANGUAGE_ID . '&module=sotbit.auth&view=edit&ID='. $_GET['ID'] .'&entity=person_buyer'
                                    ],
                                ],
                                [
                                    'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_FILE_TEXT'),
                                    'url' => 'sotbit_auth_files.php?lang=' . LANGUAGE_ID,
                                    'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_FILE_TITLE')
                                ]
                            ],
                        ],
                        $Settings['settings'],
                        [
                            'text' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_STATISTICS'),
                            'url' => 'sotbit_auth_statistics.php?lang=' . LANGUAGE_ID,
                            'title' => Loc::getMessage(\SotbitAuth::idModule . '_MENU_SOTBIT_AUTH_STATISTICS')
                        ],
                    ]
                ];
            }
            $arGlobalMenu['global_menu_sotbit']['items']['sotbit.auth'] = $menu;
        }
    }
}

?>