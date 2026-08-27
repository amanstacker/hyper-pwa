(function(wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var RichText = wp.blockEditor.RichText || wp.editor.RichText;
    var PanelBody = wp.components.PanelBody;
    var ColorPalette = wp.components.ColorPalette;
    var RangeControl = wp.components.RangeControl;

    registerBlockType('hyper-pwa/install-button', {
        title: 'PWA Install Button',
        description: 'Visual, customizable PWA install button for the block editor.',
        icon: 'button',
        category: 'widgets',
        supports: {
            align: ['left', 'center', 'right']
        },
        attributes: {
            text: { type: 'string', default: 'Install App' },
            bgColor: { type: 'string', default: '#2563eb' },
            textColor: { type: 'string', default: '#ffffff' },
            borderRadius: { type: 'number', default: 8 },
            paddingVertical: { type: 'number', default: 12 },
            paddingHorizontal: { type: 'number', default: 24 },
            align: { type: 'string', default: 'center' }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            function onChangeText(newVal) { setAttributes({ text: newVal }); }
            function onChangeBgColor(newVal) { setAttributes({ bgColor: newVal }); }
            function onChangeTextColor(newVal) { setAttributes({ textColor: newVal }); }
            function onChangeBorderRadius(newVal) { setAttributes({ borderRadius: parseInt(newVal, 10) || 0 }); }
            function onChangePaddingVertical(newVal) { setAttributes({ paddingVertical: parseInt(newVal, 10) || 0 }); }
            function onChangePaddingHorizontal(newVal) { setAttributes({ paddingHorizontal: parseInt(newVal, 10) || 0 }); }

            var btnRadius = attributes.borderRadius + 'px';
            var btnPadding = attributes.paddingVertical + 'px ' + attributes.paddingHorizontal + 'px';

            // Colors layout styling
            var labelStyle = {
                display: 'block',
                marginBottom: '8px',
                fontSize: '13px',
                fontWeight: '500',
                color: '#1e1e1e'
            };

            // Text alignment style
            var blockAlign = attributes.align || 'center';

            return el('div', { 
                className: 'hypwa-gutenberg-block-wrapper', 
                style: { 
                    padding: '20px', 
                    background: '#f8fafc', 
                    border: '1px dashed #cbd5e1', 
                    borderRadius: '8px',
                    textAlign: blockAlign
                } 
            },
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Install Button Style Settings', initialOpen: true },
                        el('div', { style: { marginBottom: '15px' } },
                            el('label', { style: labelStyle }, 'Background Color'),
                            el(ColorPalette, {
                                value: attributes.bgColor,
                                onChange: onChangeBgColor
                            })
                        ),
                        el('div', { style: { marginBottom: '15px' } },
                            el('label', { style: labelStyle }, 'Text Color'),
                            el(ColorPalette, {
                                value: attributes.textColor,
                                onChange: onChangeTextColor
                            })
                        ),
                        el(RangeControl, {
                            label: 'Border Radius (px)',
                            value: attributes.borderRadius,
                            onChange: onChangeBorderRadius,
                            min: 0,
                            max: 50
                        }),
                        el(RangeControl, {
                            label: 'Vertical Padding (px)',
                            value: attributes.paddingVertical,
                            onChange: onChangePaddingVertical,
                            min: 0,
                            max: 50
                        }),
                        el(RangeControl, {
                            label: 'Horizontal Padding (px)',
                            value: attributes.paddingHorizontal,
                            onChange: onChangePaddingHorizontal,
                            min: 0,
                            max: 100
                        })
                    )
                ),
                // Editor block preview using RichText for inline typing & bold/italic formatting
                el('div', { style: { margin: '10px 0', display: 'block' } },
                    el(RichText, {
                        tagName: 'button',
                        value: attributes.text,
                        onChange: onChangeText,
                        style: {
                            backgroundColor: attributes.bgColor,
                            color: attributes.textColor,
                            borderRadius: btnRadius,
                            padding: btnPadding,
                            border: 'none',
                            fontWeight: '600',
                            fontSize: '16px',
                            cursor: 'text',
                            outline: 'none',
                            boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
                            display: 'inline-block'
                        },
                        placeholder: 'Install App',
                        keepPlaceholderOnFocus: true,
                        allowedFormats: ['core/bold', 'core/italic', 'core/underline']
                    })
                ),
                el('div', { 
                    style: { 
                        fontSize: '10px', 
                        fontWeight: '700', 
                        color: '#94a3b8', 
                        textTransform: 'uppercase', 
                        marginTop: '12px',
                        letterSpacing: '0.5px'
                    } 
                }, 'PWA Install Button (Click text to edit)')
            );
        },
        save: function() {
            return null;
        }
    });
})(window.wp);
