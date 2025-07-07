// Init
var frm = frm || {};

/*******************************************************************************
Framework - Spinner
*******************************************************************************/
frm.spinner = {};
frm.spinner.count = 0;

/**
 * Show the Overlay and load the Spinner
 */
frm.spinner.start = async function (buyTime) {
    buyTime = buyTime || false;

    if (!frm.spinner.count++) {
        $("#ss-overlay").show();
        $("body").css("cursor", "progress");
        frm.spinner.load();

        if (buyTime) {
            // Give time before ramping up high CPU process
            await frm.sleep();
        }
    }
};

/**
 * Load the Spinner, pulsing
 */
frm.spinner.load = function () {
    $("#ss-overlay .spinner").fadeIn(800, function () {
        $("#ss-overlay .spinner").fadeOut(800);
        if (frm.spinner.count)
            frm.spinner.load();
    });
};

/**
 * Hide the Overlay and stop the Spinner
 */
frm.spinner.stop = function () {
    if (frm.spinner.count) {
        // Do not go negative
        frm.spinner.count--;
    }

    if (!frm.spinner.count) {
        // Close the spinner
        $("#ss-overlay").fadeOut('slow');
        $("body").css("cursor", "default");
    }
};

/**
 * Stop all Spinners
 */
frm.spinner.clear = function () {
    // reset spinner count
    frm.spinner.count = 0;

    // Close the spinner
    $("#ss-overlay").fadeOut('slow');
    $("body").css("cursor", "default");
};