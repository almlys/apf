<?
if($_SERVER["REMOTE_ADDR"]!="172.26.1.17" && $_SERVER["REMOTE_ADDR"]!="172.26.0.10" && $_SERVER["REMOTE_ADDR"]!="127.0.0.1" && $_SERVER["REMOTE_ADDR"]!="172.26.0.17")
{
  die("!");
  } else {
   phpinfo();
  }
?>


