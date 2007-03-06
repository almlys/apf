<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>test</title>
</head>
<body>

<script language="JavaScript" type="text/javascript">
function parent_callback(a,b) {
	var out=document.getElementById("debug");
	out.innerHTML+=a+" "+b+"<br />";
}
</script>

<?php

require_once(dirname(__FILE__) . '/../lib/widgets/notebook.php');
require_once(dirname(__FILE__) . '/../lib/widgets/upload.php');
$up=new UploadCtrl('','img','parent_callback');
$uploadctrl=$up->get();

$book = new ApfNoteBook();
$book->AddPage("Upload Test","
<h1>Upload</h1>
$uploadctrl
");
$book->AddPage("FileSystem Test",'
<h1>FileSystem Test</h1>
ToDo
');
$book->AddPage("Another page",'
The content of the page
');
$book->Write();
?>

<hr />
<div id='debug'>

</div>


</body>
</html>