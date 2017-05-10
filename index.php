<?php
include_once 'CSRF.php';

$n = new \Tyndale\CSRF();
//$n->create(5);  //optional to set an expires time in minutes, else defaults to 1 hour

//use this function in your view
echo $n->generate_form_field();
echo '<br>';


// get a nonce then verify it
echo $n->nonce;
echo '<br>';
if (\Tyndale\CSRF::validate('RGJtV2ZseE8wVUlUOU54MzRCbXNyYkRMd1hGMTVyMG9hcUlSellUclB3TUhIT25GblFQSFlKZGRqUExjVVhvYQ==')) {
  echo 'valid';
};
