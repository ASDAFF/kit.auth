
$(document).ready(function(){
    var referralSubmit = document.querySelector('input[name="referral_submit_button"]'),
     referralExit = document.querySelector('input[name="referral_exit_button"]'),
     referralForm = document.querySelector('.referral-form'),
     registerForm = document.querySelector('form[name="regform"]'),
     confirmBtn = document.querySelector('.confirm-form-add .btn_confirm'),
     confirmBlock = document.querySelector('.confirm-form-add'),
     registerErrorBlock = document.querySelector('.regform-error'),
     confirmCancel = document.querySelector('.confirm-form-add .btn_cancel'),
     referralLink = document.querySelector('a.register-referral-link'),
     blockSucñess = document.querySelector('.success-form-add'),
     registerSubmit = document.querySelector('input[name="register_submit_button"]'),
     btnOk = document.querySelector('.success-form-add .btn_ok');
    btnOk.addEventListener("click", closeModal);


    btnOk.onclick= function() {
        registerErrorBlock.innerHTML = '';
        blockSucñess.style.display = "none";
        registerForm.style.display = "block";
        closeModal();
    };
   
    referralLink.onclick = function() {
        registerForm.style.display = "none";
        referralForm.style.display = "block";
    };

    referralSubmit.onclick = function() {
        BX.showWait();
        registerFormData = new FormData(document.querySelector('#referralform'));
        var errorReferal = document.querySelector('.referral-form .error-block');
        var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.register', 'sendReferralForm', {
            mode: 'class',
            data: registerFormData,
        });

        request.then(function (response) {
            if (response.data.error === true) {
                if(response.data.userId){
                    referralForm.style.display = "none";
                    confirmBlock.style.display = "block";
                }
                else {
                    let erorText = '<div class="bitrix-error"><label class="validation-invalid-label errortext">' + response.data.errorMessage+ '</label></div>'
                    errorReferal.innerHTML = erorText;
                }
            } else {
                document.querySelector('#referralform').reset();
                showSuccessForm(referralForm, response.data.successMessage);
            }
            BX.closeWait();
        });
    };

    referralExit.onclick = function() {
        referralForm.style.display = "none";
        registerForm.style.display = "block";
    };

    registerSubmit.onclick = function() {
        BX.showWait();
        registerFormData = new FormData(registerForm);
        var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.register', 'sendForm', {
            signedParameters: window.arParams,
            mode: 'class',
            data: registerFormData,
        });
        request.then(function (response) {
            if (response.data.error === true) {
                if(response.data.userId){
                    registerForm.style.display = "none";
                    confirmBtn.setAttribute('data-staff-id', response.data.userId);
                    confirmBlock.style.display = "block";
                }
                else {
                    let erorTextForm = '';
                    for (var key in response.data.errorMessage) {
                        erorTextForm += '<div class="bitrix-error"><label class="validation-invalid-label errortext">' + response.data.errorMessage[key]+ '</label></div>'
                    }
                    registerErrorBlock.innerHTML = erorTextForm;
                }
            }
            else {
                showSuccessForm(registerForm, response.data.successMessage);
            }
            BX.closeWait();
        });
    };

    confirmBtn.onclick = function() {
        BX.showWait();

        var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.register', 'confirmStaff', {
            signedParameters: window.arParams,
            mode: 'class',
            data: registerFormData
        });

        request.then(function (response) {
            if (response.data.error === false) {
                showSuccessForm(confirmBlock, response.data.successMessage);
            } else {
                registerErrorBlock.innerHTML = '<div class="bitrix-error"><label class="validation-invalid-label errortext">' + response.data.errorMessage+ '</label></div>';
                confirmBlock.style.display = "none";
                registerForm.style.display = "block";
            }
            BX.closeWait();
        });
    };

    confirmCancel.onclick = function() {
        registerErrorBlock.innerHTML = "";
        confirmBlock.style.display = "none";
        registerForm.style.display = "block";
    };

    function showSuccessForm(obj, title) {
        let titleSucñess = document.querySelector('.success-form-add p');
        registerForm.reset();
        titleSucñess.innerHTML = title;
        obj.style.display = "none";
        blockSucñess.style.display = "block";
    }

    document.querySelector('input[name="register_reset-form"]').onclick = function () {
        registerErrorBlock.innerHTML = "";
        closeModal();
    };
});

function closeModal() {
    document.querySelector('.regform-error').innerHTML = "";
    document.querySelector('.success-form-add').style.display = "none";
    document.querySelector('.confirm-form-add').style.display = "none";
    document.querySelector('.referral-form').style.display = "none";
    document.querySelector('form[name="regform"]').style.display = "block";
    document.querySelector('.popup-staff-register').style.display = "none";
    document.body.style.overflow = 'visible';
}