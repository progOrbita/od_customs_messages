if ($('#checkout-delivery-step').hasClass('js-current-step') || $('#checkout-payment-step').hasClass('js-current-step')) {
    addCustomMessage();
    if ($('#checkout-delivery-step').hasClass('js-current-step')) {
        $('.od_custom_message').css("display", "");
    }
}

$(document).on("click", ".step-edit", function () {
    addCustomMessage();
    if ($('#checkout-delivery-step').hasClass('js-current-step')) {
        $('.od_custom_message').css("display", "");
    }
})

/**
 * function to add the custom message
 */
function addCustomMessage() {
    if ($('#checkout-delivery-step').hasClass('-reachable')) {
        $('div').remove('.od_custom_message');
        $('#checkout-delivery-step h1').before("<div class='od_custom_message'>" + window.msg + "</div>");
        $('.od_custom_message').css("display", "none");
    }
}