<?php

namespace Sotbit\Auth\Internals;

use Bitrix\Main;
use Bitrix\Main\Type;

/**
 *
 * @author E. Prokhorenko < e.prokhorenko@sotbit.ru >
 */
class StatisticsTable extends \DataManagerEx_Auth
{
    /**
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'sotbit_auth_statistics';
    }
    /**
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ),
            'OPEN_MESSAGE' => array(
                'data_type' => 'string',
            ),
            'ID_MESSAGE' => array(
                'data_type' => 'integer',
            ),
            'MESSAGE_TRANSITION' => array(
                'data_type' => 'string',
            ),
            'ID_USER' => array(
                'data_type' => 'integer',
            ),
            'IP' => array(
                'data_type' => 'string',
            ),
            'DEVICE' => array(
                'data_type' => 'string',
            ),
            'EVENT_NAME' => array(
                'data_type' => 'string',
            ),
            'EVENT_TEMPLATE' => array(
                'data_type' => 'string',
            ),
            'LOCATION' => array(
                'data_type' => 'string',
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
                'required' => true,
                'default_value' => new Type\DateTime()
            ),
            'DATE_OPEN' => array(
                'data_type' => 'datetime',
            ),
        );
    }

    public static function getOpenMails($arFilter){
        $result = self::getList(array('filter'=>$arFilter));
        $persentY = $Y = 0;
        $persentN = $N = 0;
        while ($data = $result->fetch()){
            if($data['OPEN_MESSAGE'] == 'Y'){$Y++;}else{$N++;}
        }
        if($Y + $N !== 0) {
            $persentY = $Y / ($Y + $N) * 100;
            $persentN = 100 - $persentY;
        }
        return array('Y'=>$persentY, 'N'=>$persentN);
    }
    public static function getReturnMails($arFilter){
        $result = self::getList(array('filter'=>$arFilter));
        $persentY = $Y = 0;
        $persentN = $N = 0;
        while ($data = $result->fetch()){
            if($data['MESSAGE_TRANSITION'] != ''){$Y++;}else{$N++;}
        }
        if($Y + $N !== 0) {
            $persentY = $Y / ($Y + $N) * 100;
            $persentN = 100 - $persentY;
        }
        return array('Y'=>$persentY, 'N'=>$persentN);
    }

}
?>