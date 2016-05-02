$( document ).ready(function() {

	$( '#proj_struct ul li ul' ).hide();
	$( '#code_area .file_contents' ).hide();
	$( '.s-results' ).hide();

	$( '#proj_struct ul li' ).on( 'click', function(event){
		event.preventDefault();
		$(this).children( 'ul' ).slideToggle( "slow" );
	});

	$( '#proj_struct ul li ul li' ).on( 'click', function( event, line ){

		event.stopPropagation();
		var el_class = $( this ).children( 'a' ).attr( 'class' );
		var split_ids = el_class.split( '_' );

		var data = {
			proj_id : split_ids[0],
			file_id : split_ids[1],
			action: 'get_file_content'
		};

		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajax.php",
			data: data,
			success: function(data) {
				var content = '<div class="f_name">' + data.file_name +'</div>';
				for( var i = 0; i < data.content.length; i++ ) {
					var line_num = i + 1;
					content += '<div class="line l_' + line_num + ( i % 2 == 0 ? ' alt-row' : '' ) + '"><span>' + line_num + '</span> ';
					content += data.content[i].replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
					content += '</div>';
				}
				$( '#code_area' ).html( content );
				// Check if event was force triggered
				if ( event.originalEvent === undefined ) {
					$( '.line.l_' + line ).css( 'background', '#ccff99' );
					$('html, body').animate({
						scrollTop: $( '.line.l_' + line ).offset().top
					}, 2000);
				}
			}
		});
	});

	$( '#proj_options > a' ).on( 'click', function( event ){
		$( '#proj_struct ul li ul' ).hide( 'slow' );
	});

	$( '#proj_options .btn' ).on( 'click', function( event ){
		event.preventDefault();
		var proj_id = $("input[name='db_projects']:checked").val();
		var query = $( '#proj_options .form-control' ).val();

		var data = {
			proj_id : proj_id,
			query : query,
			action: 'get_functions'
		};

		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajax.php",
			data: data,
			success: function(data) {
				var content = '';
				if( data ) {
					content += 'Showing results for: <em>' + query + '</em>';
					content += '<ul class="func_results">';
					for( var i = 0; i < data.length; i++ ) {
						var proj_id = data[i]['proj_id'];
						var $obj = data[i]['function_info'];
						content += '<li rel=' + proj_id + '_' + $obj['file_id'] + ' data-linenum=' + $obj['def_line'] + '><a href="#">' + $obj['name'] + '</a> | ' + files[$obj['file_id']] + ' (' + $obj['def_line'] + ')</li>';
					}
					content += '</ul>';
				} else {
					content = 'No results match your query';
				}
				$( '.s-results' ).html( content );
				$( '.s-results' ).show();
			}
		});
	});

	$('body').on('click', '.func_results li a', function( event ) {
		event.preventDefault();
	    var proj_file_id = $( this ).parent().attr( 'rel' );
	    var line_num = $( this ).parent().attr( 'data-linenum' );
		$( ".files_container li a." + proj_file_id ).parent().trigger( 'click', [ line_num ]  );
	});
});