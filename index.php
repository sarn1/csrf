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
if ( \Tyndale\CSRF::validate('UlFIVDh2aEN6aGlJK0wwOXR5bVo1eGhnMVgwVFFhTlhhL0lKdVM5VEtOUm05dWt5RnFSMGgrczdyTWFhNkdQWA==') ) {
  echo 'valid';
};
