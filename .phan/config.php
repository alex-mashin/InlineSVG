<?php
$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';
// Analyze this extension's own Composer dependencies (signatures only):
$cfg['directory_list'] = array_merge( $cfg['directory_list'], [ 'vendor' ] );
$cfg['exclude_analysis_directory_list'] = array_merge( $cfg['exclude_analysis_directory_list'], [ 'vendor' ] );
$cfg['suppress_issue_types'] = array_merge( $cfg['suppress_issue_types'], [
	'PhanUndeclaredClassMethod', // extensions only suggested.
	// MediaWiki core is not visible outside a checkout (REVIEW.md D9):
	'PhanUndeclaredInterface',
	'PhanUndeclaredTypeParameter',
	'PhanUndeclaredTypeProperty',
	'PhanUndeclaredFunction',
] );
return $cfg;
