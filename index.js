$(document).ready(function () {
    if (frm.location)
        // Load content from location
        frm.ss.engine.load(frm.location.pathname + frm.location.search);
    else
        // Load default content + search
        frm.ss.engine.load(frm.config.url.home + window.location.search);

    // Set overall header title
    $('.navbar-brand').find('[name="title"]').text(frm.label.getStatic('i-title'));
    // Set overall footer
    $('footer').find('[name="www"]').attr('href', frm.config.url.www);
    $('footer').find('[name="www"]').find('img').attr('src', frm.config.logo.variant.sprintf([frm.label.lang]));
    $('footer').find('[name="privacy"]').attr('href', frm.config.url.privacy);
    $('footer').find('[name="contactus"]').attr('href', 'mailto:' + frm.config.email.timetable[0]);

    // Bind navbar
    $('#navbar-menu, #breadcrumb, footer').find('[name="home"]').once('click', function (e) {
        e.preventDefault();
        frm.ss.engine.load(frm.config.url.home);
    });
    $('#navbar-menu, footer').find('[name="lecture"]').once('click', function (e) {
        e.preventDefault();
        frm.ss.engine.load(frm.config.url.lecture);
    });
    $('#navbar-menu, footer').find('[name="venue"]').once('click', function (e) {
        e.preventDefault();
        frm.ss.engine.load(frm.config.url.venue);
    });
    $('#navbar-menu, footer').find('[name="department"]').once('click', function (e) {
        e.preventDefault();
        frm.ss.engine.load(frm.config.url.department);
    });
    $('#navbar-menu, footer').find('[name="student"]').once('click', function (e) {
        e.preventDefault();
        if (frm.msal.isAuthenticated())
            frm.ss.engine.load(frm.config.url.student);
        else
            frm.msal.login();
    });

    // Bind buttons
    $('button[name="admin"').once('click', function () {
        if (frm.msal.isAuthenticated())
            frm.ss.engine.load(frm.config.url.admin);
        else
            frm.msal.login();
    });
    $('button[name="draft"').once('click', frm.msal.login);
    $('button[name="logout"').once('click', frm.msal.logout);

    // Set language
    $('#navbarLanguage').find('span').text(frm.label.getStatic(frm.config.language[frm.label.lang]));
    // Populate languages
    $.each(frm.config.language, function (lang, language) {
        $('[aria-labelledby="navbarLanguage"]').append(
            $('<li>', {
                html: $('<a>', {
                    class: 'dropdown-item' + (lang == frm.label.lang ? ' active' : ''),
                    href: '#',
                    text: frm.label.getStatic(language)
                }).attr('lang', lang)
            })
        );
    });
    // Bind language
    $('[aria-labelledby="navbarLanguage"]').find('a').once('click', function (e) {
        e.preventDefault();
        // Store lang
        frm.label.storeLang($(this).attr('lang'), true);
        // Reload
        window.location.href = frm.uri.getHashlessURL();
    });

    // Build social
    if (frm.config.social.lenght)
        frm.config.social.forEach(social => {
            $('#social').append($('<a>', {
                href: social.href,
                target: '_blank',
                title: social.title,
                class: 'text-light p-2',
                html: $('<i>', {
                    class: social.icon
                })
            }));
        });
    else
        $('#social').parent().hide();
});