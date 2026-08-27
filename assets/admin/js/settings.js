(function($) {
    'use strict';

    $(function() {
        
        if ( $.fn.wpColorPicker ) {
            $('.hypwa-color-input').wpColorPicker({
                change: function( event, ui ) {
                    $( this ).val( ui.color.toString() ).trigger( 'change' );
                },
                clear: function() {
                    $( this ).val( '' ).trigger( 'change' );
                }
            });
        }

        $('.hypwa-select2').select2({
            width: '100%',
            placeholder: 'Select options...',
            allowClear: true
        });


        // Load other post types on change of a select for start page, 404 page and offline page
        $('.hypwa-ajax-page-search').each(function () {
            var $select = $(this);

            var localOptions = [];
            $select.find('option').each(function () {
                var $opt = $(this);
                if ($opt.val() !== '') {
                    localOptions.push({
                        id: String($opt.val()),
                        text: $opt.text()
                    });
                }
            });

            $select.select2({
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: hypwa_settings_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    
                    transport: function (params, success, failure) {
                        var term = params.data.search_term ? params.data.search_term.trim().toLowerCase() : '';

                        var matchedLocals = $.grep(localOptions, function (item) {
                            return item.text.toLowerCase().indexOf(term) !== -1;
                        });

                        if (term.length < 2) {
                            success({ success: true, data: matchedLocals });
                            return;
                        }

                        return $.ajax(params).done(function(ajaxResponse) {
                            if (ajaxResponse.success && ajaxResponse.data) {
                                
                                var combinedResults = [].concat(matchedLocals);
                                
                                $.each(ajaxResponse.data, function (i, remoteItem) {
                                    var normalizedItem = {
                                        id: String(remoteItem.id),
                                        text: remoteItem.text
                                    };

                                    var isDuplicate = $.grep(combinedResults, function (localItem) {
                                        return localItem.id === normalizedItem.id;
                                    }).length > 0;

                                    if (!isDuplicate) {
                                        combinedResults.push(normalizedItem);
                                    }
                                });

                                success({ success: true, data: combinedResults });
                            } else {
                                success({ success: true, data: matchedLocals });
                            }
                        }).fail(failure);
                    },
                    data: function (params) {
                        return {
                            action: 'hypwa_search_all_post_types',
                            nonce: hypwa_settings_ajax.nonces.save,
                            search_term: params.term
                        };
                    },
                    processResults: function (response) {
                        var formattedResults = $.map(response.data || [], function (item) {
                            return {
                                id: String(item.id),
                                text: item.text
                            };
                        });

                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });
        });

        // Accordion Framework Panel Toggles
        $('.hypwa-card').on('click', '.hypwa-card-header', function(e) {
            // Safeguard against input/selection loops firing accordion closures natively
            if ($(e.target).closest('.hypwa-switch, input, select, textarea, .hypwa-upgrade-link, .hypwa-doc-link').length) {
                return;
            }

            var $card = $(this).closest('.hypwa-card');
            var $content = $card.find('.hypwa-card-content');
            var isOpen = $card.hasClass('open');

            if ($content.length) {
                $card.toggleClass('open', !isOpen);
                $content.slideToggle(200); // Smoother, standard WordPress transition
            }
        });

        // Checkbox Slider Text Switcher Status
        $('.hypwa-switch').on('change', 'input[type="checkbox"]', function() {
            var $labelSpan = $(this).closest('.hypwa-toggle-label-wrap').find('.hypwa-toggle-txt');
            if ($labelSpan.length) {
                $labelSpan.text(this.checked ? 'ON' : 'OFF');
            }
        });

        // Media Library Custom Asset Uploader Hooks
        $(document).on('click', '.hypwa-upload-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var targetId = $button.attr('data-target');
            var $targetInput = $('#' + targetId);
            
            var customUploader = wp.media({
                title: 'Select Graphic Asset Node',
                button: { text: 'Use Selected Asset' },
                multiple: false
            });

            customUploader.on('select', function() {
                var attachment = customUploader.state().get('selection').first().toJSON();
                if ($targetInput.length && attachment.url) {
                    $targetInput.val(attachment.url);
                    $targetInput.trigger('input');
                }
            });

            customUploader.open();
        });

        // Global Top Fixed Header Button Submitter Trigger
        $('.hypwa-save-global-trigger').on('click', function(e) {
            e.preventDefault();
            $('#hypwa-settings-form').trigger('submit');
        });

        /**
         * -----------------------------------------
         * Save Settings
         * -----------------------------------------
         * */
        $('#hypwa-settings-form').on('submit', function (e) {
            e.preventDefault();

            var $submitBtn = $('.hypwa-save-changes-btn');
            const $loadingButton = $( '#hypwa-save-changes-load-btn' );
            var formData   = $(this).serializeArray();

            formData.push({ name: 'action', value: 'hypwa_save_settings' });
            formData.push({ name: 'nonce',  value: hypwa_settings_ajax.nonces.save });

            $submitBtn.addClass('hypwa-hide');
            $loadingButton.removeClass('hypwa-hide');

            $.ajax({
                url:  hypwa_settings_ajax.ajax_url,
                type: 'POST',
                data: formData,
                beforeSend: function () { $submitBtn.prop('disabled', true).text('Saving...'); },
                success: function (response) {
                    $submitBtn.removeClass('hypwa-hide');
                    $loadingButton.addClass('hypwa-hide');
                    if ( response.success ) {
                        hypwa_show_notice('success', response.data.message);
                    } else {
                        hypwa_show_notice('error', response.data.message);
                    }
                },
                error: function (
                    ) { 
                    $submitBtn.removeClass('hypwa-hide');
                    $loadingButton.addClass('hypwa-hide');
                    hypwa_show_notice('error', 'Something went wrong. Please try again.'); 
                },
                complete: function () { 
                    $submitBtn.removeClass('hypwa-hide');
                    $loadingButton.addClass('hypwa-hide');
                    $submitBtn.prop('disabled', false).text('Save Changes'); 
                }
            });
        });

        /**
         * -----------------------------------------
         * Reset Settings
         * -----------------------------------------
         * */
        $(document).on('click', '#hypwa-reset-btn', function (e) {
            e.preventDefault();

            if ( ! confirm('Are you sure you want to reset all settings to defaults?') ) return;

            var $btn = $(this);
            var loadingBtn = $('#hypwa-reset-settings-loading-btn');

            $.ajax({
                url:  hypwa_settings_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hypwa_reset_settings',
                    nonce:  hypwa_settings_ajax.nonces.reset,
                },
                beforeSend: function () { 
                    $btn.addClass('hypwa-hide');
                    loadingBtn.removeClass('hypwa-hide'); 
                },
                success: function (response) {
                    if ( response.success ) {
                        let fieldId     =   '#hypwa-reset-settings-success-msg'
                        hypwaShowMessage( fieldId, response.data.message );
                    } else {
                        let fieldId     =   '#hypwa-reset-settings-error-msg'
                        hypwaShowMessage( fieldId, response.data.message, 'error' );
                    }
                    $btn.removeClass('hypwa-hide');
                    loadingBtn.addClass('hypwa-hide');
                },
                error: function () { 
                    let fieldId     =   '#hypwa-reset-settings-error-msg'
                    hypwaShowMessage( fieldId, 'Something went wrong.', 'error' );
                    $btn.removeClass('hypwa-hide');
                    loadingBtn.addClass('hypwa-hide');
                },
                complete: function () { 
                    $btn.removeClass('hypwa-hide');
                    loadingBtn.addClass('hypwa-hide');
                }
            });
        });

        /**
        * -----------------------------------------
        * Export Settings
        * -----------------------------------------
        */
        $(document).on('click', '#hypwa-export-btn', function (e) {
            e.preventDefault();

            var $btn = $(this);
            $btn.prop('disabled', true).text('Exporting...');

            $.ajax({
                url:  hypwa_settings_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hypwa_export_settings',
                    nonce:  hypwa_settings_ajax.nonces.export,
                },
                success: function (response) {
                    if ( response.success ) {
                        var blob  = new Blob(
                            [ JSON.stringify( response.data.settings, null, 2 ) ],
                            { type: 'application/json' }
                        );
                        var url   = URL.createObjectURL( blob );
                        var $link = $('<a>').attr({ href: url, download: response.data.filename }).appendTo('body');
                        $link[0].click();
                        $link.remove();
                        URL.revokeObjectURL( url );
                        hypwa_show_notice('success', response.data.message);
                    } else {
                        hypwa_show_notice('error', response.data.message);
                    }
                },
                error: function () { hypwa_show_notice('error', 'Export failed. Please try again.'); },
                complete: function () {
                    $btn.prop('disabled', false).html(
                        '<span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;"></span> Download Export File'
                    );
                }
            });
        });

        /**
         * -----------------------------------------
         * Import Settings — Inline Dropzone Handling
         * -----------------------------------------
         */
        var $dropzone    = $('#hypwa-import-dropzone');
        var $fileInput   = $('#hypwa-import-file');
        var $defaultView = $('#hypwa-dropzone-default-view');
        var $fileView    = $('#hypwa-dropzone-file-view');
        var $nameDisplay = $('#hypwa-import-filename-display');
        var $processBtn  = $('#hypwa-import-btn');

        // Handle clicking the dropzone container
        $dropzone.on('click', function (e) {
            if ($(e.target).closest('input[type="file"]').length === 0) {
                $fileInput.click();
            }
        });

        // Neutralize global browser drag/drop layout redirections
        $(document).on('dragenter dragover drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        // Dropzone drag style triggers
        $dropzone.on('dragenter dragover', function (e) {
            $(this).css({ 'border-color': '#3b82f6', 'background': '#f0f9ff' });
        });

        $dropzone.on('dragleave drop', function (e) {
            $(this).css({ 'border-color': '#cbd5e1', 'background': '#fafafa' });
        });

        // Intercept drop event action data values
        $dropzone.on('drop', function (e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $fileInput[0].files = files; // Sync hidden input DOM data references
                handleFileSelection(files[0]);
            }
        });

        // Standard dynamic file-picker change event pipeline
        $fileInput.on('change', function () {
            if (this.files.length) {
                handleFileSelection(this.files[0]);
            }
        });

        // Trigger change link to clear file selection
        $(document).on('click', '#hypwa-dropzone-file-view span', function(e) {
            e.stopPropagation(); // Prevent triggering the container click
            clearSelection();
        });

        function handleFileSelection(file) {
            if (!file) return;

            if (!file.name.endsWith('.json')) {
                alert('Please select a valid .json config backup file.');
                clearSelection();
                return;
            }

            // Bind values and alternate visual displays (using flex for inline look)
            $nameDisplay.text(file.name);
            $defaultView.hide();
            $fileView.css('display', 'flex');

            // Toggle processing buttons interface
            $processBtn.prop('disabled', false).css({
                'opacity': '1',
                'cursor': 'pointer'
            });
        }

        function clearSelection() {
            $fileInput.val('');
            $nameDisplay.text('');
            $fileView.hide();
            $defaultView.css('display', 'flex');
            $processBtn.prop('disabled', true).css({
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
        }

        // Process Import Form Submission Actions
        $(document).on('click', '#hypwa-import-btn', function () {
            var file = $fileInput[0].files[0];
            if ( ! file ) return;

            var $btn   = $(this);
            const $loadingButton = $( '#hypwa-import-load-btn' );
            var reader = new FileReader();

            $btn.addClass('hypwa-hide');
            $loadingButton.removeClass('hypwa-hide');

            reader.onload = function (e) {
                $.ajax({
                    url:  hypwa_settings_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action:        'hypwa_import_settings',
                        nonce:         hypwa_settings_ajax.nonces.import,
                        settings_json: e.target.result,
                    },
                    success: function (response) {
                        $btn.removeClass('hypwa-hide');
                        $loadingButton.addClass('hypwa-hide');
                        if ( response.success ) {
                            hypwa_show_notice('success', response.data.message);
                        } else {
                            hypwa_show_notice('error', response.data.message);
                        }
                    },
                    error: function () { 
                        $btn.removeClass('hypwa-hide');
                        $loadingButton.addClass('hypwa-hide');
                        hypwa_show_notice('error', 'Import failed. Please try again.'); 
                    },
                    complete: function () {
                        $btn.removeClass('hypwa-hide');
                        $loadingButton.addClass('hypwa-hide');
                    }
                });
            };

            reader.readAsText( file );
        });

        /**
         * ---------------------------------------------
         * UTM Tracking render dynamic url preview change
         * ---------------------------------------------
         * */
        $(document).on( 'keyup', '.hypwa-cf-utm-input-fields', function( e )  {

            $('#hypwa-cf-utm-source').text($('#hypwa-cf-utm-source-input').val());
            $('#hypwa-cf-utm-medium').text($('#hypwa-cf-utm-medium-input').val());
            $('#hypwa-cf-utm-campaign').text($('#hypwa-cf-utm-campaign-input').val());
            $('#hypwa-cf-utm-term').text($('#hypwa-cf-utm-term-input').val());
            $('#hypwa-cf-utm-content').text($('#hypwa-cf-utm-content-input').val());

        });

    });

    function hypwa_show_notice(type, message) {
        var $bar  = $('.hypwa-bottom-status');
        var color = type === 'success' ? '#10b981' : '#ef4444';
        var icon  = type === 'success' ? '✓' : '✕';

        $bar.html('<span style="color:' + color + '; font-weight:600;">' + icon + ' ' + message + '</span>');

        setTimeout(function () { $bar.html(''); }, 3000);
    }

    // Support Ticket Submission Handler starts here    

    $( document ).on( 'click', '.hypwa-support', function( e ) {

    	e.preventDefault();

    	const $button        = $( this );
    	const $loadingButton = $( '.hypwa-saving-btn' );
    	const $message       = $( '.hypwa-ticket-message' );

    	const ticket_email   = $( '#ticket_email' ).val().trim();
    	const ticket_subject = $( '#ticket_subject' ).val().trim();
    	const description    = $( '#detailed_description' ).val().trim();

    	$message.html( '' );

    	if ( ! ticket_subject ) {

    		$message.html(
    			'<span style="color:#dc2626;">Please enter a ticket subject.</span>'
    		);

    		return;
    	}

    	if ( ! ticket_email ) {

    		$message.html(
    			'<span style="color:#dc2626;">Please enter a ticket email.</span>'
    		);

    		return;
    	}

    	const email_pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    	if ( ! email_pattern.test( ticket_email ) ) {

    		$message.html(
    			'<span style="color:#dc2626;">Please enter a valid email address.</span>'
    		);

    		return;
    	}

    	if ( ! description ) {

    		$message.html(
    			'<span style="color:#dc2626;">Please enter a detailed description.</span>'
    		);

    		return;
    	}

    	$button.addClass( 'hypwa-hide' );
    	$loadingButton.removeClass( 'hypwa-hide' );

    	$.ajax( {
    		url: hypwa_settings_ajax.ajax_url,
    		type: 'POST',
    		dataType: 'json',
    		data: {
    			action: 'hypwa_submit_support_ticket',
    			nonce: hypwa_settings_ajax.nonce,
    			ticket_subject: ticket_subject,
    			ticket_email: ticket_email,
    			detailed_description: description
    		},
    		success: function( response ) {

    			if ( response.success ) {

    				$( '#ticket_subject' ).val( '' );
    				$( '#ticket_email' ).val( '' );
    				$( '#detailed_description' ).val( '' );

    				$message.html(
    					'<span style="color:#16a34a;">' +
    					response.data +
    					'</span>'
    				);

    			} else {

    				$message.html(
    					'<span style="color:#dc2626;">' +
    					response.data +
    					'</span>'
    				);
    			}
    		},
    		error: function() {

    			$message.html(
    				'<span style="color:#dc2626;">Unable to submit ticket. Please try again.</span>'
    			);
    		},
    		complete: function() {

    			$loadingButton.addClass( 'hypwa-hide' );
    			$button.removeClass( 'hypwa-hide' );
    		}
    	} );
    } );

    // Support Ticket Submission Handler ends here

    /**
     *  ---------------------------------------------
     *  Screenshot repeater code starts here
     *  ---------------------------------------------
     * */
    function hypwa_update_screenshot_thumb($item, url) {
        var $thumbWrap = $item.find('.hypwa-screenshot-thumb-wrap');

        if ( $thumbWrap.length ) {
            $thumbWrap.find('img').attr('src', url);
        } else {
            var $newThumb = $(
                '<div class="hypwa-screenshot-thumb-wrap">' +
                    '<img src="' + url + '" alt="" class="hypwa-screenshot-thumb" />' +
                '</div>'
            );
            $item.find('.hypwa-repeater-fields').append($newThumb);
            $newThumb.hide().slideDown(180);
        }
    }
        
    function hypwa_build_repeater_item( factor, nameBase, index ) {
            var uid = 'hypwa-screenshot-url-' + factor + '-' + index;
 
            return '<div class="hypwa-repeater-item hypwa-repeater-item--new" data-index="' + index + '">' +
                '<div class="hypwa-repeater-badge">' + ( index + 1 ) + '</div>' +
                '<div class="hypwa-repeater-fields">' +
                    '<div class="hypwa-upload-wrapper">' +
                        '<input type="text"' +
                            ' id="' + uid + '"' +
                            ' class="hypwa-text-input hypwa-screenshot-url-input"' +
                            ' name="' + nameBase + '[' + index + '][url]"' +
                            ' value=""' +
                            ' placeholder="https://example.com/screenshot.png"' +
                        ' />' +
                        '<button type="button"' +
                            ' class="hypwa-upload-btn hypwa-widget-btn-outline hypwa-screenshot-upload-btn"' +
                            ' data-target="' + uid + '"' +
                        '>' +
                            '<span class="dashicons dashicons-upload"></span> Upload' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="hypwa-repeater-remove-btn" title="Remove screenshot">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                '</button>' +
            '</div>';
        }
 
        /**
         * Re-index all items inside a repeater wrap after add/remove.
         * Updates: data-index, badge number, name attributes, and input IDs.
         */
        function hypwa_reindex_repeater( $wrap ) {
            var factor   = $wrap.data('factor');
            var nameBase = $wrap.data('name-base');
 
            $wrap.find('.hypwa-repeater-item').each(function( i ) {
                var $item = $(this);
                $item.attr('data-index', i);
                $item.find('.hypwa-repeater-badge').text( i + 1 );
 
                var uid = 'hypwa-screenshot-url-' + factor + '-' + i;
 
                // Update URL input
                $item.find('.hypwa-screenshot-url-input')
                    .attr('id', uid)
                    .attr('name', nameBase + '[' + i + '][url]');
 
                // Update upload button target
                $item.find('.hypwa-screenshot-upload-btn')
                    .attr('data-target', uid);
 
                // Update caption input
                $item.find('.hypwa-repeater-label-input')
                    .attr('name', nameBase + '[' + i + '][label]');
            });
        }
 
        // Add Screenshot
        $(document).on('click', '.hypwa-repeater-add-btn', function() {
            var targetId = $(this).data('target');
            var $wrap    = $('#' + targetId);
            var factor   = $wrap.data('factor');
            var nameBase = $wrap.data('name-base');
            var newIndex = $wrap.find('.hypwa-repeater-item').length;
 
            var $newItem = $( hypwa_build_repeater_item( factor, nameBase, newIndex ) );
            $wrap.append( $newItem );
 
            // Animate new row in
            $newItem.hide().slideDown( 200 );
        });
 
        // Remove Screenshot
        $(document).on('click', '.hypwa-repeater-remove-btn', function() {
            var $item = $(this).closest('.hypwa-repeater-item');
            var $wrap = $item.closest('.hypwa-repeater-wrap');
 
            // Keep at least one row
            if ( $wrap.find('.hypwa-repeater-item').length <= 1 ) {
                // Just clear the inputs instead of removing
                $item.find('.hypwa-screenshot-url-input').val('');
                $item.find('.hypwa-repeater-label-input').val('');
                $item.find('.hypwa-screenshot-thumb-wrap').remove();
                return;
            }
 
            $item.slideUp( 150, function() {
                $(this).remove();
                hypwa_reindex_repeater( $wrap );
            });
        });

        $(document).on('input', '.hypwa-screenshot-url-input', function() {
            var url   = $(this).val().trim();
            var $item = $(this).closest('.hypwa-repeater-item');
            if ( ! $item.length ) return;
 
            if ( url ) {
                hypwa_update_screenshot_thumb($item, url);
            } else {
                $item.find('.hypwa-screenshot-thumb-wrap').slideUp(150, function() {
                    $(this).remove();
                });
            }
        });
    /**
     *  ---------------------------------------------
     * Screenshot repeater ends starts here
     *  ---------------------------------------------
     * */

    /** 
     *  ---------------------------------------------
     *  Precaching starts here
     *  ---------------------------------------------
     * */

    $('.hypwa-pc-specific-select').select2({
        width: '100%',
        placeholder: 'Select specific posts…',
        allowClear: true,
    });

    // On page load — restore saved visual state
    $('.hypwa-pc-row').each(function() {
        var $row  = $(this);
        var val   = $row.find('.hypwa-pc-specific-select').val();
        var count = $row.find('.hypwa-pc-count-input').val();

        if ( val && val.length > 0 ) {
            $row.find('.hypwa-pc-count-wrap').addClass('hypwa-pc-side--muted');
        } else if ( count !== '' ) {
            $row.find('.hypwa-pc-specific-wrap').addClass('hypwa-pc-side--muted');
        }
    });

    // Checkbox: toggle active row styling
    $(document).on('change', '.hypwa-pc-type-checkbox', function() {
        $(this).closest('.hypwa-pc-row').toggleClass('hypwa-pc-row--active', this.checked);
    });

    // Count input focused → mute specific side
    $(document).on('focus', '.hypwa-pc-count-input', function() {
        var $row = $(this).closest('.hypwa-pc-row');
        $row.find('.hypwa-pc-count-wrap').removeClass('hypwa-pc-side--muted');
        $row.find('.hypwa-pc-specific-wrap').addClass('hypwa-pc-side--muted');
        $row.find('.hypwa-pc-specific-select').val(null).trigger('change.select2');
    });

    $(document).on('select2:opening', '.hypwa-pc-specific-select', function(e) {
        var $row = $(this).closest('.hypwa-pc-row');

        // If checkbox is unchecked, prevent the dropdown from opening
        if ( ! $row.find('.hypwa-pc-type-checkbox').is(':checked') ) {
            e.preventDefault();
            return;
        }

        $row.find('.hypwa-pc-specific-wrap').removeClass('hypwa-pc-side--muted');
        $row.find('.hypwa-pc-count-wrap').addClass('hypwa-pc-side--muted');
        $row.find('.hypwa-pc-count-input').val('');
    });

    // Select cleared via × → revert to count mode
    $(document).on('change', '.hypwa-pc-specific-select', function() {
        if ( ! $(this).val() || $(this).val().length === 0 ) {
            var $row = $(this).closest('.hypwa-pc-row');
            $row.find('.hypwa-pc-specific-wrap').addClass('hypwa-pc-side--muted');
            $row.find('.hypwa-pc-count-wrap').removeClass('hypwa-pc-side--muted');
        }
    });

    /** 
     *  ---------------------------------------------
     *  Precaching ends here
     *  ---------------------------------------------
     * */

    /** 
     *  ---------------------------------------------
     *  Super PWA migration starts here
     *  ---------------------------------------------
     * */
    $(document).on('click', '.hypwa-pwa-migration-btn', function(e) {
        e.preventDefault();

        var $submitBtn = $(this);
        const $loadingButton = $(this).closest('.hypwa-form-row').find('.hypwa-migrte-loading-btn');
        const plugin = $(this).data('plugin');
        console.log('plugin ', plugin);

        $submitBtn.addClass('hypwa-hide');
        $loadingButton.removeClass('hypwa-hide');

        $.ajax({
            url:  hypwa_settings_ajax.ajax_url,
            type: 'POST',
            data: {plugin: plugin, action: 'hypwa_perform_migration', nonce: hypwa_settings_ajax.nonces.migrate},
            beforeSend: function () { $submitBtn.prop('disabled', true); },
            success: function (response) {
                $submitBtn.removeClass('hypwa-hide');
                $loadingButton.addClass('hypwa-hide');
                if ( response.success ) {
                    let fieldId     =   '#hypwa-'+plugin+'-success-msg'
                    hypwaShowMessage( fieldId, response.data.message );
                } else {
                    let fieldId     =   '#hypwa-'+plugin+'-error-msg'
                    hypwaShowMessage( fieldId, response.data.message, 'error' );
                }
            },
            error: function (
                ) { 
                $submitBtn.removeClass('hypwa-hide');
                $loadingButton.addClass('hypwa-hide');
                hypwa_show_notice('error', 'Something went wrong. Please try again.'); 
            },
            complete: function () { 
                $submitBtn.removeClass('hypwa-hide');
                $loadingButton.addClass('hypwa-hide');
            }
        });
    });


    let hypwaMessageTimer;

    function hypwaShowMessage(selector, message, type = 'success', duration = 3000) {

        const $message = $(selector);

        $message
            .removeClass(
                'hypwa-field-message-success ' +
                'hypwa-field-message-error ' +
                'hypwa-field-message-warning ' +
                'hypwa-field-message-info ' +
                'hypwa-hide'
            )
            .addClass('hypwa-field-message-' + type);

        let icon = 'yes-alt';

        switch (type) {
            case 'error':
                icon = 'warning';
                break;

            case 'warning':
                icon = 'warning';
                break;

            case 'info':
                icon = 'info';
                break;
        }

        $message.html(
            '<span class="dashicons dashicons-' + icon + '"></span>' +
            '<span class="hypwa-message-text">' + message + '</span>'
        );

        clearTimeout(hypwaMessageTimer);

        if (duration > 0) {
            hypwaMessageTimer = setTimeout(function () {
                $message.addClass('hypwa-hide');
            }, duration);
        }
    }

    // Connect to hyperpushx.com
    $(document).on('click', '#hypwa-push-connect-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var apiKey = $('#hypwa-push-api-key-input').val().trim();
        var $feedback = $('#hypwa-push-connection-feedback');

        if (!apiKey) {
            $feedback.html('<span style="color:#ef4444;">API Key is required.</span>');
            return;
        }

        $btn.prop('disabled', true).text('Connecting...');
        $feedback.html('<span style="color:#64748b;">Verifying API Key...</span>');

        $.ajax({
            url: hypwa_settings_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hypwa_push_connect',
                nonce: hypwa_settings_ajax.nonces.save,
                api_key: apiKey
            },
            success: function(response) {
                if (response.success) {
                    $feedback.html('<span style="color:#10b981;">' + response.data.message + ' Reloading settings...</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $feedback.html('<span style="color:#ef4444;">' + response.data.message + '</span>');
                    $btn.prop('disabled', false).text('Connect');
                }
            },
            error: function() {
                $feedback.html('<span style="color:#ef4444;">Connection failed. Please try again.</span>');
                $btn.prop('disabled', false).text('Connect');
            }
        });
    });

    // Disconnect from hyperpushx.com
    $(document).on('click', '#hypwa-push-disconnect-btn', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to disconnect your website from hyperpushx.com?')) {
            return;
        }
        var $btn = $(this);
        var $feedback = $('#hypwa-push-connection-feedback');

        $btn.prop('disabled', true).text('Disconnecting...');

        $.ajax({
            url: hypwa_settings_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hypwa_push_disconnect',
                nonce: hypwa_settings_ajax.nonces.save
            },
            success: function(response) {
                if (response.success) {
                    $feedback.html('<span style="color:#10b981;">' + response.data.message + ' Reloading...</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $feedback.html('<span style="color:#ef4444;">' + response.data.message + '</span>');
                    $btn.prop('disabled', false).text('Disconnect');
                }
            },
            error: function() {
                $feedback.html('<span style="color:#ef4444;">Disconnect failed. Please try again.</span>');
                $btn.prop('disabled', false).text('Disconnect');
            }
        });
    });

    // Refresh stats from hyperpushx.com
    $(document).on('click', '#hypwa-push-refresh-stats', function(e) {
        e.preventDefault();
        var $link = $(this);
        var $icon = $link.find('.dashicons');

        $link.css('pointer-events', 'none').css('opacity', '0.6');
        $icon.css('animation', 'spin 1s linear infinite');

        // Append inline styles for spin keyframe if it doesn't exist
        if (!$('#hypwa-spin-keyframes').length) {
            $('<style id="hypwa-spin-keyframes">@keyframes spin { 100% { transform: rotate(360deg); } }</style>').appendTo('head');
        }

        $.ajax({
            url: hypwa_settings_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hypwa_push_refresh_stats',
                nonce: hypwa_settings_ajax.nonces.save
            },
            success: function(response) {
                $link.css('pointer-events', '').css('opacity', '');
                $icon.css('animation', '');
                if (response.success) {
                    $('#hypwa-push-stat-total').text(response.data.total);
                    $('#hypwa-push-stat-active').text(response.data.active);
                    $('#hypwa-push-stat-expired').text(response.data.expired);
                } else {
                    alert(response.data.message || 'Failed to refresh statistics.');
                }
            },
            error: function() {
                $link.css('pointer-events', '').css('opacity', '');
                $icon.css('animation', '');
                alert('Connection error. Failed to refresh statistics.');
            }
        });
    });

    // Send manual push notification
    $(document).on('click', '#hypwa-push-send-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var title = $('#hypwa-manual-title').val().trim();
        var message = $('#hypwa-manual-message').val().trim();
        var redirectUrl = $('#hypwa-manual-url').val().trim();
        var image = $('#hypwa-manual-image').val().trim();
        var $feedback = $('#hypwa-manual-push-feedback');

        if (!title || !message) {
            $feedback.html('<span style="color:#ef4444;">Title and Message are required.</span>');
            return;
        }

        $btn.prop('disabled', true).text('Sending...');
        $feedback.html('<span style="color:#64748b;">Broadcasting push notification...</span>');

        $.ajax({
            url: hypwa_settings_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hypwa_send_push_notification',
                nonce: hypwa_settings_ajax.nonces.save,
                title: title,
                message: message,
                url: redirectUrl,
                image: image
            },
            success: function(response) {
                if (response.success) {
                    $feedback.html('<span style="color:#10b981;">' + response.data.message + '</span>');
                    $('#hypwa-manual-title').val('');
                    $('#hypwa-manual-message').val('');
                    $('#hypwa-manual-image').val('');
                } else {
                    $feedback.html('<span style="color:#ef4444;">' + response.data.message + '</span>');
                }
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-paper-plane" style="vertical-align: middle; margin-right: 4px; font-size: 16px; width: 16px; height: 16px;"></span> Send Notification');
            },
            error: function() {
                $feedback.html('<span style="color:#ef4444;">Failed to send push notification. Please try again.</span>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-paper-plane" style="vertical-align: middle; margin-right: 4px; font-size: 16px; width: 16px; height: 16px;"></span> Send Notification');
            }
        });
    });

    // ── PWA Install Button Shortcode Live Preview ──
    $(function() {
        var $btnTextInput = $('#cf_ib_text');
        var $bgColorInput = $('#cf_ib_bg_color');
        var $textColorInput = $('#cf_ib_text_color');
        var $borderRadiusInput = $('#cf_ib_border_radius');
        var $paddingInput = $('#cf_ib_padding');
        var $previewBtn = $('#hypwa-ib-preview-btn-el');

        if ( !$previewBtn.length ) {
            return;
        }

        function updatePreview() {
            var text = $btnTextInput.val() || 'Install App';
            var bg = $bgColorInput.val() || '#2563eb';
            var color = $textColorInput.val() || '#ffffff';
            var radius = $borderRadiusInput.val() || '8';
            var padding = $paddingInput.val() || '12px 24px';

            // Format border-radius px
            if ( /^\d+$/.test(radius) ) {
                radius = radius + 'px';
            }

            $previewBtn.text(text);
            $previewBtn.css({
                'background-color': bg,
                'color': color,
                'border-radius': radius,
                'padding': padding
            });
        }

        $btnTextInput.on('input change', updatePreview);
        $bgColorInput.on('change input irischange', updatePreview);
        $textColorInput.on('change input irischange', updatePreview);
        $borderRadiusInput.on('input change', updatePreview);
        $paddingInput.on('input change', updatePreview);

        // Initialize preview on page load
        updatePreview();
    });

    // ── Connectivity Notice Live Preview ──
    $(function() {
        var $previewNotice = $('#hypwa-cn-preview-notice-el');
        if ( !$previewNotice.length ) {
            return;
        }

        var $titleEl = $('#hypwa-cn-preview-title-el');
        var $descEl = $('#hypwa-cn-preview-desc-el');
        var $iconEl = $('#hypwa-cn-preview-icon-span');

        var activeMode = 'offline'; // 'offline' or 'online'

        function updateNoticePreview() {
            var title, desc, bg, color, iconClass;
            
            if ( activeMode === 'offline' ) {
                title = $('#hypwa_cf_conn_notice_title_input').val() || "You're Offline";
                desc = $('#hypwa_cf_conn_notice_description_input').val() || "It looks like you are not connected to the internet. Please check your connection and try again.";
                bg = $('#hypwa_cf_conn_notice_bg_color_input').val() || '#2563eb';
                color = $('#hypwa_cf_conn_notice_text_color_input').val() || '#ffffff';
                iconClass = $('#hypwa_cf_conn_notice_icon_input').val() || 'dashicons-wifi';
            } else {
                title = $('#hypwa_cf_conn_online_notice_title_input').val() || "Back online";
                desc = $('#hypwa_cf_conn_online_notice_description_input').val() || "Your internet connection has been restored.";
                bg = $('#hypwa_cf_conn_online_notice_bg_color_input').val() || '#16a34a';
                color = $('#hypwa_cf_conn_online_notice_text_color_input').val() || '#ffffff';
                iconClass = $('#hypwa_cf_conn_online_notice_icon_input').val() || 'dashicons-wifi';
            }

            $titleEl.text(title);
            $descEl.text(desc);
            $previewNotice.css({
                'background-color': bg,
                'color': color
            });

            var isWifi = (iconClass === 'dashicons-wifi');
            var isWifiAlt = (iconClass === 'dashicons-wifi-alt2');
            var $svgEl = $('#hypwa-cn-preview-icon-svg');

            if ( isWifi || isWifiAlt ) {
                $svgEl.show();
                $iconEl.hide();
                var pathD = isWifi 
                    ? 'M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01'
                    : 'M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20';
                $svgEl.find('path').attr('d', pathD);
            } else {
                $svgEl.hide();
                $iconEl.show().removeClass().addClass('dashicons ' + iconClass + ' hypwa-cn-preview-icon-span');
            }
        }

        // Tab selection - Use event delegation and e.preventDefault()
        $(document).on('click', '.hypwa-cn-toggle-tab', function(e) {
            e.preventDefault();
            $('.hypwa-cn-toggle-tab').removeClass('active');
            $(this).addClass('active');
            activeMode = $(this).attr('data-target') || $(this).data('target');
            updateNoticePreview();
        });

        // Listeners for Offline
        $(document).on('input change', '#hypwa_cf_conn_notice_title_input, #hypwa_cf_conn_notice_description_input', function() {
            if ( activeMode === 'offline' ) updateNoticePreview();
        });
        $(document).on('change input irischange', '#hypwa_cf_conn_notice_bg_color_input, #hypwa_cf_conn_notice_text_color_input', function() {
            if ( activeMode === 'offline' ) updateNoticePreview();
        });
        $(document).on('change', '#hypwa_cf_conn_notice_icon_input', function() {
            if ( activeMode === 'offline' ) updateNoticePreview();
        });

        // Listeners for Online
        $(document).on('input change', '#hypwa_cf_conn_online_notice_title_input, #hypwa_cf_conn_online_notice_description_input', function() {
            if ( activeMode === 'online' ) updateNoticePreview();
        });
        $(document).on('change input irischange', '#hypwa_cf_conn_online_notice_bg_color_input, #hypwa_cf_conn_online_notice_text_color_input', function() {
            if ( activeMode === 'online' ) updateNoticePreview();
        });
        $(document).on('change', '#hypwa_cf_conn_online_notice_icon_input', function() {
            if ( activeMode === 'online' ) updateNoticePreview();
        });

        // ---- ICON PICKER FOR CONNECTIVITY NOTICES ----
        var CN_ICONS = [
            'dashicons-wifi', 'dashicons-wifi-alt2', 'dashicons-warning', 'dashicons-yes',
            'dashicons-info', 'dashicons-admin-home', 'dashicons-bell', 'dashicons-email',
            'dashicons-phone', 'dashicons-location', 'dashicons-rss', 'dashicons-share',
            'dashicons-admin-settings', 'dashicons-editor-help', 'dashicons-admin-generic'
        ];

        function buildCNIconPopover( $trigger, $iconVal, $iconPreview ) {
            $('.hypwa-cn-icon-popover').remove();

            var currentIcon = $iconVal.val();
            var $grid = $('<div class="hypwa-cn-icon-popover-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; padding: 10px; max-height: 180px; overflow-y: auto; box-sizing: border-box;"></div>');
            
            $.each( CN_ICONS, function( i, icon ) {
                var isActive = icon === currentIcon;
                var innerHTML = '';
                if ( icon === 'dashicons-wifi' ) {
                    innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01"></path></svg>';
                } else if ( icon === 'dashicons-wifi-alt2' ) {
                    innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20"></path></svg>';
                } else {
                    innerHTML = '<span class="dashicons ' + icon + '" style="font-size: 16px; width: 16px; height: 16px; display: inline-block;"></span>';
                }

                var $opt = $(
                    '<div class="hypwa-cn-icon-option" data-icon="' + icon + '" style="display: flex; align-items: center; justify-content: center; padding: 6px; border-radius: 4px; cursor: pointer; transition: background 0.2s; border: 1px solid ' + (isActive ? '#3b82f6' : 'transparent') + '; background: ' + (isActive ? '#e0f2fe' : 'transparent') + '; color: ' + (isActive ? '#0369a1' : 'inherit') + ';">' +
                        innerHTML +
                    '</div>'
                );
                
                if (!isActive) {
                    $opt.on('mouseenter', function() { $(this).css('background', '#f1f5f9'); })
                        .on('mouseleave', function() { $(this).css('background', 'transparent'); });
                }

                $grid.append( $opt );
            });

            var $popover = $('<div class="hypwa-cn-icon-popover" style="position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 999; width: 150px; box-sizing: border-box;"></div>').append( $grid );
            $trigger.append( $popover );

            $popover.on('click', '.hypwa-cn-icon-option', function(e) {
                e.stopPropagation();
                var chosen = $(this).attr('data-icon') || $(this).data('icon');
                $iconVal.val( chosen ).trigger('change');
                
                // Replace picker box element preview
                var $currentPreview = $trigger.find('.hypwa-bn-icon-preview');
                if ( chosen === 'dashicons-wifi' ) {
                    $currentPreview.replaceWith('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01"></path></svg>');
                } else if ( chosen === 'dashicons-wifi-alt2' ) {
                    $currentPreview.replaceWith('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20"></path></svg>');
                } else {
                    $currentPreview.replaceWith('<span class="dashicons ' + chosen + ' hypwa-bn-icon-preview"></span>');
                }
                
                $popover.remove();
            });
        }

        $(document).on('click', '.hypwa-cn-icon-pick', function(e) {
            e.stopPropagation();
            var $trigger     = $(this);
            var $iconVal     = $trigger.find('.hypwa-bn-icon-val');
            var $iconPreview = $trigger.find('.hypwa-bn-icon-preview, svg.hypwa-bn-icon-preview');

            if ( $trigger.find('.hypwa-cn-icon-popover').length ) {
                $('.hypwa-cn-icon-popover').remove();
                return;
            }

            buildCNIconPopover( $trigger, $iconVal, $iconPreview );
        });

        // Close popovers on body click
        $(document).on('click', function() {
            $('.hypwa-cn-icon-popover').remove();
        });

        // Initial render
        updateNoticePreview();
    });

})(jQuery);