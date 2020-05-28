<?php
    /*ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);*/
require_once('/Applications/MAMP/htdocs/Project1/api/DB.php');
require_once('/Applications/MAMP/htdocs/Project1/api/Mail.php');
    $db=new DB("localhost","SocialNetwork","root","root");
    if ($_SERVER['REQUEST_METHOD'] == "GET")
    {

            if ($_GET['url'] == "auth")
            {

            }
            else if ($_GET['url'] == "search1")
            {
                $tosearch=explode(" ",$_GET['query']);
                    if(count($tosearch)==1)
                    {
                        $tosearch=str_split($tosearch[0],2);
                    }
                    $whereclause="";
                    $paramsarray=array(':body'=>'%'.$_GET['query'].'%');
                    for($i=0;$i<count($tosearch);$i++)
                    {
                        if($i % 2)
                        {
                            $whereclause.="OR body LIKE :p$i ";
                            $paramsarray[":p$i"]=$tosearch[$i];
                        }
                    }
                    
                    $posts=$db->query('SELECT posts.id,posts.body,users.username,posts.posted_at FROM posts,users WHERE users.id=posts.user_id AND posts.body LIKE :body '.$whereclause.' LIMIT 10',$paramsarray);
                    $b=json_encode($posts);
                    echo $b;
            }
            else if ($_GET['url'] == "search2")
            {
                $tosearch=explode(" ",$_GET['query']);
                    if(count($tosearch)==1)
                    {
                        $tosearch=str_split($tosearch[0],2);
                    }
                    
                    $whereclause="";
                    $paramsarray=array(':username'=>'%'.$_GET['query'].'%');
                    for($i=0;$i<count($tosearch);$i++)
                    {
                        $whereclause.="OR username LIKE :u$i ";
                        $paramsarray[":u$i"]=$tosearch[$i];
                    }
                    $users=$db->query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'',$paramsarray);
                    $a=json_encode($users);
                    echo $a;
            }
            else if ($_GET['url'] == "users")
            {
                $token = $_COOKIE['SNID'];

                $user_id= $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                $username=$db->query('SELECT username FROM users WHERE id=:userid',array(':userid'=>$user_id))[0]['username'];
                echo $username;
            }
            else if ($_GET['url'] == "messages")
            {
                $sender=$_GET['sender'];
                $token = $_COOKIE['SNID'];

                $receiver = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                $messages=$db->query('SELECT messages.id, messages.body, s.username AS sender, r.username AS receiver FROM messages LEFT JOIN users s ON messages.sender=s.id LEFT JOIN users r ON messages.receiver=r.id WHERE ( (r.id=:r AND s.id=:s) OR (r.id=:s AND s.id=:r))',array(':r'=>$receiver,':s'=>$sender));
                echo json_encode($messages);
            }
            else if ($_GET['url'] == "musers")
            {
                $token = $_COOKIE['SNID'];

                $userid= $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
                $username=$db->query('SELECT username FROM users WHERE id=:userid',array(':userid'=>$userid))[0]['username'];
                $users=$db->query('SELECT  s.username AS sender, r.username AS receiver,s.id AS senderID, r.id AS receiverID FROM messages LEFT JOIN users s ON messages.sender=s.id LEFT JOIN users r ON messages.receiver=r.id WHERE (r.id=:userid OR s.id=:userid) ',array(':userid'=>$userid));
                $u=array();
                foreach($users as $user)
                {
                    if(!in_array(array('username'=>$user['receiver'],'id'=>$user['receiverID']),$u) && $username!=$user['receiver'])
                    {
                        array_push($u,array('username'=>$user['receiver'],'id'=>$user['receiverID']));
                    }
                    if(!in_array(array('username'=>$user['sender'],'id'=>$user['senderID']),$u) && $username!=$user['sender'])
                    {
                        array_push($u,array('username'=>$user['sender'],'id'=>$user['senderID']));
                    }
                }
                echo json_encode($u);
            }
            else if ($_GET['url'] == "comments" && isset($_GET['postid']))
            {
                $output="";
                if($db->query('SELECT comments.comment,users.username FROM comments,users WHERE post_id=:postid AND comments.user_id=users.id;',array(':postid'=>$_GET['postid'])))
                {
                    $comments=$db->query('SELECT comments.comment,users.username FROM comments,users WHERE post_id=:postid AND comments.user_id=users.id;',array(':postid'=>$_GET['postid']));
                    $output .="[";
                    foreach($comments as $comment)
                    {
                        $output .= "{";
                        $output .= '"Comment": "'.$comment['comment'].'",';
                        $output .= '"CommentedBy": "'.$comment['username'].'"';
                        $output .= "},";
                        //echo $comment['comment']."~".$comment['username']."<hr />";
                    }
                    $output = substr($output, 0, strlen($output)-1);
                    $output .="]";
                }
                else
                {
                    $output="[]";
                }
                echo $output;
            }
        else if ($_GET['url'] == "profileposts") {
                $start = (int)$_GET['start'];
                $userid = $db->query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];

                $followingposts = $db->query('SELECT posts.id, posts.body, posts.posted_at,posts.postimg, posts.likes, users.`username` FROM users, posts
                WHERE users.id = posts.user_id
                AND users.id = :userid
                ORDER BY posts.posted_at DESC LIMIT 5
                OFFSET '.$start.';', array(':userid'=>$userid));
                $response = "[";
                foreach($followingposts as $post) {

                        $response .= "{";
                                $response .= '"PostId": '.$post['id'].',';
                                $text=explode(" ",$post['body']);
                                $newstring="";
                                foreach($text as $word)
                                {
                                    if(substr($word,0,1)=="@")
                                    {
                                        $newstring.="<a href='profile.php?username=".substr($word,1)."'>".htmlspecialchars($word)." </a>";
                                    }
                                    else if(substr($word,0,1)=="#")
                                    {
                                        $newstring.="<a href='topics.php?topic=".substr($word,1)."'>".htmlspecialchars($word)." </a>";
                                    }
                                    else
                                    {
                                        $newstring.=htmlspecialchars($word)." ";
                                    }
                                    
                                }
                                $response .= '"PostBody": "'.$newstring.'",';
                                $response .= '"PostedBy": "'.$post['username'].'",';
                                $response .= '"PostDate": "'.$post['posted_at'].'",';
                                $response .= '"PostImage": "'.$post['postimg'].'",';
                                $response .= '"Likes": '.$post['likes'].'';
                        $response .= "},";


                }
                $response = substr($response, 0, strlen($response)-1);
                $response .= "]";

                http_response_code(200);
                echo $response;

        }
            else if ($_GET['url'] == "posts")
            {
                $start = (int)$_GET['start'];
                $token = $_COOKIE['SNID'];

                $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];

                $followingposts = $db->query('SELECT posts.id, posts.body, posts.posted_at,posts.postimg, posts.likes, users.`username` FROM users, posts, followers WHERE (posts.user_id = followers.user_id OR posts.user_id=:userid) AND users.id = posts.user_id AND follower_id = :userid ORDER BY posts.posted_at DESC LIMIT 5 OFFSET '.$start.';', array(':userid'=>$userid),array(':userid'=>$userid));
                $response = "[";
                foreach($followingposts as $post) {

                        $response .= "{";
                                $response .= '"PostId": '.$post['id'].',';
                                $text=explode(" ",$post['body']);
                                $newstring="";
                                foreach($text as $word)
                                {
                                    if(substr($word,0,1)=="@")
                                    {
                                        $newstring.="<a href='profile.php?username=".substr($word,1)."'>".htmlspecialchars($word)." </a>";
                                    }
                                    else if(substr($word,0,1)=="#")
                                    {
                                        $newstring.="<a href='topics.php?topic=".substr($word,1)."'>".htmlspecialchars($word)." </a>";
                                    }
                                    else
                                    {
                                        $newstring.=htmlspecialchars($word)." ";
                                    }
                                    
                                }
                                $response .= '"PostBody": "'.$newstring.'",';
                                //$response .= '"PostBody": "'.$post['body'].'",';
                                $response .= '"PostedBy": "'.$post['username'].'",';
                                $response .= '"PostDate": "'.$post['posted_at'].'",';
                                $response .= '"PostImage": "'.$post['postimg'].'",';
                                $response .= '"Likes": '.$post['likes'].'';
                        $response .= "},";


                }
                $response = substr($response, 0, strlen($response)-1);
                $response .= "]";

                http_response_code(200);
                echo $response;
            }

    }
    else if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
        
        if ($_GET['url'] == "users")
        {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $username = $postBody->username;
                $email = $postBody->email ;
                $password = $postBody->password;
            
            
            if(!$db->query('SELECT username FROM users WHERE username=:username',array(':username'=>$username)))
            {
                if(strlen($username)>=3 && strlen($username)<=32)
                {
                    if(preg_match('/[a-zA-Z0-9]+/',$username))
                    {
                        if(strlen($password)>=6 && strlen($password)<=60)
                           {
                                    if(filter_var($email, FILTER_VALIDATE_EMAIL))
                                    {
                                        if(!$db->query('SELECT email FROM users WHERE email=:email',array(':email'=>$email)))
                                        {
                                            $db->query('INSERT INTO users VALUES (null,:username,:password,:email,\'0\',null)',array(':username'=>$username,':password'=>password_hash($password,PASSWORD_BCRYPT),':email'=>$email));
                                            Mail::sendMail('Welcome to our Social Network','Your account has been created!',$email);
                                            //echo 'Success!';
                                            echo '{ "Success": "User created!" }';
                                            http_response_code(200);
                                        }
                                        else
                                        {
                                            echo '{ "Error": "Email already registered!" }';
                                            http_response_code(409);
                                        }
                                    }
                                    else
                                    {
                                        echo '{ "Error": "Invalid email!" }';
                                        http_response_code(409);
                                        
                                    }
                           }
                        else
                        {
                            echo '{ "Error": "Invalid password!" }';
                            http_response_code(409);
                        }
                    }
                    else
                    {
                        echo '{ "Error": "Invalid username!" }';
                        http_response_code(409);
                    }
                }
                else
                {
                    echo '{ "Error": "Invalid username!" }';
                    http_response_code(409);
                }
            }
            else
            {
                echo '{ "Error": "User already exists!" }';
                http_response_code(409);
            }
        }

            if ($_GET['url'] == "auth")
            {
                    $postBody = file_get_contents("php://input");
                    $postBody = json_decode($postBody);

                    $username = $postBody->username;
                    $password = $postBody->password;

                    if ($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username)))
                    {
                            if (password_verify($password, $db->query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password']))
                            {
                                    $cstrong = True;
                                    $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                                    $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username'=>$username))[0]['id'];
                                    $db->query('INSERT INTO login_tokens VALUES (null, :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$user_id));
                                    echo '{ "Token": "'.$token.'" }';
                            }
                            else
                            {
                                echo '{ "Error": "Invalid username or password!" }';
                                    http_response_code(401);
                            }
                    }
                    else
                    {
                        echo '{ "Error": "Invalid username or password!" }';
                            http_response_code(401);
                    }

            }
        
        else if ($_GET['url'] == "message")
        {
            if (isset($_COOKIE['SNID'])) {
              $token = $_COOKIE['SNID'];
            } else {
              die();
            }
            //$token = $_COOKIE['SNID'];
            $user_id = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);

            $body = $postBody->body;
            $receiver = $postBody->receiver;
            if (strlen($body) > 100) {
                    echo "{ 'Error': 'Message too long!' }";
            }
            if ($body == null) {
              $body = "";
            }
            if ($receiver == null) {
              die();
            }
            if ($user_id == null) {
              die();
            }
            $db->query('INSERT INTO messages VALUES(null,:body,:sender,:receiver,0)',array(':body'=>$body,':sender'=>$user_id,':receiver'=>$receiver));
            
        }
        else if($_GET['url']=="likes")
        {
            $postid=$_GET['id'];
            $token = $_COOKIE['SNID'];

            $likerId = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
            if(!$db->query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid',array(':postid'=>$postid,':userid'=>$likerId)))
            {
            $db->query('UPDATE posts SET likes=likes+1 WHERE id=:postid',array(':postid'=>$postid));
                $db->query('INSERT INTO post_likes VALUES (null,:postid,:userid)',array(':postid'=>$postid,':userid'=>$likerId));
                //Notify::createNotify("",$postid);
            }
            else
            {
                $db->query('UPDATE posts SET likes=likes-1 WHERE id=:postid',array(':postid'=>$postid));
                $db->query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid',array(':postid'=>$postid,':userid'=>$likerId));
            }
            echo "{";
            echo '"Likes":';
            echo $db->query('SELECT likes FROM posts WHERE id=:postid',array(':postid'=>$postid))[0]['likes'];
            echo "}";
        }

    }
    else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            if ($_GET['url'] == "auth") {
                    if (isset($_GET['token'])) {
                            if ($db->query("SELECT token FROM login_tokens WHERE token=:token", array(':token'=>sha1($_GET['token'])))) {
                                    $db->query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_GET['token'])));
                                    echo '{ "Status": "Success" }';
                                    http_response_code(200);
                            } else {
                                    echo '{ "Error": "Invalid token" }';
                                    http_response_code(400);
                            }
                    } else {
                            echo '{ "Error": "Malformed request" }';
                            http_response_code(400);
                    }
            }
    } else {
            http_response_code(405);
    }
?>
