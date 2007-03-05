<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>test</title>
</head>
<body>

<script language="JavaScript" type="text/javascript">
function parent_callback() {
	alert("parent callback");
}
</script>

<?php

require_once(dirname(__FILE__) . '/../lib/widgets/notebook.php');

$book = new ApfNoteBook();
$book->AddPage("Upload",'
<iframe name="upload" src="iframe.php?page=upload" frameborder="0" width="100%" height="300">
Sorry, Your browser does not support the iframe tag, and it does not meet the minimal requirements for this application.
</iframe>
');
$book->AddPage("Something else",'
Another fieldset
<table><tr><td>a table</td></tr><tr><td>la , la, la la</td></tr></table>
<hr />
Ooossp
');
$book->AddPage("Another page",'
The content of the page
');
$book->AddPage("WEEEEEEEEEEEEEEEEEE",'
stha
faf
fs<br />
afjkaf
saf
');
$book->Write();

?>

<hr />



</body>
</html>