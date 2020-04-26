
    function OutputByIdVK(obj) { if(obj.id == 'None') return; 
	   if( $("#"+obj.id).length == 0) $(document.body).append("<span id="+obj.id+"></span>"); 
           $("#"+obj.id).html(obj.str); 
    }
    function testajax() {
        var url='http://192.168.10.113/dimagApps.php'; 
     	$.ajax({url: url, type: 'post', dataType: 'json', cache: false, 
	  success: function(data) {
	    if(data.html) $('#testajax').html(data.html); 
	    $('#attr').text('success: ' + JSON.stringify(data.attr, null, 4) );  
	  }, 
	  error: function(data){$('#error').text('error:' + JSON.stringify(data, null, 4) ); } 
	});
    }
  

   function Logout() { 
	   localStorage.clear(); $('#userinfo').html('<button onclick=\"Logout();\">Login</button>');
	   var d=$('#userinfo').data(); d.logged=0; 
	   dimagApps({'logout':1}); 
   }
   function localStorage2uinfo() {
	   if(localStorage.getItem('logged')) {
 		 var d=$('#userinfo').data(); d.logged=1; d.uid=localStorage.getItem('uid');
 			d.name=localStorage.getItem('name');  d.priv=localStorage.getItem('priv');  
 			d.AdminLevel=localStorage.getItem('AdminLevel');  
 			d.since=localStorage.getItem('since'); 
 		 for (var i = 0; i < localStorage.length; i++){ var k=localStorage.key(i); 
			 if(localStorage.getItem(k) == 'COURSES') {var d = $('#info').data(); d[k]='COURSES'; }
		 }
 	   }
   }
   function dimag(O) { dimagApps(O); }

//---------------------------
   
function toggleVK(id) { var txt=$('#B'+id).text(); $('#'+id).toggle(); if(txt=='+') txt='-'; else txt='+'; $('#B'+id).text(txt); }
function toggleVK2(i,dis) { $(i).toggle(); if($(dis).text()=='+') $(dis).text('-'); else $(dis).text('+'); }
function ShowBy(id,f) { if(f=='c') $('.'+id).show(); if(f=='id') $('#'+id).show(); if(f=='e') $(id).show(); if(f=='n') $("[name="+id+"]").show(); }
function HideBy(id,f) { if(f=='c') $('.'+id).hide(); if(f=='id') $('#'+id).hide(); if(f=='e') $(id).hide(); if(f=='n') $("[name="+id+"]").hide(); }
function Add2Info(In) { var d = $('#info').data(); for(var k in In) { d[k] = In[k]; } } 
function outoutidconversion(id,platform) {
	if((id=='middle') || (id=='middle2') || (id=='inside-COURSES-Assessment') ) {
		return 'top'; 
	} else 	return id;
}

//---------------------------

 function DeviceInfoVK(O) {
        var element = document.getElementById(O.id);
        element.innerHTML = 'Device Name: '     + device.name     + '<br />' +
                            'Device Cordova: '  + device.cordova  + '<br />' +
                            'Device Model: '    + device.model    + '<br />' +
                            'Device Platform: ' + device.platform + '<br />' +
                            'Device UUID: '     + device.uuid     + '<br />' +
                            'Device version: '  + device.version     + '<br />' +
                            'Device manufact: ' + device.manufacturer     + '<br />' +
                            'Device isVirtual: '+ device.isVirtual     + '<br />' +
                            'Device serial: '   + device.serial  + '<br />';
    }
//---------------------------
// Camera

    var pictureSource, destinationType; 
    document.addEventListener("deviceready",onDeviceReady,false);

    // device APIs are available
    function onDeviceReady() {
        pictureSource=navigator.camera.PictureSourceType;
        destinationType=navigator.camera.DestinationType;
    }

    // Called when a photo is successfully retrieved
    function onPhotoDataSuccess(imageData) {
      var smallImage = document.getElementById('smallImage');
      smallImage.style.display = 'block';
      smallImage.src = "data:image/jpeg;base64," + imageData;
    }

    function onPhotoURISuccess(imageURI) {
      var largeImage = document.getElementById('largeImage');
      largeImage.style.display = 'block';
      largeImage.src = imageURI;
    }

    function capturePhoto() { // Take picture using device camera and retrieve image as base64-encoded string
      navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 50,
        destinationType: destinationType.DATA_URL });
    }

    function capturePhotoEdit() { // Take picture using device camera, allow edit, and retrieve image as base64-encoded string
      navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 20, allowEdit: true,
        destinationType: destinationType.DATA_URL });
    }

    function getPhoto(source) { // Retrieve image file location from specified source
      navigator.camera.getPicture(onPhotoURISuccess, onFail, { quality: 50,
        destinationType: destinationType.FILE_URI,
        sourceType: source });
    }

    function onFail(message) { $('#message').html('Failed because: ' + message); }

//---------------------------

