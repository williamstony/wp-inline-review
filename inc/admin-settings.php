<?php
/*---------------------------------------------
*
* Admin/Settings Page
*
--------------------------------------------*/

/* Enqueue Scripts on Admin Page
--------------------------------*/
add_action( 'admin_enqueue_scripts', 'nwxrview_admin_scripts' );

function nwxrview_admin_scripts() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'nwxrview_color_picker', plugins_url( 'js/flexi-color-picker/colorpicker.min.js', dirname( __FILE__ ) ), array(), '1.1', false );
	wp_enqueue_style( 'nwxrview_color_style', plugins_url( 'css/nwxrviewadmin.min.css', dirname( __FILE__ ) ) );

}

/* Settings Page */
/*--------------*/
add_action( 'admin_menu', 'nwxrview_options_page' );

/* Adding sub page to settings menu */
/*---------------------------------*/
function nwxrview_options_page() {
	add_options_page( 'Inline Review Options', 'Inline Review Options', 'administrator', 'rview-admin', 'nwxrview_page_gen' );
}

/* Register settings, add fields */
/*------------------------------*/
add_action( 'admin_init', 'nwxrview_init' );

function nwxrview_init() {
	register_setting( 'nwxrview_options', 'nwxrview_options', 'nwxrview_options_validate' );
	add_settings_section( 'main_section', '', 'nwxrview_text', 'rview-admin' );
	add_settings_field( 'rview_header_bg', 'Header Backgrounds:', 'nwxrview_header_bg', 'rview-admin', 'main_section' );
	add_settings_field( 'rview_highlight_color', 'Highlight color(includes bars):', 'nwxrview_highlight_color', 'rview-admin', 'main_section' );
	add_settings_field( 'rview_border_style', 'Border Style:', 'nwxrview_border_style', 'rview-admin', 'main_section' );
	add_settings_field( 'rview_own_styles', 'Use My Own Styles:', 'nwxrview_own_style', 'rview-admin', 'main_section' );

}

function  nwxrview_text() {
	echo '<div style="width: 800px;"><p>Enter style settings below. Use hex values with the "#" for the colors. ex: #020202</p><strong>Using The Color Picker:</strong> To use the color picker at the bottom of this page, first select the field you\'d like to change the color of. Then use the color picker at the bottom to find the color you\'d like. The valuse of the field ex: #010101 will change as you move around the color selection area. When you are satisified with the colors simply click "Save Changes" and you are done.<p> Use the "Use My Own Styles" to override the embedded styles set by this page.</p></div><br><div class="nwxrview_opt_page">';
}

/*Field callback functions */
/*------------------------*/
function nwxrview_highlight_color() {
	$options = get_option( 'nwxrview_options' );
	echo '<input id="rview_highlight_color" class="nwxhighlight_color" name="nwxrview_options[highlight_color]" size="40" onFocus="setId(this.id)" type="text" value="' . $options['highlight_color'] . '" />';
}

function nwxrview_border_style() {
	$options    = get_option( 'nwxrview_options' );
	$nwx_styles = array( 'Solid', 'Dashed', 'Dotted', 'Hidden' );
	$nwxrview_utilities = new nwxrview_util();
	
	echo '<select id="style_select" class="nwxborder_style" name="nwxrview_options[border_style]" />';
	echo $nwxrview_utilities->select_build ($options['border_style'], $nwx_styles);
	echo '</select>';
}

function nwxrview_header_bg() {
	$options = get_option( 'nwxrview_options' );
	echo '<input id="plugin_text_color" class="nwxheader_bg" name="nwxrview_options[header_bg]" size="40" type="text" onFocus="setId(this.id)" value="' . $options['header_bg'] . '" />';
}

function nwxrview_own_style() {
	$options = get_option( 'nwxrview_options' );
	if ( $options['own_style'] == 'yes' ) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	echo '<input type="checkbox" name="nwxrview_options[own_style]" id="rview_own_styles" value="yes"' . $checked . ' />';
}

/* Options Page Function */
/*--------------------- */
function nwxrview_page_gen() {
	?>
	<div class="opt_wrap">

		<div class="icon32" id="icon-options-general"><br></div>
		<h1> Inline Review Options</h1>

		<form action="options.php" method="post">
			<?php settings_fields( 'nwxrview_options' ); ?>
			<?php do_settings_sections( 'rview-admin' ); ?>
			<p class="submit">
				<input name="Submit" type="submit" class="nwxrview_save"
				       value="<?php esc_attr_e( 'Save Changes' ); ?>"/>
			</p>
		</form>
	</div>
	<div class="nwxrview_opt_right">
		<div id="color-picker" class="cp-normal"></div>
	</div>

	<script type="text/javascript">
		var nwxCur_id;
		function setId(id) {
			nwxCur_id = id;
		}

		ColorPicker(
			document.getElementById('color-picker'),

			function (hex) {
				var nwxMyelm = document.getElementById(nwxCur_id);
				nwxMyelm.value = hex;
			});

	</script>
<?php
}

/*Validate everything before saving*/
/*----------------------------*/
function nwxrview_options_validate( $input ) {
	$input['header_bg']       = sanitize_text_field( $input['header_bg'] );
	$input['border_style']    = sanitize_text_field( $input['border_style'] );
	$input['highlight_color'] = sanitize_text_field( $input['highlight_color'] );
	$input['own_style']       = sanitize_option( 'own_style', $input['own_style']);

	return $input; //Validated
}
