<?php
session_start();

if(!isset($_SESSION['email'])) {
    die('unauthorized');
}

$email = $_SESSION['email'];
$user = explode("@", $email)[0];
$new_rules = fopen($_SERVER['HOME']."/.mailsort_rules/rules_".$user.".json", "w") or die ("Unable to open file!");
$json = file_get_contents('php://input');

$obj = json_decode($json);
$json_new = json_encode($obj, JSON_PRETTY_PRINT);
fwrite($new_rules, $json_new);

return "Success";
