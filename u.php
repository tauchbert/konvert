<?php
sleep(4); // VERY VERY UGLY hack
$myFile = "./last.filename"; // get name of uploaded file
$fh = fopen($myFile, 'r');
$theData = fread($fh, filesize($myFile));
fclose($fh);
echo "<h2>uploaded: ".$theData."</h2>"; // name of uploaded file

$myFile = "temp/".$theData; // load content of uploaded file 
$fh = fopen($myFile, 'r');
$theData = fread($fh, filesize($myFile));
fclose($fh);

// echo "This is a response from the server. Your file is: ".$myFile."<br>";
$logtext = "";
$uploadedfilename = $myFile;
//$logtext .=  "uploadfile: $uploadedfilename<br>";
if (strrpos($uploadedfilename, ".ale") === strlen($uploadedfilename)-strlen(".ale")) {
	$downloadfile = str_replace (".ale", ".txt", $uploadedfilename);
} elseif (strrpos($uploadedfilename, ".ALE") === strlen($uploadedfilename)-strlen(".ALE")) {
	$downloadfile = str_replace (".ALE", ".txt", $uploadedfilename);
} elseif (strrpos($uploadedfilename, ".txt") === strlen($uploadedfilename)-strlen(".txt")) {
	$downloadfile = str_replace (".txt", ".ale", $uploadedfilename);
} elseif (strrpos($uploadedfilename, ".TXT") === strlen($uploadedfilename)-strlen(".TXT")) {
	$downloadfile = str_replace (".TXT", ".ale", $uploadedfilename);
} else {
	$logtext .= "str_add_<br>";	
	$downloadfile = $uploadedfilename.".ale";
}

//$logtext .=  "downloadfile: $downloadfile<br>";
$filename=$myFile;
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);
unlink($filename);

// unify the cr/nl problem on windows/unix/os x 
$contents = str_replace("\r\n","\n",$contents);
$contents = str_replace("\r","\n",$contents);
$contents = str_replace("\n","\r\n",$contents);
$lines = explode("\r\n", $contents);
$converted_video_file = array();

//$logtext .="<a href='./$downloadfile'>download converted ALE-file</a><hr>";
$niced_download_text = substr($downloadfile, 5);
$logtext .= "<a href='./$downloadfile'><div id='downloadbutton'>download $niced_download_text</div></a><br>";
$logtext .= "<hr>";
if (strstr($lines[0], 'Heading')) {
	$logtext .=  "seems to be AVID<br>"; // ALE --> FINCAL RAP
	for ($i=0; $i<sizeof($lines); $i++) { // find column-description
		if ($lines[$i]=="Column") {
			$line_of_column_description = $i+1;
		}
	}
	$columns = preg_split ("/[\t]+/", $lines[$line_of_column_description]);
	$column_nr_tape = array_search  ("Tape",  $columns);
	$logtext .=  "column_nr_tape: $column_nr_tape <br>";
	$column_nr_name = array_search  ("Name",  $columns);
	$logtext .=  "column_nr_name: $column_nr_name<br>";
	$column_nr_tcin = array_search  ("Start",  $columns);
	$logtext .=  "column_nr_tcin: $column_nr_tcin<br>";
	$column_nr_tcout = array_search  ("End",  $columns);
	$logtext .=  "column_nr_tcout: $column_nr_tcout<br>";
	$column_nr_tracks = array_search  ("Tracks ",  $columns);
	$logtext .=  "column_nr_tracks: $column_nr_tracks<br>";
	if (!($column_nr_tracks = array_search  ("Tracks ",  $columns))) {
		$column_nr_tracks  = -1;
	}
	if (!($column_nr_note = array_search  ("Note",  $columns))) {
		if (!($column_nr_note = array_search  ("Log Note",  $columns))) {
			if (!($column_nr_note = array_search  ("Gudrun",  $columns))) {
				$column_nr_note  = -1;
			}
		}
	}
	$logtext .=  "column_nr_note: $column_nr_note<br>";	
	if (!($column_nr_comments = array_search  ("Comments",  $columns))) {
		if (!($column_nr_comments = array_search  ("Comment 1",  $columns))) {
			$column_nr_comments  = -1;
		}
	}
	$logtext .=  "column_nr_comments: $column_nr_comments<br>";	
	
	array_push ($converted_video_file, "Name	Reel	Media Start	Media End	Tracks	Comment 1	Log Note");
	for ($i=$line_of_column_description+3; $i<sizeof($lines); $i++) {
		$columns = preg_split ("/[\t]/", $lines[$i]);
		if ($column_nr_tracks == -1) { 
			$column_tracks = "1V,4A";
		} else {
			$column_tracks_ale = trim($columns[$column_nr_tracks]);
			if ($column_tracks_ale == "V") { $column_tracks = "1V";} 
			elseif ($column_tracks_ale == "A1") { $column_tracks = "1A";} 
			elseif ($column_tracks_ale == "A1A2") { $column_tracks = "2A";} 
			elseif ($column_tracks_ale == "A1A2A3") { $column_tracks = "3A";} 
			elseif ($column_tracks_ale == "A1A2A3A4") { $column_tracks = "4A";} 
			elseif ($column_tracks_ale == "VA1A2A3A4") { $column_tracks = "1V, 4A";} 
			else { $column_tracks = "1V, 4A";} 
		}
		if ($column_nr_note == -1) { 
			$column_note = "";
		} else {
			$column_note = $columns[$column_nr_note];
		}
		if ($column_nr_comments == -1) { 
			$column_comments = "";
		} else {
			$column_comments = $columns[$column_nr_comments];
		}
		$l = $columns[$column_nr_name]."\t".$columns[$column_nr_tape]."\t".$columns[$column_nr_tcin]."\t".$columns[$column_nr_tcout]."\t".$column_tracks."\t".$column_note."\t".$column_comments;
		array_push ($converted_video_file, $l);
	}
	$logtext .= "<h2>converted video file</h2>";
	$fh = fopen("./$downloadfile", 'w') or die("can't open file");
	for ($i=0; $i < sizeof($converted_video_file)-1; $i++) {
		$logtext .= $converted_video_file[$i]."<br>";
		fwrite($fh, $converted_video_file[$i]."\r"); // \r\n
	}
	fclose($fh);
} elseif (strstr($lines[0], 'Media Start')) {
	$logtext .=  "seems to be FinalCrap<br>"; // FINAL CUT/TXT ----> AVID/ALE
	$columns = preg_split ("/[\t]+/", $lines[0]);
	$column_nr_tape = array_search  ("Reel",  $columns);
	$logtext .=  "column_nr_tape: $column_nr_tape <br>";
	$column_nr_name = array_search  ("Name",  $columns);
	$logtext .=  "column_nr_name: $column_nr_name<br>";
	$column_nr_tcin = array_search  ("Media Start",  $columns);
	$logtext .=  "column_nr_tcin: $column_nr_tcin<br>";
	$column_nr_tcout = array_search  ("Media End",  $columns);
	$logtext .=  "column_nr_tcout: $column_nr_tcout<br>";
	if (!($column_nr_tracks = array_search  ("Tracks",  $columns))) {
		$column_nr_tracks  = -1;
	}
	if (!($column_nr_comment_1 = array_search  ("Comment 1",  $columns))) {
		$column_nr_comment_1 = -1;
		//echo "<h1>comment_1 = -1</h1>";
	}
	$logtext .=  "column_nr_comment_1: $column_nr_comment_1<br>";
	if (!($column_nr_log_note = array_search  ("Log Note",  $columns))) {
		$column_nr_log_note  = -1;
	}
	$logtext .=  "column_nr_log_note: $column_nr_log_note<br>";	
	
	array_push ($converted_video_file, "Heading");
	array_push ($converted_video_file, "FIELD_DELIM	TABS");
	array_push ($converted_video_file, "VIDEO_FORMAT	1080");
	array_push ($converted_video_file, "AUDIO_FORMAT	48khz");
	array_push ($converted_video_file, "FPS	25");
	array_push ($converted_video_file, "");
	array_push ($converted_video_file, "Column");
	array_push ($converted_video_file, "Name	Tape	Start	End	Tracks	Comments	Note	Color");
	array_push ($converted_video_file, "");
	array_push ($converted_video_file, "Data");

	for ($i=1; $i<sizeof($lines)-1; $i++) {
		$columns = preg_split ("/[\t]/", $lines[$i]);
		if ($column_nr_tracks == -1) { 
			$column_tracks = "VA1A2A3A4";
		} else {
			$column_tracks_fcp = $columns[$column_nr_tracks];
			if ($column_tracks_fcp == "1V") { $column_tracks = "V";} 
			elseif ($column_tracks_fcp == "1Á") { $column_tracks = "A1";} 
			elseif ($column_tracks_fcp == "2A") { $column_tracks = "A1A2";} 
			elseif ($column_tracks_fcp == "3A") { $column_tracks = "A1A2A3";} 
			elseif ($column_tracks_fcp == "4A") { $column_tracks = "A1A2A3A4";} 
			elseif ($column_tracks_fcp == "1V, 4A") { $column_tracks = "VA1A2A3A4";} 
			else { $column_tracks = "VA1A2A3A4";} 
		}
		if ($column_nr_log_note == -1) { 
			$column_log_note = "";
		} else {
			$column_log_note = $columns[$column_nr_log_note];
		}
		if ($column_nr_comment_1 == -1) { 
			$column_comment_1 = "";
			$column_color = "";
		} else {
			$column_comment_1 = $columns[$column_nr_comment_1];
			if ($column_comment_1 == 1) {
				$column_color = "Green";
			} elseif ($column_comment_1 == 2) {
				$column_color = "Yellow";
			} else {
				$column_color = "";			
			}
		}

		$l = $columns[$column_nr_name]."\t".$columns[$column_nr_tape]."\t".$columns[$column_nr_tcin]."\t".$columns[$column_nr_tcout]."\t".$column_tracks."\t".$column_log_note."\t".$column_comment_1."\t".$column_color;
		array_push ($converted_video_file, $l);
	}
	$logtext .= "<h2>converted video file</h2>";
	$fh = fopen("./$downloadfile", 'w') or die("can't open file");
	for ($i=0; $i < sizeof($converted_video_file); $i++) {
		$logtext .= $converted_video_file[$i]."<br>";
		fwrite($fh, $converted_video_file[$i]."\r\n");
	}
	fclose($fh);
} elseif (strstr($lines[0], 'TITLE')) {
	$logtext .=  "seems to be an EDL<br>"; // EDL --> ALE
	array_push ($converted_video_file, "Heading");
	array_push ($converted_video_file, "FIELD_DELIM	TABS");
	array_push ($converted_video_file, "VIDEO_FORMAT	1080");
	array_push ($converted_video_file, "AUDIO_FORMAT	48khz");
	array_push ($converted_video_file, "FPS	25");
	array_push ($converted_video_file, "");
	array_push ($converted_video_file, "Column");
	array_push ($converted_video_file, "Name	Tape	Start	End	Tracks	Comments	Note	Color");
	array_push ($converted_video_file, "");
	array_push ($converted_video_file, "Data");	
	for ($i=1; $i<sizeof($lines)-1; $i++) {
		$sequenznumber = preg_match('/^[\d]{3}(.*)$/', $lines[$i]); 
		if ($sequenznumber != 0) {  // beginnt mit einer Nummer wie 000, 001, ... 999
			$logtext .= "zeile".$i.":".$position." ->".$lines[$i]."<br>";
			// get TCIN und TCOUT
			$matches = Array ();
			$position_tcin_begin = preg_match_all('/[\d]{2}:[\d]{2}:[\d]{2}:[\d]{2}/', $lines[$i], $matches); // finde: "dd:dd:dd:dd" d= Dezimalwert
			$tcin  = $matches[0][2];
			$tcout = $matches[0][3];
			$logtext .= "tcin :".$tcin."<br>";
			$logtext .= "tcout :".$tcout."<br>";

			$columns = preg_split ("/[\s]+/", $lines[$i]); // an Whitespace(s) trennen
			// get REELNAME
			$reelname = "Tape_001";	// Annahme: zweite Spalte ist immer Clipname
			$logtext .= "reelname:".$reelname."<br>";
			$cline = $lines[$i+2];			
			$logtext .= "lines+2:".$cline."<br>";			
			if (strpos($cline, "FINAL CUT PRO REEL") !== false) {
				$real_reelname_startpos = 20; // nach 
				$real_reelname_endpos = strrpos ($cline, "REPLACED BY:");
				$logtext .= "real_reelname_startpos: $real_reelname_startpos <br>";
				$logtext .= "real_reelname_endpos: $real_reelname_endpos <br>";
				$real_reelname = substr ($cline, $real_reelname_startpos, ($real_reelname_endpos - $real_reelname_startpos));
				$real_reelname = preg_replace(array("#[^a-zA-Z0-9]#"),array("_"),trim($real_reelname));  
			} else {
				$real_reelname = preg_replace(array("#[^a-zA-Z0-9]#"),array("_"),trim($reelname));  
			}
			$logtext .= "real_reelname: ".$real_reelname."<br>";			
			// get CLIPNAME
			$cline = $lines[$i+1];			
			$logtext .= "lines+1:".$cline."<br>";			
			if (strpos($cline, "FROM CLIP NAME") !== false) {
				$clipname = substr ($cline, 19);
				$clipname = preg_replace(array("#[^a-zA-Z0-9]#"),array("_"),trim($clipname));  
			} else {
				$clipname = "unknown";
			}
			$logtext .= "clipname: ".$clipname."<br>";			
			$l = $clipname."\t".$real_reelname."\t".$tcin."\t".$tcout."\t"."VA1A2A3A4"."\t".""."\t".""."\t"."";
			array_push ($converted_video_file, $l);
		}
	}
	$logtext .= "<h2>converted video file</h2>";
	$fh = fopen("./$downloadfile", 'w') or die("can't open file");
	for ($i=0; $i < sizeof($converted_video_file); $i++) {
		$logtext .= $converted_video_file[$i]."<br>";
		fwrite($fh, $converted_video_file[$i]."\r\n");
	}
	fclose($fh);
}
//$logtext .="<a href='./$downloadfile'>download converted file</a><hr>";
$logtext .="<hr>";
for ($i=0; $i<sizeof($lines); $i++) { // print plain input file
	$logtext .= "".$i.":".$lines[$i]."<br>";
}

//////////////////
echo $logtext;
?>
<script type="text/javascript">
$('#refreshbutton').show(); 
/* $('#downloadbutton').show(); */
$('#dropZone').hide();
</script>
