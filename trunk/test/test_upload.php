<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>Upload Test Page</title>
</head>
<body>

<script language="JavaScript" type="text/javascript">
function parent_callback(a,b) {
	alert(a + " " + b);
}
</script>

<h1>Upload</h1>

<?php

require_once(dirname(__FILE__) . '/../lib/widgets/upload.php');
$up=new UploadCtrl('','img','parent_callback');
$up->write();

?>

Something else
<hr />

</body>
</html>