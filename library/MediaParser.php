<?php

function parseflv($url, $width = 0, $height = 0) {
	global $_G;
	$lowerurl = strtolower($url);
	$flv = $iframe = $imgurl = '';		
	if(empty($_G['setting']['parseflv']) || !is_array($_G['setting']['parseflv'])) {
		return FALSE;
	}
	
	foreach($_G['setting']['parseflv'] as $script => $checkurl) {
		$check = FALSE;
		foreach($checkurl as $row) {
			if(strpos($lowerurl, $row) !== FALSE) {
			    $check = TRUE;
			    break;
			}
		}
		if($check) {
			@include_once libfile('media/'.$script, 'function');
			if(function_exists('media_'.$script)) {
			    list($flv, $iframe, $url, $imgurl) = call_user_func('media_'.$script, $url, $width, $height);
			}
			break;
		}
	}	    	
	if($flv) {
		if(!$width && !$height) {
			return array('flv' => $flv, 'imgurl' => $imgurl);
		} else {
			$width = addslashes($width);
			$height = addslashes($height);
			$flv = addslashes($flv);
			$iframe = addslashes($iframe);
			$randomid = 'flv_'.random(3);
			$enablemobile = $iframe ? 'mobileplayer() ? "<iframe height=\''.$height.'\' width=\''.$width.'\' src=\''.$iframe.'\' frameborder=0 allowfullscreen></iframe>" : ' : '';
			return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=('.$enablemobile.'AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$flv.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\'));</script>';
		}
	} else {
		return FALSE;
	}
}
