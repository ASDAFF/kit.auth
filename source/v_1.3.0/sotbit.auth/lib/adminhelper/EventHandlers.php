<?php

namespace DigitalWand\AdminHelper;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

/**
 * ������������ �������.
 *
 * ��� ������� �������, ������������ � �������, ������ ���������� ����������� ������-��������, ��������
 * � ������ ������ ���������� �����. ����� ������ ���� ��������������� � ������� ����� ���������� ������.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class EventHandlers
{
    /**
     * �������������� ����������� ������ � �������.
     *
     * ���� �������, ��������� ������������� ����������� � ����������� ������� ����� �������
     * ����������� ������-�������.
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function onPageStart()
    {
        if (Context::getCurrent()->getRequest()->isAdminSection())
        {
            Loader::includeModule('digitalwand.admin_helper');
        }
    }
}