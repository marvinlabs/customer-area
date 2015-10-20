/**
 * Get the input field of a license control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlInput(licenseControl) {
    return licenseControl.find('input');
}

/**
 * Get the result container of a license control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlResultContainer(licenseControl) {
    return licenseControl.find('.cuar-ajax-container > span');
}

/**
 * Get the add-on referred to by this control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlAddOn(licenseControl) {
    return getLicenseControlInput(licenseControl).data('addon');
}

/**
 * Validate a license
 * @param licenseControl The control to enter the license key
 */
function validateLicense($, licenseControl) {
    var licenseInput = getLicenseControlInput(licenseControl);
    var checkResultContainer = getLicenseControlResultContainer(licenseControl);

    if (licenseInput.val().trim()=='') {
        checkResultContainer.html('');
        return;
    }

    checkResultContainer.html(cuar.checking).removeClass().addClass('cuar-ajax-running');
    licenseInput.prop('disabled', true);

    var data = {
        action: 'cuar_validate_license',
        addon_id: getLicenseControlAddOn(licenseControl),
        license: licenseInput.val()
    };

    $.post(ajaxurl, data, function(response) {
            licenseInput.prop('disabled', false);
            checkResultContainer
                .removeClass()
                .addClass(response.success ? 'cuar-ajax-success' : 'cuar-ajax-failure')
                .html(response.message);
        },
        "json",
        function() {
            licenseInput.prop('disabled', false);
            checkResultContainer
                .removeClass()
                .addClass('cuar-ajax-failure')
                .html(cuar.unreachableLicenseServerError);
        }
    );
}

// Runs the necessary logic on the license controls of the page
jQuery(document).ready(function($) {
    var licenceControls = $(".license-control");

    // Used in the licensing options page to check license key when the input value changes
    licenceControls.each(function() {
        var licenseControl = $(this);

        // Check license when page is displayed
        validateLicense($, licenseControl);

        // Check license when input value changes
        getLicenseControlInput(licenseControl).change(function() {
            validateLicense($, licenseControl);
        });
    });
});