<?php

   include 'DB.php';

   // Connect to DB
   $link = mysqli_connect( $host, $username, $password, $db ) or die( 'Could not connect to server.' );
   mysqli_select_db( $link, $db ) or die( 'Could not select database.' );

   // Check if pade is loaded via AJAX
   if( is_ajax() ) {
      if( isset( $_POST['action'] ) ) {
         // Determine action action based on the type of information that needs to be pulled
         $action = $_POST['action'];
         switch( $action ) {
            case 'get_file_content':
               get_file_content( $link );
               break;
            case 'get_functions':
               get_functions( $link );
               break;
         }
      }
   }

   /**
    *
    * Determines if the page is loaded via AJAX or not
    *
    * @param    none
    * @return   True if page is loaded via AJAX, false otherwise
    *
    */
   function is_ajax() {
      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
   }

   /**
    *
    * AJAX call to retrieve the contents of a specific file
    *
    * @param    $link - connection to the MySQL server
    * @return   The JSON representation of the file content
    *
    */
   function get_file_content( $link ) {

      // File and project IDs needed in order to pull the content
      if ( !isset( $_POST['file_id'] ) || !isset( $_POST['proj_id'] ) ) {
         echo json_encode(null);
         die();
      }

      $file_id = $_POST['file_id'];
      $proj_id = $_POST['proj_id'];

      // Query the DB
      $query = "SELECT * FROM files_$proj_id WHERE id=$file_id";
      $res = mysqli_fetch_row( mysqli_query( $link, $query ) );

      // Split content string in new lines
      $res_by_line = explode( "\n", $res[2] );
      $file_name = $res[1];

      // Convert the response in a JSON format
      echo json_encode( array( 'content' => $res_by_line, 'file_name' => $file_name ) );
   }

   /**
    *
    * AJAX call to pull all functions matching a specific query from the DB
    *
    * @param    $link - connection to the MySQL server
    * @return   The JSON representation of the functions retrieved
    *
    */
    function get_functions( $link ) {

      // Check what params have been sent to this AJAX call
      if( isset( $_POST['query'] ) ) {
          $search_for = $_POST['query'];
      }
      if( isset( $_POST['proj_id'] ) ) {
          $proj_id = $_POST['proj_id'];
      }      

      $response = array();

      // Determine whether the search is pulling functions defined in multiple projects,
      // or just a single project (project_id=0 indicates multi-projects search)
      if( $proj_id != 0 ) {

         // Determine the search method (by ID or name)
         if( isset( $_POST['func_id'] ) ) {
            // Search by ID
            $func_id = $_POST['func_id'];
            $query = "SELECT * FROM functions_" . $proj_id . " WHERE id=$func_id";
         } else {
            // Search by name
            $query = "SELECT * FROM functions_" . $proj_id . " WHERE name LIKE '%" . $search_for . "%'";
         }

         $res = mysqli_query( $link, $query );
         while( ( $row1 = mysqli_fetch_array( $res, MYSQL_ASSOC ) ) ) {
            $temp = array(
               'function_info' => $row1,
               'proj_id'   => $proj_id
            );
            // Keep all results
            array_push( $response, $temp );
         }
      } else {

         // Search across all projects
         $query = mysqli_query( $link, "SELECT id FROM projects" );
         while( ( $row = mysqli_fetch_assoc( $query ) ) != NULL ) {
            $sub_query = "SELECT * FROM functions_" . $row['id'] . " WHERE name LIKE '%" . $search_for . "%'";
            $sub_res = mysqli_query( $link, $sub_query );
            while( ( $row2 = mysqli_fetch_assoc( $sub_res ) ) != NULL ) {
               $temp = array(
                  'function_info' => $row2,
                  'proj_id'   => $row['id']
               );
               // Keep all results
               array_push( $response, $temp );
            }
         }
      }

      // Convert the response in a JSON format
      echo json_encode( $response );
   }

   // Close the opened DB connection
   mysqli_close( $link );
?>