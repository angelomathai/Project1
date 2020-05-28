<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
   class Image
    {
        public static function uploadImage($formname,$query,$params)
        {
            $image=base64_encode(file_get_contents($_FILES[$formname]['tmp_name']));
            $options=array('http'=>array('method'=>"POST",'header'=>"Authorization: Bearer \n".
                "Content-Type: application/x-www-form-urlencoded",
                'content'=>$image
                ));
            $imgurURL="https://api.imgur.com/3/image";
            $context=stream_context_create($options);
            if($_FILES[$formname]['size']>10240000)
            {
                die('Image too big, must be less than 10MB');
            }
            $response = file_get_contents($imgurURL,false,$context);
            $response=json_decode($response);
            $preparams=array($formname=>$response->data->link);
            $params=$preparams+$params;
            DB::query($query,$params);
        }
    }
?>
