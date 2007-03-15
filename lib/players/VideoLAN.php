<?php
/*
  Copyright (c) 2005-2007 Alberto Montañola Lacort.
  Licensed under the GNU GPL. For full terms see the file COPYING.

  Id: $Id$
*/

require_once(dirname(__FILE__) . '/basePlayer.php');
require_once(dirname(__FILE__) . '/../widgets/button.php');

class VideoLANPlayer implements iPlayer {

	private $path;
	private $parent;
	private $width=660;
	private $height=298;
	//400x300 4:3
	//480x360 4:3
	//500x375 4:3
	//480x270 16:9
	//500x226 221:100
	//600x271 221:100

	function __construct($parent,$path) {
		$this->path=$path;
		$this->parent=$parent;
	}

	function write() {
		?>
		<div class='error' id='vlc_error_msg'>
		</div>
		<div class='vlc_player' id='player'>
		<object classid='clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921'
			codebase='http://downloads.videolan.org/pub/videolan/vlc/latest/win32/axvlc.cab#Version=0,8,6,0'
			width='<?php echo($this->width); ?>'
			height='<?php echo($this->height); ?>'
			id='vlc'
			events='True'>
		<param name='MRL' value='<?php echo($this->path); ?>' />
		<param name='ShowDisplay' value='True' />
		<param name="AutoLoop" value="False" />
		<param name="AutoPlay" value="True" />
		<param name="Volume" value="50" />
		<param name="StartTime" value="0" />
		<param name="ShowControls" value="True"  />
		<param name="type" value="application/x-vlc-plugin" />
		</object>
		<div id='embed_player'>
		<embed type="application/x-vlc-plugin" pluginspage="http://www.videolan.org" 
			version="VideoLAN.VLCPlugin.2"
			name="vlc"
			autoplay="true"
			hidden="no"
			loop="no"
			width="<?php echo($this->width); ?>"
			height="<?php echo($this->height); ?>"
			target="<?php echo($this->path); ?>" />
		</div>
		<div class='player_controls'>
		<!-- Controls -->
		<?php /*<a href="javascript:;" onclick="vlc.playlist.play()">Play RTSP</a>
		<a href="javascript:;" onclick="vlc.playlist.togglePause()">Pause RTSP</a>
		<a href="javascript:;" onclick="vlc.playlist.stop()">Stop RTSP</a>
		<a href="javascript:;" onclick="vlc.video.fullscreen=true">Fullscreen RTSP</a>
		<a href="javascript:;" onclick="vlc.input.position=0.5">Seek</a> 
		<br />*/ ?>
		<table width='100%' border='0' cellpadding='0' cellspacing='0'>
		<tr><td align='center'>
		<div id='pgbar_drag' class='bar_drag'>
<?php /*<img class='bar2_drag' width='<?php echo($this->width); ?>' height='1' alt='Progress' src='<?php echo($this->parent->buildBaseUri('imgs/progress_point_white.png')); ?>' /> */ ?>
<?php /*
<img class='bar2_drag' id='pbar_green' width='<?php echo($this->width); ?>' height='5' alt='Progress' src='<?php echo($this->parent->buildBaseUri('imgs/progress_point.png')); ?>' /> */ ?>
<?php /* <img class='bar2_drag' width='<?php echo($this->width); ?>' height='1' alt='Progress' src='<?php echo($this->parent->buildBaseUri('imgs/progress_point_white.png')); ?>' /> */ ?>
		<div id='pgbar_green' class='bar2_drag'></div>
		</div>
		</td></tr>
		<tr><td align='center'>
		<table class='player_controls_table' border='0' cellpadding='0' cellspacing='5'><tr><td>
		<img class='button' id='play_button' width='32' height='32' alt='Play/Pause' src='<?php echo($this->parent->buildBaseUri('imgs/play.png')); ?>' onclick='play_video();' />
		<img class='button' id='stop_button' width='32' height='32' alt='Stop' src='<?php echo($this->parent->buildBaseUri('imgs/stop.png')); ?>' onclick='stop_video();' />
		<img class='button' id='fullscren_button' width='32' height='32' alt='Stop' src='<?php echo($this->parent->buildBaseUri('imgs/fullscreen.png')); ?>' onclick='fullscreen_video();' />
		</td><td>
		<b>AR:</b>
		<select onchange='change_aspect_ratio(this.value)'>
		<option value='default'><?php echo(_t('default')); ?></option>
		<option value='1:1'>1:1</option>
		<option value='4:3'>4:3</option>
		<option value='16:9'>16:9</option>
		<option value='221:100'>221:100</option>
		<option value='5:4'>5:4</option>
		</select>
		</td>
		<td>
		<img class='button' width='32' height='32' alt='VolumeUp' src='<?php echo($this->parent->buildBaseUri('imgs/volumeup.png')); ?>' onclick='volume_update(+10);' />
		<img width='32' height='32' alt='Volume' src='<?php echo($this->parent->buildBaseUri('imgs/volume.png')); ?>' />
		</td><td>
		<span id='volumeText'>--</span>
		</td><td>
		<img class='button' width='32' height='32' alt='VolumeDown' src='<?php echo($this->parent->buildBaseUri('imgs/volumedown.png')); ?>' onclick='volume_update(-10);' />
		</td><td>
		<span id='playstats'>00:00/00:00</span>
		</td>
		</tr>
		</table>
		</td></tr></table>
		</div>

		<?php
		/*
		$b=new ApfButton($this->parent,'test','imgs/config.png','play_video()');
		$b->write();
		$b=new ApfButton($this->parent,'test','imgs/config.png','play_video()');
		$b->write();
		*/
		?>
		</div>
		<?php
		if(1) {
		?>
		<div id="debug" style='visibility:hidden;height:0px'>
		<?php
		} else {
		?>
		<div id="debug">
		<?php
		}
		?>
		<hr />
		</div>
		<script type='text/javascript'>
			//<![CDATA[
			var errormsg = document.getElementById("vlc_error_msg");
			var player=document.getElementById("player");
			var embed_player=document.getElementById("embed_player");
			function log_write(text) {
				var out=document.getElementById("debug");
				out.innerHTML+=text+"<br />";
			}
			log_write("Initializing...");
			var vlc=null;
			try {
				if (window.document['vlc']) {
					vlc=window.document['vlc'];
				} else if(navigator.appName.indexOf("Microsoft Internet")==-1) {
					if (document.embeds && document.embeds['vlc']) {
						vlc=document.embeds['vlc'];
					}
				} else {
					vlc=document.getElementById('vlc');
				}
			} catch(e) {
				vlc=null;
			}
			try {
				var vlc_version=vlc.versionInfo();
			} catch(e) {
				vlc=null;
			}

			var btnPlay = document.getElementById("play_button");
			var volumeText = document.getElementById("volumeText");
			var playstats = document.getElementById("playstats");
			var pgbar_green = document.getElementById("pgbar_green");
			var pgbar_drag = document.getElementById("pgbar_drag");
			var mywidth=<?php echo($this->width); ?>;
			pgbar_drag.style.width=(mywidth)+'px';
			pgbar_green.style.width=0+'px';


			if(vlc==null) {
				log_write("VideoLAN not found, or unsuported browser");
				errormsg.innerHTML='<?php echo(_t('vlcNotFound')); ?>'
				errormsg.innerHTML+='<br /><a href="<?php echo($this->path); ?>"><?php echo(_t('vlcManual')); ?></a>';
			} else {
				if (navigator.appName.indexOf("Microsoft Internet")!=-1) {
					embed_player.innerHTML="Unsuported brownser!!!";
				}
				var version=vlc.VersionInfo;
				log_write("VideoLAN version: " + version);
				self.setTimeout("do_events()",1000);
				volumeText.innerHTML = vlc.audio.volume/2+"%";
			}

			var oldstate = 11;

			function do_events() {
				self.setTimeout("do_events()",1000);
				var state = vlc.input.state;
				if(oldstate!=state) {
					oldstate=state;
					switch (state) {
						case 0: //IDLE/CLOSE
							log_write("Idle/Close");
							onStop();
							break;
						case 1: //OPENING
							log_write("Opening");
							onOpen();
							break;
						case 2: //BUFFERING
							log_write("Buffering");
							onBuffer();
							break;
						case 3: //PLAYING
							//log_write("Playing");
							onPlay();
							break;
						case 4: //PAUSED
							//log_write("Paused");
							onPause();
							break;
						case 5: //STOPPING
							log_write("Stopping");
							//onStop();
							break;
						case 6: //ERROR
						default: //ERROR
							log_write("ERROR!!");
							break;
					}
				}
				updateStats();
			}

			function play_video() {
				if(vlc.input.state==3 || vlc.input.state==4) {
					vlc.playlist.togglePause();
				} else if(vlc.input.state==0) {
					vlc.playlist.play();
				}
			}

			function stop_video() {
				// Work around for unsolved bug #908 http://trac.videolan.org/vlc/ticket/908
				if(vlc!=null) {
					vlc.playlist.stop();
				}
			}

			function fullscreen_video() {
				vlc.video.fullscreen=true;
			}

			function change_aspect_ratio(val) {
				vlc.video.aspectRatio = val;
			}

			function volume_update(val) {
				try {
					vlc.audio.volume += val;
					volumeText.innerHTML = vlc.audio.volume/2+"%";
				} catch(e) {
					//NOOP
				}
			}

			function seek_video(val) {
				if(val<0.0 || val>1.0) return;
				vlc.input.position = val;
			}
			
			function formatTime(timeVal) {
					var timeHour = Math.round(timeVal / 1000);
					var timeSec = timeHour % 60;
					if( timeSec < 10 )
							timeSec = '0'+timeSec;
					timeHour = (timeHour - timeSec)/60;
					var timeMin = timeHour % 60;
					if( timeMin < 10 )
							timeMin = '0'+timeMin;
					timeHour = (timeHour - timeMin)/60;
					if( timeHour > 0 )
							return timeHour+":"+timeMin+":"+timeSec;
					else
							return timeMin+":"+timeSec;
			}
			
			function updateStats() {
				playstats.innerHTML=formatTime(vlc.input.time)+"/"+formatTime(vlc.input.length);
				pgbar_green.style.width=(vlc.input.position*pgbar_drag.clientWidth) + 'px';
			}

			/* Events */

			function onOpen() {

			}
			function onBuffer() {
				
			}
			function onPlay() {
				btnPlay.src='<?php echo($this->parent->buildBaseUri('imgs/pause.png')); ?>';
			}
			function onPause() {
				btnPlay.src='<?php echo($this->parent->buildBaseUri('imgs/play.png')); ?>';
			}
			function onStop() {
				btnPlay.src='<?php echo($this->parent->buildBaseUri('imgs/play.png')); ?>';
			}

			// Experimental progress bar stuff

			var _startX = 0; // mouse starting positions
			var _startY = 0;
			var _offsetX = 0; // current element offset
			var _offsetY = 0;
			var _dragElement; // needs to be passed from OnMouseDown to OnMouseMove
			var _oldZIndex = 0; // we temporarily increase the z-index during drag
			var _debug = document.getElementById("debug"); // makes life easier

			InitDragDrop();

			function InitDragDrop() {
				document.onmousedown = OnMouseDown;
				//document.onmouseup = OnMouseUp; 
			}

			function OnMouseDown(e) { 
				// IE is retarded and doesn't pass the event object
				if (e == null)
					e = window.event;
				
				// IE uses srcElement, others use target
				var target = e.target != null ? e.target : e.srcElement;
				
				//_debug.innerHTML = target.className == 'bar_drag' ? 'draggable element clicked' : 'NON-draggable element clicked';
				
				// for IE, left click == 1 
				// for Firefox, left click == 0
				if ((e.button == 1 && window.event != null || e.button == 0) && (target.className == 'bar_drag' || target.className == 'bar2_drag')) {
					//log_write(e.clientX + " " + ExtractNumber(target.style.left));
					//log_write(target.offsetLeft);
					var pos = getPosition(target);
					//log_write(e.clientX - pos.x);
					//log_write(target.clientWidth);
					//var video_progress = (e.clientX - pos.x)/target.clientWidth;
					var video_progress = (e.clientX - pos.x)/pgbar_drag.clientWidth;
					log_write(video_progress);
					seek_video(video_progress);
					/*
					// grab the mouse position
					_startX = e.clientX;
					_startY = e.clientY;

					// grab the clicked element's position
					_offsetX = ExtractNumber(target.style.left);
					_offsetY = ExtractNumber(target.style.top);

					// bring the clicked element to the front while it is being dragged
					 _oldZIndex = target.style.zIndex;
					 target.style.zIndex = 10000;

					// we need to access the element in OnMouseMove
					_dragElement = target;

					// tell our code to start moving the element with the mouse
					document.onmousemove = OnMouseMove; // cancel out any text selections
					document.body.focus(); // prevent text selection in IE 
					document.onselectstart = function () { return false; }; 
					// prevent text selection (except IE)
					return false; */
				} 
			}

			function getPosition(e){
				var left = 0;
				var top  = 0;
			
				while (e.offsetParent){
					left += e.offsetLeft;
					top  += e.offsetTop;
					e     = e.offsetParent;
				}
			
				left += e.offsetLeft;
				top  += e.offsetTop;
			
				return {x:left, y:top};
			}

			function ExtractNumber(value) {
				var n = parseInt(value);
				return n == null || isNaN(n) ? 0 : n; 
			}

			//]]>
		</script>
		<?php
		
	}

}


?>