var strict;

jQuery(document).ready(function ($) {
    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    hypwa_deactivate_link = $('.wp-admin.plugins-php tr[data-slug="hyper-pwa"] .row-actions .deactivate a');
    hypwa_deactivate_link_url = hypwa_deactivate_link.attr('href');

    hypwa_deactivate_link.click(function (e) {
        e.preventDefault();

        // only show feedback form once per 30 days
        var c_value = hypwa_admin_get_cookie("hypwa_keep_hidden_feedback_popup");

        if (c_value === undefined) {
            $('#hypwa-feedback-overlay').show();
        } else {
            // click on the link
            window.location.href = hypwa_deactivate_link_url;
        }
    });
    // show text fields
    
    $('input[name="hypwa_disable_reason"]').on('change', function() {
        const selectedId = $(this).attr('id');

        // Hide all textareas first
        $('.hypwa-reason-details textarea').addClass('hypwa-d-none');

        // Show the matching textarea if it exists
        $('.hypwa-reason-details textarea[data-id="' + selectedId + '"]').removeClass('hypwa-d-none');
    });


    // send form or close it
    $('#hypwa-feedback-content form').submit(function (e) {
        e.preventDefault();

        hypwa_set_feedback_cookie();

        // Send form data
        $.post(hypwa_feedback_local.ajax_url, {
            action: 'hypwa_send_feedback',
            data: $('#hypwa-feedback-content form').serialize() + "&hypwa_security_nonce=" + hypwa_feedback_local.hypwa_security_nonce
        },
                function (data) {

                    if (data == 'sent') {
                        // deactivate the plugin and close the popup
                        $('#hypwa-feedback-overlay').remove();
                        window.location.href = hypwa_deactivate_link_url;
                    } else {
                        console.log('Error: ' + data);
                        alert(data);
                    }
                }
        );
    });

    $("#hypwa-feedback-content .hypwa-only-deactivate").click(function (e) {
        e.preventDefault();

        hypwa_set_feedback_cookie();        
        $('#hypwa-feedback-overlay').remove();
        window.location.href = hypwa_deactivate_link_url;
    });

    // close form without doing anything
    $('.hypwa-fd-stop-deactivation').click(function (e) {
        e.preventDefault();
        $('#hypwa-feedback-content form')[0].reset();                
        $('.hypwa-reason-details textarea').addClass('hypwa-d-none');
        $('#hypwa-feedback-overlay').hide();
        $(".hypwa-reason-details").addClass('hypwa-display-none')        
    });

    function hypwa_admin_get_cookie(name) {
        var i, x, y, hypwa_cookies = document.cookie.split(";");
        for (i = 0; i < hypwa_cookies.length; i++)
        {
            x = hypwa_cookies[i].substr(0, hypwa_cookies[i].indexOf("="));
            y = hypwa_cookies[i].substr(hypwa_cookies[i].indexOf("=") + 1);
            x = x.replace(/^\s+|\s+$/g, "");
            if (x === name)
            {
                return unescape(y);
            }
        }
    }

    function hypwa_set_feedback_cookie() {
        // set cookie for 30 days
        var exdate = new Date();
        exdate.setSeconds(exdate.getSeconds() + 2592000);
        document.cookie = "hypwa_keep_hidden_feedback_popup=1; expires=" + exdate.toUTCString() + "; path=/";
    }
});