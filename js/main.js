$( document ).ready(function() {

	// Hide files under specific project unless project is selected & expanded
	$( '#proj_struct ul li ul' ).hide();

	// Hide search results area before search
	$( '.s-results' ).hide();

	// Hide popover content since it's used as a placeholder for the HTML only
	$( '#up-popover-text' ).hide();

	// Assign popover content and title
	$( '#upload-proj-popover' ).popover({
		html: true,
		content: function() {
			return $( '#up-popover-text' )[0].innerHTML;
		},
		title: 'Upload New Project'
	});

	// Event listener for project folder links
	// Upon clicking the project folder, hide files under this project if visible,
	// show them otherwise
	$( '#proj_struct ul li' ).on( 'click', function( event ){
		event.preventDefault();
		$(this).children( 'ul' ).slideToggle( "slow" );
	});

	// Event listener for file links
	// Shows content from the file clicked
	$( '#proj_struct ul li ul li' ).on( 'click', function( event, line ){

		// Prevent bubbling up of events
		event.stopPropagation();

		// Get file id and project id stored as a class of the clicked element
		// format: proj-id_file-id (ex: 10_1)
		var el_class = $( this ).children( 'a' ).attr( 'class' );
		var split_ids = el_class.split( '_' );
		var proj_id = split_ids[0];
		var file_id = split_ids[1];

		// Prepare needed variables to query the DB before passing to the ajax
		var data = {
			proj_id : proj_id,
			file_id : file_id,
			action: 'get_file_content'
		};

		// AJAX to pull the corresponding file content
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajax.php",
			data: data,
			// If AJAX successful, prepare & add content to the code area
			success: function(data) {

				// Remove all existing classes on the element
				$( '#code_area' ).attr( 'data-file-info', proj_id + '_' + file_id );

				// Get name of file to be shown
				var content = '<div class="f_name">' + data.file_name +'</div>';
				// For each line display line number along with line content
				for( var i = 0; i < data.content.length; i++ ) {
					var line_num = i + 1;
					// Append the "alt-row" class to every other line to visually separate
					// the lines later on
					content += '<div class="line l_' + line_num + ( i % 2 == 0 ? ' alt-row' : '' ) + '"><span class="l_num">' + line_num + '</span> ';
					// Replace some special characters with their HTML character entities
					// Also, convert function links to actual HTML links 
					content += data.content[i].replace( /&/g, '&amp;' )
											  .replace( /</g, '&lt;' )
											  .replace( />/g, '&gt;' )
											  .replace( /\[func-id=(\d+)\](.+)\[\/func-id\]/g, '<a class="func_link" href="#" rel="$1">$2</a>');
					content += '</div><br>';
				}

				// Remove background of the area - needed because of the initial
				// image displayed on page load
				$( '#code_area' ).css( 'background', 'none' );

				// Add the prepared content to the code area
				$( '#code_area' ).html( content );

				// Color code
				$( '#code_area' ).removeClass( 'prettyprinted' );
				PR.prettyPrint();

				// Check if event was not triggered by an actual click on the file
				// Such situation will occur if we're loading the file content upon
				// searching for a specific function & wishing to highlight the 
				// section in this file where this function appears
				if ( event.originalEvent === undefined ) {
					// Highlight line of function declaration
					$( '.line.l_' + line ).css( 'background', '#CCFF99' );
					// Animate to it
					$( '#code_area' ).animate({
						scrollTop: $( '.line.l_' + line ).offset().top
					}, 2000);
				}
			},
			// Upon error, display an error message in the code area
			error: function() {
				$( '#code_area' ).html( '<p>Something went wrong..</p>' );
			}
		});
	});

	// Event listener for the Project Options button
	// Shows additional options section if currently hidden, hides it otherwise
	// Also, hides the currently expanded project file structure (if any)
	$( 'btn.proj_opt' ).on( 'click', function( event ){
		$( '#proj_struct ul li ul' ).hide( 'slow' );
	});

	// Event listener for the Search Functions button
	$( '#options .btn' ).on( 'click', function( event ){
		event.preventDefault();

		// Get the project in which we're searching
		var proj_id = $("input[name='db_projects']:checked").val();

		// Get the function query
		var query = $( '#proj_options .form-control' ).val();

		// Prepare needed variables to query the DB before passing to the ajax
		var data = {
			proj_id : proj_id,
			query : query,
			action: 'get_functions'
		};

		// AJAX to pull all functions matching the query
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajax.php",
			data: data,
			// If AJAX successful, display function results
			success: function(data) {
				var content = '<span class="glyphicon glyphicon-remove close-fsearch"></span>';
				if( data ) {
					content += 'Showing results for: <em>' + query + '</em>';
					content += '<ul class="func_results">';
					for( var i = 0; i < data.length; i++ ) {
						var proj_id = data[i]['proj_id'];
						var $obj = data[i]['function_info'];
						// Save the function declaration line as an argument to the element
						// Will be used later when animating to this exact part of the code
						content += '<li rel=' + proj_id + '_' + $obj['file_id'] + ' data-linenum=' + $obj['def_line'] + '><a href="#">' + $obj['name'] + '</a> | ' + files[proj_id][$obj['file_id']] + ' (' + $obj['def_line'] + ')</li>';
					}
					content += '</ul>';
				} else {
					// If no such function(s) found, display appropriate message instead
					content = 'No results match your query';
				}
				// Add results to search results area & show it
				$( '.s-results' ).html( content );
				$( '.s-results' ).show( 'slow' );
			},
			// Upon error, display an error message in the search results
			error: function() {
				$( '.s-results' ).html( '<p>Something went wrong. Please try again!</p>' );
				$( '.s-results' ).show( 'slow' );
			}
		});
	});

	// Event listener for the "X" icon in the function search area
	// Upon click, hides the function results section
	$( 'body' ).on( 'click', 'span.close-fsearch', function() {
		$( '.s-results' ).hide( 'slow' );
	});

	// Event listener for function links in the function search results section
	// Simulates a click on the appropriate file link the function is declared in
	$('body').on('click', '.func_results li a', function( event ) {
		event.preventDefault();
	    var proj_file_id = $( this ).parent().attr( 'rel' );
	    var line_num = $( this ).parent().attr( 'data-linenum' );
		$( ".files_container li a." + proj_file_id ).parent().trigger( 'click', [ line_num ]  );
	});

	// Event listener for function links in the code
	// Display a popover upon function click
	$('body').on('click', 'a.func_link', function( event ) {

		// The clicked element
		var $el = $(this);

		// Prepare needed variables to query the DB before passing to the ajax
		var data = {
			func_id: $(this).attr('rel'),
			proj_id: $( '#code_area' ).attr( 'data-file-info' ).split( '_' )[0],
			action: 'get_functions'
		};

		// AJAX call to retrieve function information
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajax.php",
			data: data,
			// If AJAX successful, display the popover with the appropriate information
			success: function(data) {
				var $obj = data[0]['function_info'];
				var proj_id = data[0]['proj_id'];
				var content = '<strong>Name:</strong> ' + $obj['name'] + '\n';
				content += '<strong>Location:</strong> ' + files[proj_id][$obj['file_id']] + '\n';
				content += '<strong>Line:</strong> ' + $obj['def_line'] + '\n';
				content += '<strong>Return Type:</strong> ' + $obj['return_type'] + '\n';
				content += '<a href="#" data-loc="' + proj_id + '_' + $obj['file_id'] + '_' + $obj['def_line'] + '">See function</a>';
				$el.unbind('click').popover({
					title: 'Function Information',
					html: true,
					content : content,
					delay: {show: 200, hide: 100}
				}).popover('show');
			}
		});
	});

	// Event listener for function links & jumping to func in the code area
	$('body').on('click', '.line .popover a', function( event ) {

		// Retrieve component IDs
		var $ids = $(this).attr( 'data-loc' ).split( '_' );

		// Load file content & jump to the specific line
		$( ".files_container li a." + $ids[0] + '_' + $ids[1] ).parent().trigger( 'click', [ $ids[2] ]  );
	});
});