
document.addEventListener("DOMContentLoaded", function () {
    const popup = document.querySelector(".popup-company-choose-wrap");
    const closeBtnPopup = document.querySelector(".popup-company-choose__close-btn");
    const itemCompany = document.querySelectorAll('.company-item');
    const inputSearch = document.querySelector('.company-choose__search');

    if(popup){
        appendPopup(popup);
        popup.addEventListener('click', function(e){
            if(e.target.closest('.popup-company-choose') === null){
                closePopup(popup);
            }
        });
    }

    if(closeBtnPopup){
        closeBtnPopup.addEventListener("click", function(){
            closePopup(popup);
        });
    }

    if(inputSearch){
        inputSearch.addEventListener("input", searchCompany);
    }

    for (var i = 0; i<itemCompany.length; i++){
        itemCompany[i].addEventListener("mouseover", showHint);
        itemCompany[i].addEventListener("mouseout", hideHint);
        itemCompany[i].addEventListener("click", setCompanyID);
    }

    function showHint(){
        console.log(this.querySelector(".current-company__hint"));
        let hintCurrent = this.querySelector(".current-company__hint");
        if(!hintCurrent) {
            this.querySelector(".company-item__hint").style.display = "inline";
        }
        else {
            hintCurrent.textContent = 'продолжить работу';
        }
    }
    function hideHint(){
        let hintCurrent = this.querySelector(".current-company__hint");
        if(!hintCurrent) {
            this.querySelector(".company-item__hint").style.display = "none";
        }
        else {
            hintCurrent.textContent = 'текущая компания';
        }
    }

    function appendPopup(popup){
       document.body.append(popup);
    }
    function closePopup(popup){
        popup.style.display = "none";
    }

    function setCompanyID() {
        var blockCompany = this;
        let isCurrentCompany = this.getAttribute("data-current-company");
        if(isCurrentCompany){
            closePopup(popup);
        }
        else {
            let companyId = this.getAttribute("data-company-id");
            BX.showWait();

            var request = BX.ajax.runComponentAction('sotbit:auth.company.choose', 'changeCompany', {
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
                        blockCompany.classList.add("company-not-found");
                        BX.closeWait();
                    }
                }

            });
        }
    }

    function searchCompany() {
        const companyItem = document.querySelectorAll('.company-item');
        for (let i = 0; i < companyItem.length; i++) {
            let nameCompany = companyItem[i].querySelector('.company-item__name-company span');

            if (!nameCompany.textContent.toLowerCase().includes(this.value.toLowerCase())) {
                companyItem[i].style.display = 'none';
            } else {
                companyItem[i].style.display = 'flex';
            }
        }
    }

    window.addEventOpenPopup = function () {
        if(popup.parentElement !== document.body) {
            document.body.append(popup);
        }
        document.querySelector(".popup-company-choose-wrap").style.display = "flex";
    }
});

