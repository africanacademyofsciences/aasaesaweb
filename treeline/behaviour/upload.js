// JavaScript Document

var handleUpload = function(event) {
	console.log("Submit clicked");		
	event.preventDefault();
	event.stopPropagation();
	
	var data = new FormData();
	
	var files = document.getElementById("file");
	
	for (var i=0; i<files.files.length; ++i) {
		console.log(files.files[i].name);
		data.append('file[]', files.files[i]);
	}
	//console.log(files.files.length);

	var progress = document.getElementById("upload_progress");
	while (progress.hasChildNodes()) {
		progress.removeChild(progress.firstChild);
	}

	if (files.files.length>0) {
		var request = XMLHttpRequest();
		
		// Something was uploaded.
		request.upload.addEventListener("progress", function(event) {
			console.log("Progress called");
			if (event.lengthComputable) {
				var percent = event.loaded / event.total;
				while (progress.hasChildNodes()) {
					progress.removeChild(progress.firstChild);
				}
				progress.appendChild(document.createTextNode(Math.round(percent*100)+"%"));
			}
		});
		
		// Upload complete
		request.upload.addEventListener("load", function(event){
			//alert ("upload completed");													 	
			//document.getElementById("upload_progress").style.display = "none";
		});
		
		request.upload.addEventListener("error", function(event) {
			alert("Upload failed");
		});
		
		request.addEventListener("readystatechange", function(event) {
			// complete
			if (this.readyState == 4) {
				if (this.status == 200) {
					// Success, all files have been uploaded
					var links = document.getElementById("upload_progress");
					while (links.hasChildNodes()) {
						links.removeChild(links.firstChild);
					}
					links.appendChild(document.createTextNode("Your file has been uploaded"));
					//console.log(this.response);
				}
				else {
					console.log("Ajax response code "+this.status);
				}
			}
		});
	
	
		document.getElementById("upload_progress").style.display = "block";
		console.log("Post to upload script");
		request.open("POST", '/treeline/upload.php');
		request.setRequestHeader('Cache-Control', 'no-cache');
		request.send(data);
	}
	else {
		progress.appendChild(document.createTextNode("No files were uploaded"));
	}
}


window.addEventListener("load", function(event) {
										 
	var submit = document.getElementById("submit");
	submit.addEventListener("click", handleUpload);
	console.log("Loaded window event listener");
	
});