<?php
  function makeSQLSafe($mysqli, $string) {
    $string = trim($string);
    while(strpos($string,"  ") !== false) {
      $string = str_replace("  "," ",$string);
    }
    if (get_magic_quotes_gpc()) $string = stripslashes($string);
    return $mysqli->real_escape_string($string);
  }
?>