$(function() {

	$('#upload_new_upload').change(function() {
  		$('.file-upload form').submit();
	});

	$('.file-upload .upload').on('click', function() {
		$('#upload_new_upload').click();
	});

});	