// JavaScript Document
var jcropAPI;
var goToURL;
var fileInfo = new Object();

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
	
	/* delete profile picture */
	$('#upload-delete-picture').click(function()
	{
		confirmAction = function()
		{
			deleteProfilePicture();
		};
		
		populateConfirmActionAlert('delete your current profile picture', "<p class='clear margin-top medium width-100 center'>You will not be able to join any games.</p>");
	})
	
	$('#confirm-action').click(function()
	{
		confirmAction();
	})
	
	$('#deny-action').click(function()
	{
		$(this).parents('.alert-container').find('.alert-x').trigger('click');
	})
	
	$('#signup-import-alert-cancel').click(function()
	{
		$('#signup-import-main-img,.narrow-column-picture').css({width: 'auto',
																 height: 'auto',
																 marginTop: 0,
																 marginLeft: 0})
														   .attr('src', '/images/users/profile/pic/large/default.jpg');
														   
		$('.signup-alert-rotate').hide();
		
		fileInfo = new Object();
		
		jcropAPI.destroy();
		
		$('#signup-import-alert-img').css({width: 'auto',
																 height: 'auto',
																 marginTop: 0,
																 marginLeft: 0})
									 .attr('src','/images/global/profile/xl/default.jpg')
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
																				 		 setSelect: [0,0,200,200],
																						 onSelect: updateProfilePic
																						 },function(){
       																						jcropAPI = this;
																							})
													
													$('.signup-alert-rotate').show();				
													$('#signup-import-alert-accept').show();
													$('#signup-import-alert-cancel').show();
																				 
													
												}
	})
	
	$('.signup-alert-rotate').click(function()
	{
		var leftOrRight = $(this).attr('id').replace(/signup-alert-rotate-/,'');
		var src = $('#signup-import-alert-img').attr('src');

		rotateImage(src, leftOrRight, populateUploadedImg);
	})
	
	$('#signup-import-alert-accept').click(function()
	{
		uploadProfilePic();
	});
	
})

/**
 * delete profile picture
 */
function deleteProfilePicture()
{
	$.ajax({
		url: '/ajax/delete-profile-picture',
		type: 'POST',
		data: {},
		success: function(data) {
			window.location = '/';
		}
	})
}


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

/**
 * upload profile pic, submit the picture to be uploaded
 */
function uploadProfilePic()
{
	
	$.ajax({
		url: '/ajax/upload-profile-pic',
		type: 'POST',
		data: {fileInfo: fileInfo},
		success: function(data) {
			
			var location = '/';
			if (goToURL) {
				location = goToURL;
			}
			setTimeout(function() {
				window.location = location;
			}, 200);
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

/**
 * callback function from jCrop to update the import-main img to reflect cropped image
 * @params(coords => coordinates of jcrop)
 */
function updateProfilePic(coords)
{
	// 199 = width of preview image
	var rx = 199 / coords.w;
	// 160 = height of preview image
	var ry = 160 / coords.h; 
	var height = $('#signup-import-alert-img').height(); // height of original image
	var width  = $('#signup-import-alert-img').width() //width of original image

	
	fileInfo.fileWidth = coords.w;
	fileInfo.fileHeight = coords.h;
	fileInfo.fileX = coords.x;
	fileInfo.fileY = coords.y;
	fileInfo.src = $('#signup-import-alert-img').attr('src');
	
}
