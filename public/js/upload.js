// JavaScript Document
var jcropAPI;

$(function()
{
	$('#profilePic').change(function()
	{
		if (this.files[0].size > 6291456) {
			alert('File size is too large. There is a strict 6MB limit.');
			return false;
		}

		$(this).parents('form').submit();
	})
	
	
	/* ajax submit of import profile pic */
	$('#upload-profile-pic').ajaxForm({success: function(data) {
		
													if (data == 'errorFormat') {
														alert('The file you submitted is in the wrong format. Please select a jpg, png, or gif.');
														return false;
													} else if(data == 'errorUpload') {
														alert('An error occurred, please try again later.');
														return false;
													}
													if (jcropAPI) {
														jcropAPI.destroy()
													}
													$('#fileName').val(data);
													$('#signup-import-alert-img').attr('src',data)
																				 .maintainRatio()
																				 .Jcrop({aspectRatio: 1.26,
																				 		 setSelect: [0,0,200,200]
																						 },function(){
       																						jcropAPI = this;
																							})
													
													$('.signup-alert-rotate').show();				
													$('#signup-import-alert-accept').show();
																				 
													
												}
	})
	
	$('.signup-alert-rotate').click(function()
	{
		var leftOrRight = $(this).attr('id').replace(/signup-alert-rotate-/,'');
		var src = $('#signup-import-alert-img').attr('src');

		rotateImage(src, leftOrRight, populateUploadedImg);
	})
	
})


/**
 * rotate user uploaded image
 * @params (src => img src (should be relative),
 *			leftOrRight => 'left' or 'right')
 */
function rotateImage(src, leftOrRight, callback)
{
	$.ajax({
		url: '/ajax/rotate-image',
		type: 'POST',
		data: {src: src,
			   leftOrRight: leftOrRight},
		success: function(data) {
			callback(data);

		}
	})
}

function populateUploadedImg(data)
{		
	if (jcropAPI) {
		jcropAPI.destroy()
	}
	$('#fileName').val(data);
	$('#signup-import-main-img,.narrow-column-picture').attr('src',data)
	$('#signup-import-alert-img').attr('src',data)
								 .maintainRatio()
								 .Jcrop({aspectRatio: 1.26,
										 setSelect: [0,0,200,200],
										 onSelect: updateProfilePic
										 },function(){
											jcropAPI = this;
											})
}