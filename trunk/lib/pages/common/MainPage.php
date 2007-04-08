<?php
/*
  Copyright (c) 2005-2006 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . '/../base/manager.php'); 

///Página principal
class ApfMainPage extends ApfManager implements iDocument {
	///Constructor
	function __construct() {
		parent::__construct(_t(main_page));
	}
	
	///Método cuerpo
	function body() {
		/*$lan=ApfLocal::getDefaultLanguage();
		$query='select value from vid_cfg where `key`="intro_' . $lan . '"';
		//echo($query);
		$this->query($query);
		$vals=$this->fetchArray();
		echo($vals[0]);*/
		echo(_t('main_welcome_msg'));
		echo('<br /><br /><div>');
		$this->writeCategoryListControl();
		echo('</div>');
		echo('<table><tr><td>');
		echo('<h2>' . _t('new_videos') . '</h2>');
		echo('<div style="height:210px;overflow:hidden">');
		$this->printNewMedia(5);
		echo('</div>');
		echo('</td></tr><tr><td>');
		echo('<h2>' . _t('most_viewed_videos') . '</h2>');
		echo('<div style="height:210px;overflow:hidden">');
		$this->printTopMedia(5);
		echo('</div>');
		echo('</td></tr></table>');
	}
}

?>