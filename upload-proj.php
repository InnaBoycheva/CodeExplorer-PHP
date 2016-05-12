<?php

  // This script is responsible for uploading projects for analysis from the
  // front-end. Everything works as expected and files are successfully added to
  // the server but I was unable to launch the executable that performs the
  // actual analysis due to some kind of permission issues.

  $count = 0;
  if ( false &&  $_SERVER['REQUEST_METHOD'] == 'POST' ){

    $dir = isset( $_POST['proj-name'] ) ? $_POST['proj-name'] : 'NoName';
    mkdir( 'upload/' . $dir );

     foreach ( $_FILES['files']['name'] as $i => $name ) {
         if ( strlen( $_FILES['files']['name'][$i] ) > 1 ) {
             if ( move_uploaded_file( $_FILES['files']['tmp_name'][$i], 'upload/' . $dir . '/' . $name ) ) {
                 $count++;
             }
         }
     }
  }

  // Desperate attempts at launching the exe
  // var_dump( shell_exec( 'C:\\inetpub\\wwwroot\\spinna\\code-explorer.exe' ) );
  // var_dump( shell_exec('powershell C:\\inetpub\\wwwroot\\spinna\\code-explorer.exe') );

?>