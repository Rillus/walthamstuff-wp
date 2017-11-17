<?php 

require_once('CssToInline/CssToInlineStyles.php');

use \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

function inline_newsletter($css,$content){
	// create instance
	$cssToInlineStyles = new CssToInlineStyles();

		$cssToInlineStyles->setHTML($content);
		$cssToInlineStyles->setCSS($css);

		// output
	return $cssToInlineStyles->convert();	

}
?>