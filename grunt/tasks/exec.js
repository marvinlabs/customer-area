module.exports = function (grunt, options) {
    return {
        txpull: { // Pull Transifex translation - grunt exec:txpull
            cmd: 'tx pull -a --minimum-perc=100' // Change the percentage with --minimum-perc=yourvalue
        },
        txpush_s: { // Push pot to Transifex - grunt exec:txpush_s
            cmd: 'tx push -s'
        }
    };
};