$(document).ready(function() {
    var sendJoinForm = document.querySelector('input[name="company-join-send"]'),
        inputInn = document.querySelector('.join__search-company'),
        btnOk = document.querySelector('.btn_ok'),
        resetBtn = document.querySelector('input[type="reset"]'),
        companyItem = document.querySelectorAll('.select__company-item');
    if(sendJoinForm){
        sendJoinForm.addEventListener('click', joiningCompany);
    }
    if(inputInn){
        inputInn.addEventListener('input', searchCompany);
    }
    if(btnOk) {
        btnOk.addEventListener('click', closeSuccessPopup);
    }
    if(resetBtn){
        resetBtn.addEventListener('click', resetForm);
    }
    if(companyItem){
        for (let i = 0; i < companyItem.length; i++) {
            companyItem[i].addEventListener('click', checkCompany);
        }

    }
});

function checkCompany() {
   this.classList.toggle("checked");
}

function resetForm() {
    let select = document.querySelectorAll('.select__company-item');
    for (let i = 0; i < select.length; i++) {
        select[i].style.display = 'block';
        select[i].classList.remove("checked");
    }
    closeModal();
}

function closeSuccessPopup() {
    let successBlock = document.querySelector('.joinCompany__success-block');
    let formJoin = document.querySelector('form[name="joinCompany"]');
    let errorBlock = document.querySelector('.joinCompany__error-block');

    if(errorBlock)
        errorBlock.innerHTML = '';
    if(successBlock)
        successBlock.style.display='none';
    if(formJoin)
        formJoin.style.display='block';
    closeModal();
}

function closeModal() {
    let popapJoinForm = document.querySelector('.popup-join-company');
    let successBlock = document.querySelector('.joinCompany__success-block');
    let formJoin = document.querySelector('form[name="joinCompany"]');

    if(successBlock)
        successBlock.style.display = "none";
    if(formJoin)
        formJoin.style.display = "block";
    popapJoinForm.style.display = "none";
}

function searchCompany() {
    const select = this.parentNode.querySelectorAll('.select__company-item');
    for (let i = 0; i < select.length; i++) {
        if (!select[i]
            .textContent
            .toLowerCase()
            .includes(this.value.toLowerCase())) {
            select[i].style.display = 'none';
        } else {
            select[i].style.display = 'block';
        }
    }
}

function joiningCompany() {
    BX.showWait();
    let companyId = [];
    let companyChecked = document.querySelectorAll('.select__company-item.checked');
    for (let i = 0; i < companyChecked.length; i++) {
        companyId[i] = companyChecked[i].getAttribute("data-id");
    }

    let formJoin = document.querySelector('form[name="joinCompany"]');
    let errorBlock = document.querySelector('.joinCompany__error-block');

    var request = BX.ajax.runComponentAction('kit:auth.company.join', 'joiningCompany', {
        mode: 'class',
        data: {JOIN_COMPANY_ID: companyId }
    });

    request.then(function (response) {
        if (response.data.error === false) {
            errorBlock.innerHTML='';
            formJoin.style.display='none';
            document.querySelector('.joinCompany__success-block').style.display='block';
            const blockSelect = document.querySelector('.company-join__select-block');
            for (let i = 0; i < response.data.companyId.length; i++) {
                if (blockSelect.querySelector('.select__company-item.checked[data-id="' + response.data.companyId[i] + '"]')) {
                    blockSelect.querySelector('.select__company-item.checked[data-id="' + response.data.companyId[i] + '"]').remove();
                }
            }
        } else {
            if(response.data.errorMessage){
                errorBlock.innerHTML = '<div class="bitrix-error"><label class="validation-invalid-label errortext">' + response.data.errorMessage+ '</label></div>';
            }
        }
        BX.closeWait();
    });
}