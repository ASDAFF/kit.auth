$(document).ready(function() {
    var joinButton = document.querySelector('.company-list__group-button .index_company-join_organization-button');
    joinButton.addEventListener('click', showFormJoinCompany);
    var actionButton = document.querySelectorAll('.main-grid-row-action-button');
    for (let i=0; i<actionButton.length; i++){
        let dataAction = actionButton[i].getAttribute("data-actions");
        if(dataAction.includes("deactivate")){
            actionButton[i].style.pointerEvents = "none";
        }

    }
});

function showFormJoinCompany() {
    let popapJoinForm = document.querySelector('.popup-join-company');
    popapJoinForm.style.display = 'flex';
}

