<?php
/*-------------------------------------------
 *
 * Post Editor Screen
 *
 -------------------------------------------*/

/* Fire our meta box setup function on the post editor screen */
/*------------------------------------------------*/
add_action( 'load-post.php', 'nwxrview_meta_boxes_setup' );
add_action( 'load-post-new.php', 'nwxrview_meta_boxes_setup' );

/* Meta box setup function. */
/*--------------------*/
function nwxrview_meta_boxes_setup () {

	add_action( 'add_meta_boxes', 'nwxrview_add_meta_boxes' );
	add_action( 'save_post', 'save_nwxrview_meta', 10, 2 );

}

/* Create the meta box to be displayed */
/*------------------------------*/
function nwxrview_add_meta_boxes() {

	add_meta_box (
		'nwxrview',                                 //Unique ID
		esc_html__( 'Review Data', 'nwxrview' ),    //Title
		'nwxrview_meta_box',                        //Callback function
		'post',                                     //Admin page (or post type)
		'normal',                                   //Context
		'default'                                   //Priority
	);
}

/* Display the post meta box. */
/*-----------------------*/
function nwxrview_meta_box( $object, $box ) {
	$nwxrview_meta_data = get_post_meta( get_the_id(), 'nwxrview', true);
	wp_nonce_field( basename( __FILE__ ), 'nwxrview_nonce' ); ?>

	<p>
	<label for="nwxrview"><?php _e( "Add a review to this post", 'example' ); ?> </label>
	</p>
	<p>

	Review Box Position: <select name="nwxrview-position">
							<option value="default">Default (Bottom)</option>
							<option value="top">Top</option>
							<option value="shortcode">Shortcode</option>
						</select>
	</p>
	<p>

	Summary: <textarea class="widefat" type="text" name="nwx-rview-sum" id="nwxrview_summary" value="" size="50" ><?php echo htmlentities( get_post_meta( get_the_id(), 'nwx-rview-sum', true ) );  ?></textarea>

	<?php for ( $w=1; $w <= 10; $w++ ){ ?>

		<b>Attribute <?php echo $w; ?>:</b><input type="text" name="nwxrview[<?php echo $w; ?>][name]" id="nwxrview-<?php echo $w; ?>" value="<?php if( !empty( $nwxrview_meta_data[$w]['name'] )) echo esc_html_e( $nwxrview_meta_data[$w]['name'], 'nwxrview' ); ?>" size="20" />

		<b>Score:</b><input type="range" id="nwxrview-<?php echo $w; ?>range" name="nwxrview-<?php echo $w; ?>range" min="0" max="100" value="<?php if( !empty( $nwxrview_meta_data[$w]['score'] ) ) echo $nwxrview_meta_data[$w]['score']; ?>" onchange="nwxUpdateTextInput<?php echo $w; ?>(this.value);">

		<input type="text" id="nwxrview[<?php echo $w; ?>][score]" name="nwxrview[<?php echo $w; ?>][score]" value="<?php if( !empty( $nwxrview_meta_data[$w]['score'] ) ) echo $nwxrview_meta_data[$w]['score']; ?>"  style="width:50px; opacity: 0.7;" />

		<script type="text/javascript">
			function nwxUpdateTextInput<?php echo esc_js( $w ); ?>(val) {
				document.getElementById('nwxrview[<?php echo $w; ?>][score]').value=val;
			}</script>

		</p>
	<?php } }

/* Save the meta box's post metadata. */
/*------------------------------*/
function save_nwxrview_meta( $post_id, $post ) {

	/* Verify noonce before proceeding */
	/*----------------------------*/
	if ( !isset( $_POST['nwxrview_nonce'] ) || !wp_verify_nonce( $_POST['nwxrview_nonce'], basename(__FILE__) ) ) {

		return $post_id;
	}

	/* Get post type objcet */
	/*-----------------*/
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post */
	/*-----------------------------------------------*/
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {

		return $post_id;
	}


	if ( isset( $_POST['nwxrview'] ) ) {

		update_post_meta( $post_id, 'nwxrview', $_POST['nwxrview'] );
		update_post_meta( $post_id, 'nwx-rview-sum', $_POST['nwx-rview-sum'] );
		update_post_meta( $post_id, 'nwxrview-position', $_POST['nwxrview-position'] );
	}

}
