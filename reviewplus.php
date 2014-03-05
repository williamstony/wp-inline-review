<?php
    /*
    Plugin Name: Quick Meta Review
    Plugin URI: http://www.nodewerx.com/quick-meta-review
    Description: Review engine for WordPress
    Author: Tony J Williams
    Version: 0.6.0
    Author URI:

    TODO: Clean up code
    TODO: Sanitize meta box save
    TODO: add brackets per new coding standards
    */


/* Setting our defaults to avoid an error*/
/*------------------------------*/
register_activation_hook(__FILE__, 'nwxrview_defaults');
function nwxrview_defaults() {
    $tmp = get_option('nwxrview_options');
    if(!is_array($tmp)) {
        $arr = array("text_color"=>"222", "highlight_color" => "0f0", "border_style" => "Dotted");
        update_option('nwxrview_options', $arr);
    }
}

/* Bring the styles in */
/*---------------*/
function nwxrview_styles(){
    wp_register_style ( 'nwxrview',  plugins_url('nwxrviewstyle.css', __FILE__));

    wp_enqueue_style ( 'nwxrview', plugins_url('nwxrviewstyle.css', __FILE__));
}

add_action( 'wp_enqueue_scripts', 'nwxrview_styles');

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
                    esc_html__( 'Review Data', 'example' ),    //Title
                    'nwxrview_meta_box',        //Callback function
                    'post',                                              //Admin page (or post type)
                    'normal',                                             //Context
                    'default'                                         //Priority
          );
}

/* Display the post meta box. */
/*-----------------------*/
function nwxrview_meta_box( $object, $box )  {
          $nwxrview_meta_data = get_post_meta( get_the_id(), 'nwxrview', true);
          wp_nonce_field( basename( __FILE__ ), 'nwxrview_nonce' ); ?>

        <p>
            <label for="nwxrview"><?php _e( "Add a review to this post", 'example' ); ?> </label>
            <br>

            Summary: <textarea class="widefat" type="text" name="nwx-rview-sum" id="nwxrview_summary" value="" size="50" ><?php echo get_post_meta( get_the_id(), 'nwx-rview-sum', true );  ?></textarea>

            <?php for ( $w=1; $w <= 10; $w++ ){ ?>

            <b>Attribute <?php echo $w; ?>:</b><input type="text" name="nwxrview[<?php echo $w; ?>][name]" id="nwxrview-<?php echo $w; ?>" value="<?php if( !empty( $nwxrview_meta_data[$w]['name'] )) echo $nwxrview_meta_data[$w]['name']; ?>" size="20" />
            <b>Score:</b><input type="range" id="nwxrview-<?php echo $w; ?>range" name="nwxrview-<?php echo $w; ?>range" min="0" max="100" value="<?php if( !empty( $nwxrview_meta_data[$w]['score'] ) ) echo $nwxrview_meta_data[$w]['score']; ?>" onchange="updateTextInput<?php echo $w; ?>(this.value);">
            <input type="text" id="nwxrview[<?php echo $w; ?>][score]" name="nwxrview[<?php echo $w; ?>][score]" value="<?php if( !empty( $nwxrview_meta_data[$w]['score'] ) ) echo $nwxrview_meta_data[$w]['score']; ?>"  style="width:50px; opacity: 0.7;" />
                         <script type="text/javascript">
                             function updateTextInput<?php echo $w; ?>(val) {
                             document.getElementById('nwxrview[<?php echo $w; ?>][score]').value=val;
                         }</script>
    </p>
<?php } }

/* Save the meta box's post metadata. */
/*------------------------------*/
function save_nwxrview_meta($post_id, $post)
{

    /* Verify noonce before proceeding */
    /*----------------------------*/
    if (!isset($_POST['nwxrview_nonce']) || !wp_verify_nonce($_POST['nwxrview_nonce'], basename(__FILE__)))
        return $post_id;

    /* Get post type objcet */
    /*-----------------*/
    $post_type = get_post_type_object($post->post_type);

    /* Check if the current user has permission to edit the post */
    /*-----------------------------------------------*/
    if (!current_user_can($post_type->cap->edit_post, $post_id))
        return $post_id;


    if (isset($_POST['nwxrview'])) {
        update_post_meta($post_id, 'nwxrview', $_POST['nwxrview']);
        update_post_meta($post_id, 'nwx-rview-sum', $_POST['nwx-rview-sum']);
    }

}

/* Display the Review box after the post content */
/*-------------------------------------*/
function get_my_meta($content)
{
    global $post;
    $nwxrview_meta_data = get_post_meta(get_the_id(), 'nwxrview', true);
    $nwxrview_opts = get_option('nwxrview_options');
    if (is_array($nwxrview_meta_data) && is_single()) {
        $content .= '<div class="nwxrview" style="border-top: 2px ' . $nwxrview_opts['border_style'] . ' #' . $nwxrview_opts['highlight_color'] . '; border-right: 2px ' . $nwxrview_opts['border_style'] . ' #' . $nwxrview_opts['highlight_color'] . '; " itemprop="review" itemscope itemtype="http://schema.org/Review"><h1>Review Scores</h1> <div itemprop="author" itemscope itemtype"http://schema.org/Person"><span itemprop="name" style="display:none">' . get_the_author_link() . '</span></div> <span itemprop="name" style="display:none">' . get_the_title(get_the_id()) . '</span>';
        $nwx_total_calc = 0;
        $nwx_score = 0;
        foreach ($nwxrview_meta_data as $nwx_attribs) {
            if (!empty($nwx_attribs['name'])) {
                if ($nwx_attribs['score'] / 10 < 1)
                    $nwx_attribs['score'] = 10;
                if ($nwx_attribs['score'] / 10 > 10)
                    $nwx_attribs['score'] = 100;
                $content .= $nwx_attribs['name'] . " - " . $nwx_attribs['score'] / 10 . '<br /><div class="nwxbar" style="width: ' . $nwx_attribs['score'] . '%; background-color: #' . $nwxrview_opts['highlight_color'] . ';"> &nbsp </div><br>';
                $nwx_score += $nwx_attribs['score'];
                $nwx_total_calc++;
            }
        }

        $nwx_total_score = ($nwx_score / $nwx_total_calc) / 10;
        $nwx_total_score = round($nwx_total_score * 2, 0) / 2;
        $content .= '<div class="nwx-rview-sum" style=" border-right: 2px solid #' . $nwxrview_opts['highlight_color'] . '; border-bottom: 2px solid #' . $nwxrview_opts['highlight_color'] . ';"><div style="background: #' . $nwxrview_opts['header_bg'] . '; height: 30px; padding: 0px 5px; color: #' . $nwxrview_opts['highlight_color'] . ';"><strong>Summary:</strong></div> <span itemprop="description">' . get_post_meta(get_the_id(), 'nwx-rview-sum', true) . '</span></div><div class="nwx-total-score" style=" border-left: 2px solid #' . $nwxrview_opts['highlight_color'] . '; border-bottom: 2px solid #' . $nwxrview_opts['highlight_color'] . ';"><div style="background: #' . $nwxrview_opts['header_bg'] . '; height: 30px; color: #' . $nwxrview_opts['highlight_color'] . '">Total Score:</div><h1><span itemprop="ratingValue">' . $nwx_total_score . '</span></h1></div></div>';
        return $content;
    } else {
        return $content;
    }
}
add_filter ('the_content', 'get_my_meta');

/* Options Page */
/*-----------*/
add_action('admin_menu', 'nwxrview_options_page');

// Add sub page to the settings Menu
function nwxrview_options_page()
{
    add_options_page('QMR Options', 'Quick Meta Review Options', 'administrator', 'rview-admin', 'nwxrview_page_gen');
}

//Register our settings. Add the settings section, and fields
add_action('admin_init', 'nwxrview_init');

function nwxrview_init()
{
    register_setting('nwxrview_options', 'nwxrview_options', 'nwxrview_options_validate');
    add_settings_section('main_section', 'Style Settings', 'nwxrview_text', 'rview-admin');
    add_settings_field('rview_header_bg', 'Header Backgrounds:', 'nwxrview_header_bg', 'rview-admin', 'main_section');
    add_settings_field('rview_highlight_color', 'Highlight color(includes bars):', 'nwxrview_highlight_color', 'rview-admin', 'main_section');
    add_settings_field('rview_border_style', 'Border Style:', 'nwxrview_border_style', 'rview-admin', 'main_section');

}

function  nwxrview_text()
{
    echo '<p>Enter style settings below.</p>';
}

//Field callback function
function nwxrview_highlight_color()
{
    $options = get_option('nwxrview_options');
    echo '<input id="rview_highlight_color" name="nwxrview_options[highlight_color]" size="40" type="text" value="' . $options['highlight_color'] . '" />';
}

function nwxrview_border_style()
{
    $options = get_option('nwxrview_options');
    $nwx_styles = array('Solid', 'Dashed', 'Dotted', 'Hidden');
    echo '<select id="style_select" name="nwxrview_options[border_style]" />';
    foreach ($nwx_styles as $styles) {
        $selection = ($options['border_style'] == $styles) ? 'selected="selected"' : ' ';
        echo '<option value="' . $styles . '"' . $selection . '>' . $styles . '</option>';
    }
    echo '</select>';
}

function nwxrview_header_bg()
{
    $options = get_option('nwxrview_options');
    echo '<input id="plugin_text_color" name="nwxrview_options[header_bg]" size="40" type="text" value="' . $options['header_bg'] . '" />';
}

// Options Page Function
function nwxrview_page_gen()
{
    ?>
    <div class="opt_wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2> Quick Meta Review Options</h2>

        <form action="options.php" method="post">
            <?php settings_fields('nwxrview_options'); ?>
            <?php do_settings_sections('rview-admin'); ?>
            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>"/>
            </p>
        </form>
    </div>
<?php
}

/*Validate everything before saving*/
/*----------------------------*/
function nwxrview_options_validate($input)
{
    $input['header_bg'] = sanitize_text_field($input['header_bg']);
    $input['border_style'] = sanitize_text_field($input['border_style']);
    $input['highlight_color'] = sanitize_text_field($input['highlight_color']);
    return $input; //Validated
}

