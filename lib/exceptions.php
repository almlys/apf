<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

//Lanzar errores como si fueran excepciones
class PHPError extends Exception {
	public static function errorHandlerCallback($code, $string, $file, $line, $context) {
	$errortype = array (
               E_ERROR              => 'Error',
               E_WARNING            => 'Warning',
               E_PARSE              => 'Parsing Error',
               E_NOTICE            => 'Notice',
               E_CORE_ERROR        => 'Core Error',
               E_CORE_WARNING      => 'Core Warning',
               E_COMPILE_ERROR      => 'Compile Error',
               E_COMPILE_WARNING    => 'Compile Warning',
               E_USER_ERROR        => 'User Error',
               E_USER_WARNING      => 'User Warning',
               E_USER_NOTICE        => 'User Notice',
               E_STRICT            => 'Runtime Notice',
               E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
               );
		$e = new self($errortype[$code] . ": ". $string, $code);
		$e->line = $line;
		$e->file = $file;
		throw $e;
	}
}
$e_types=E_ALL & ~E_NOTICE;
//$e_types=E_ALL | E_STRICT;
set_error_handler(array("PHPError", "errorHandlerCallback"), $e_types);

function print_exception($e,$body_tags=False) {
	if($body_tags) {
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Exception <?php echo(get_class($e)); ?></title>
</head>
<body>
<?php
	}
?>
	<table border="1" cellpadding="0" cellspacing="0">
	<TR>
		<TD bgcolor="Yellow">
		<font color="Red"><b>Exception: <?php echo get_class($e); ?></b></font>
		</td></tr><tr><td bgcolor="Black">
		<font color="White"><b><?php echo $e->getMessage()." ".$e->getCode(); ?></b><br /><br />
		At file: <?php echo $e->getFile()." line: ".$e->getLine(); ?><br />
		</font>
		<pre><font color="White"><code><?php echo $e->getTraceAsString(); ?></code></font></pre>
		</td>
	</tr>
	</table>
<?php
	if($body_tags) {
?>
</body>
</html>
<?php
	}
}

?>