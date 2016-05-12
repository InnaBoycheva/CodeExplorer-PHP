<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title>Code Explorer</title>
	<link rel="stylesheet" href="css/reset.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" href="css/fonts.css">
	<link rel="stylesheet" href="css/styles.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
	<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
	<script src="js/main.js"></script>
</head>
<body>
	<div class="row row-eq-height">
		<div id="proj_struct" class="col-lg-3 col-md-3">
			<?php require 'proj_struct.php'; ?>
		</div>
		<pre id="code_area" class="col-lg-9 col-md-9 prettyprint"></pre>
	</div>
	<a id="upload-proj-popover" href="#" data-toggle="popover" data-placement="left"></a>
	<div id="up-popover-text">
		<form action="upload-proj.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="100000000"/>
			<input type="file" name="files[]" multiple/>
			<input type="text" placeholder="Enter Project Name.." name="proj-name" class="form-control"/>
			<span class="input-group-btn">
				<button class="btn btn-info" type="button">Upload Project</button>
			</span>
		</form>
	</div>
</body>
</html>