function setCompanyID(companyId) {
    BX.showWait();
    var request = BX.ajax.runComponentAction('kit:auth.company.choose', 'changeCompany', {
        mode: 'class',
        data: {
            companyID: companyId
        }
    });

    request.then(function (response) {
        if (response.data.error === false) {
            window.location.reload(false);
        } else {
            if(response.data.companyId){
                let node = document.querySelector(".auth-company-change__item[data-company-id='"+ response.data.companyId +"']");
                if(node){
                    node.classList.add("error-company");
                    node.removeAttribute("onclick");
                }
            }
        }
        BX.closeWait();
    });
}

function ToggleCompanyDropdown(selector) {

    let node = document.querySelector(selector);
    if (node) {
        if (node.classList.contains('open-dropdown')) {
            node.classList.remove('open-dropdown');
        } else {
            node.classList.add('open-dropdown');
        }
    }
}

document.onclick = function(e) {
    if(!e.target.classList.contains("auth-company-change__current")){
        let companyList = document.querySelector(".auth-company-change__list-wrapper");
        if(companyList.classList.contains('open-dropdown')){
            companyList.classList.remove('open-dropdown');
        }
    }
}

