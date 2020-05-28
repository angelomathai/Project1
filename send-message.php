<?php
    session_start();
    $cstrong = True; $token=bin2hex(openssl_random_pseudo_bytes(64,$cstrong));
    if(!isset($_SESSION['token']))
    {
     $_SESSION['token']=$token;
    }
include('/Applications/MAMP/htdocs/Project1/classes/DB.php');
include('/Applications/MAMP/htdocs/Project1/classes/Login.php');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if(Login::isLoggedIn())
    {
        $userid=Login::isLoggedIn();
    }
    else
    {
        die('Not Logged In');
    }
    if(isset($_POST['send']))
    {
        if(!isset($_POST['nocsrf']))
        {
            die('invalid token');
        }
        if($_POST['nocsrf']!=$_SESSION['token'])
        {
            die('INVALID TOKEN');
        }
        if(DB::query('SELECT id FROM users WHERE id=:receiver',array(':receiver'=>$_GET['receiver'])))
        {
            DB::query('INSERT INTO messages VALUES(null,:body,:sender,:receiver,0)',array(':body'=>$_POST['body'],':sender'=>$userid,':receiver'=>htmlspecialchars($_GET['receiver'])));
            echo 'Message Sent!';
        }
        else
        {
            die('incorrect id');
        }
        session_destroy();
    }
?>
<h1>Send a Message</h1>
<form action="send-message.php?receiver=<?php echo htmlspecialchars($_GET['receiver']); ?>" method="post">
<textarea name="body" rows="8" cols="80"></textarea>
<input type="hidden" name="nocsrf" value="<?php echo $_SESSION['token']; ?>">
<input type="submit" name="send" value="Send Message">

</form>
