<?php

   include 'DB.php';

   mysql_connect( $host, $username, $password ) or die( 'Could not connect to server.' );
   mysql_select_db( $db ) or die( 'Could not select database.' );

   if( is_ajax() ) {

      if( isset( $_POST['action'] ) ) {
         $action = $_POST['action'];
         switch( $action ) {
            case 'get_file_content':
               get_file_content();
               break;
            case 'get_functions':
               get_functions();
               break;
         }
      }
   }

   mysql_close();

   function is_ajax() {
      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
   }

   function get_file_content() {
      if ( !isset( $_POST['file_id'] ) || !isset( $_POST['proj_id'] ) ) {
         echo json_encode(null);
         die();
      }

      $file_id = $_POST['file_id'];
      $proj_id = $_POST['proj_id'];

      $query = "SELECT * FROM files_$proj_id WHERE id=$file_id";
      $res = mysql_fetch_row( mysql_query( $query ) );
      $res_by_line = explode( "\n", $res[2] );     // split string by new line
      $file_name = $res[1];

      echo json_encode( array( 'content' => $res_by_line, 'file_name' => $file_name ) );
   }

   function get_functions() {
      if ( !isset( $_POST['query'] ) || !isset( $_POST['query'] ) ) {
         echo json_encode(null);
         die();
      }

      $search_for = $_POST['query'];
      $proj_id = $_POST['proj_id'];

      $response = array();
      if( $proj_id != 0 ) {
         $query = "SELECT * FROM functions_" . $proj_id . " WHERE name LIKE '%" . $search_for . "%'";
         $res = mysql_query( $query );
         while( ( $row1 = mysql_fetch_array( $res, MYSQL_ASSOC ) ) ) {
            $temp = array(
               'function_info' => $row1,
               'proj_id'   => $proj_id
            );
            array_push( $response, $temp );
         }
      } else {
         $query = mysql_query( "SELECT id FROM projects" );
         while( ( $row = mysql_fetch_assoc( $query ) ) != NULL ) {
            $sub_query = "SELECT * FROM functions_" . $row['id'] . " WHERE name LIKE '%" . $search_for . "%'";
            $sub_res = mysql_query( $sub_query );
            while( ( $row2 = mysql_fetch_assoc( $sub_res ) ) != NULL ) {
               $temp = array(
                  'function_info' => $row2,
                  'proj_id'   => $row['id']
               );
               array_push( $response, $temp );
            }
         }
      }

      echo json_encode( $response );
   }
?>