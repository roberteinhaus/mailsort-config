<?php
session_start();

if(isset($_SESSION['email'])) {
    header('Location: index.php');
}

include('cfg/recaptcha.php');
$doRecaptcha = (isset($RECAPTCHA_SECRET) && isset($RECAPTCHA_SITEKEY));

if (isset($_REQUEST['email'])) {
    $_SESSION['input_email'] = $_REQUEST['email'];

    $everythingOk = true;

    /***************
    *  recaptcha  *
    ***************/
    if($doRecaptcha) {
        $post_data = http_build_query(
            array(
                'secret' => $RECAPTCHA_SECRET,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if (!$result->success) {
            $message = 'Fehler beim Captcha!';
            $everythingOk = false;
        }
    }
    if($everythingOk) {
        $email = $_REQUEST['email'];
        $passwd = $_REQUEST['pass'];
        $host = "{localhost:993/imap/ssl/novalidate-cert}";
        $mbox = imap_open($host, $email, $passwd);

        if($mbox) {
            $folders = imap_list($mbox, $host, "*");
            imap_close($mbox);
            $_SESSION['email'] = $email;
            natcasesort($folders);
            $_SESSION['folders'] = $folders;

            header('Location: index.php');
        }
        else {
            $message = 'Anmeldung fehlgeschlagen! Bitte überprüfen Sie die eingegebenen Daten!';
        }
    }
}
require('loginform.php');
