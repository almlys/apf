<?

require_once(dirname(__FILE__) . '/../lib/pages/base/simplepage.php');

///Test class
class ApfTestPage extends ApfSimplePage implements iDocument {

	function __construct() {
		parent::__construct('test');
	}

	function body() {
?>
<script language="JavaScript" type="text/javascript">
function parent_callback(a,b) {
	var out=document.getElementById("debug");
	out.innerHTML+=a+" "+b+"<br />";
}
</script>

<?php

require_once(dirname(__FILE__) . '/../lib/widgets/notebook.php');
require_once(dirname(__FILE__) . '/../lib/widgets/upload.php');
require_once(dirname(__FILE__) . '/../lib/widgets/filesystem.php');
$up=new UploadCtrl($this,'img','parent_callback');
$uploadctrl=$up->get();
$fs=new ApfFilesystem($this);
$fsctrl=$fs->get();

$book = new ApfNoteBook();
$book->AddPage("Upload Test","
<h1>Upload</h1>
$uploadctrl
");
$book->AddPage("FileSystem Test","
<h1>FileSystem Test</h1>
$fsctrl
");
$book->AddPage("Another page",'
The content of the page
');
$book->Write();
?>

<hr />
<div id='debug'>

</div>
<?php

	}
}

?>