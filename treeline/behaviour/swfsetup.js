// JavaScript Document
var swfu; 
var info_debug="";
var current_upload="";


// The file added to the queue handler
var myFileQueuedHandler = function (file) {
	//alert("File "+file.name+" was added to the queue");
	swfu.startUpload(file.id);
}


// The uploadStart event handler. 
// This function variable is assigned to upload_start_handler in the settings object 
var myCustomUploadStartEventHandler = function (file) { 
	var continue_with_upload = true; 
	var progress = document.getElementById("progress-span");
	var info = document.getElementById("upload-info");
	
	// Test/Fail for demo purposes only. Lets hope noone ever uploads this file :o)
	if (file.name === "the sky is blue - demo") { 
		info_debug = '<p>You cannot upload this file</p>'+info_debug;
		info.innerHTML=info_debug;
		continue_with_upload = false; 
	} 
	else {
		// Show the file we have just added to the queue
		current_upload="Upload_file <strong>"+file.name+"</strong>";
		info.innerHTML='<p>'+current_upload+' ... </p>'+info_debug;
	}

	// Set the progress to the beginning
	progress.style.width="0px";
	
	return continue_with_upload; 
}; 


// File progress handler caller repeatedly during the upload process
var myUploadProgressHandler = function(file, completed, total) {
	var progress = document.getElementById("progress-span");
	var info = document.getElementById("upload-info");
	
	var progress_width = 400;
	var current = Math.floor(completed/total*progress_width);
	
	progress.style.width=current+"px";
	
	//info_debug="<p>File "+file.name+" uploaded("+completed+"/"+total+") needs ("+current+"px width)</p>"+info_debug;
	//info.innerHTML=info_debug;
	//alert ("File "+file.name+" uploaded("+completed+"/"+total+")");
}


// The uploadSuccess event handler. 
// This function variable is assigned to upload_success_handler in the settings object 
var myCustomUploadSuccessEventHandler = function (file, server_data, receivedResponse) { 
	//alert("The file " + file.name + " has been delivered to the server. The server responded with " + server_data); 
	var info = document.getElementById("upload-info");
	var progress = document.getElementById("progress-span");

	info_debug = '<p>'+current_upload+" "+server_data+'</p>'+info_debug;
	info.innerHTML=info_debug;
	current_upload='';

	// Should we check if there are items remaining in the queue?
	var stats = swfu.getStats(); 
	if (stats.files_queued>0) {
		swfu.startUpload();
	}
	else {
		//alert ("All files have been uploaded");
		progress.style.width="0px";
		info.innerHTML='<p>Completed</p>'+info_debug;
		info_debug='';
	}
}; 



// File error handler, called if there is a problem uploading a file
var myUploadErrorHandler = function (file, code, message) {
	alert("There was an error - "+message);
}




// Initialise the upload object
function swfsetup(gallery_id, upload_script_name) {
	//alert ("onload called to "+upload_script_name);
	var settings = { 
		upload_url : upload_script_name+".php", 
		flash_url : "/treeline/galleries/Flash/swfupload.swf", 
		file_types : "*.jpg;*.gif;*.png", 
		file_size_limit : "2 MB",
		debug : false,
		use_query_string : false,

		post_params : { 
			"Test parameter" : "aardvark",
			"gallery_id" : gallery_id,
			"version": "v1"
		},
		
		button_placeholder_id : "upload-button", 
		button_image_url : "/treeline/galleries/button.gif", 
		button_width : 100, 
		button_height : 24, 
		button_text : '', 
		button_text_style : ".white-txt { color: #FFFFFF; font-weight: bold; }", 
		button_text_left_padding : 10, 
		button_text_top_padding : 0, 
		button_action : SWFUpload.BUTTON_ACTION.SELECT_FILES, 
		button_disabled : false, 
		button_cursor : SWFUpload.CURSOR.HAND, 
		button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT, 
		
		file_queued_handler : myFileQueuedHandler,
		upload_start_handler : myCustomUploadStartEventHandler, 
		upload_progress_handler : myUploadProgressHandler,
		upload_error_handler : myUploadErrorHandler,
		upload_success_handler : myCustomUploadSuccessEventHandler 		
		
	};
	return settings;
}