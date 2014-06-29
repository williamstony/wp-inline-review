<?php

class nwxrview_util {

  public function select_build ( $compare, $opt_value ) {

    $opt_form = '';

    foreach ( $opt_value as $value ) {
		    $selection = ( $compare == $value ) ? 'selected="selected"' : ' ';
		    $opt_form .= '<option value="' . $value . '"' . $selection . '>' . ucfirst( $value ) . '</option>';
    }
	return $opt_form;
  }
}
