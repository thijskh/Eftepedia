<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/Randomunapprovedpage/Randomunapprovedpage.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
        'path' => __FILE__,
        'name' => 'Randomunapprovedpage',
        'author' => 'J',
        'url' => '',
        'descriptionmsg' => 'myextension-desc',
        'version' => '0.0.0',
);
 
$wgAutoloadClasses[ 'SpecialRandomunapprovedpage' ] = __DIR__ . '/SpecialRandomunapprovedpage.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'Randomunapprovedpage' ] = __DIR__ . '/Randomunapprovedpage.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
//$wgExtensionMessagesFiles[ 'RandomunapprovedpageAlias' ] = __DIR__ . '/MyExtension.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages[ 'Randomunapprovedpage' ] = 'SpecialRandomunapprovedpage'; # Tell MediaWiki about the new special page and its class name