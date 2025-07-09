// Init
var frm = frm || {};

/*******************************************************************************
Framework - Modal
*******************************************************************************/
frm.modal = {};

/**
 * Pop a Confirm Modal in Bootstrap
 * @param {*} message 
 * @param {*} callbackMethod 
 * @param {*} callbackParams 
 */
frm.modal.confirm = function (message, callbackMethod, callbackParams) {
    // Set the body of the Modal - Empty the container first
    $("#modal-confirm").find(".modal-body > p").empty().html(message);

    $("#modal-confirm").find("[name=confirm]").once("click", function () {
        // Must wait for the async transition to finsh before invoking the callback function that may be a cascade confirm
        // https://stackoverflow.com/questions/10860171/run-function-after-delay
        $("#modal-confirm").modal('hide').delay(100).queue(function () {
            $(this).dequeue();
            callbackMethod(callbackParams);
        });
    });

    // Force the modal to re-initialise before displaying in case of cascade confirm modals
    $("#modal-confirm").modal('show');
};

/**
 * Pop a Success Modal in Bootstrap
 * @param {*} message 
 */
frm.modal.success = function (message) {

    // Set the body of the Modal
    $("#modal-success").find(".modal-body > p").empty().html(message);

    // Display the Modal
    $("#modal-success").modal('show');
};

/**
 * Pop an Error Modal in Bootstrap
 * @param {*} message 
 */
frm.modal.error = function (message) {
    var errorOutput = null;

    // Parse array or objcet of errors as well
    if (($.isArray(message) && message.length)
        || ($.isPlainObject(message) && !$.isEmptyObject(message))) {
        errorOutput = $("<ul>", {
            class: "list-group"
        });
        $.each(message, function (_index, value) {
            var error = $("<li>", {
                class: "list-group-item bg-light text-dark rounded-8 mb-1",
                html: value.toString()
            });
            errorOutput.append(error);
        });
    } else
        // Plain error
        errorOutput = message;

    // Set the body of the Modal
    $("#modal-error").find(".modal-body > p").empty().html(errorOutput);

    // Display the Modal
    $("#modal-error").modal('show');
};

/**
 * Pop an Information Modal in Bootstrap
 * @param {*} message 
 */
frm.modal.information = function (message) {

    // Set the body of the Modal
    $("#modal-information").find(".modal-body > p").empty().html(message);

    // Display the Modal
    $("#modal-information").modal('show');
};

/**
 * Pop an Error Modal in Bootstrap
 * @param {*} message 
 */
frm.modal.exception = function (message) {
    // Set the body of the Modal
    $("#modal-exception").find(".modal-body > p").empty().html(message);

    // Display the Modal
    $("#modal-exception").modal('show');
};

/**
 * Pop a Warning Modal in Bootstrap
 * @param {*} message 
 */
frm.modal.warning = function (message) {

    // Set the body of the Modal
    $("#modal-warning").find(".modal-body > p").empty().html(message);

    // Display the Modal
    $("#modal-warning").modal('show');
};

/**
 * Fix issues with printing modals
 */
frm.modal.fixPrint = function () {

    // Hide unnecessary elements for printing
    $('body').on('show.bs.modal', function (e) {
        $('#navbar, #breadcrumb, #ss-container, #ss-overlay, footer').addClass('d-print-none');
    });

    // Restore unnecessary elements for printing
    $('body').on('hide.bs.modal', function (e) {
        //update non modal divs to print css
        $('#navbar, #breadcrumb, #ss-container, #ss-overlay, footer').removeClass('d-print-none');

    });

    // Fix printing responsive table
    $(window).on("beforeprint", function () {
        $(".table-responsive").removeClass("table-responsive");
    });
};