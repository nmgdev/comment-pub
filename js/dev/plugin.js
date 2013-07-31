(function ($) {

	"use strict";

	$(function () {

		// If the comment form is visible, set it's enctype to support uploading files
		if ($('#commentform').length > 0) {
			$('#commentform').attr('enctype', 'multipart/form-data');
		} // end if

		// Setup an event handler so we can notify the user whether or not the file type is valid
		$('#comment_pub').change(function () {

			// If the file isn't empty, verify it's a valid file
			if ($(this).val() !== '') {

				var aFileName, sFileType;

				aFileName = $(this).val().split('.');
				sFileType = aFileName[aFileName.length - 1].toString().toLowerCase();

				if (sFileType === 'png' || sFileType === 'gif' || sFileType === 'jpg' || sFileType === 'jpeg') {
					$('#comment-pub-error').hide();
				} else {
					$('#comment-pub-error').show();
				} // end if

			} // end if

		});
		
		// Setup an event handler so we can notify the user if the field is empty
		$('#comment_pub').onload(function () {

			// If the file isn't empty, verify it's a valid file
			if ($(this).val() = '') { 
			
					$('#comment-pub-empty').show();
					$('#comment-pub-error').hide();
				 

			} else {
					$('#comment-pub-error').show();
					$('#comment-pub-empty').hide();
			} // end if

		});

	});

}(jQuery));