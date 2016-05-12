<?php

   include 'DB.php';

   // Connect to DB
   $link = mysqli_connect( $host, $username, $password, $db ) or die( 'Could not connect to server.' );
   mysqli_select_db( $link, $db ) or die( 'Could not select database.' );

   // Select all projects existing in the DB
   $query = "SELECT * FROM projects";
   $res = mysqli_query( $link, $query );

   // If no projects exist display appropriate message
   if( mysqli_num_rows( $res ) == 0 ) {
      echo 'No projects exist in DB.';
      return;
   }

   $files = array();
   $radio_labels = array();

   // Output project structure(s) section
   echo '<h1>Code Explorer</h1>';
   echo '<div class="files_container">';
   echo '<p id="proj-title">Select a project:</p>';
   
   while ( $row = mysqli_fetch_assoc( $res ) ) {
      $proj_id = $row['id'];
      $proj_name = pathinfo( $row['base_path'] )['filename'];
      echo '<ul>';
      echo '<li class="folder_li"><a href="#">' . $proj_name . '</a><ul>';
      $radio_labels[$proj_id] = $proj_name;
      $query = "SELECT * FROM files_" . $proj_id . " ORDER BY name ASC";
      $res_sub = mysqli_query( $link, $query );

      while ( $row_sub = mysqli_fetch_assoc( $res_sub ) ) {
         $file_id = $row_sub['id'];
         echo '<li><a href="#" class="' . $proj_id . '_' . $file_id . '">' . $row_sub['name'] . '</a></li>';
         $files[$proj_id][$file_id] = $row_sub['name'];
      }
      echo '</ul></li></ul>';
   }
   echo '</div>';

   ?>

   <script type="text/javascript"> var files = <?php echo json_encode( $files ); ?></script>

   <hr noshade="noshade">
   <div id="proj_options">
      <button data-toggle="collapse" data-target="#options" class="btn btn-default proj_opt">Project Options &gt;</button>
      <div id="options" class="collapse">
         <div class="radio">
           <label>
             <input type="radio" name="db_projects" value="0" checked>
             All Projects
           </label>
         </div>
         <?php
            foreach ( $radio_labels as $key => $value ) {
               echo '<div class="radio"><label>';
               echo '<input type="radio" name="db_projects" value="' . $key . '">' . $value;
               echo '</label></div>';
            }
         ?>
         <div class="input-group">
            <input type="text" class="form-control" placeholder="Search for function...">
            <span class="input-group-btn">
               <button class="btn btn-default" type="button">
                  <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
               </button>
            </span>
         </div>
         <div class="s-results"></div>
      </div>
   </div>

<?php
   // Close the opened DB connection
   mysqli_close( $link );
?>