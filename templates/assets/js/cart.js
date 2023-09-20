jQuery('<span class="dec qtybtn">-</span>').insertBefore('.pro-qty input');
jQuery('<span class="inc qtybtn">+</span>').insertAfter('.pro-qty input');
jQuery('.pro-qty').each(function ()


{
    var spinner = jQuery(this),
            input = spinner.find('input[type="text"]'),
            btnUp = spinner.find('.inc'),
            btnDown = spinner.find('.dec'),
            min = input.attr('min'),
            max = input.attr('max');
    btnUp.click(function () {
//        console.log('clicou em +');
        var oldValue = parseFloat(input.val());
        if (oldValue >= max) {
            var newVal = oldValue;
        } else {
            var newVal = oldValue + 1;
        }
        spinner.find("input").val(newVal);
        document.getElementById('txtquantidade').value = newVal;
        spinner.find("input").trigger("change");
    });
    btnDown.click(function () {
//        console.log('clicou em -');

        var oldValue = parseFloat(input.val());
        if (oldValue <= min) {
            var newVal = oldValue;
        } else {
            var newVal = oldValue - 1;
        }
        spinner.find("input").val(newVal);
        document.getElementById('txtquantidade').value = newVal;
        spinner.find("input").trigger("change");
    });
});



