(function($) {
	getImageFilesStructure();
	
	$("#fileUpload").change(function(){
	checkImage( this );
	});

	function checkImage(input) {
		if (input.files && input.files[0]) {
			var i=0;
			$(input.files).each(function () {
				var reader = new FileReader();
				reader.readAsDataURL(this);
				reader.onload = function (e) {
					var numFies = input.files.length;
					var fileSize = input.files[i].size;
					var sizeInMb = fileSize/1024;
					var sizeLimit= 1024;
					if (sizeInMb > sizeLimit) {
						errorReport("Upload Failed!. selected file : \" "+ input.files[i].name + " \" is more than 1MB.",1);
					}
					else {
						serverUpload(e.target.result,i);
						var curNumFies = i+1;
						op="<div style=\"background-image:url('"+ e.target.result +"');background-size:100% 100px;width:100%;height:100px;border-radius: 2px;\"><div style=\"width: 100%;height: 2px;text-align: center;color: #fff;background:rgba(66,165,245,0.6);\" id=\"imgProgress"+i+"\"><div id='progressText"+i+"' style=\"padding-top: 40px;font-weight:600;\">0%</div></div></div>";
						$('#container').append("<div class='uploadingContainer' id='imgProgressMain"+i+"'>"+ op +"<h5>Uploading file "+ curNumFies +" of "+ numFies +" </h5></div>");
					}
					i++;		
				}	
			});
		}
	}
	function serverUpload(imgData,val,hash)
	{
		var url='upload_image.php';
		imgData= "'" + imgData + "'";
		var hash = md5(imgData);
		var params = {'img_data': imgData,'token': hash };		
		$.ajax({
		url : url,
		type: "POST",
		data : params,
		dataType : "json",
		xhr: function(){
			//upload Progress
			var xhr = $.ajaxSettings.xhr();
			if (xhr.upload) {
				xhr.upload.addEventListener('progress', function(event) {
					var percent = 0;
					var position = event.loaded || event.position;
					var total = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					//update progressbar
					$("#imgProgress"+val).css("height", + percent +"px");
					$("#progressText"+val).text(percent +"%");
				}, true);
			}
			return xhr;
		}
	}).done(function(dataq){
		$('#noImg').remove();
		$('.allImgNoImg').remove();
		var op;
		op="<div class='displayImageContainer'><div class=\"imageCard\"><img id='img' src='uploads/thumbnails/"+ dataq.image +"'/></div><p>Name : "+ dataq.image +" </p><p>Size : "+ dataq.size +" </p></div>";
		$('#recentImages').append(op);
		$('#allImages').append(op);
		errorReport("Upload success!. selected file : \" "+ dataq.image + " \"",0);
		$('#imgProgressMain'+val).remove();
	});	
	}
	function getImageFilesStructure()
	{
		var url='imageList.php';
		var imageLists = $.get( url, function(imgData) {
			if(imgData.status==0)
			{
				$("#imgCount").html(" ( "+imgData.files.length +" ) ");
				imgData.files.map(function (db) {
					op="<div class='displayImageContainer'><div class=\"imageCard\"><img id='img' src='uploads/thumbnails/"+ db.filename +"'/></div><p>Name : "+ db.filename +" </p><p>Size : "+ db.size +"</p></div>";
					$('#allImages').append(op);
				});
			}else
			{
				errorReport("No image Found!",1);
				$('#allImages').append("<p class='allImgNoImg'>No image Found !</p>");
			}
		})
		.fail(function() {
			console.log( "error" );
		})
		.always(function() {
			errorReport(" All Images Loaded",0);
		});
	}
	function errorReport(content,status)
	{
		var color="green";
		if(status==0)
		{
			color="green";
		}else
		{
			color="red";
		}
		var dt = new Date();
		$("#error").append("[ "+ dt +" ] : <span style='color:"+ color +";'>"+ content +"</span><br/>");
	}
})(jQuery);