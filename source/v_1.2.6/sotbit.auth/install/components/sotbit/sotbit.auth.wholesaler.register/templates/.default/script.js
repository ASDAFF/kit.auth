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