$(document).ready(function(){
    var addStaffButton = document.querySelector('#staff-list__add-staff');
    if(addStaffButton){
        addStaffButton.addEventListener("click", showFormRegister);
    }
});


function showFormRegister() {
    document.body.style.overflow = 'hidden';
    document.querySelector('.popup-staff-register').style.display = "flex";
}

function logInUser($userId) {
    var request = BX.ajax.runComponentAction('sotbit:company.staff.list', 'logInUser', {
        mode: 'class',
        data: {
            userId: $userId
        }
    });

    request.then(function (response) {
        if (response.data.error === false) {
           window.location.replace(window.location.origin + window.location.pathname);
        } else {
            console.log(response.data.errorMessage);
        }
    });
}

function removeUserCompany(userTableId, companyId) {
    var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.list', 'removeUserCompany', {
        mode: 'class',
        data: {
            userTableId: userTableId,
            companyId: companyId,
        }
    });

    request.then(function (response) {
        if (response.data.error === false) {
            BX.Main.gridManager.reload('STAFF_LIST','');
        } else {
            console.log(response.data.errorMessage);
        }
    });
}

function confirmUser(userTableId, companyId) {
    var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.list', 'confirmUser', {
        mode: 'class',
        data: {
            userTableId: userTableId,
            companyId: companyId,
        }
    });

    request.then(function (response) {
        if (response.data.error === false) {
            BX.Main.gridManager.reload('STAFF_UNCONFIRMED_LIST','');
            BX.Main.gridManager.reload('STAFF_LIST','');
        } else {
            console.log(response.data.errorMessage);
        }
    });
}

function unconfirmUser(userTableId, companyId) {
    var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.list', 'unconfirmUser', {
        mode: 'class',
        data: {
            userTableId: userTableId,
            companyId: companyId,
        }
    });

    request.then(function (response) {
        if (response.data.error === false) {
            BX.Main.gridManager.reload('STAFF_UNCONFIRMED_LIST','');
        } else {
            console.log(response.data.errorMessage);
        }
    });
}

function showAllUsers() {
    var request = BX.ajax.runComponentAction('sotbit:auth.company.staff.list', 'showAllUsers', {
        mode: 'class',
    });

    request.then(function (response) {
            BX.Main.gridManager.reload('STAFF_LIST','');
    });
}