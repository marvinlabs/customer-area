module.exports = function (grunt, options) {
    return {
        dist: {
            options: {
                poDel: false // Set to true if you want to erase the .po
            },
            files: [{
                expand: true,
                cwd: options.i18n.poPath,
                src: ['*.po'],
                dest: options.i18n.poPath,
                ext: '.mo',
                nonull: true
            }]
        }
    };
};