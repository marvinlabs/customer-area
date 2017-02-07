/**
 * Get the input field of a license control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlInput(licenseControl) {
    return licenseControl.find('.cuar-js-license-key');
}

/**
 * Get the input field of a license control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlValidateButton(licenseControl) {
    return licenseControl.find('.cuar-js-validate-button');
}

/**
 * Get the result container of a license control
 * @param licenseControl
 * @returns {*}
 */
function getLicenseControlResultContainer(licenseControl) {
    return licenseControl.find('.cuar-js-result > span');
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
    var validateButton = getLicenseControlValidateButton(licenseControl);
    var checkResultContainer = getLicenseControlResultContainer(licenseControl);

    var licenseKey = licenseInput.val().trim();
    if (licenseKey.length==0) {
        checkResultContainer.html('');
        return;
    }

    checkResultContainer.html(cuar.checkingLicense).removeClass().addClass('cuar-ajax-running');
    licenseInput.prop('disabled', true);
    validateButton.prop('disabled', true);

    var data = {
        action: 'cuar_validate_license',
        addon_id: getLicenseControlAddOn(licenseControl),
        license: licenseKey
    };

    $.post(cuar.ajaxUrl, data, function(response) {
            licenseInput.prop('disabled', false);
            validateButton.prop('disabled', false);
            checkResultContainer
                .removeClass()
                .addClass(response.success ? 'cuar-ajax-success' : 'cuar-ajax-failure')
                .html(response.message);
        },
        "json",
        function() {
            licenseInput.prop('disabled', false);
            validateButton.prop('disabled', false);
            checkResultContainer
                .removeClass()
                .addClass('cuar-ajax-failure')
                .html(cuar.unreachableLicenseServerError);
        }
    );
}

// Runs the necessary logic on the license controls of the page
jQuery(document).ready(function($) {
    // Used in the licensing options page to check license key when the input value changes
    $(".cuar-js-license-field").each(function() {
        var licenseControl = $(this);

        // Check license when input value changes
        licenseControl.on("click", ".cuar-js-validate-button", function(event) {
            event.preventDefault();
            validateLicense($, licenseControl);
            return false;
        });
    });
});