<?php
/*-----------------------------------------------
 *
 * Output on post
 *
 -----------------------------------------------*/

add_action( 'wp_enqueue_scripts', 'nwxrview_styles');

/* Bring the styles and scripts in
----------------------------------*/
function nwxrview_styles() {
	wp_register_style ( 'nwxrview',  plugins_url('css/nwxrviewstyle.min.css', dirname( __FILE__ ) ) );
	wp_enqueue_style ( 'nwxrview' );
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