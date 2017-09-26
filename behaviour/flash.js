/**
This is used to load the Flash in Personal Stories
**/

function getFlashMovieObject(movieName){
  if (window.document[movieName]){
      return window.document[movieName];
  }
  if (navigator.appName.indexOf("Microsoft Internet")==-1){
    if (document.embeds && document.embeds[movieName]){
      return document.embeds[movieName]; 
	}
  } else // if (navigator.appName.indexOf("Microsoft Internet")!=-1)
  {
    return document.getElementById(movieName);
  }
}
 
function FlashGallery(galleryID){
 var flashMovie=getFlashMovieObject("sidebar_v2");
 //var f=document.getElementById("fFlash");
 flashMovie.SetVariable("galleryID", galleryID);
 flashMovie.GotoFrame(3);
}
 
function FlashCurrentGallery(){
 var flashMovie=getFlashMovieObject("sidebar_v2");
 var a=flashMovie.GetVariable("galleryID");
 alert("showing gallery("+a+")");
}
 
 
function FlashVideo(file){
 var flashMovie=getFlashMovieObject("sidebar_v2");
 flashMovie.SetVariable("videoFile", file);
 flashMovie.GotoFrame(1);
}
