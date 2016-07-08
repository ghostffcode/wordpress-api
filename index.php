<?php

header('Content-Type: application/json');

include 'inc/apimaker.php';

$api = new apimaker();

$api->getPosts();

 ?>
