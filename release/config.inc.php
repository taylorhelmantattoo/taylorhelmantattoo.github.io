<?php

     // site prefix
     if(!isset($site_prefix))
             $site_prefix='';


     if(!isset($base_dir))
             $base_dir='';  // was 'release' - local Windows folder name, breaks asset URLs on server

     // check if settings are being passed to this file
     if(!isset($settings)) {
             $file='configs/tattoo.php';

             if(isset($_GET['release']) && !empty($_GET['release']) && preg_match('/^[a-z0-9_]+$/i', $_GET['release']) && file_exists('configs/'.$_GET['release'].'.php'))
                     $file='configs/'.$_GET['release'].'.php';

             include($file);
     }
?>
