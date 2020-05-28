<?php
include('/Applications/MAMP/htdocs/Project1/classes/DB.php');
include('/Applications/MAMP/htdocs/Project1/classes/Mail.php');
 if(isset($_POST['resetpassword']))
    {
        
         $cstrong = True;
         $token=bin2hex(openssl_random_pseudo_bytes(64,$cstrong));
        $email=$_POST['email'];
        $user_id=DB::query('SELECT id FROM users WHERE email=:email',array(':email'=>$email))[0]['id'];
         DB::query('INSERT INTO password_tokens VALUES (null,:token,:user_id)',array(':token'=>sha1($token),':user_id'=>$user_id));
        Mail::sendMail('Forgot Password!',"<a href='http://localhost:8888/Project1/change-password.php?token=$token'>http://localhost:8888/Project1/change-password.php?token=$token</a>",$email);
        echo 'Email sent';
        /*echo '<br />';
        echo $token;*/
    }
    
?>
<h1>Forgot Password</h1>
<form action="forgot-password.php" method="post">
<input type="text" name="email" value="" placeholder="Email ..."><p />
<input type="submit" name="resetpassword" value="Reset Password"><p />
</form>
