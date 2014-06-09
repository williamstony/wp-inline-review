<?php
    /*
    Plugin Name: Inline Review
    Plugin URI: http://tonyw.io/inline-review
    Description: A Review engine for WordPress
    Author: TonyW
    Version: 1.1.0


    */

/* Setting our defaults to avoid an error*/
/*------------------------------*/
register_activation_hook( __FILE__, 'nwxrview_defaults' );
function nwxrview_defaults() {
    $tmp = get_option('nwxrview_options');
    if( !is_array( $tmp ) ) {
        $arr = array( "highlight_color" => "#0f0", "border_style" => "Dotted", "header_bg" => "#CCC" );
        update_option( 'nwxrview_options', $arr );
    }
}

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
                    'nwxrview',                         //Unique ID
                    esc_html__( 'Review Data', 'nwxrview' ),    //Title
                    'nwxrview_meta_box',        //Callback function
                    'post',                                              //Admin page (or post type)
                    'normal',                                             //Context
                    'default'                                         //Priority
          );
}

/* Display the post meta box. */
/*-----------------------*/
function nwxrview_meta_box( $object, $box ) {
          $nwxrview_meta_data = get_post_meta( get_the_id(), 'nwxrview', true);
          wp_nonce_field( basename( __FILE__ ), 'nwxrview_nonce' ); ?>

        <p>
            <label for="nwxrview"><?php _e( "Add a review to this post", 'example' ); ?> </label>
            <br>

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
    }

}

/*-----------------------------------------------
 *
 * Output on post
 *
 -----------------------------------------------*/

add_action( 'wp_enqueue_scripts', 'nwxrview_styles');

/* Bring the styles and scripts in
----------------------------------*/
function nwxrview_styles() {
    wp_register_style ( 'nwxrview',  plugins_url('css/nwxrviewstyle.min.css', __FILE__));
    wp_enqueue_style ( 'nwxrview');
}

add_action ( 'wp_head', 'nwxrview_embed_styles' );

/* Embed styles in head */
/*---------------------*/

function nwxrview_embed_styles () {
    global $post;
    $nwxrview_opts = get_option( 'nwxrview_options' );
    $nwxrview_css = '';

    $nwxrview_css .= '
            .nwxrview {
               border: 2px ' . esc_html($nwxrview_opts['border_style']) . ' ' . esc_html($nwxrview_opts['highlight_color']) . ';
             }

            .nwxbar {
               background-color: ' . esc_html($nwxrview_opts['highlight_color']) . ';
            }

            .nwx-rview-sum {
               border-right: 2px solid ' . esc_html($nwxrview_opts['highlight_color']) . ';
               border-bottom: 2px solid ' . esc_html($nwxrview_opts['highlight_color']) . ';
            }

            .nwx-total-score {
                border-left: 2px solid ' . esc_html($nwxrview_opts['highlight_color']) . ';
                border-bottom: 2px solid ' . esc_html($nwxrview_opts['highlight_color']) . ';
             }';

    echo '<style type="text/css" media="screen">' . $nwxrview_css . '</style>';
}

add_filter ( 'the_content', 'nwxrview_get_meta' );

/* Display the Review box after the post content */
/*-------------------------------------*/
function nwxrview_get_meta( $content ) {
    global $post;
    $nwxrview_meta_data = get_post_meta( get_the_id(), 'nwxrview', true );
    $nwxrview_opts = get_option( 'nwxrview_options' );
	$nwxrview_content = '';
    if ( !empty($nwxrview_meta_data) ) {
      //  echo var_dump( $nwxrview_meta_data);
        if (is_array($nwxrview_meta_data) && is_single()) {
	        $nwxrview_calc_data = nwxrview_calculation( $nwxrview_meta_data );
            $nwxrview_content .= '<div class="nwxrview" itemprop="review" itemscope itemtype="http://schema.org/Review">
                        <h1>Review Scores</h1>
                    <div itemprop="author" itemscope itemtype"http://schema.org/Person">
                        <span itemprop="name" style="display:none">' . esc_html(get_the_author_link()) . '</span>
                    </div>
                    <span itemprop="name" style="display:none">' . esc_html(get_the_title(get_the_id())) . '</span> <ul class="nwxrview_attribs">'
             . $nwxrview_calc_data[0] . '</ul><div class="nwx-rview-sum">
                        <div style="background: ' . esc_html($nwxrview_opts['header_bg']) . '; height: 30px; padding: 0px 5px; color: ' . esc_html($nwxrview_opts['highlight_color']) . ';">
                            <strong>Summary:</strong>
                        </div>
                            <span itemprop="description">' . esc_html(get_post_meta(get_the_id(), 'nwx-rview-sum', true)) . '</span>
                    </div>
                    <div class="nwx-total-score">
                        <div style="background: ' . esc_html($nwxrview_opts['header_bg']) . '; height: 30px; color: ' . esc_html($nwxrview_opts['highlight_color']) . '">
                            Total Score:
                        </div>
                        <h1><span itemprop="ratingValue">' . esc_html($nwxrview_calc_data[1]) . '</span></h1>
                    </div>
                    </div>';
	        $content .= $nwxrview_content;
            return $content;
        } else {

        }

    }else {
        return $content;
    }
}

/*--------------------------------------------
 *
 * Calculation Function
 *
 -------------------------------------------*/
function nwxrview_calculation ( $scores ) {
	$nwx_total_calc = 0;
	$nwx_score = 0;
	foreach ($scores as $nwx_attribs) {
		if (!empty($nwx_attribs['name'])) {
			// Dump scores down and make sure they don't go to 0 or above 10
			if ($nwx_attribs['score'] / 10 < 1)
				$nwx_attribs['score'] = 10;
			if ($nwx_attribs['score'] / 10 > 10)
				$nwx_attribs['score'] = 100;
			$nwxrview_calc_content .='<li>' . esc_html($nwx_attribs['name']) . " - " . esc_html($nwx_attribs['score']) / 10 . '
                        <div class="nwxbar" style="width: ' . esc_html($nwx_attribs['score']) . '%;"> &nbsp </div>
                        </li>';
			$nwx_score += $nwx_attribs['score'];
			$nwx_total_calc++;
		}
	}

	$nwx_total_score = ($nwx_score / $nwx_total_calc) / 10;
	$nwx_total_score = round($nwx_total_score * 2, 0) / 2;
	return array($nwxrview_calc_content, $nwx_total_score);
}

/*---------------------------
 *
 * Admin Stuff being included in
 *
 *---------------------------*/

require_once( plugin_dir_path( __FILE__  ) . 'inc/admin-settings.php' );


/*------------------------------------------
 *
 * Ouput Class Build out
 *
 *----------------------------------------*/
class nwxrview_output {

	public $nwxrview_options, $nwxrview_css, $nwxrview_output, $nwxrview_score, $nwxrview_totalscore;

	public function __construct( $nwxrview_opts, $nwxrview_meta) {


	}

	function nwxrview_calc() {


	}
}