<?php
/*----------------------------------
*
* Utility class for Inline Review
*
*---------------------------------*/

class NwxrviewUtil {

  /*----------------------------------
  *
  * Builds out <option> for selects.
  *
  * You pass in the saved select value
  * and an array of possible values.
  *
  * Returned is the <option> part of the form
  * with the saved value selected.
  *
  *----------------------------------*/
  public function select_build( $compare, $opt_value ) {

    $opt_form = '';

    foreach ( $opt_value as $value ) {
      $selection = ( $compare == $value ) ? 'selected="selected"' : ' ';
      $opt_form .= '<option value="' . $value . '"' . $selection . '>' . ucfirst( $value ) . '</option>';
    }
    return $opt_form;
  }
}
