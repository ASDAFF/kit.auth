$(document).ready(function(){
    $('.js_person_type .js_checkbox_person_type').click(function(){
        changePersonalBlock(this);
    });

    changePersonalBlock($('.js_person_type .js_checkbox_person_type:checked'));
});

function changePersonalBlock(obj) {
    let index = $('.js_person_type .js_checkbox_person_type').index(obj);
    $('.js_person_type .js_person_type_block').hide();
    $('.js_person_type .js_person_type_block').eq(index).show();
}

function sendForm() {
   BX.showWait();
    const registerForm = document.querySelector('.js_person_type_block[style=""] #company-register');
    const formData = new FormData(registerForm);
    const errorBlock = document.querySelector(".bitrix-error");
    let confirmJoin = registerForm.querySelector("#CONFIRM_JOIN");

    var request = BX.ajax.runComponentAction('sotbit:auth.company.register', 'registerCompany', {
        signedParameters: window.arParams,
        mode: 'class',
        data: formData,
    });
    request.then(function (response) {
        BX.closeWait();
        if(response.data.errors){
            errorBlock.innerHTML = response.data.errors;
            window.scroll(0,0);
        }
        if(response.data == "COMPANY_ISSET"){
            let confirmResult = confirm("Компания уже существует. Присоединиться?");
            if (confirmResult == false) {
                confirmJoin.value = "N";
            }
            else {
                confirmJoin.value = "Y";
                sendForm();
            }
        }
        else {
            if(response.data.message){
                document.querySelector('.card-body').remove();
                const successBlock = document.querySelector('.success-block__body');
                successBlock.querySelector('.success-block__title').innerHTML = response.data.message;
                successBlock.style.display = "block";
                if(response.data.authorize && response.data.authorize=="Y"){
                    successBlock.querySelector('.success-block__btn').style.display = "block";
                }
            }
        }
    });
}

