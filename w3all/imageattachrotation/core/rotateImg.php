<?php
/*
* Rotate phpBB imgages attachments 
* axew3.com
*/
	defined( 'IN_PHPBB' ) or die( 'forbidden' );

	$config = $phpbb_container->get('config');
  //$user->add_lang_ext('w3all/imageattachrotation', 'common'); // already included

  $phpbb_root_path = './../';
	$fsource = $phpbb_root_path . $config['upload_path']. '/' .$attachment['physical_filename'];
  $validImgExt = array("jpg", "jpeg", "gif", "png");
	
 if (!in_array(strtolower($attachment['extension']), $validImgExt)) {
    send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
 }
 
// JPG	
 if( strtolower($attachment['extension']) == 'jpg' OR strtolower($attachment['extension']) == 'jpeg' ){
	 $source = @imagecreatefromjpeg($fsource);
	} elseif(strtolower($attachment['extension']) == 'gif'){
	 $source = @imagecreatefromgif($fsource);
	} elseif(strtolower($attachment['extension']) == 'png'){
	 $source = @imagecreatefrompng($fsource);
	} 

	  if(!$source)
    {
     echo "ERROR-SOURCEIMG-NOTEXIST-OR-CANTCREATE"; exit; 
    }

// a little img for the little data_uri container
$desired_width="100"; // resize max width
$desired_height="100"; // resize max height

$width = $orig_width = @imagesx($source);
$height = $orig_height = @imagesy($source);

 if(!$width OR !$height OR $width < 5 OR $height < 5){
	 echo "The image is too small"; exit; // ??
  }

 if($orig_width > $desired_width OR $orig_height > $desired_height){

    if ($height > $desired_height) { // taller
        $width_res = ($desired_height / $height) * $width;
        $height_res = $desired_height;
    }

    if ($width > $desired_width) { // wider
        $height_res = ($desired_width / $width) * $height;
        $width_res = $desired_width;
    }
 } else { $width_res = $width; $height_res = $height; }

 $width_res = intval($width_res);
 $height_res = intval($height_res);
 //$html_img_output_minContainer = $width_res > $height_res ? $width_res : $height_res;
 
// Going to create just a little jpg for the scope (and the bg of any image will be black (as code is))

$thumb = imagecreatetruecolor($width_res, $height_res);

imagecopyresized($thumb, $source, 0, 0, 0, 0, $width_res, $height_res, $width, $height);
ob_start();
$thumb = imagejpeg($thumb); // before to follow here, should check if the image has been possible to be created
$imgOut = ob_get_contents();
ob_end_clean();
imagedestroy($source);
function img_data_uri($file, $mime) 
{ 
  $base64   = base64_encode($file); 
  return ('data:' . $mime . ';base64,' . $base64);
}

 $cookie_domain = $config['cookie_domain'][0] == '.' ? substr($config['cookie_domain'], 1) : $config['cookie_domain'];

// raw popup output
 
	echo '<html><head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
   document.domain = "'.$cookie_domain.'";
    let w3Delta = 0;
    let countW3 = 0;
 
   function w3rotateByDeg(e){
     w3Delta+=90;
     e.style.webkitTransform="rotate("+w3Delta+"deg)";
     e.style.transform = "rotate("+w3Delta+"deg)";
     if(w3Delta > 270){w3Delta = 0};
    // console.log(w3Delta);
     countW3++;
   }
    
function w3sendThis(){
 
 if(countW3 < 1){ 
  alert("'.$user->lang['W3POPUP_ALERT'].'");
  return;
 }

 let ARY = [];
 ARY[0] = w3Delta;
 ARY[1] = "'.$attach_id.'";

 values =  JSON.stringify(ARY);
 
  var XHR = new XMLHttpRequest();
  var urlEncodedData = "";
  var urlEncodedDataPairs = [];
 
 urlEncodedDataPairs.push(encodeURIComponent("data") + "=" + encodeURIComponent(ARY));

   XHR.addEventListener("error", function(event) {
    console.log("Error: " + event);
   });
   XHR.addEventListener("timeout", function(event) {
    console.log("Error: timeout");
   });
   XHR.onreadystatechange = function() {
    if (XHR.readyState === 0 || XHR.readyState === 1) { 
      //console.log(XHR.response + " starting");
    } else if (XHR.readyState === 3) {
    	//console.log(XHR.response + " waiting");
    } else if (XHR.readyState === 4) { // onload -> done
      //console.log(XHR.response);   
       if (XHR.response.indexOf("OK-IMG-PROCESSED") > -1 !== false){
        parent.w3_fileSrc_ajaxup(XHR.response);
        w3Delta = countW3 = 0; // reset to actual 0 degrees if popup not closed, and another rotation occur
        // alert("The image has been rotated, closing popup ...");
        //console.log(XHR.response + " -> done");
       } 
      }
   }

  XHR.open("POST", "'.$phpbb_root_path.'ext/w3all/imageattachrotation/core/fileRotate.php");
  XHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  XHR.send(urlEncodedDataPairs);
}
</script>
<style>
.w3Body{
background: -moz-linear-gradient(left, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.9) 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(left, rgba(0,0,0,0.9) 0%,rgba(0,0,0,0.9) 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(to right, rgba(0,0,0,0.9) 0%,rgba(0,0,0,0.9) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#e6000000\', endColorstr=\'#e6000000\',GradientType=1 ); /* IE6-9 */
color:#f1f1f1;
    max-width: 100%;
    height: auto;
    width: auto\9; /* ie8 */
}
img{
    max-width: 100%;
    height: auto;
    width: auto\9; /* ie8 */
    padding:10px;
    margin:0px;
}

button {
  background-color:#000;
} 
button:hover {
  background-color:green;
}

.w3divCont{
text-align:center;
padding:15px;
color:#f1f1f1;
}

.w3divimg{
text-align:center;
padding:0px;
margin:0px;
}

.w3Bround{
/*font-size:1.2em;*/
border-radius: 8px;
}
</style></head>
<body class="w3Body">';
echo'<div class="w3divContainer">';
echo '<div class="w3divCont"><strong>'.$user->lang['W3POPUP_TEXTEXPLAIN'].'</strong></div>';
echo'<div class="w3divimg"><img id="w3-img-rotate" src="'.img_data_uri($imgOut, 'image/'.$attachment['extension'].'').'" onclick="w3rotateByDeg(this)" /></div>';
echo'<div class="w3divCont"><button class="w3divCont w3Bround" type="submit" id="btn" onclick="w3sendThis();">'.$user->lang['W3POPUP_BUTTONTEXT'].'</button></div>';
echo'</div></body></html>';
exit;
