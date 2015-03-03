module.exports = function (grunt, options) {
    return {
        options:{
            text_domain: options.i18n.textDomain,
            correct_domain: true,
            keywords: options.i18n.keywords
        },
        files:{
            src:  options.i18n.sources,
            expand: true
        }
    };
};