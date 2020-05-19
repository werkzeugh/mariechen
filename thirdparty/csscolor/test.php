<?php

// Tell the browser that this is CSS instead of HTML
header("Content-type: text/css");

// Get the color generating code
include_once("csscolor.php");

// Set the error handing for csscolor.
// If an error occurs, print the error
// within a CSS comment so we can see
// it in the CSS file.
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');
function errorHandler($err) {
    echo("/* ERROR " . $err->getMessage() . " */");
}

// Define a couple color palettes
$base = new CSS_Color('C9E3A6');
$highlight = new CSS_Color('746B8E');

// Trigger an error just to see what happens
// $trigger = new CSS_Color('');

?>


.box {

  /* Use the base color, two shades darker */
  background:#<?= $base->bg['-2'] ?>;

  /* Use the corresponding foreground color */
  color:#<?= $base->fg['-2'] ?>;

  /* Use the highlight color as a border */
  border:5px solid #<?= $highlight->bg['0'] ?>

}
