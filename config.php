<?php

define("HOST", "localhost");
define("USER", "root");
define("PASS", "1fuckfuck0");
define("DB", "testing");

$db = @mysqli_connect(HOST, USER, PASS, DB) or die('Connection error');
mysqli_set_charset($db, 'utf8') or die('Charset error');

