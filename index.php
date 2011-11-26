<!DOCTYPE html>

<html>
<head>
    <title>KONVERT - AVID-FINAL CUT-LOG-FILE-CONVERTER (ALE|EDL|TXT)</title>
    <link href="./style.css" rel="Stylesheet" />
	<style type="text/css">
a:link { font-weight:bold; color:#0000E0; text-decoration:none }
a:visited { font-weight:bold; color:#000080; text-decoration:none }
a:hover { font-weight:bold; color:#E00000; text-decoration:none }
a:active { font-weight:bold; color:#E00000; text-decoration:underline }
a:focus { font-weight:bold; color:#00E000; text-decoration:underline }	
	</style>
    <script src="./jquery-1.6.5.js"></script>
	
    <script>
        var dropZone;
	var uploadDone = false;

        // Initializes the dropZone
        $(document).ready(function () {
            dropZone = $('#dropZone');
            dropZone.removeClass('error');
	    $('#refreshbutton').hide();
			
			
            // Check if window.FileReader exists to make 
            // sure the browser supports file uploads
            if (typeof(window.FileReader) == 'undefined') {
                dropZone.text('Browser Not Supported!');
                dropZone.addClass('error');
                return;
            }

            // Add a nice drag effect
            dropZone[0].ondragover = function () {
                dropZone.addClass('hover');
                return false;
            };

            // Remove the drag effect when stopping our drag
            dropZone[0].ondragend = function () {
                dropZone.removeClass('hover');
                return false;
            };

            // The drop event handles the file sending
            dropZone[0].ondrop = function(event) {
                // Stop the browser from opening the file in the window
                event.preventDefault();
                dropZone.removeClass('hover');

                // Get the file and the file reader
                var file = event.dataTransfer.files[0];

                // Validate file size
                //if(file.size > 40000) {
                //    dropZone.text('File Too Large!');
                //    dropZone.addClass('error');
                //    return false;
                //}

                // Send the file
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', uploadProgress, false);
                xhr.onreadystatechange = stateChange;
				xhr.open('POST', './uploaded.php', true);
                xhr.setRequestHeader('X-FILE-NAME', file.name);
                xhr.send(file);


				$.ajax({
						type: "GET",
						url: "u.php",
						datatype: "text",
						success: function(data){
						$("#message").html(data);
					},
				});
				uploadDone == false;

            };
        });

        // Show the upload progress
        function uploadProgress(event) {
            var percent = parseInt(event.loaded / event.total * 100);
            $('#dropZone').text('Uploading: ' + percent + '%');
        }

        // Show upload complete or upload failed depending on result
        function stateChange(event) {
            if (event.target.readyState == 4) {
                if (event.target.status == 200 || event.target.status == 304) {
                    $('#dropZone').text('uploading and converting ...');
                }
                else {
                    dropZone.text('Upload Failed!');
                    dropZone.addClass('error');
                }
            }
        }
    </script>
</head>
<body>
<table width=99%><tr><td align="left"><a href="faq.php"><div id="links">FAQ</div></a></td><td><div id="title">KONVERT</div></td><td><a href="code"><div id="links">code</div></a></td></tr></table>
    <a href="./index.php"><div id="refreshbutton">refresh</div></a>
    <form id="form1">
    <div id="dropZone">
        <h1>DROP HERE</h1><br><br><br>AVID.ALE<br><br><br>FINAL CUT.TXT
    </div>
	<div id="message">Version 0.1 (2011-11-26)</div>
	</form>
	<script type="text/javascript">
		$('#downloadbutton').hide();
	</script>

</body>
</html>
