$(document).on("change keyup input", "#INN", function(){
	var val = $(this).val();
	$('#NAME').val(val);
});
$(document).on("change", "#person-type", function(){
    var post = 'change_person_type=' + this.value;
    if(this.value !== '') {
        $('#change_person_type').val(true);
        $('#PERSON_TYPE').val(this.value);
    }
});


function submitForm() {
    if(!document.querySelector('.main-user-consent-request input').checked){
        return;
    }
    let companyId = getGet('EDIT_ID');
    if(!companyId){
        formData.append('save','Y');
        document.addOrg.submit();
        return;
    }
    BX.showWait();
    let formData = new FormData(document.addOrg);
    formData.append('EDIT_ID',companyId);
    formData.append('save','Y');
    var request = BX.ajax.runComponentAction('kit:auth.company.add', 'checkFields', {
        mode: 'class',
        data: formData
    });

    request.then(function (response) {
        if(response.data == "Y"){
            BX.closeWait();
            let confirmResult = confirm(title_send_moderation);
            if (confirmResult == false) return false;
            else {
                document.getElementById("apply").value = "Y";
                document.addOrg.submit();
            }
        }
        else {
            document.getElementById("apply").value = "N";
            BX.closeWait();
            document.addOrg.submit();
        }
    });
}

function getGet(name) {
    var s = window.location.search;
    s = s.match(new RegExp(name + '=([^&=]+)'));
    return s ? s[1] : false;
}

function goToList() {
    document.location.href = path_to_list;
}