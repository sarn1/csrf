<?php
include_once 'CSRF.php';

$n = new \Tyndale\CSRF();

$n->create(5);

echo $n->generate_form_field();