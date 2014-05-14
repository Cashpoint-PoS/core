<?
	$GLOBALS["tpl_start"]=-microtime(true);
	if(!isset($tpl_styles)||!is_array($tpl_styles))
		$tpl_styles=array();
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>

	<title>Kassensystem &mdash; <?=$tpl_title?></title>

	<meta charset="utf-8" />
	<meta name="description" content="" />
	<meta name="author" content="" />		
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	
<? foreach($tpl_styles as $file) { ?>
	<link rel="stylesheet" href="./css/<?=$file?>" type="text/css" media="screen" title="no title" />
<? } ?>
</head>
