/*******************************************************************************
App - Initialise - Loads
*******************************************************************************/

// Load resources
$(document).ready(function () {
    // Toasts
    frm.ajax.content.load("#toast", "./toast.html");
    // Timetable templates
    frm.ajax.content.load("#timetable-compact", "./timetable.compact.html");
    frm.ajax.content.load("#timetable-full", "./timetable.full.html");
    // Modal templates
    frm.ajax.content.load("#modal-app", "./modal.app.html");
    frm.ajax.content.load("#modal-frm", "./modal.frm.html");
});