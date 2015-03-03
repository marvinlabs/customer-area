module.exports = function (grunt, options) {
    return {
        default: {
            options: {
                cwd: "",                          // Directory of files to internationalize.
                domainPath: options.i18n.poPath,
                exclude: [
                    "node_modules/.*",
                    "libs/.*",
                    "grunt/.*",
                    "assets/.*"
                ],
                include: options.i18n.sources,
                mainFile: options.i18n.mainFile,
                potComments: options.i18n.copyright,
                potFilename: options.i18n.textDomain + ".pot",
                potHeaders: {
                    poedit: true,
                    "x-poedit-keywordslist": true
                },
                type: options.i18n.projectType,
                updateTimestamp: true,
                updatePoFiles: false,
                processPot: function (pot) {
                    pot.headers["report-msgid-bugs-to"] = options.pkg.bugs.url + "\n";
                    pot.headers["last-translator"] = options.pkg.author.name + " <" + options.pkg.author.email + ">\n";
                    pot.headers["language-team"] = options.pkg.author.url + "\n";
                    pot.headers["language"] = "en_US";

                    var excluded_meta = [
                        "Plugin Name of the plugin/theme",
                        "Plugin URI of the plugin/theme",
                        "Author of the plugin/theme",
                        "Author URI of the plugin/theme"
                    ];
                    for (var translation in pot.translations[""]) {
                        if ("undefined" !== typeof pot.translations[""][translation].comments.extracted) {
                            if (excluded_meta.indexOf(pot.translations[""][translation].comments.extracted) >= 0) {
                                console.log("Excluded meta: " + pot.translations[""][translation].comments.extracted);
                                delete pot.translations[""][translation];
                            }
                        }
                    }
                    return pot;
                }
            }
        }
    };
};