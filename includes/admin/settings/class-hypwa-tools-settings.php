<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HYPWA_Tools_Settings {

	public static function render() {
		?>

		<div class="hypwa-form-row">
			<div class="hypwa-label-col">
				<label><?php esc_html_e( 'Export Configuration', 'hyper-pwa' ); ?></label>
				<span class="hypwa-field-desc">
					<?php esc_html_e( 'Download your current settings, integrations, and design configurations as a JSON file.', 'hyper-pwa' ); ?>
					<a href="https://hyperpwa.com/knowledge-base/how-to-import-hyper-pwa-settings-and-restore-your-configuration/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link" style="margin-left: 5px; display: inline-flex; align-items: center; gap: 3px; text-decoration: underline;">
						<?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
						<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
					</a>
				</span>
			</div>

			<div class="hypwa-input-col">
				<button type="button" id="hypwa-export-btn" class="hypwa-btn-blue hypwa-export-btn">
					<span class="dashicons dashicons-download hypwa-btn-icon"></span>
					<?php esc_html_e( 'Download Export File', 'hyper-pwa' ); ?>
				</button>
			</div>
		</div>

		<div class="hypwa-form-row">
			<div class="hypwa-label-col">
				<label><?php esc_html_e( 'Select Import File', 'hyper-pwa' ); ?></label>

				<span class="hypwa-field-desc">
					<?php esc_html_e( 'Upload a previously exported .json file to overwrite current settings.', 'hyper-pwa' ); ?>
					<strong><?php esc_html_e( 'Note:', 'hyper-pwa' ); ?></strong>
					<?php esc_html_e( 'This cannot be undone.', 'hyper-pwa' ); ?>
					<a href="https://hyperpwa.com/knowledge-base/how-to-import-hyper-pwa-settings-and-restore-your-configuration/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link" style="margin-left: 5px; display: inline-flex; align-items: center; gap: 3px; text-decoration: underline;">
						<?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
						<span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
					</a>
				</span>
			</div>

			<div class="hypwa-input-col">
				<div class="hypwa-import-row">

					<div id="hypwa-import-dropzone" class="hypwa-import-dropzone-container">
						<input
							type="file"
							id="hypwa-import-file"
							accept="<?php echo esc_attr( '.json' ); ?>"
							class="hypwa-import-file-input"
						>

						<div id="hypwa-dropzone-default-view" class="hypwa-dropzone-default-view">
							<span class="dashicons dashicons-upload hypwa-dropzone-icon"></span>

							<span class="hypwa-dropzone-label">
								<strong class="hypwa-dropzone-browse"><?php esc_html_e( 'Browse', 'hyper-pwa' ); ?></strong>
								<?php esc_html_e( 'or drop JSON', 'hyper-pwa' ); ?>
							</span>
						</div>

						<div id="hypwa-dropzone-file-view" class="hypwa-dropzone-file-view">
							<span class="dashicons dashicons-document hypwa-dropzone-file-icon"></span>
							<span id="hypwa-import-filename-display" class="hypwa-import-filename"></span>
							<span class="hypwa-dropzone-change"><?php esc_html_e( 'Change', 'hyper-pwa' ); ?></span>
						</div>
					</div>

					<div class="hypwa-import-actions">
						<button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide" id="hypwa-import-load-btn" disabled>
							<span class="hypwa-spinner"></span>
							<span class="hypwa-btn-text"><?php esc_html_e( 'Importing...', 'hyper-pwa' ); ?></span>
						</button>

						<button type="button" id="hypwa-import-btn" class="hypwa-btn-blue hypwa-import-btn hypwa-import-btn--disabled" disabled>
							<span class="dashicons dashicons-upload hypwa-btn-icon"></span>
							<?php esc_html_e( 'Process Import', 'hyper-pwa' ); ?>
						</button>
					</div>

				</div>
			</div>
		</div>

		<?php

		HYPWA_Settings::render(
			'checkbox',
			[
				'id'          => 'hypwa_tools_dou_text_field',
				'name'        => 'hypwa_options[remove_data_on_uninstall]',
				'value'       => HYPWA_Options::get( 'remove_data_on_uninstall', '0' ),
				'placeholder' => '',
				'label'       => esc_html__( 'Remove data on uninstall', 'hyper-pwa' ),
				'desc'        => esc_html__( 'Automatically deletes all plugin data when the plugin is uninstalled, ensuring a clean removal.', 'hyper-pwa' ),
			]
		);

		?>

		<div class="hypwa-form-row">
			<div class="hypwa-label-col">
				<label><?php esc_html_e( 'Reset all settings', 'hyper-pwa' ); ?></label>
				<span class="hypwa-field-desc"><?php esc_html_e( "Reset all settings of the plugin to It's default.", 'hyper-pwa' ); ?></span>
			</div>

			<div class="hypwa-input-col">
				<button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide" id="hypwa-reset-settings-loading-btn" disabled>
					<span class="hypwa-spinner"></span>
					<span class="hypwa-btn-text"><?php esc_html_e( 'Resetting...', 'hyper-pwa' ); ?></span>
				</button>

				<button type="button" id="hypwa-reset-btn" class="hypwa-btn-blue hypwa-export-btn" style="width: auto;">
					<span class="dashicons dashicons-image-rotate hypwa-btn-icon"></span>
					<?php esc_html_e( 'Click here to reset', 'hyper-pwa' ); ?>
				</button>

				<div class="hypwa-field-message hypwa-field-message-success hypwa-hide" id="hypwa-reset-settings-success-msg">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>

				<div class="hypwa-field-message hypwa-field-message-error hypwa-hide" id="hypwa-reset-settings-error-msg">
					<span class="dashicons dashicons-warning"></span>
				</div>
			</div>
		</div>

		<?php
	}
}