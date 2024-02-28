<?php
  	require_once("class/class.user.php");
  	$user = new USER();
    $user->showAllError();
  
    if(isset($_POST['upload'])){
        $file = $_FILES['file'];
        $upload = $user->file_upload($file, $type = ['png', 'jpg', 'jpeg']);
        print_r($upload);
    }


?>


