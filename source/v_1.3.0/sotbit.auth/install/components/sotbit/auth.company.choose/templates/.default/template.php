<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
global $USER;
?>

<?if(!empty($arResult['COMPANIES'])):?>
    <div class="auth-company-change">
        <div class="auth-company-change__current"
             <?if(!empty($arResult['COMPANIES']) && count($arResult['COMPANIES'])>1):?>onclick=ToggleCompanyDropdown('.auth-company-change__list-wrapper')<?endif;?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16" fill="none">
                <path d="M12 12.0469V11.2188C12.5521 10.9062 13.0208 10.401 13.4062 9.70312C13.8021 9.00521 14 8.27083 14 7.5C14 6.875 13.9844 6.29167 13.9531 5.75C13.9219 5.19792 13.8125 4.71875 13.625 4.3125C13.4375 3.90625 13.1406 3.58854 12.7344 3.35938C12.3281 3.11979 11.75 3 11 3C10.25 3 9.67188 3.11979 9.26562 3.35938C8.85938 3.58854 8.5625 3.90625 8.375 4.3125C8.1875 4.71875 8.07812 5.19792 8.04688 5.75C8.01562 6.29167 8 6.875 8 7.5C8 8.27083 8.19271 9.00521 8.57812 9.70312C8.97396 10.401 9.44792 10.9062 10 11.2188V12.0469C9.15625 12.1094 8.36458 12.2604 7.625 12.5C6.89583 12.7292 6.26042 13.0208 5.71875 13.375C5.1875 13.7188 4.76562 14.1198 4.45312 14.5781C4.15104 15.026 4 15.5 4 16H18C18 15.5 17.8438 15.026 17.5312 14.5781C17.2292 14.1198 16.8073 13.7188 16.2656 13.375C15.7344 13.0208 15.099 12.7292 14.3594 12.5C13.6302 12.2604 12.8438 12.1094 12 12.0469ZM3.23438 9.625C3.13021 9.66667 3.02604 9.70833 2.92188 9.75C2.82812 9.79167 2.72917 9.83333 2.625 9.875H7.40625C7.39583 9.83333 7.38021 9.79167 7.35938 9.75C7.33854 9.70833 7.31771 9.66667 7.29688 9.625H3.23438ZM0.421875 11.625C0.401042 11.6667 0.375 11.7083 0.34375 11.75C0.322917 11.7917 0.302083 11.8333 0.28125 11.875H6.10938C6.21354 11.8333 6.3125 11.7917 6.40625 11.75C6.51042 11.7083 6.60938 11.6667 6.70312 11.625H0.421875ZM1.375 10.625C1.3125 10.6667 1.25521 10.7083 1.20312 10.75C1.16146 10.7917 1.11458 10.8333 1.0625 10.875H8C7.96875 10.8333 7.9375 10.7917 7.90625 10.75C7.88542 10.7083 7.85938 10.6667 7.82812 10.625H1.375ZM5.26562 9.125C5.04688 9.15625 4.83333 9.19271 4.625 9.23438C4.42708 9.27604 4.22917 9.32292 4.03125 9.375H7.20312C7.19271 9.33333 7.17708 9.29167 7.15625 9.25C7.14583 9.20833 7.13542 9.16667 7.125 9.125H5.26562ZM6 8.625V8.875H7.04688C7.03646 8.83333 7.02604 8.79167 7.01562 8.75C7.01562 8.70833 7.01042 8.66667 7 8.625H6ZM5.84375 8.125C5.875 8.14583 5.90104 8.16146 5.92188 8.17188C5.95312 8.18229 5.97917 8.19792 6 8.21875V8.375H6.95312C6.94271 8.33333 6.93229 8.29167 6.92188 8.25C6.92188 8.20833 6.91667 8.16667 6.90625 8.125H5.84375ZM0.03125 12.625C0.0208333 12.6667 0.0104167 12.7083 0 12.75C0 12.7917 0 12.8333 0 12.875H4.5C4.55208 12.8333 4.60417 12.7917 4.65625 12.75C4.71875 12.7083 4.77604 12.6667 4.82812 12.625H0.03125ZM1.71875 10.375H7.67188C7.66146 10.3542 7.65104 10.3333 7.64062 10.3125C7.63021 10.2917 7.61458 10.2708 7.59375 10.25C7.58333 10.2292 7.57292 10.2083 7.5625 10.1875C7.55208 10.1667 7.54167 10.1458 7.53125 10.125H2.14062C2.06771 10.1667 1.99479 10.2083 1.92188 10.25C1.84896 10.2917 1.78125 10.3333 1.71875 10.375ZM4 3.625C4 3.66667 4 3.70833 4 3.75C4 3.79167 4 3.83333 4 3.875H7.34375C7.34375 3.86458 7.34896 3.85417 7.35938 3.84375C7.36979 3.8125 7.38542 3.77604 7.40625 3.73438C7.42708 3.69271 7.44792 3.65625 7.46875 3.625H4ZM0.171875 12.125C0.151042 12.1667 0.135417 12.2083 0.125 12.25C0.114583 12.2917 0.0989583 12.3333 0.078125 12.375H5.1875C5.26042 12.3333 5.33333 12.2917 5.40625 12.25C5.47917 12.2083 5.55208 12.1667 5.625 12.125H0.171875ZM4.64062 0.875H9.35938C9.31771 0.833333 9.27604 0.791667 9.23438 0.75C9.20312 0.708333 9.16667 0.666667 9.125 0.625H4.875C4.83333 0.666667 4.79167 0.708333 4.75 0.75C4.71875 0.791667 4.68229 0.833333 4.64062 0.875ZM0.609375 11.375H7.45312C7.57812 11.3333 7.70312 11.2969 7.82812 11.2656C7.96354 11.2344 8.09896 11.2031 8.23438 11.1719C8.22396 11.1615 8.21354 11.1562 8.20312 11.1562C8.20312 11.1458 8.20312 11.1354 8.20312 11.125H0.8125C0.78125 11.1667 0.744792 11.2083 0.703125 11.25C0.671875 11.2917 0.640625 11.3333 0.609375 11.375ZM5.25 7.625C5.29167 7.66667 5.33333 7.70833 5.375 7.75C5.42708 7.79167 5.47396 7.83333 5.51562 7.875H6.89062C6.89062 7.83333 6.88542 7.79167 6.875 7.75C6.875 7.70833 6.875 7.66667 6.875 7.625H5.25ZM4.84375 7.125C4.875 7.16667 4.90625 7.20833 4.9375 7.25C4.96875 7.29167 5 7.33333 5.03125 7.375H6.875C6.875 7.33333 6.875 7.29167 6.875 7.25C6.875 7.20833 6.875 7.16667 6.875 7.125H4.84375ZM4.25 1.625C4.23958 1.66667 4.22917 1.70833 4.21875 1.75C4.20833 1.79167 4.19792 1.83333 4.1875 1.875H9.8125C9.80208 1.83333 9.79167 1.79167 9.78125 1.75C9.77083 1.70833 9.76042 1.66667 9.75 1.625H4.25ZM4.125 2.125C4.125 2.16667 4.11979 2.20833 4.10938 2.25C4.09896 2.29167 4.09375 2.33333 4.09375 2.375H8.71875C8.80208 2.32292 8.89062 2.27604 8.98438 2.23438C9.08854 2.19271 9.19271 2.15625 9.29688 2.125H4.125ZM4.01562 3.125C4.01562 3.16667 4.01562 3.20833 4.01562 3.25C4.01562 3.29167 4.01562 3.33333 4.01562 3.375H7.60938C7.64062 3.33333 7.67188 3.29167 7.70312 3.25C7.73438 3.20833 7.76562 3.16667 7.79688 3.125H4.01562ZM4.0625 2.625C4.05208 2.66667 4.04688 2.70833 4.04688 2.75C4.04688 2.79167 4.04167 2.83333 4.03125 2.875H8.03125C8.07292 2.83333 8.11979 2.79167 8.17188 2.75C8.22396 2.70833 8.27604 2.66667 8.32812 2.625H4.0625ZM5.85938 0.125C5.74479 0.15625 5.63021 0.192708 5.51562 0.234375C5.41146 0.276042 5.31771 0.322917 5.23438 0.375H8.76562C8.68229 0.322917 8.58333 0.276042 8.46875 0.234375C8.36458 0.192708 8.25 0.15625 8.125 0.125H5.85938ZM4.54688 6.625C4.56771 6.66667 4.58854 6.70833 4.60938 6.75C4.63021 6.79167 4.65625 6.83333 4.6875 6.875H6.875C6.875 6.83333 6.875 6.79167 6.875 6.75C6.875 6.70833 6.875 6.66667 6.875 6.625H4.54688ZM4.46875 1.125C4.44792 1.16667 4.42708 1.20833 4.40625 1.25C4.38542 1.29167 4.36458 1.33333 4.34375 1.375H9.65625C9.63542 1.33333 9.61458 1.29167 9.59375 1.25C9.57292 1.20833 9.55208 1.16667 9.53125 1.125H4.46875ZM4.15625 5.625C4.16667 5.66667 4.17708 5.70833 4.1875 5.75C4.19792 5.79167 4.21354 5.83333 4.23438 5.875H6.90625C6.90625 5.83333 6.90625 5.79167 6.90625 5.75C6.91667 5.70833 6.92188 5.66667 6.92188 5.625H4.15625ZM4.3125 6.125C4.33333 6.16667 4.34896 6.20833 4.35938 6.25C4.38021 6.29167 4.40104 6.33333 4.42188 6.375H6.89062C6.89062 6.33333 6.89062 6.29167 6.89062 6.25C6.89062 6.20833 6.89062 6.16667 6.89062 6.125H4.3125ZM4 4.125C4 4.16667 4 4.20833 4 4.25C4 4.29167 4 4.33333 4 4.375H7.15625C7.16667 4.33333 7.17708 4.29167 7.1875 4.25C7.20833 4.20833 7.22396 4.16667 7.23438 4.125H4ZM4 4.625C4 4.66667 4 4.70833 4 4.75C4.01042 4.79167 4.01562 4.83333 4.01562 4.875H7.03125C7.04167 4.83333 7.05208 4.79167 7.0625 4.75C7.07292 4.70833 7.08333 4.66667 7.09375 4.625H4ZM4.04688 5.125C4.05729 5.16667 4.0625 5.20833 4.0625 5.25C4.07292 5.29167 4.08333 5.33333 4.09375 5.375H6.95312C6.96354 5.33333 6.96875 5.29167 6.96875 5.25C6.97917 5.20833 6.98438 5.16667 6.98438 5.125H4.04688Z" fill="white"/>
            </svg>

            <?= $arResult['CURRENT_COMPANY']?>
            <?if(!empty($arResult['COMPANIES']) && count($arResult['COMPANIES'])>1):?>
                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="6" viewBox="0 0 8 6" fill="none">
                    <path d="M6.73438 1.07812C6.83854 0.973958 6.96875 0.921875 7.125 0.921875C7.28125 0.921875 7.41146 0.973958 7.51562 1.07812C7.63021 1.18229 7.6875 1.3125 7.6875 1.46875C7.6875 1.61458 7.63021 1.74479 7.51562 1.85938L4.39062 4.92188C4.28646 5.02604 4.15625 5.07812 4 5.07812C3.84375 5.07812 3.71354 5.02604 3.60938 4.92188L0.484375 1.85938C0.380208 1.74479 0.328125 1.61458 0.328125 1.46875C0.328125 1.3125 0.380208 1.18229 0.484375 1.07812C0.588542 0.973958 0.71875 0.921875 0.875 0.921875C1.03125 0.921875 1.16146 0.973958 1.26562 1.07812L4 3.59375L6.73438 1.07812Z" fill="white"/>
                </svg>
            <?endif;?>
        </div>
        <? if (!empty($arResult['COMPANIES'])) { ?>
            <div class="auth-company-change__list-wrapper">
                <ul class="auth-company-change__list">
                    <? foreach ($arResult['COMPANIES'] as $company) {
                        if($company["ID_COMPANY"] == $_SESSION['AUTH_COMPANY_CURRENT_ID'])
                            continue;
                        ?>
                        <li class="auth-company-change__item"
                            data-company-id="<?= $company['ID_COMPANY'] ?>"
                            onclick=setCompanyID(<?= $company['ID_COMPANY'] ?>)>
                            <?= $company['COMPANY_NAME'] ?>
                        </li>
                        <?
                    } ?>
                </ul>
            </div>
        <? } ?>
    </div>
<?endif;?>
