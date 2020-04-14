$(function() {
    var alreadyFilled = false;
    var states = ['CBC/CP/pBood Film','TC,DC,Hb% ESR','MP','C.E.Count','LE Cell','BT/CT','APTT','Prothrombin Time','Platelet Count','T3','FT3','T4','FT4','Prolactin','Estradiol','Progesterone','Testosterone','Growth Hormone','TSH','LH','LH','FSH','Cortisol','Tuberculin Test/MT','Semen Analysis','Skin/Nail Scraping'];
	
    function initDialog() {
        clearDialog();
        for (var i = 0; i < states.length; i++) {
            $('.dialog').append('<div>' + states[i] + '</div>');
        }
    }
    function clearDialog() {
        $('.dialog').empty();
    }
    $('.test_name input').click(function() {
        if (!alreadyFilled) {
            $('.dialog').addClass('open');
        }

    });
    $('body').on('click', '.dialog > div', function() {
        $('.test_name input').val($(this).text()).focus();
        $('.test_name .close').addClass('visible');
        alreadyFilled = true;
    });
    $('.test_name .close').click(function() {
        alreadyFilled = false;
        $('.dialog').addClass('open');
        $('.test_name input').val('').focus();
        $(this).removeClass('visible');
    });

    function match(str) {
        str = str.toLowerCase();
        clearDialog();
        for (var i = 0; i < states.length; i++) {
            if (states[i].toLowerCase().startsWith(str)) {
                $('.dialog').append('<div>' + states[i] + '</div>');
            }
        }
    }
    $('.test_name input').on('input', function() {
        $('.dialog').addClass('open');
        alreadyFilled = false;
        match($(this).val());
    });
    $('body').click(function(e) {
        if (!$(e.target).is("input, .close")) {
            $('.dialog').removeClass('open');
        }
    });
    initDialog();
	
});