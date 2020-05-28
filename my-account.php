<?php
include('/Applications/MAMP/htdocs/Project1/classes/DB.php');
include('/Applications/MAMP/htdocs/Project1/classes/Login.php');
include('/Applications/MAMP/htdocs/Project1/classes/Image.php');
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
    if(isset($_POST['uploadprofileimg']))
    {
        Image::uploadImage('profileimg',"UPDATE users SET profileimg=:profileimg WHERE id=:userid",array(':userid'=>$userid));
    }
?>
<h1>My Account</h1>
<form action="my-account.php" method="post" enctype="multipart/form-data">
Upload a profile image:
<input type="file" name="profileimg">
<input type="submit" name="uploadprofileimg" value="Upload Image">
</form>
