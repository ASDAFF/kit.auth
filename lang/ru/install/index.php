<?
$MESS["kit.auth_MODULE_NAME"] = "KIT: Расширенная регистрация и авторизация";
$MESS["kit.auth_MODULE_DESC"] = "KIT: Расширенная регистрация и авторизация";
$MESS["kit.auth_PARTNER_NAME"] = "KIT";
$MESS["kit.auth_PARTNER_URI"] = "https://asdaff.github.io/";

$MESS["kit.auth_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_NAME"] = "Восстановление пароля";
$MESS["kit.auth_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_DESCRIPTION"] = "#USER_ID# - ID пользователя
#LOGIN# - Логин
#EMAIL# - E-mail
#USER_NEW_PASSWORD# - Новый пароль";
$MESS["kit.auth_KIT_AUTH_NEW_USER_PASSWORD_NAME"] = "Отправка письма пользователю, зарегистрированному в административной части";
$MESS["kit.auth_KIT_AUTH_NEW_USER_PASSWORD_DESCRIPTION"] = "#LOGIN# - Логин
#EMAIL# - E-mail
#USER_NEW_PASSWORD# - Новый пароль";

$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_NAME"] = "Подтверждение регистрации";
$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_NAME"] = "Подтверждение покупателя";
$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_DESCRIPTION"] = "";

$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_SUBJECT"] = "Требуется подтверждение регистрации";
$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_MESSAGE"] = "Здравствуйте. Зарегистрирован новый пользователь, требуется подтверждение. <br> Ссылка на пользователя: http://#SERVER_NAME#/bitrix/admin/kit_admin_helper_route.php?lang=ru&module=kit.auth&view=edit&ID=#ID#&entity=person_user";

$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_SUBJECT_USER"] = "Подтверждение регистрации";
$MESS["kit.auth_KIT_AUTH_CONFIRM_REGISTRATION_MESSAGE_USER"] = "Здравствуйте. Спасибо за регистрацию. Ожидайте подтверждения менеджером.";


$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_SUBJECT"] = "Требуется подтверждение организации";
$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_MESSAGE"] = "Здравствуйте. Зарегистрирована новая организация, требуется подтверждение. <br> Ссылка на организацию: http://#SERVER_NAME#/bitrix/admin/kit_admin_helper_route.php?lang=ru&module=kit.auth&view=edit&ID=#ID#&entity=person_buyer";

$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_SUBJECT_USER"] = "Подтверждение организации";
$MESS["kit.auth_KIT_AUTH_CONFIRM_BUYER_MESSAGE_USER"] = "Здравствуйте. Спасибо за добавление организации. Ожидайте подтверждения менеджером.";

$MESS["kit.auth_KIT_AUTH_SEND_NAME"] = "Перехватчик писем";
$MESS["kit.auth_KIT_AUTH_SEND_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_SUBJECT"] = "#SERVER_NAME#: Новый пароль для сайта.";
$MESS["kit.auth_KIT_AUTH_SUCCESS_CHANGE_PASSWORD_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Данное сообщение отправлено вам так как вы запросили восстановление пароля на сайте #SITE_NAME#.<br>
<br>
Ваш новый пароль:<br>
Email: #EMAIL#<br>
Пароль: #USER_NEW_PASSWORD#<br>
<br>
Сообщение сгенерировано автоматически.";
$MESS["kit.auth_KIT_AUTH_NEW_USER_PASSWORD_SUBJECT"] = "#SERVER_NAME#: Вы зарегистрированы на сайте.";
$MESS["kit.auth_KIT_AUTH_NEW_USER_PASSWORD_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Вы зарегистрированы на сайте #SITE_NAME#.<br>
<br>
Ваши данные:<br>
Логин: #LOGIN#<br>
Email: #EMAIL#<br>
Пароль: #USER_NEW_PASSWORD#<br>
<br>
Сообщение сгенерировано автоматически.";
$MESS["INSTALL_TITLE"] = "Установка модуля";
$MESS["kit.auth_STAFF_ADMIN_ROLE"] = "Управляющий компанией";
$MESS["kit.auth_STAFF_EMPLOYEE_ROLE"] = "Сотрудник компании";

$MESS["kit.auth_KIT_AUTH_COMPANY_REGISTER_NAME"] = "Организация зарегистрирована";
$MESS["kit.auth_KIT_AUTH_COMPANY_REGISTER_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_REGISTER_SUBJECT"] = "#SITE_NAME#: Организация зарегистрирована.";
$MESS["kit.auth_KIT_AUTH_COMPANY_REGISTER_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Компания #COMPANY# успешно зарегистрирована.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_MODERATION_NAME"] = "Организация отправлена на модерацию";
$MESS["kit.auth_KIT_AUTH_COMPANY_MODERATION_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_MODERATION_SUBJECT"] = "#SITE_NAME#: Организация отправлена на модерацию.";
$MESS["kit.auth_KIT_AUTH_COMPANY_MODERATION_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Компания #COMPANY# отправлена на модерацию. Ожидайте подтверждение.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_CONFIRM_NAME"] = "Организация одобрена";
$MESS["kit.auth_KIT_AUTH_COMPANY_CONFIRM_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_CONFIRM_SUBJECT"] = "#SITE_NAME#: Организация одобрена.";
$MESS["kit.auth_KIT_AUTH_COMPANY_CONFIRM_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Компания #COMPANY# одобрена.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_REJECTED_NAME"] = "Организация отклонена";
$MESS["kit.auth_KIT_AUTH_COMPANY_REJECTED_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_REJECTED_SUBJECT"] = "#SITE_NAME#: Организация отклонена.";
$MESS["kit.auth_KIT_AUTH_COMPANY_REJECTED_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Компания #COMPANY# отклонена.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_ADMIN_CONFIRM_NAME"] = "Руководитель организации одобрен";
$MESS["kit.auth_KIT_AUTH_ADMIN_CONFIRM_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_ADMIN_CONFIRM_SUBJECT"] = "#SITE_NAME#: профиль одобрен.";
$MESS["kit.auth_KIT_AUTH_ADMIN_CONFIRM_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Вы одобрены как руководитель компании #COMPANY#.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_ADMIN_REJECTED_NAME"] = "Руководитель организации отклонен";
$MESS["kit.auth_KIT_AUTH_ADMIN_REJECTED_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_ADMIN_REJECTED_SUBJECT"] = "#SITE_NAME#: профиль отклонен.";
$MESS["kit.auth_KIT_AUTH_ADMIN_REJECTED_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Вы отклонены как руководитель компании #COMPANY#.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_CHANGES_REJECTED_NAME"] = "Изменения компании отклонены";
$MESS["kit.auth_KIT_AUTH_COMPANY_CHANGES_REJECTED_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_CHANGES_REJECTED_SUBJECT"] = "#SITE_NAME#: изменения компании отклонены.";
$MESS["kit.auth_KIT_AUTH_COMPANY_CHANGES_REJECTED_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Изменения компании #COMPANY# отклонены.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_STAFF_INVITE_NAME"] = "Сотрудник добавлен в компанию";
$MESS["kit.auth_KIT_AUTH_STAFF_INVITE_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_STAFF_INVITE_SUBJECT"] = "#SITE_NAME#: приглашение в компанию.";
$MESS["kit.auth_KIT_AUTH_STAFF_INVITE_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Вы были добавлены в компанию #COMPANY#.<br>
<br>
<a href='#SERVER_NAME#/b2bcabinet/personal/'>ссылка на профиль</a><br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_NAME"] = "Новая заявка в компанию";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_SUBJECT"] = "#SITE_NAME#: новая заявка в компанию.";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Новая заявка в компанию #COMPANY#.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_NAME"] = "Заявка на присоединение к компании отклонена";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_SUBJECT"] = "#SITE_NAME#: заявка отклонена.";
$MESS["kit.auth_KIT_AUTH_COMPANY_JOIN_REQUEST_REJECTED_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Заявка на присоединение к компании #COMPANY# отклонена.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_STAFF_REMOVED_NAME"] = "Сотрудник удален";
$MESS["kit.auth_KIT_AUTH_STAFF_REMOVED_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_STAFF_REMOVED_SUBJECT"] = "#SITE_NAME#: профиль удален.";
$MESS["kit.auth_KIT_AUTH_STAFF_REMOVED_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Ваш профиль удален из компании #COMPANY#.<br>
<br>
Сообщение сгенерировано автоматически.";

$MESS["kit.auth_KIT_AUTH_STAFF_CONFIRM_NAME"] = "Заявка сотрудника в компанию одобрена";
$MESS["kit.auth_KIT_AUTH_STAFF_CONFIRM_DESCRIPTION"] = "";
$MESS["kit.auth_KIT_AUTH_STAFF_CONFIRM_SUBJECT"] = "#SITE_NAME#: заявка в компанию одобрена.";
$MESS["kit.auth_KIT_AUTH_STAFF_CONFIRM_MESSAGE"] = "Добрый день!<br>
<br>
Информационное сообщение сайта #SITE_NAME#<br>
------------------------------------------ <br>
<br>
Заявка на присоединение к компании #COMPANY# одобрена.<br>
<br>
<a href='#SERVER_NAME#/b2bcabinet/personal/'>ссылка на профиль</a><br>
<br>
Сообщение сгенерировано автоматически.";
?>