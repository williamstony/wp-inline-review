<?php
/*------------------------------------------
 *
 * Ouput Class Build out
 *
 *----------------------------------------*/
class nwxrview_output {

	public $options, $nwxmeta;

	public function __construct( ) {


		$this->options   = get_option( 'nwxrview_options' );
		$this->highlight = $this->options['highlight_color'];
		$this->border    = $this->options['border_style'];
		$this->header_bg = $this->options['header_bg'];
		$this->own_style = $this->options['own_style'];

		add_action( 'wp_enqueue_scripts', array( $this, 'frontstyles' ) );
		add_action ( 'wp_head', array( $this, 'css') );

		add_filter ( 'the_content', array( $this, 'output') );



	}

	function frontstyles () {

		wp_register_style ( 'nwxrview',  plugins_url('css/nwxrviewstyle.min.css', dirname( __FILE__ ) ) );
		wp_enqueue_style ( 'nwxrview' );
	}

	function css () {
		$nwxrview_css = '';

		if( $this->own_style == 'yes' ) {
			return;
		}

		$nwxrview_css .= '
            .nwxrview {
               border: 2px ' . esc_html($this->border) . ' ' . esc_html($this->highlight) . ';
             }

            .nwxbar {
               background-color: ' . esc_html($this->highlight) . ';
            }

            .nwx-rview-sum {
               border-right: 2px solid ' . esc_html($this->highlight) . ';
               border-bottom: 2px solid ' . esc_html($this->highlight) . ';
            }

            .nwx-total-score {
                border-left: 2px solid ' . esc_html($this->highlight) . ';
                border-bottom: 2px solid ' . esc_html($this->highlight) . ';
             }

             .nwxrview ul li {
                list-style-type: none;
             }';

		echo '<style type="text/css" media="screen">' . apply_filters( 'nwxrview_css', $nwxrview_css ) . '</style>';

	}

	function calc( $calcs ) {

		$nwx_total_calc = 0;
		$nwx_score = 0;
		$nwxrview_calc_content = '';

		foreach ($calcs as $nwx_attribs) {

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

		return array( apply_filters( 'nwxrview_attribs', $nwxrview_calc_content ), apply_filters( 'nwxrview_score_final', $nwx_total_score ) );


	}

	function output ( $content ) {
		global $post;

		$this->review_sum = get_post_meta(get_the_id(), 'nwx-rview-sum', true);
		$this->nwxmeta = get_post_meta( get_the_id(), 'nwxrview', true );
		$nwxrview_calc_data = $this->calc( $this->nwxmeta );
		$original = $content;
		$nwxrview_content = '';

		if ( !empty($this->nwxmeta) && is_array($this->nwxmeta) && is_single() ) {
			$nwxrview_content .= '<div class="nwxrview" itemprop="review" itemscope itemtype="http://schema.org/Review">
                        <h1>Review Scores</h1>
                    <div itemprop="author" itemscope itemtype"http://schema.org/Person">
                        <span itemprop="name" style="display:none">' . esc_html(get_the_author_link()) . '</span>
                    </div>
                    <span itemprop="name" style="display:none">' . esc_html(get_the_title(get_the_id())) . '</span> <ul class="nwxrview_attribs">'
			                     . $nwxrview_calc_data[0] . '</ul><div class="nwx-rview-sum">
                        <div class="nwxrview_header" style="background: ' . esc_html($this->header_bg) . '; height: 30px; color: ' . esc_html($this->highlight) . ';">
                            <strong>Summary:</strong>
                        </div>
                            <span itemprop="description">' . esc_html($this->review_sum) . '</span>
                    </div>
                    <div class="nwx-total-score">
                        <div class="nwxrview_header" style="background: ' . esc_html($this->header_bg) . '; height: 30px; color: ' . esc_html($this->highlight) . '">
                            <strong>Total Score:</strong>
                        </div>
                        <h1><span itemprop="ratingValue">' . esc_html($nwxrview_calc_data[1]) . '</span></h1>
                    </div>
                    </div>';

			$nwxrview_output = $original . apply_filters( 'nwxrview_output', $nwxrview_content );
			
			return $nwxrview_output;

		} else {

			return $original;

		}

	}
}
