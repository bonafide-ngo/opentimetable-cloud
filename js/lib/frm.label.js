// Init
var frm = frm || {};

/*******************************************************************************
Framework - Label
*******************************************************************************/
frm.label = {};
frm.label.lang = null;
frm.label.dictionary = {};

/**
 * Load a language
 */
frm.label.loadLang = function () {
    // Read language from GET or Cookie
    var targetLang = frm.uri.getParam('lang') || Cookies.get(frm.config.cookie.property.user.lang.name);
    if (!targetLang)
        // Get system language otherwise
        targetLang = window.navigator.language.substring(0, 2);

    // Reset Cookie language in case the target language fails
    frm.crypto.setCookie(frm.config.cookie.property.user.lang, frm.config.lang);

    // Store the reset language for later use
    frm.label.lang = frm.config.lang;

    // Load the default language
    $.when(
        frm.ajax.config("/lang/" + frm.config.lang + ".base.json"),
        frm.ajax.config("/lang/" + frm.config.lang + ".instance.json")
    ).done(function (baseLang, instanceLang) {
        $.extend(true, frm.label.dictionary, baseLang[0], instanceLang[0]);
    });

    // Attempt to merge the target language if different from the default one
    if (targetLang != frm.config.lang) {
        $.when(
            frm.ajax.config("/lang/" + targetLang + ".base.json"),
            frm.ajax.config("/lang/" + targetLang + ".instance.json")
        ).done(function (baseLang, instanceLang) {
            // Extend lable sourced form target language
            $.extend(true, frm.label.dictionary, baseLang[0], instanceLang[0]);

            // Set the target language in the cookie
            frm.crypto.setCookie(frm.config.cookie.property.user.lang, targetLang);

            // Store for later use
            frm.label.lang = targetLang;
        });
    }

    // Set moment locale
    moment.locale(frm.label.lang);
}

/**
 * Store a language
 */
frm.label.storeLang = function (lang, sync) {
    lang = lang || frm.config.lang;
    sync = sync || false;

    // Set the language in the cookie
    frm.crypto.setCookie(frm.config.cookie.property.user.lang, lang);
}

/**
 * Parse the Static Labels into the HTML context
 * @param {*} keyword 
 * @returns 
 */
frm.label.getStatic = function (keyword) {
    keyword = keyword || null;

    /**
     * Return first an instance keyword then a static one, else [keyword]
     * @param {*} keyword 
     */
    function seek(keyword) {
        if (frm.label.dictionary.instance[keyword])
            return frm.label.dictionary.instance[keyword];
        else if (frm.label.dictionary.static[keyword])
            return frm.label.dictionary.static[keyword];
        else
            return "[" + keyword + "]";
    }

    if (keyword)
        return seek(keyword);
    else {
        // Parse all Labels and PopOvers in the DOM
        $("[label], [label-alt], [label-title], [label-content], [label-placeholder]").each(function (index) {
            // Get the keyword if exists in the lang
            var keywordLabel = $(this).attr("label");
            var keywordAlt = $(this).attr("label-alt");
            var keywordTitle = $(this).attr("label-title");
            var keywordContent = $(this).attr("label-content");
            var keywordPlaceholder = $(this).attr("label-placeholder");

            var valueLabel = seek(keywordLabel);
            var valueAlt = seek(keywordAlt);
            var valueTitle = seek(keywordTitle);
            var valueContent = seek(keywordContent);
            var valuePlaceholder = seek(keywordPlaceholder);

            if (keywordLabel) {
                // Parse label
                $(this).html(valueLabel);
                // Remove the attribute to avoid double-parsing
                $(this).removeAttr("label");
            }

            if (keywordAlt) {
                // Parse alt
                $(this).attr('alt', valueAlt);
                // Remove the attribute to avoid double-parsing
                $(this).removeAttr("label-alt");
            }

            if (keywordTitle) {
                // Parse title
                $(this).attr("data-bs-title", valueTitle);
                // Remove the attribute to avoid double-parsing
                $(this).removeAttr("label-title");
            }

            if (keywordContent) {
                // Parse content
                $(this).attr("data-bs-content", valueContent);
                // Remove the attribute to avoid double-parsing
                $(this).removeAttr("label-content");
            }

            if (keywordPlaceholder) {
                // Parse content
                $(this).attr("placeholder", valuePlaceholder);
                // Remove the attribute to avoid double-parsing
                $(this).removeAttr("label-placeholder");
            }
        });
    }

};

/**
 * Parse the Dynamic Labels into the HTML context
 * 
 * @param {*} keyword 
 * @param {*} params 
 * @returns 
 */
frm.label.parseDynamic = function (keyword, params) {
    params = params || [];

    var label = frm.label.dictionary.dynamic[keyword];

    // Check if the label is an array of labels to join
    if (Array.isArray(label)) {
        // Join elements into string
        label = label.join('');
    }

    if (label)
        return label.sprintf(params);
    else
        return "[" + keyword + "]";
};

/**
 * Set the page title
 * 
 * @param {*} title 
 */
frm.label.setMetaTitle = function (title) {
    title = title || null;
    homeTitle = frm.label.getStatic('i-title');

    title = title && title != homeTitle ? homeTitle + ' - ' + title : homeTitle;
    $("title").text(frm.stripHTML(title));
}

/**
 * Set the meta description
 * 
 * @param {*} description 
 */
frm.label.setMetaDescription = function (description) {
    description = description || frm.label.getStatic('i-description');
    $("meta[name='description']").attr("content", frm.stripHTML(description));
}

/**
 * Set the meta language
 * 
 * @param {*} description 
 */
frm.label.setMetaLang = function (lang) {
    lang = lang || frm.label.lang;
    $("html").attr("lang", lang);
}

/**
 * Se alternate links language based
 */
frm.label.setAlternateLinks = function () {
    $('html, head').children('link[rel="alternate"]').remove();
    $.each(frm.config.language, function (lang, language) {
        $('html, head').prepend($('<link>', {
            rel: "alternate",
            hreflang: lang,
            href: frm.uri.addParam('lang', lang)
        }));
    });
}

/**
 * Set Open Graph properties
 * For sharing only, not suitable for social web crawlers not processing javascript
 * @param {*} title 
 * @param {*} description 
 * @param {*} url 
 */
frm.label.setOG = function (title, description, url) {
    url = url || window.location.href;

    // Open Graph
    $('meta[property="og:title"]').attr('content', title)
    $('meta[property="og:description"]').attr('content', description)
    $('meta[property="og:url"]').attr('content', url);
    $('meta[property="og:image"]').attr('content', frm.config.logo.og);
    $('meta[property="og:locale"]').attr('content', frm.label.lang);

    // Twitter
    $('meta[name="twitter:title"]').attr('content', title);
    $('meta[name="twitter:description"]').attr('content', description);
    $('meta[property="twitter:domain"]').attr('content', URI(url).hostname());
    $('meta[property="twitter:url"]').attr('content', url);
    $('meta[name="twitter:image"]').attr('content', frm.config.logo.og);
}

/**
 * Format time according to the locale
 * Matching PHP /Lang::FormatTime
 * @param {*} seconds 
 * @param {*} toLowerCase 
 * @returns 
 */
frm.label.formatTime = function (seconds, toLowerCase) {
    seconds = seconds || 0;
    toLowerCase = toLowerCase || false;

    var output = '';
    if (seconds / 60 < 1)
        // Seconds
        output = seconds.toFixed(0).toLocaleString(frm.label.lang) + ' ' + frm.label.getStatic('seconds');
    else if (seconds / 60 / 60 < 1)
        // Minutes
        output = (seconds / 60).toFixed(0).toLocaleString(frm.label.lang) + ' ' + frm.label.getStatic('minutes');
    else if (seconds / 60 / 60 / 24 < 1)
        // Hours
        output = (seconds / 60 / 60).toFixed(0).toLocaleString(frm.label.lang) + ' ' + frm.label.getStatic('hours');
    else
        // Days
        output = (seconds / 60 / 60 / 24).toFixed(0).toLocaleString(frm.label.lang) + ' ' + frm.label.getStatic('days');

    return toLowerCase ? output.toLowerCase() : output;
}
