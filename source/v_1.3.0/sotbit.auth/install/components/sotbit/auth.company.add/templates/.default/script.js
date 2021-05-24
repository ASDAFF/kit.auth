$(document).on("change keyup input", "#INN", function(){
	var val = $(this).val();
	$('#NAME').val(val);
});
$(document).on("change", "#person-type", function(){
    form = $("#add-org");
    ajaxID = form.find("[name=bxajaxid]").attr("id");
    ajaxValue = form.find("[name=bxajaxid]").attr("value");
    var obForm = top.BX(ajaxID).form;
    BX.ajax.submitComponentForm(obForm, 'comp_'+ajaxValue, false);
    BX.submit(obForm, "change_person_type", "Y", function(){
        //ajaxFunction();
    });
});