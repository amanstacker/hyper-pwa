<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HYPWA_Core_Feature_Settings {

	public static function render() {

	    $accordions = self::get_accordions();

	    foreach ( $accordions as $accordion ) {

	    	$toggle_option 	= 	HYPWA_Options::get( $accordion['fields'][0]['id'], 1 );	
	    	$toggle_name 	=	$accordion['fields'][0]['name'];

	        ?>
	        <div class="hypwa-card">
                <div class="hypwa-card-header">
                    <div class="hypwa-card-title-block">
                        <div class="hypwa-card-icon blue-icon"><span class="<?php echo esc_attr( $accordion['icon'] ); ?>"></span></div>
                        <div>
                            <h3><?php echo esc_html( $accordion['title'] ); ?></h3>
                            <p><?php echo esc_html( $accordion['desc'] ); ?></p>
                        </div>
                    </div>
                    <div class="hypwa-card-actions">
                        <?php if ( ! empty( $accordion['doc_link'] ) ) : ?>
                            <a href="<?php echo esc_url( $accordion['doc_link'] ); ?>" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                                <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        <?php endif; ?>
                        <div class="hypwa-toggle-label-wrap">
                            <label class="hypwa-switch">
                            	<input type="hidden" name="<?php echo esc_attr( $toggle_name ); ?>" value="0">
                                <input type="checkbox" name="<?php echo esc_attr( $toggle_name ); ?>" value="1" <?php checked( $toggle_option, '1'); ?>>
                                <span class="hypwa-slider"></span>
                            </label>
                            <span class="hypwa-toggle-txt">
								<?php echo $toggle_option ? esc_html__( 'ON', 'hyper-pwa' ) : esc_html__( 'OFF', 'hyper-pwa' ); ?>
							</span>
                        </div>
                        <span class="dashicons dashicons-arrow-down-alt2 hypwa-chevron"></span>
                    </div>
                </div>
                <div class="hypwa-card-content">

                	<?php
                	if ( $accordion['id'] === 'hypwa_cf_connectivity_notices_icons' ) {
                		self::render_connectivity_notices_custom();
                	} else {
                		foreach ( $accordion['fields'] as $key => $fields ) {

                			// Skip first toggle
                			if ( $key === 0 ) continue;

                			// Section head separator
                			if ( $fields['type'] === 'section_head' ) {
                				?>
                				<div class="hypwa-section-head" style="--hypwa-sh-color: <?php echo esc_attr( $fields['color'] ); ?>; --hypwa-sh-bg: <?php echo esc_attr( $fields['bg'] ); ?>;">
                					<div class="hypwa-section-head-icon">
                						<span class="dashicons <?php echo esc_attr( $fields['icon'] ); ?>"></span>
                					</div>
                					<div>
                						<strong><?php echo esc_html( $fields['label'] ); ?></strong>
                						<span><?php echo esc_html( $fields['desc'] ); ?></span>
                					</div>
                				</div>
                				<?php
                				continue;
                			}

                			HYPWA_Settings::render( $fields['type'], [
                				'id'            => $fields['id'],
                				'class' 		=> isset( $fields['class'] ) ? $fields['class'] : '', 		
                				'name'          => $fields['name'],
                				'value'         => $fields['value'],
                				'placeholder'   => '',
                				'label'         => $fields['label'],
                				'desc'          => $fields['desc'],
                				'options'		=> isset( $fields['options'] ) ? $fields['options'] : [], 
                			]);	

                		}
                	}

                	// Pre-caching accordion – render repeater
                	if ( $accordion['id'] === 'hypwa_cf_pre_caching' ) {
                		self::render_pre_caching_fields();
                	}

                	// Push notifications accordion – render fields and forms
                	if ( $accordion['id'] === 'hypwa_cf_push_notifications' ) {
                		self::render_push_notifications_fields();
                	}

                	// Screenshots accordion – render repeater
                	if ( $accordion['id'] === 'hypwa_cf_screenshots' ) {
                		self::render_screenshots_repeater();
                	}

                 	// Install button accordion – render fields
                 	if ( $accordion['id'] === 'hypwa_cf_install_button' ) {
                 		self::render_install_button_fields();
                 	}

                	if ( $accordion['id'] === 'hypwa_cf_utm_tracking' ) {

                		$utm_url 		=	get_home_url();
                		$utm_source 	=	HYPWA_Options::get( 'cf_utm_source' );
                		$utm_medium 	=	HYPWA_Options::get( 'cf_utm_medium' );	
                		$utm_campaign 	=	HYPWA_Options::get( 'cf_utm_campaign' );	
                		$utm_term 		=	HYPWA_Options::get( 'cf_utm_term' );	
                		$utm_content 	=	HYPWA_Options::get( 'cf_utm_content' );	
                		?>
				        <div class="hypwa-form-row">
						    <div class="hypwa-label-col">
						        <label>
						            <?php echo esc_html__( 'URL Preview', 'hyper-pwa' ); ?>
						        </label>
						    </div>

						    <div class="hypwa-input-col">

						        <?php
						        $full_url =
						            get_home_url() . '/?' .
						            'utm_source=<span id="hypwa-cf-utm-source">' . esc_html( $utm_source ) . '</span>' .
						            '&utm_medium=<span id="hypwa-cf-utm-medium">' . esc_html( $utm_medium ) . '</span>' .
						            '&utm_campaign=<span id="hypwa-cf-utm-campaign">' . esc_html( $utm_campaign ) . '</span>' .
						            '&utm_term=<span id="hypwa-cf-utm-term">' . esc_html( $utm_term ) . '</span>' .
						            '&utm_content=<span id="hypwa-cf-utm-content">' . esc_html( $utm_content ) . '</span>';
						        ?>

						        <div class="hypwa-cf-utm-preview">
						            <code><?php echo wp_kses_post( $full_url ); ?></code>
						        </div>

						    </div>
						</div>
				        <?php
                	}
                	
				    if ( ! empty( $accordion['doc_link'] ) ) {
				     	do_action( 'hypwa_learnmore_doc', $accordion['doc_link'] );  
				    }
				    ?>

                </div>
            </div>

	        <?php
	    }
	}

	/**
	 * Render pre caching fields
	 * */
	public static function render_pre_caching_fields() {
 
		// Retrieve all publicly queryable post types
		$post_types = get_post_types( [ 'public' => true, 'show_ui' => true ], 'objects' );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );	
		}
		
		// Saved values
		$saved_pt  = HYPWA_Options::get( 'cf_pre_cache_post_types', [] );
		if ( ! is_array( $saved_pt ) ) $saved_pt = [];
 
		$saved_urls = HYPWA_Options::get( 'cf_pre_cache_manual_urls', '' );
 
		?>
 
		<div class="hypwa-pre-cache-section-head">
			<span class="dashicons dashicons-category"></span>
			<?php esc_html_e( 'Post Types', 'hyper-pwa' ); ?>
			<span class="hypwa-field-desc" style="font-weight:400; margin-left:6px;">
				<?php esc_html_e( 'Choose how to pre-cache each post type — by latest count or by specific selection.', 'hyper-pwa' ); ?>
			</span>
		</div>
 
		<div class="hypwa-pc-grid-header">
			<div class="hypwa-pc-col-type"><?php esc_html_e( 'Post Type', 'hyper-pwa' ); ?></div>
			<div class="hypwa-pc-col-latest"><?php esc_html_e( 'Latest Count', 'hyper-pwa' ); ?></div>
			<div class="hypwa-pc-col-divider"><?php esc_html_e( 'OR', 'hyper-pwa' ); ?></div>
			<div class="hypwa-pc-col-specific"><?php esc_html_e( 'Select Specific', 'hyper-pwa' ); ?></div>
		</div>
		<?php foreach ( $post_types as $pt_slug => $pt_obj ) :
 
			$pt_data    = isset( $saved_pt[ $pt_slug ] ) ? $saved_pt[ $pt_slug ] : [];
			$enabled    = ! empty( $pt_data['enabled'] ) ? '1' : '0';
			$count      = isset( $pt_data['count'] ) ? absint( $pt_data['count'] ) : '';
			$specific   = isset( $pt_data['specific'] ) && is_array( $pt_data['specific'] ) ? $pt_data['specific'] : [];
			$mode       = ! empty( $specific ) ? 'specific' : 'count'; // which side is active
 
			$posts_for_select = get_posts( [
				'post_type'      => $pt_slug,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );
 
			$base_name = 'hypwa_options[cf_pre_cache_post_types][' . esc_attr( $pt_slug ) . ']';
			$row_id    = 'hypwa-pc-row-' . $pt_slug;
		?>
 
		<div class="hypwa-pc-row <?php echo $enabled === '1' ? 'hypwa-pc-row--active' : ''; ?>" id="<?php echo esc_attr( $row_id ); ?>">
 
			<!-- Col 1: Checkbox + post type label -->
			<div class="hypwa-pc-col-type">
				<input
					type="hidden"
					name="<?php echo esc_attr( $base_name ); ?>[enabled]"
					value="0"
				/>
				<label class="hypwa-pc-type-label">
					<input
						type="checkbox"
						class="hypwa-pc-type-checkbox"
						name="<?php echo esc_attr( $base_name ); ?>[enabled]"
						value="1"
						data-row="<?php echo esc_attr( $row_id ); ?>"
						<?php checked( $enabled, '1' ); ?>
					/>
					<span class="hypwa-pc-type-icon">
						<span class="dashicons <?php echo $pt_slug === 'page' ? 'dashicons-admin-page' : ( $pt_slug === 'attachment' ? 'dashicons-admin-media' : 'dashicons-admin-post' ); ?>"></span>
					</span>
					<span class="hypwa-pc-type-name"><?php echo esc_html( $pt_obj->label ); ?></span>
				</label>
			</div>
 
			<!-- Col 2: Latest N posts (count mode) -->
			<div class="hypwa-pc-col-latest">
				<div class="hypwa-pc-count-wrap <?php echo $mode === 'specific' ? 'hypwa-pc-side--muted' : ''; ?>">
					<input
						type="number"
						class="hypwa-text-input hypwa-pc-count-input"
						name="<?php echo esc_attr( $base_name ); ?>[count]"
						value="<?php echo esc_attr( $count ); ?>"
						min="0"
						max="50"
						placeholder="5"
					/>
					<span class="hypwa-pc-count-label"><?php esc_html_e( 'latest', 'hyper-pwa' ); ?></span>
				</div>
			</div>
 
			<!-- Col 3: OR divider -->
			<div class="hypwa-pc-col-divider">
				<span class="hypwa-pc-or-badge"><?php esc_html_e( 'OR', 'hyper-pwa' ); ?></span>
			</div>
 
			<!-- Col 4: Select specific posts -->
			<div class="hypwa-pc-col-specific">
				<div class="hypwa-pc-specific-wrap <?php echo $mode === 'count' && empty( $specific ) ? 'hypwa-pc-side--muted' : ''; ?>">
					<select
						class="hypwa-pc-specific-select"
						name="<?php echo esc_attr( $base_name ); ?>[specific][]"
						multiple
					>
						<?php foreach ( $posts_for_select as $post_item ) : ?>
							<option
								value="<?php echo esc_attr( $post_item->ID ); ?>"
								<?php echo in_array( $post_item->ID, array_map( 'intval', $specific ) ) ? 'selected' : ''; ?>
							>
								<?php echo esc_html( $post_item->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
 
		</div><!-- /.hypwa-pc-row -->
 
		<?php endforeach; ?>
 
		<!-- Manual URLs  -->
		<div class="hypwa-form-row" style="margin-top: 8px;">
			<div class="hypwa-label-col">
				<label for="hypwa-pc-manual-urls">
					<?php esc_html_e( 'Manual URLs', 'hyper-pwa' ); ?>
				</label>
				<span class="hypwa-field-desc">
					<?php esc_html_e( 'Enter URLs to pre-cache, per line.', 'hyper-pwa' ); ?>
					<br><code style="font-size:10px;"><?php echo esc_html( home_url('/about') ); ?>, <?php echo esc_html( home_url('/contact') ); ?></code>
				</span>
			</div>
			<div class="hypwa-input-col">
				<textarea
					id="hypwa-pc-manual-urls"
					class="hypwa-textarea-input hypwa-pc-manual-urls"
					name="hypwa_options[cf_pre_cache_manual_urls]"
					placeholder="<?php echo esc_attr( home_url('/about') . ', ' . home_url('/contact') ); ?>"
					rows="4"
				><?php echo esc_textarea( $saved_urls ); ?></textarea>
			</div>
		</div>
 
		<?php
	}

	/**
	 * Render the Screenshots repeater for Narrow + Wide form factors
	 * */
	public static function render_screenshots_repeater() {
 
		$form_factors = [
			'narrow' => [
				'label' => esc_html__( 'Form Factor – Narrow', 'hyper-pwa' ),
				'desc'  => esc_html__( 'Screenshots for narrow/mobile viewports (e.g. 390×844). Displayed in mobile install prompts.', 'hyper-pwa' ),
				'icon'  => 'dashicons-smartphone',
				'key'   => 'cf_screenshots_narrow',
			],
			'wide' => [
				'label' => esc_html__( 'Form Factor – Wide', 'hyper-pwa' ),
				'desc'  => esc_html__( 'Screenshots for wide/desktop viewports (e.g. 1280×800). Displayed in desktop install prompts.', 'hyper-pwa' ),
				'icon'  => 'dashicons-desktop',
				'key'   => 'cf_screenshots_wide',
			],
		];
 
		foreach ( $form_factors as $factor_key => $factor ) :
 
			$rows = HYPWA_Options::get( $factor['key'], [] );
			if ( ! is_array( $rows ) || empty( $rows ) ) {
				$rows = [ [ 'url' => '' ] ];
			}
 
			$base_name = 'hypwa_options[' . $factor['key'] . ']';
			?>
 
			<div class="hypwa-form-row hypwa-screenshots-ff-row">
 
				<!-- Label Column -->
				<div class="hypwa-label-col">
					<label>
						<span class="dashicons <?php echo esc_attr( $factor['icon'] ); ?> hypwa-ff-icon"></span>
						<?php echo esc_html( $factor['label'] ); ?>
					</label>
					<span class="hypwa-field-desc"><?php echo esc_html( $factor['desc'] ); ?></span>
				</div>
 
				<!-- Repeater Column -->
				<div class="hypwa-input-col">
 
					<div
						class="hypwa-repeater-wrap"
						id="hypwa-repeater-<?php echo esc_attr( $factor_key ); ?>"
						data-factor="<?php echo esc_attr( $factor_key ); ?>"
						data-name-base="<?php echo esc_attr( $base_name ); ?>"
					>
 
						<?php foreach ( $rows as $index => $row ) :
							$url   = isset( $row['url'] )   ? esc_url( $row['url'] )   : '';
							$uid   = 'hypwa-screenshot-url-' . $factor_key . '-' . $index;
						?>
 
						<div class="hypwa-repeater-item" data-index="<?php echo esc_attr( $index ); ?>">
 
							<div class="hypwa-repeater-badge"><?php echo esc_html( $index + 1 ); ?></div>
 
							<div class="hypwa-repeater-fields">
 
								
								<div class="hypwa-upload-wrapper">
									<input
										type="text"
										id="<?php echo esc_attr( $uid ); ?>"
										class="hypwa-text-input hypwa-screenshot-url-input"
										name="<?php echo esc_attr( $base_name ); ?>[<?php echo esc_attr( $index ); ?>][url]"
										value="<?php echo esc_attr( $url ); ?>"
										placeholder="https://example.com/screenshot.png"
									/>
									<button
										type="button"
										class="hypwa-upload-btn hypwa-widget-btn-outline hypwa-screenshot-upload-btn"
										data-target="<?php echo esc_attr( $uid ); ?>"
									>
										<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Upload', 'hyper-pwa' ); ?>
									</button>
								</div>
 
								<?php if ( $url ) : ?>
								<div class="hypwa-screenshot-thumb-wrap">
									<img
										src="<?php echo esc_url( $url ); ?>"
										class="hypwa-screenshot-thumb"
									/>
								</div>
								<?php endif; ?>
 
							</div><!-- /.hypwa-repeater-fields -->
 							
 							<?php
 							if ( $index !== 0 ) {
 							?>
								<button
									type="button"
									class="hypwa-repeater-remove-btn"
									title="<?php esc_attr_e( 'Remove screenshot', 'hyper-pwa' ); ?>"
								>
									<span class="dashicons dashicons-no-alt"></span>
								</button>
							<?php } ?>
 
						</div><!-- /.hypwa-repeater-item -->
 
						<?php endforeach; ?>
 
					</div><!-- /.hypwa-repeater-wrap -->
 
					<button
						type="button"
						class="hypwa-repeater-add-btn"
						data-target="hypwa-repeater-<?php echo esc_attr( $factor_key ); ?>"
					>
						<span class="dashicons dashicons-plus-alt2"></span>
						<?php esc_html_e( 'Add Screenshot', 'hyper-pwa' ); ?>
					</button>
 
				</div><!-- /.hypwa-input-col -->
 
			</div><!-- /.hypwa-form-row -->
 
		<?php endforeach;
	}

	private static function get_accordions() {

	    $post_types = get_post_types( [ 'public' => true, 'show_ui' => true ], 'objects' );
	    if ( isset( $post_types['attachment'] ) ) {
	        unset( $post_types['attachment'] ); 
	    }

	    $post_type_options = [];
	    foreach ( $post_types as $slug => $post_type_obj ) {
	        $post_type_options[ $slug ] = $post_type_obj->labels->singular_name;
	    }

	    $taxonomies = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );

	    $taxonomy_options = [];
	    foreach ( $taxonomies as $slug => $taxonomy_obj ) {
	        $taxonomy_options[ $slug ] = $taxonomy_obj->labels->singular_name;
	    }

	    $latest_pages = [];
	    $get_pages = get_posts([
	        'post_type'      => 'any', 
	        'posts_per_page' => 10,
	        'orderby'        => 'date',
	        'order'          => 'DESC',
	    ]);

	    if ( ! empty( $get_pages ) && is_array( $get_pages ) ) {
	        foreach ( $get_pages as $page_obj ) {
	            $label = ! empty( $page_obj->post_type ) ? ' (' . ucfirst( $page_obj->post_type ) . ')' : '';
	            $latest_pages[ $page_obj->ID ] = $page_obj->post_title . $label;
	        }
	    }

	    $excluded_types = HYPWA_Options::get( 'cf_exclude_caching_posts', [] ); 
	    if ( ! is_array( $excluded_types ) ) {
	        $excluded_types = ! empty( $excluded_types ) ? [ $excluded_types ] : [];
	    }
	    
	    $excluded_types = array_filter( array_map( 'intval', $excluded_types ) );

	    $ensure_saved_exists = function( $saved_ids, $current_list ) {
	        $missing_ids = [];
	        foreach ( $saved_ids as $id ) {
	            if ( ! empty( $id ) && ! isset( $current_list[ $id ] ) ) {
	                $missing_ids[] = $id;
	            }
	        }

	        if ( ! empty( $missing_ids ) ) {
	            $fetched_posts = get_posts([
	                'post__in'       => $missing_ids,
	                'post_type'      => 'any',
	                'posts_per_page' => -1,
	            ]);

	            foreach ( $fetched_posts as $post ) {
	                $post_type_obj = get_post_type_object( $post->post_type );
	                $label         = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst( $post->post_type );
	                $current_list[ $post->ID ] = $post->post_title . ' (' . $label . ')';
	            }
	        }
	        return $current_list;
	    };


	    return [
	        [
	            'title' => esc_html__( 'Push Notifications', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Connect your site to HyperPush and send push notifications to your subscribers.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-integrate-hyperpush-x-with-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-networking',
	            'id'    => 'hypwa_cf_push_notifications',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_push_status',
	                    'name'  => 'hypwa_options[cf_push_status]',
	                    'value' => HYPWA_Options::get( 'cf_push_status', 0 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	            ],
	        ],

	        [
	            'title' => esc_html__( 'iOS & Safari', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Configure install prompts, icons, and splash screens for iOS & Safari.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-ios-and-safari-settings-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-smartphone',
	            'id'    => 'hypwa_cf_ios_compatibility',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'ios_prompt_status',
	                    'name'  => 'hypwa_options[ios_prompt_status]',
	                    'value' => HYPWA_Options::get( 'ios_prompt_status', 0 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'hypwa_apple_touch_icon_upload',
	                    'name'  => 'hypwa_options[apple_touch_icon]',
	                    'value' => HYPWA_Options::get('apple_touch_icon'),
	                    'label' => esc_html__('Apple Touch Icon', 'hyper-pwa'),
	                    'desc'  => esc_html__('Icon used on iOS/iPadOS home screens and Safari on macOS. Recommended: Square PNG, 180×180 pixels. Falls back to default app icon.', 'hyper-pwa'),
	                ],
	                [
	                    'type'     => 'select',
	                    'class'    => 'hypwa-select2',
	                    'id'       => 'hypwa_apple_status_bar_style_select_field',
	                    'name'     => 'hypwa_options[apple_status_bar_style]',
	                    'value'    => HYPWA_Options::get('apple_status_bar_style', 'default'),
	                    'label'    => esc_html__('iOS Status Bar Style', 'hyper-pwa'),
	                    'desc'     => esc_html__('Appearance of the status bar on iOS devices in standalone mode.', 'hyper-pwa'),
	                    'options'  => [
	                        'default'           => esc_html__('Default (White/Black status bar background)', 'hyper-pwa'),
	                        'black'             => esc_html__('Black (Black status bar background)', 'hyper-pwa'),
	                        'black-translucent' => esc_html__('Black Translucent (App content spans under status bar)', 'hyper-pwa'),
	                    ],
	                ],
	                [
	                    'type'  => 'checkbox',
	                    'id'    => 'hypwa_ios_splash_screens_enabled_field',
	                    'name'  => 'hypwa_options[ios_splash_screens_enabled]',
	                    'value' => HYPWA_Options::get('ios_splash_screens_enabled', '0'),            
	                    'label' => esc_html__('iOS Splash Screens', 'hyper-pwa'),
	                    'desc'  => esc_html__('Generates Apple Startup Image tags to show a branded splash screen on iOS/iPadOS.', 'hyper-pwa'),
	                ],
	                [
	                    'type'  => 'section_head',
	                    'id'    => 'hypwa_ios_prompt_head',
	                    'icon'  => 'dashicons-smartphone',
	                    'color' => '#007aff',
	                    'bg'    => '#f0f7ff',
	                    'label' => esc_html__( 'Custom Install Prompt Settings', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Configure custom messages shown to iOS/Safari users to prompt app installation.', 'hyper-pwa' ),
	                    'name'  => '',
	                    'value' => '',
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa_ios_prompt_title_input',
	                    'name'  => 'hypwa_options[ios_prompt_title]',
	                    'value' => HYPWA_Options::get( 'ios_prompt_title' ),
	                    'label' => esc_html__( 'Prompt Title', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Customize the main title text in the iOS install banner.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'textarea',
	                    'id'    => 'hypwa_ios_prompt_desc_input',
	                    'name'  => 'hypwa_options[ios_prompt_desc]',
	                    'value' => HYPWA_Options::get( 'ios_prompt_desc' ),
	                    'label' => esc_html__( 'Prompt Description', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Customize the body/description text in the banner.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa_ios_prompt_step1_input',
	                    'name'  => 'hypwa_options[ios_prompt_step1]',
	                    'value' => HYPWA_Options::get( 'ios_prompt_step1' ),
	                    'label' => esc_html__( 'Step 1 Text', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Text explaining the first step (tapping share button).', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa_ios_prompt_step2_input',
	                    'name'  => 'hypwa_options[ios_prompt_step2]',
	                    'value' => HYPWA_Options::get( 'ios_prompt_step2' ),
	                    'label' => esc_html__( 'Step 2 Text', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Text explaining the second step (tapping Add to Home Screen).', 'hyper-pwa' ),
	                ],
	            ],
	        ],

	        [
	            'title' => esc_html__( 'Screenshots', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Add app screenshots for install prompts.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-mobile-and-desktop-screenshots-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-camera',
	            'id'    => 'hypwa_cf_screenshots',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_screenshots_status',
	                    'name'  => 'hypwa_options[cf_screenshots_status]',
	                    'value' => HYPWA_Options::get( 'cf_screenshots_status', 1 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	            ],
	        ],

	        [
	            'title' => esc_html__( 'Caching Strategies', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Pages cache strategy and runtime cache controls.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-caching-strategies-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-database',
	            'name'  => 'hypwa_options[site_identity]',
	            'id'    => 'hypwa_cf_caching_strategies',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_caching_status',
	                    'name'  => 'hypwa_options[cf_caching_status]',
	                    'value' => HYPWA_Options::get( 'cf_caching_status', 1 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	                [
	                    'type'     => 'select',
	                    'class'    => 'hypwa-select2',
	                    'id'       => 'hypwa_cf_page_cache_strategy_select_field',
	                    'name'     => 'hypwa_options[cf_page_cache_strategy]',
	                    'value'    => HYPWA_Options::get( 'cf_page_cache_strategy' ),
	                    'label'    => __( 'Pages Cache Strategy', 'hyper-pwa' ),
	                    'desc'     => __( 'Control how pages are cached for faster loading and better performance.', 'hyper-pwa' ),
	                    'options'  => [ 
	                        'stale_while_revalidate' => __( 'Stale While Revalidate (Recommended)', 'hyper-pwa' ), 
	                        'network_first'           => __( 'Network First', 'hyper-pwa' ), 
	                        'cache_first'             => __( 'Cache First', 'hyper-pwa' ), 
	                        'network_only'            => __( 'Network Only', 'hyper-pwa' ), 
	                    ],
	                ],
	                [
	                    'type'     => 'select',
	                    'class'    => 'hypwa-select2',
	                    'id'       => 'hypwa_cf_static_assets_cache_strategy_select_field',
	                    'name'     => 'hypwa_options[cf_static_assets_cache_strategy]',
	                    'value'    => HYPWA_Options::get('cf_static_assets_cache_strategy'),
	                    'label'    => __( 'Static Assets Strategy-( CSS, JS, Fonts )', 'hyper-pwa' ),
	                    'desc'     => __( 'Optimize caching for CSS, JS, and fonts to improve loading speed and performance.', 'hyper-pwa' ),
	                    'options'  => [ 
	                        'cache_first'             => __( 'Cache First (Recommended)', 'hyper-pwa' ), 
	                        'stale_while_revalidate'  => __( 'Stale While Revalidate', 'hyper-pwa' ),
	                        'network_first'           => __( 'Network First', 'hyper-pwa' ),  
	                    ],
	                ],
	                [
	                    'type'     => 'select',
	                    'class'    => 'hypwa-select2',
	                    'id'       => 'hypwa_cf_image_cache_strategy_select_field',
	                    'name'     => 'hypwa_options[cf_image_cache_strategy]',
	                    'value'    => HYPWA_Options::get('cf_image_cache_strategy'),
	                    'label'    => __( 'Images Cache Strategy', 'hyper-pwa' ),
	                    'desc'     => __( 'Control image caching to improve load times and reduce bandwidth usage.', 'hyper-pwa' ),
	                    'options'  => [ 
	                        'cache_first'             => __( 'Cache First (Recommended)', 'hyper-pwa' ), 
	                        'stale_while_revalidate'  => __( 'Stale While Revalidate', 'hyper-pwa' ),
	                        'network_first'           => __( 'Network First', 'hyper-pwa' ),  
	                    ],
	                ],
	            ],
	        ],

	        [
	            'title' => esc_html__( 'Pre Caching', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Select post types and URLs to pre-cache for offline access.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-set-up-pre-caching-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-backup',
	            'id'    => 'hypwa_cf_pre_caching',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_pre_caching_status',
	                    'name'  => 'hypwa_options[cf_pre_caching_status]',
	                    'value' => HYPWA_Options::get( 'cf_pre_caching_status', 1 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	            ],
	        ],


	        [
	            'title' => esc_html__( 'Exclude from Caching', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Select post types and URLs to exclude from caching.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-exclude-pages-posts-or-urls-from-caching-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-backup',
	            'id'    => 'hypwa_cf_exclude_from_caching',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_exclude_from_caching_status',
	                    'name'  => 'hypwa_options[cf_exclude_from_caching_status]',
	                    'value' => HYPWA_Options::get( 'cf_exclude_from_caching_status' ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	                [
	                    'type'    => 'multiselect',
	                    'class'   => 'hypwa-select2',
	                    'id'      => 'cf_exclude_caching_post_types',
	                    'name'    => 'hypwa_options[cf_exclude_caching_post_types]',
	                    'value'   => HYPWA_Options::get( 'cf_exclude_caching_post_types' ),
	                    'label'   => __( 'Post Types', 'hyper-pwa' ),
	                    'desc'    => __( 'Select post types that should not be cached', 'hyper-pwa' ), // Fixed typo
	                    'options' => $post_type_options,
	                ],
	                [
	                    'type'    => 'multiselect',
	                    'class'   => 'hypwa-select2',
	                    'id'      => 'cf_exclude_caching_taxonomies',
	                    'name'    => 'hypwa_options[cf_exclude_caching_taxonomies]',
	                    'value'   => HYPWA_Options::get( 'cf_exclude_caching_taxonomies' ),
	                    'label'   => __( 'Taxonomies', 'hyper-pwa' ),
	                    'desc'    => __( 'Select taxonomies that should not be cached', 'hyper-pwa' ), // Fixed typo
	                    'options' => $taxonomy_options,
	                ],
	                [
	                    'type'    => 'multiselect',
	                    'class'   => 'hypwa-select2 hypwa-ajax-page-search',
	                    'id'      => 'cf_exclude_caching_posts',
	                    'name'    => 'hypwa_options[cf_exclude_caching_posts]',
	                    'value'   => $excluded_types,
	                    'label'   => __( 'Specific Content', 'hyper-pwa' ),
	                    'desc'    => __( 'Select specific posts, pages, custom post types, products etc you want to exclude from caching.', 'hyper-pwa' ),
	                    'options' => $ensure_saved_exists( $excluded_types, $latest_pages ),
	                ],
	                [
	                    'type'  => 'textarea',
	                    'id'    => 'cf_exclude_caching_url_patterns',
	                    'name'  => 'hypwa_options[cf_exclude_caching_url_patterns]',
	                    'value' => HYPWA_Options::get( 'cf_exclude_caching_url_patterns' ),
	                    'label' => __( 'URLs & URL Patterns', 'hyper-pwa' ),
	                    'desc'  => __( 'Enter one URL path or pattern per line', 'hyper-pwa' ),
	                ],
	            ],
	        ],

	        [
	            'title' => esc_html__( 'UTM Tracking', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Manage preloaded resources.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-utm-tracking-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-visibility',
	            'id'    => 'hypwa_cf_utm_tracking',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_utm_tracking_status',
	                    'name'  => 'hypwa_options[cf_utm_tracking_status]',
	                    'value' => HYPWA_Options::get( 'cf_utm_tracking_status' ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa-cf-utm-source-input',
	                    'name'  => 'hypwa_options[cf_utm_source]',
	                    'class' => 'hypwa-cf-utm-input-fields',
	                    'value' => HYPWA_Options::get( 'cf_utm_source' ),
	                    'label' => esc_html__( 'Source', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'The primary visible name of your PWA application.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa-cf-utm-medium-input',
	                    'class' => 'hypwa-cf-utm-input-fields',
	                    'name'  => 'hypwa_options[cf_utm_medium]',
	                    'value' => HYPWA_Options::get( 'cf_utm_medium' ),
	                    'label' => esc_html__( 'Medium', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'The primary visible name of your PWA application.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa-cf-utm-campaign-input',
	                    'class' => 'hypwa-cf-utm-input-fields',
	                    'name'  => 'hypwa_options[cf_utm_campaign]',
	                    'value' => HYPWA_Options::get( 'cf_utm_campaign' ),
	                    'label' => esc_html__( 'Campaign', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'The primary visible name of your PWA application.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa-cf-utm-term-input',
	                    'class' => 'hypwa-cf-utm-input-fields',
	                    'name'  => 'hypwa_options[cf_utm_term]',
	                    'value' => HYPWA_Options::get( 'cf_utm_term' ),
	                    'label' => esc_html__( 'Term', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'The primary visible name of your PWA application.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'text',
	                    'id'    => 'hypwa-cf-utm-content-input',
	                    'class' => 'hypwa-cf-utm-input-fields',
	                    'name'  => 'hypwa_options[cf_utm_content]',
	                    'value' => HYPWA_Options::get( 'cf_utm_content' ),
	                    'label' => esc_html__( 'Content', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'The primary visible name of your PWA application.', 'hyper-pwa' ),
	                ],
	            ],
	        ],              
	        
			[
			    'title' => esc_html__( 'Install Button Shortcode', 'hyper-pwa' ),
			    'desc'  => esc_html__( 'Place PWA install button anywhere.', 'hyper-pwa' ),
			    'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-use-the-install-button-shortcode-in-hyper-pwa/',
			    'icon'  => 'dashicons dashicons-button',
			    'id'    => 'hypwa_cf_install_button',
			    'fields' => [
			        [
			            'type'  => 'toggle',
			            'id'    => 'cf_install_button_status',
			            'name'  => 'hypwa_options[cf_install_button_status]',
			            'value' => HYPWA_Options::get( 'cf_install_button_status', '0' ),
			            'label' => '',
			            'desc'  => '',
			        ],
			    ],
			],

	        [
	            'title' => esc_html__( 'Legacy Icons', 'hyper-pwa' ),
	            'desc'  => esc_html__( 'Upload legacy app icons for older devices and platforms.', 'hyper-pwa' ),
	            'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-legacy-icons-in-hyper-pwa/',
	            'icon'  => 'dashicons dashicons-images-alt2',
	            'id'    => 'hypwa_cf_legacy_icons',
	            'fields' => [
	                [
	                    'type'  => 'toggle',
	                    'id'    => 'cf_legacy_icon_status',
	                    'name'  => 'hypwa_options[cf_legacy_icon_status]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_icon_status', 1 ),
	                    'label' => '',
	                    'desc'  => '',
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_72',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_72]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_72' ),
	                    'label' => esc_html__( 'App Icon (72x72)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 72×72 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_96',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_96]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_96' ),
	                    'label' => esc_html__( 'App Icon (96x96)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 96×96 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_128',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_128]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_128' ),
	                    'label' => esc_html__( 'App Icon (128x128)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 128×128 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_144',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_144]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_144' ),
	                    'label' => esc_html__( 'App Icon (144x144)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 144×144 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_152',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_152]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_152' ),
	                    'label' => esc_html__( 'App Icon (152x152)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 152×152 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_192',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_192]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_192' ),
	                    'label' => esc_html__( 'App Icon (192x192)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 192×192 PNG icon.', 'hyper-pwa' ),
	                ],
	                [
	                    'type'  => 'upload',
	                    'id'    => 'cf_legacy_app_icon_384',
	                    'name'  => 'hypwa_options[cf_legacy_app_icon_384]',
	                    'value' => HYPWA_Options::get( 'cf_legacy_app_icon_384' ),
	                    'label' => esc_html__( 'App Icon (384x384)', 'hyper-pwa' ),
	                    'desc'  => esc_html__( 'Upload a 384×384 PNG icon.', 'hyper-pwa' ),
	                ],
	            ],
	        ],

			[
			    'title' => esc_html__( 'Connectivity Notices', 'hyper-pwa' ),
			    'desc'  => esc_html__( 'Configure messages shown to users when they go online or offline.', 'hyper-pwa' ),
			    'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-and-display-connectivity-notices-in-hyper-pwa/',
			    'icon'  => 'dashicons dashicons-rss',
			    'id'    => 'hypwa_cf_connectivity_notices_icons',
			    'fields' => [
			        [
			            'type'  => 'toggle',
			            'id'    => 'cf_connectivity_notices_status',
			            'name'  => 'hypwa_options[cf_connectivity_notices_status]',
			            'value' => HYPWA_Options::get( 'cf_connectivity_notices_status', '0' ),
			            'label' => '',
			            'desc'  => '',
			        ],
			        [
			            'type'  => 'section_head',
			            'id'    => 'hypwa_conn_offline_head',
			            'icon'  => 'dashicons-wifi-alt2',
			            'color' => '#ef4444',
			            'bg'    => '#fef2f2',
			            'label' => esc_html__( 'Offline Notice', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Shown when the user loses internet connection.', 'hyper-pwa' ),
			            'name'  => '',
			            'value' => '',
			        ],
			        [
			            'type'  => 'text',
			            'id'    => 'hypwa_cf_conn_notice_title_input',
			            'name'  => 'hypwa_options[cf_conn_notice_title]',
			            'placeholder' => 'Title',
			            'class' => 'hypwa-cf-conn-notice-input-fields',
			            'value' => HYPWA_Options::get( 'cf_conn_notice_title' ),
			            'label' => esc_html__( 'Message Title', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Show a custom message when user is offline', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'textarea',
			            'id'    => 'hypwa_cf_conn_notice_description_input',
			            'name'  => 'hypwa_options[cf_conn_notice_description]',
			            'placeholder' => 'Description',
			            'class' => 'hypwa-cf-conn-notice-input-fields',
			            'value' => HYPWA_Options::get( 'cf_conn_notice_description' ),
			            'label' => esc_html__( 'Message Description', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Description text shown below the title.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'color',
			            'id'    => 'hypwa_cf_conn_notice_bg_color_input',
			            'name'  => 'hypwa_options[cf_conn_notice_bg_color]',
			            'value' => HYPWA_Options::get( 'cf_conn_notice_bg_color', '#2563eb' ),
			            'label' => esc_html__( 'Background Color', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Set background color.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'color',
			            'id'    => 'hypwa_cf_conn_notice_text_color_input',
			            'name'  => 'hypwa_options[cf_conn_notice_text_color]',
			            'value' => HYPWA_Options::get( 'cf_conn_notice_text_color', '#ffffff' ),
			            'label' => esc_html__( 'Text Color', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Set text color.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'text',
			            'id'    => 'hypwa_cf_conn_notice_icon_input',
			            'name'  => 'hypwa_options[cf_conn_notice_icon]',
			            'value' => HYPWA_Options::get( 'cf_conn_notice_icon', 'dashicons-wifi' ),
			            'label' => esc_html__( 'Message Icon', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Click to select a custom icon.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'section_head',
			            'id'    => 'hypwa_conn_online_head',
			            'icon'  => 'dashicons-wifi',
			            'color' => '#16a34a',
			            'bg'    => '#f0fdf4',
			            'label' => esc_html__( 'Online Notice', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Shown when internet connection is restored.', 'hyper-pwa' ),
			            'name'  => '',
			            'value' => '',
			        ],
			        [
			            'type'  => 'text',
			            'id'    => 'hypwa_cf_conn_online-notice_title_input',
			            'name'  => 'hypwa_options[cf_conn_online_notice_title]',
			            'placeholder' => 'Title',
			            'class' => 'hypwa-cf-conn-notice-input-fields',
			            'value' => HYPWA_Options::get( 'cf_conn_online_notice_title' ),
			            'label' => esc_html__( 'Message Title', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Show a custom message when user is back online', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'textarea',
			            'id'    => 'hypwa_cf_conn_online_notice_description_input',
			            'name'  => 'hypwa_options[cf_conn_online_notice_description]',
			            'placeholder' => 'Description',
			            'class' => 'hypwa-cf-conn-notice-input-fields',
			            'value' => HYPWA_Options::get( 'cf_conn_online_notice_description' ),
			            'label' => esc_html__( 'Message Description', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Description text shown below the title.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'color',
			            'id'    => 'hypwa_cf_conn_online_notice_bg_color_input',
			            'name'  => 'hypwa_options[cf_conn_online_notice_bg_color]',
			            'value' => HYPWA_Options::get( 'cf_conn_online_notice_bg_color', '#16a34a' ),
			            'label' => esc_html__( 'Background Color', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Set background color.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'color',
			            'id'    => 'hypwa_cf_conn_online_notice_text_color_input',
			            'name'  => 'hypwa_options[cf_conn_online_notice_text_color]',
			            'value' => HYPWA_Options::get( 'cf_conn_online_notice_text_color', '#ffffff' ),
			            'label' => esc_html__( 'Text Color', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Set text color.', 'hyper-pwa' ),
			        ],
			        [
			            'type'  => 'text',
			            'id'    => 'hypwa_cf_conn_online_notice_icon_input',
			            'name'  => 'hypwa_options[cf_conn_online_notice_icon]',
			            'value' => HYPWA_Options::get( 'cf_conn_online_notice_icon', 'dashicons-wifi' ),
			            'label' => esc_html__( 'Message Icon', 'hyper-pwa' ),
			            'desc'  => esc_html__( 'Click to select a custom icon.', 'hyper-pwa' ),
			        ],
			    ],
			],
	    ];  
	}

	public static function render_install_button_fields() {
		$app_name      = HYPWA_Options::get( 'app_name', get_bloginfo( 'name' ) );
		$text          = HYPWA_Options::get( 'cf_ib_text', 'Install App' );
		$bg_color      = HYPWA_Options::get( 'cf_ib_bg_color', '#2563eb' );
		$text_color    = HYPWA_Options::get( 'cf_ib_text_color', '#ffffff' );
		$border_radius = HYPWA_Options::get( 'cf_ib_border_radius', '8' );
		$padding       = HYPWA_Options::get( 'cf_ib_padding', '12px 24px' );
		
		$border_radius_px = is_numeric($border_radius) ? $border_radius . 'px' : $border_radius;
		?>
		<div class="hypwa-aiq-layout hypwa-ib-layout">
			<div class="hypwa-aiq-fields-col hypwa-ib-fields-col">
				<?php
				HYPWA_Settings::render( 'text', [
					'id'    => 'cf_ib_text',
					'name'  => 'hypwa_options[cf_ib_text]',
					'value' => $text,
					'label' => esc_html__( 'Button Text', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Text displayed inside the PWA install button.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'cf_ib_bg_color',
					'name'  => 'hypwa_options[cf_ib_bg_color]',
					'value' => $bg_color,
					'label' => esc_html__( 'Button Background Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Custom background color of the install button.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'cf_ib_text_color',
					'name'  => 'hypwa_options[cf_ib_text_color]',
					'value' => $text_color,
					'label' => esc_html__( 'Button Text Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Custom text color of the install button.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'text', [
					'id'    => 'cf_ib_border_radius',
					'name'  => 'hypwa_options[cf_ib_border_radius]',
					'value' => $border_radius,
					'label' => esc_html__( 'Border Radius', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Specify the border radius of the button (e.g. 8 or 12px).', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'text', [
					'id'    => 'cf_ib_padding',
					'name'  => 'hypwa_options[cf_ib_padding]',
					'value' => $padding,
					'label' => esc_html__( 'Padding', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Specify the padding inside the button (e.g. 12px 24px).', 'hyper-pwa' ),
				]);
				?>
				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label><?php esc_html_e( 'Shortcode Usage', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Use this shortcode anywhere on your website to place the button.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<div class="hypwa-ib-shortcode-box" onclick="navigator.clipboard.writeText('[hypwa_install_button]'); jQuery(this).find('.hypwa-copy-status').fadeIn(150).fadeOut(1500);" title="Click to copy shortcode">
							<code class="hypwa-ib-shortcode-code">[hypwa_install_button]</code>
							<span class="dashicons dashicons-admin-page hypwa-ib-shortcode-copy-icon"></span>
							<span class="hypwa-copy-status hypwa-ib-shortcode-copy-status"><?php esc_html_e( 'Copied!', 'hyper-pwa' ); ?></span>
						</div>
					</div>
				</div>
			</div>
			
			<div class="hypwa-aiq-preview-col hypwa-ib-preview-col">
				<div class="hypwa-aiq-preview-card hypwa-ib-preview-card">
					<div class="hypwa-cia-phone-frame">
						<!-- Status bar -->
						<div class="hypwa-cia-phone-statusbar"></div>
						
						<!-- App Header -->
						<div class="hypwa-cia-phone-header">
							<span class="hypwa-cia-phone-app-name"><?php echo esc_html( $app_name ); ?></span>
							<span class="dashicons dashicons-cart"></span>
						</div>
						
						<!-- App Body -->
						<div class="hypwa-cia-phone-body hypwa-cia-phone-body--centered">
							<div class="hypwa-cia-phone-placeholder-line hypwa-cia-w-70" style="margin-bottom: 8px;"></div>
							<div class="hypwa-cia-phone-placeholder-line hypwa-cia-w-50" style="margin-bottom: 24px;"></div>
							
							<!-- Preview Button -->
							<button type="button" 
									id="hypwa-ib-preview-btn-el" 
									class="hypwa-ib-preview-button-target" 
									style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; border-radius: <?php echo esc_attr( $border_radius_px ); ?>; padding: <?php echo esc_attr( $padding ); ?>;">
								<?php echo esc_html( $text ); ?>
							</button>
						</div>
					</div>
					<div class="hypwa-ib-preview-label"><?php esc_html_e( 'Live Preview', 'hyper-pwa' ); ?></div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_connectivity_notices_custom() {
		$app_name      = HYPWA_Options::get( 'app_name', get_bloginfo( 'name' ) );
		$offline_title = HYPWA_Options::get( 'cf_conn_notice_title', "You're Offline" );
		$offline_desc  = HYPWA_Options::get( 'cf_conn_notice_description', "It looks like you are not connected to the internet. Please check your connection and try again." );
		$offline_bg    = HYPWA_Options::get( 'cf_conn_notice_bg_color', '#2563eb' );
		$offline_color = HYPWA_Options::get( 'cf_conn_notice_text_color', '#ffffff' );
		$offline_icon  = HYPWA_Options::get( 'cf_conn_notice_icon', 'dashicons-wifi' );
		if ( empty( $offline_icon ) ) {
			$offline_icon = 'dashicons-wifi';
		}

		$online_title  = HYPWA_Options::get( 'cf_conn_online_notice_title', "Back online" );
		$online_desc   = HYPWA_Options::get( 'cf_conn_online_notice_description', "Your internet connection has been restored." );
		$online_bg     = HYPWA_Options::get( 'cf_conn_online_notice_bg_color', '#16a34a' );
		$online_color  = HYPWA_Options::get( 'cf_conn_online_notice_text_color', '#ffffff' );
		$online_icon   = HYPWA_Options::get( 'cf_conn_online_notice_icon', 'dashicons-wifi' );
		if ( empty( $online_icon ) ) {
			$online_icon = 'dashicons-wifi';
		}
		?>
		<div class="hypwa-aiq-layout hypwa-cn-layout">
			<div class="hypwa-aiq-fields-col hypwa-cn-fields-col">
				<?php
				// Render Offline Notice fields
				?>
				<div class="hypwa-section-head" style="--hypwa-sh-color: #ef4444; --hypwa-sh-bg: #fef2f2;">
					<div class="hypwa-section-head-icon">
						<span class="dashicons dashicons-wifi-alt2"></span>
					</div>
					<div>
						<strong><?php esc_html_e( 'Offline Notice', 'hyper-pwa' ); ?></strong>
						<span><?php esc_html_e( 'Shown when the user loses internet connection.', 'hyper-pwa' ); ?></span>
					</div>
				</div>
				<?php
				HYPWA_Settings::render( 'text', [
					'id'    => 'hypwa_cf_conn_notice_title_input',
					'name'  => 'hypwa_options[cf_conn_notice_title]',
					'placeholder' => 'Title',
					'class' => 'hypwa-cf-conn-notice-input-fields',
					'value' => $offline_title,
					'label' => esc_html__( 'Message Title', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Show a custom message when user is offline', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'textarea', [
					'id'    => 'hypwa_cf_conn_notice_description_input',
					'name'  => 'hypwa_options[cf_conn_notice_description]',
					'placeholder' => 'Description',
					'class' => 'hypwa-cf-conn-notice-input-fields',
					'value' => $offline_desc,
					'label' => esc_html__( 'Message Description', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Description text shown below the title.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'hypwa_cf_conn_notice_bg_color_input',
					'name'  => 'hypwa_options[cf_conn_notice_bg_color]',
					'value' => $offline_bg,
					'label' => esc_html__( 'Background Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Set background color.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'hypwa_cf_conn_notice_text_color_input',
					'name'  => 'hypwa_options[cf_conn_notice_text_color]',
					'value' => $offline_color,
					'label' => esc_html__( 'Text Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Set text color.', 'hyper-pwa' ),
				]);
				// Icon Picker for Offline Notice
				?>
				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label><?php esc_html_e( 'Message Icon', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Click to select a custom icon.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<div class="hypwa-cn-icon-pick" style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; cursor: pointer; box-sizing: border-box;">
							<?php if ( $offline_icon === 'dashicons-wifi' ) : ?>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01"></path></svg>
							<?php elseif ( $offline_icon === 'dashicons-wifi-alt2' ) : ?>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20"></path></svg>
							<?php else : ?>
								<span class="dashicons <?php echo esc_attr( $offline_icon ); ?> hypwa-bn-icon-preview"></span>
							<?php endif; ?>
							<input type="hidden"
								id="hypwa_cf_conn_notice_icon_input"
								name="hypwa_options[cf_conn_notice_icon]"
								class="hypwa-bn-icon-val"
								value="<?php echo esc_attr( $offline_icon ); ?>"
							/>
						</div>
					</div>
				</div>
				<?php

				// Render Online Notice fields
				?>
				<div class="hypwa-section-head" style="--hypwa-sh-color: #16a34a; --hypwa-sh-bg: #f0fdf4;">
					<div class="hypwa-section-head-icon">
						<span class="dashicons dashicons-wifi"></span>
					</div>
					<div>
						<strong><?php esc_html_e( 'Online Notice', 'hyper-pwa' ); ?></strong>
						<span><?php esc_html_e( 'Shown when internet connection is restored.', 'hyper-pwa' ); ?></span>
					</div>
				</div>
				<?php
				HYPWA_Settings::render( 'text', [
					'id'    => 'hypwa_cf_conn_online_notice_title_input',
					'name'  => 'hypwa_options[cf_conn_online_notice_title]',
					'placeholder' => 'Title',
					'class' => 'hypwa-cf-conn-notice-input-fields',
					'value' => $online_title,
					'label' => esc_html__( 'Message Title', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Show a custom message when user is back online', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'textarea', [
					'id'    => 'hypwa_cf_conn_online_notice_description_input',
					'name'  => 'hypwa_options[cf_conn_online_notice_description]',
					'placeholder' => 'Description',
					'class' => 'hypwa-cf-conn-notice-input-fields',
					'value' => $online_desc,
					'label' => esc_html__( 'Message Description', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Description text shown below the title.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'hypwa_cf_conn_online_notice_bg_color_input',
					'name'  => 'hypwa_options[cf_conn_online_notice_bg_color]',
					'value' => $online_bg,
					'label' => esc_html__( 'Background Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Set background color.', 'hyper-pwa' ),
				]);
				HYPWA_Settings::render( 'color', [
					'id'    => 'hypwa_cf_conn_online_notice_text_color_input',
					'name'  => 'hypwa_options[cf_conn_online_notice_text_color]',
					'value' => $online_color,
					'label' => esc_html__( 'Text Color', 'hyper-pwa' ),
					'desc'  => esc_html__( 'Set text color.', 'hyper-pwa' ),
				]);
				// Icon Picker for Online Notice
				?>
				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label><?php esc_html_e( 'Message Icon', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Click to select a custom icon.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<div class="hypwa-cn-icon-pick" style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; cursor: pointer; box-sizing: border-box;">
							<?php if ( $online_icon === 'dashicons-wifi' ) : ?>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01"></path></svg>
							<?php elseif ( $online_icon === 'dashicons-wifi-alt2' ) : ?>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hypwa-bn-icon-preview" style="color: #475569;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20"></path></svg>
							<?php else : ?>
								<span class="dashicons <?php echo esc_attr( $online_icon ); ?> hypwa-bn-icon-preview"></span>
							<?php endif; ?>
							<input type="hidden"
								id="hypwa_cf_conn_online_notice_icon_input"
								name="hypwa_options[cf_conn_online_notice_icon]"
								class="hypwa-bn-icon-val"
								value="<?php echo esc_attr( $online_icon ); ?>"
							/>
						</div>
					</div>
				</div>
				<?php
				?>
			</div>
			
			<div class="hypwa-aiq-preview-col hypwa-cn-preview-col">
				<div class="hypwa-aiq-preview-card hypwa-cn-preview-card">
					<!-- Selector Tabs -->
					<div class="hypwa-cn-preview-toggle-bar">
						<button type="button" class="hypwa-cn-toggle-tab active" data-target="offline">Offline Notice</button>
						<button type="button" class="hypwa-cn-toggle-tab" data-target="online">Online Notice</button>
					</div>

					<div class="hypwa-cia-phone-frame">
						<!-- Status bar -->
						<div class="hypwa-cia-phone-statusbar"></div>
						
						<!-- App Header -->
						<div class="hypwa-cia-phone-header">
							<span class="hypwa-cia-phone-app-name"><?php echo esc_html( $app_name ); ?></span>
							<span class="dashicons dashicons-cart"></span>
						</div>
						
						<!-- App Body -->
						<div class="hypwa-cia-phone-body">
							<div class="hypwa-cia-phone-placeholder-line hypwa-cia-w-70"></div>
							<div class="hypwa-cia-phone-placeholder-line hypwa-cia-w-50"></div>
							<div class="hypwa-cia-phone-placeholder-block"></div>
						</div>
						
						<!-- Preview Connectivity Notice Card -->
						<div id="hypwa-cn-preview-notice-el" class="hypwa-cn-preview-notice" style="background: <?php echo esc_attr( $offline_bg ); ?>; color: <?php echo esc_attr( $offline_color ); ?>;">
							<!-- Icon box -->
							<div class="hypwa-cn-preview-icon-box">
								<?php if ( $offline_icon === 'dashicons-wifi' ) : ?>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" id="hypwa-cn-preview-icon-svg" class="hypwa-cn-preview-icon-span" style="color: inherit;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01"></path></svg>
									<span id="hypwa-cn-preview-icon-span" class="dashicons hypwa-cn-preview-icon-span" style="display: none;"></span>
								<?php elseif ( $offline_icon === 'dashicons-wifi-alt2' ) : ?>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" id="hypwa-cn-preview-icon-svg" class="hypwa-cn-preview-icon-span" style="color: inherit;"><path d="M1 6s4-4 11-4 11 4 11 4M5 10s2.5-2.5 7-2.5S19 10 19 10M9 14s1.5-1.5 3-1.5 3 1.5 3 1.5M12 18h.01M2 2l20 20"></path></svg>
									<span id="hypwa-cn-preview-icon-span" class="dashicons hypwa-cn-preview-icon-span" style="display: none;"></span>
								<?php else : ?>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" id="hypwa-cn-preview-icon-svg" class="hypwa-cn-preview-icon-span" style="display: none; color: inherit;"><path d=""></path></svg>
									<span id="hypwa-cn-preview-icon-span" class="dashicons <?php echo esc_attr( $offline_icon ); ?> hypwa-cn-preview-icon-span"></span>
								<?php endif; ?>
							</div>
							
							<!-- Text body -->
							<div class="hypwa-cn-preview-text">
								<strong id="hypwa-cn-preview-title-el" class="hypwa-cn-preview-title"><?php echo esc_html( $offline_title ); ?></strong>
								<p id="hypwa-cn-preview-desc-el" class="hypwa-cn-preview-desc"><?php echo esc_html( $offline_desc ); ?></p>
							</div>
						</div>
					</div>
					<div class="hypwa-ib-preview-label"><?php esc_html_e( 'Notice Preview', 'hyper-pwa' ); ?></div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_push_notifications_fields() {
		// 1. Check if HyperPushX is active
		if ( class_exists( 'HyperPushX' ) ) {
			?>
			<div class="hypwa-notice hypwa-notice-warning" style="margin: 15px 0; padding: 12px 16px;">
				<div class="hypwa-notice-icon">
					<span class="dashicons dashicons-warning"></span>
				</div>
				<div class="hypwa-notice-text" style="white-space: normal; display: block;">
					<strong><?php esc_html_e( 'Dedicated HyperPush-X Plugin is Active', 'hyper-pwa' ); ?></strong>
					<span class="hypwa-notice-desc" style="white-space: normal; display: block; margin-top: 4px;"><?php esc_html_e( 'Push notifications are currently managed via the dedicated HyperPush-X plugin. To avoid configuration conflicts, native push settings inside Hyper PWA have been disabled.', 'hyper-pwa' ); ?></span>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hyperpushx' ) ); ?>" class="hypwa-notice-btn">
					<?php esc_html_e( 'Go to Dashboard', 'hyper-pwa' ); ?>
				</a>
			</div>
			<?php
			return;
		}

		$connected   = HYPWA_Options::get( 'cf_push_connected', '0' );
		$api_key     = HYPWA_Options::get( 'cf_push_api_key', '' );
		$website_id  = HYPWA_Options::get( 'cf_push_website_id', '' );
		$website_uuid = HYPWA_Options::get( 'cf_push_website_uuid', '' );
		$send_publish = HYPWA_Options::get( 'cf_push_send_on_publish', '0' );

		?>
		<div class="hypwa-push-notifications-wrap">
			<!-- Connection Box -->
			<div class="hypwa-form-row hypwa-push-connection-row" style="margin-bottom: 24px;">
				<div class="hypwa-label-col">
					<label><?php esc_html_e( 'Connection Status', 'hyper-pwa' ); ?></label>
					<span class="hypwa-field-desc"><?php esc_html_e( 'Connect your site with your hyperpushx.com account.', 'hyper-pwa' ); ?></span>
				</div>
				<div class="hypwa-input-col">
					<?php if ( '1' === $connected ) : ?>
						<div class="hypwa-push-status-badge connected" style="display: inline-flex; align-items: center; background: #e6f4ea; color: #137333; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 13px; margin-bottom: 12px;">
							<span class="dashicons dashicons-yes" style="margin-right: 5px;"></span>
							<?php esc_html_e( 'Connected to hyperpushx.com', 'hyper-pwa' ); ?>
						</div>

						<?php
						$stats = self::get_subscriber_stats();
						if ( is_array( $stats ) ) :
							?>
							<div class="hypwa-push-stats-container" style="display: flex; gap: 15px; margin-bottom: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; max-width: 500px;">
								<div class="hypwa-push-stat-item" style="flex: 1; text-align: center;">
									<div id="hypwa-push-stat-total" style="font-size: 20px; font-weight: 700; color: #0f172a;"><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></div>
									<div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-top: 2px;"><?php esc_html_e( 'Total', 'hyper-pwa' ); ?></div>
								</div>
								<div style="width: 1px; background: #e2e8f0; align-self: stretch;"></div>
								<div class="hypwa-push-stat-item" style="flex: 1; text-align: center;">
									<div id="hypwa-push-stat-active" style="font-size: 20px; font-weight: 700; color: #10b981;"><?php echo esc_html( number_format_i18n( $stats['active'] ) ); ?></div>
									<div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-top: 2px;"><?php esc_html_e( 'Active', 'hyper-pwa' ); ?></div>
								</div>
								<div style="width: 1px; background: #e2e8f0; align-self: stretch;"></div>
								<div class="hypwa-push-stat-item" style="flex: 1; text-align: center;">
									<div id="hypwa-push-stat-expired" style="font-size: 20px; font-weight: 700; color: #ef4444;"><?php echo esc_html( number_format_i18n( $stats['expired'] ) ); ?></div>
									<div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-top: 2px;"><?php esc_html_e( 'Expired', 'hyper-pwa' ); ?></div>
								</div>
							</div>
						<?php endif; ?>

						<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px;">
							<button type="button" id="hypwa-push-refresh-stats" class="hypwa-widget-btn-outline" style="border-color: #2563eb; color: #2563eb; display: inline-flex; align-items: center; gap: 4px;">
								<span class="dashicons dashicons-update" style="font-size: 14px; width: 14px; height: 14px; margin-top: 1px;"></span>
								<?php esc_html_e( 'Refresh Stats', 'hyper-pwa' ); ?>
							</button>
							<button type="button" id="hypwa-push-disconnect-btn" class="hypwa-widget-btn-outline" style="border-color: #dc2626; color: #dc2626; margin: 0;">
								<?php esc_html_e( 'Disconnect', 'hyper-pwa' ); ?>
							</button>
						</div>
					<?php else : ?>
						<div class="hypwa-push-status-badge disconnected" style="display: inline-flex; align-items: center; background: #fce8e6; color: #c5221f; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 13px; margin-bottom: 12px;">
							<span class="dashicons dashicons-no" style="margin-right: 5px;"></span>
							<?php esc_html_e( 'Disconnected', 'hyper-pwa' ); ?>
						</div>
						<div class="hypwa-upload-wrapper" style="max-width: 500px;">
							<input type="text" id="hypwa-push-api-key-input" class="hypwa-text-input" placeholder="<?php esc_attr_e( 'Enter hyperpushx.com API Key', 'hyper-pwa' ); ?>" value="<?php echo esc_attr( $api_key ); ?>">
							<button type="button" id="hypwa-push-connect-btn" class="hypwa-btn-blue">
								<?php esc_html_e( 'Connect', 'hyper-pwa' ); ?>
							</button>
						</div>
						<p class="description" style="margin-top: 6px;">
							<a href="<?php echo esc_url( HYPWA_PUSH_API_BASE ); ?>/register?ref=hyperpwa" target="_blank" rel="noopener noreferrer" style="font-weight: 600; text-decoration: underline;">
								<?php esc_html_e( 'Get started for free', 'hyper-pwa' ); ?>
							</a>
						</p>
					<?php endif; ?>
					<div id="hypwa-push-connection-feedback" style="margin-top: 8px; font-size: 13px; font-weight: 500;"></div>
				</div>
			</div>

			<!-- Options -->
			<div class="hypwa-form-row" style="margin-bottom: 24px;">
				<div class="hypwa-label-col">
					<label><?php esc_html_e( 'Send on Post Published', 'hyper-pwa' ); ?></label>
					<span class="hypwa-field-desc"><?php esc_html_e( 'Trigger push alerts automatically on publish.', 'hyper-pwa' ); ?></span>
				</div>
				<div class="hypwa-input-col">
					<div class="hypwa-toggle-label-wrap">
						<label class="hypwa-switch">
							<input type="hidden" name="hypwa_options[cf_push_send_on_publish]" value="0">
							<input type="checkbox" name="hypwa_options[cf_push_send_on_publish]" value="1" <?php checked( $send_publish, '1' ); ?>>
							<span class="hypwa-slider"></span>
						</label>
						<span class="hypwa-toggle-txt">
							<?php echo $send_publish ? esc_html__( 'ON', 'hyper-pwa' ) : esc_html__( 'OFF', 'hyper-pwa' ); ?>
						</span>
					</div>
					<p class="description" style="margin-top: 6px;">
						<?php esc_html_e( 'Automatically send a push notification when a new post is published.', 'hyper-pwa' ); ?>
					</p>
				</div>
			</div>

			<?php if ( '1' === $connected ) : ?>
				<!-- Manual Send Notification Form -->
				<div class="hypwa-section-head" style="--hypwa-sh-color: #ff9e00; --hypwa-sh-bg: #fffbf0; margin-top: 30px; margin-bottom: 15px;">
					<div class="hypwa-section-head-icon" style="background: #ff9e00; color: #fff;">
						<span class="dashicons dashicons-megaphone"></span>
					</div>
					<div>
						<strong><?php esc_html_e( 'Send Manual Notification', 'hyper-pwa' ); ?></strong>
						<span><?php esc_html_e( 'Draft and broadcast a custom push message directly to your subscribed devices.', 'hyper-pwa' ); ?></span>
					</div>
				</div>

				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label for="hypwa-manual-title"><?php esc_html_e( 'Notification Title', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Enter the broadcast notification title.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<input type="text" id="hypwa-manual-title" class="hypwa-text-input" placeholder="<?php esc_attr_e( 'e.g., Check out our latest post!', 'hyper-pwa' ); ?>">
					</div>
				</div>

				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label for="hypwa-manual-message"><?php esc_html_e( 'Message', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Enter the notification message body text.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<textarea id="hypwa-manual-message" class="hypwa-textarea-input" placeholder="<?php esc_attr_e( 'Type your notification text here...', 'hyper-pwa' ); ?>"></textarea>
					</div>
				</div>

				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label for="hypwa-manual-image"><?php esc_html_e( 'Notification Image', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'Optional. Upload an image to display in the push notification.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<div class="hypwa-upload-wrapper">
							<input type="text" id="hypwa-manual-image" class="hypwa-text-input" placeholder="https://example.com/wp-content/uploads/image.jpg">
							<button type="button" class="hypwa-upload-btn hypwa-widget-btn-outline" data-target="hypwa-manual-image">
								<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Media Upload', 'hyper-pwa' ); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="hypwa-form-row">
					<div class="hypwa-label-col">
						<label for="hypwa-manual-url"><?php esc_html_e( 'Redirect URL', 'hyper-pwa' ); ?></label>
						<span class="hypwa-field-desc"><?php esc_html_e( 'The page users will be redirected to upon clicking.', 'hyper-pwa' ); ?></span>
					</div>
					<div class="hypwa-input-col">
						<input type="url" id="hypwa-manual-url" class="hypwa-text-input" value="<?php echo esc_url( home_url() ); ?>" placeholder="https://example.com/page">
					</div>
				</div>

				<div class="hypwa-form-row" style="margin-bottom: 30px;">
					<div class="hypwa-label-col">&nbsp;</div>
					<div class="hypwa-input-col">
						<button type="button" id="hypwa-push-send-btn" class="hypwa-btn-blue">
							<span class="dashicons dashicons-paper-plane" style="vertical-align: middle; margin-right: 4px; font-size: 16px; width: 16px; height: 16px;"></span>
							<?php esc_html_e( 'Send Notification', 'hyper-pwa' ); ?>
						</button>
						<div id="hypwa-manual-push-feedback" style="margin-top: 10px; font-size: 13px; font-weight: 500;"></div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Recommendation Box -->
			<div class="hypwa-premium-recommendation-box" style="border: 1px dashed #d1d5db; background: #fff; padding: 20px; border-radius: 8px; display: flex; align-items: flex-start; gap: 15px;">
				<div style="background: #fff8eb; color: #ff9e00; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;">
					★
				</div>
				<div>
					<h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #1f2937;">
						<?php esc_html_e( 'Looking for Full Push Notification Control?', 'hyper-pwa' ); ?>
					</h4>
					<p style="margin: 0 0 12px 0; font-size: 12.5px; color: #4b5563; line-height: 1.5;">
						<?php esc_html_e( 'While Hyper PWA includes basic push capabilities, upgrading to a premium account on hyperpushx.com and installing the dedicated HyperPush-X plugin unlocks advanced features like RSS-to-push automation, detailed delivery schedules, subscriber segmentation, interactive opt-in styling widgets, and full analytics logs.', 'hyper-pwa' ); ?>
					</p>
					<div style="display: flex; gap: 15px; align-items: center;">
						<a href="<?php echo esc_url( HYPWA_PUSH_API_BASE ); ?>/#pricing" target="_blank" rel="noopener noreferrer" style="font-size: 12.5px; color: #ff9e00; font-weight: 600; text-decoration: underline;">
							<?php esc_html_e( 'Upgrade at hyperpushx.com', 'hyper-pwa' ); ?>
						</a>
						<span style="color: #cbd5e1;">|</span>
						<a href="<?php echo esc_url( HYPWA_PUSH_API_BASE ); ?>/#pricing" target="_blank" rel="noopener noreferrer" style="font-size: 12.5px; color: #ff9e00; font-weight: 600; text-decoration: underline;">
							<?php esc_html_e( 'Get HyperPush-X WP Plugin', 'hyper-pwa' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function get_subscriber_stats() {
		$connected = HYPWA_Options::get( 'cf_push_connected', '0' );
		if ( '1' !== $connected || class_exists( 'HyperPushX' ) ) {
			return null;
		}

		$cache = get_transient( 'hypwa_push_stats_cache' );
		if ( false !== $cache ) {
			return $cache;
		}

		$api_key      = HYPWA_Options::get( 'cf_push_api_key' );
		$website_id   = HYPWA_Options::get( 'cf_push_website_id' );
		$website_uuid = HYPWA_Options::get( 'cf_push_website_uuid' );

		if ( empty( $api_key ) || empty( $website_id ) ) {
			return null;
		}

		$target_id = ! empty( $website_uuid ) ? $website_uuid : $website_id;
		$query_args = [
			'website_id' => $target_id,
			'site_id'    => $website_id,
		];
		if ( ! empty( $website_uuid ) ) {
			$query_args['website_uuid'] = $website_uuid;
		}

		$headers = [
			'Authorization' => 'Bearer ' . $api_key,
			'X-Website-ID'  => $website_id,
			'X-Site-ID'     => $website_id,
			'Accept'        => 'application/json',
		];

		// Fetch Total
		$total_url = add_query_arg( $query_args, HYPWA_PUSH_API_BASE . '/api/v1/subscribers/count' );
		$total_res = wp_remote_get( $total_url, [ 'headers' => $headers, 'timeout' => 10 ] );
		$total_count = 0;
		if ( ! is_wp_error( $total_res ) && wp_remote_retrieve_response_code( $total_res ) === 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $total_res ), true );
			$total_count = isset( $body['count'] ) ? intval( $body['count'] ) : ( isset( $body['data']['count'] ) ? intval( $body['data']['count'] ) : ( isset( $body['total'] ) ? intval( $body['total'] ) : 0 ) );
		}

		// Fetch Active
		$active_query = array_merge( $query_args, [ 'status' => 'active' ] );
		$active_url = add_query_arg( $active_query, HYPWA_PUSH_API_BASE . '/api/v1/subscribers/count' );
		$active_res = wp_remote_get( $active_url, [ 'headers' => $headers, 'timeout' => 10 ] );
		$active_count = 0;
		if ( ! is_wp_error( $active_res ) && wp_remote_retrieve_response_code( $active_res ) === 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $active_res ), true );
			$active_count = isset( $body['count'] ) ? intval( $body['count'] ) : ( isset( $body['data']['count'] ) ? intval( $body['data']['count'] ) : ( isset( $body['total'] ) ? intval( $body['total'] ) : 0 ) );
		}

		$expired_count = max( 0, $total_count - $active_count );

		$stats = [
			'total'   => $total_count,
			'active'  => $active_count,
			'expired' => $expired_count,
		];

		set_transient( 'hypwa_push_stats_cache', $stats, HOUR_IN_SECONDS );
		return $stats;
	}

}
