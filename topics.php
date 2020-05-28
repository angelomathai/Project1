<?php
include('/Applications/MAMP/htdocs/Project1/classes/DB.php');
include('/Applications/MAMP/htdocs/Project1/classes/Login.php');
include('/Applications/MAMP/htdocs/Project1/classes/Post.php');
include('/Applications/MAMP/htdocs/Project1/classes/Image.php');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if(isset($_GET['topic']))
    {
        if(DB::query('SELECT topics FROM posts WHERE FIND_IN_SET(:topic,topics)',array(':topic'=>$_GET['topic'])))
        {
            $posts=DB::query('SELECT * FROM posts WHERE FIND_IN_SET(:topic,topics)',array(':topic'=>$_GET['topic']));
            foreach($posts as $post)
            {
                echo $post['body']."<br />";
            }
        }
    }
?>

