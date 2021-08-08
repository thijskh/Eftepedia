<?php
/**
 * Eftepedia004 skin
 *
 * @file
 * @ingroup Skins
 * @author Jos Visser, Thijs Kinkhorst
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to the MediaWiki package and cannot be run standalone.' );
}

$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'Eftepedia004', // name as shown under [[Special:Version]]
	'namemsg' => 'skinname-eftepedia004', // used since MW 1.24, see the section on "Localisation messages" below
	'version' => '1.0',
	'url' => 'https://www.eftepedia.nl',
	'author' => 'Jos Visser, Thijs Kinkhorst',
	'descriptionmsg' => 'eftepedia004-skin-desc', // see the section on "Localisation messages" below
	'license' => 'GPL-2.0+',
);

$wgValidSkinNames['eftepedia004'] = 'Eftepedia004';

$wgAutoloadClasses['SkinEftepedia004'] = __DIR__ . '/Eftepedia004.skin.php';
$wgMessagesDirs['Eftepedia004'] = __DIR__ . '/i18n';

$wgResourceModules['skins.eftepedia004'] = array(
	'styles' => array(
		'resources/screen.css' => array( 'media' => 'screen' ),
		'resources/print.css' => array( 'media' => 'print' ),
	),
	'remoteSkinPath' => 'Eftepedia004',
	'localBasePath' => __DIR__,
);

$wgResourceModules['skins.eftepedia004.js'] = array(
	'scripts' => array(
		'Eftepedia004/video.js',
		'Eftepedia004/watishier.js',
		'Eftepedia004/maps.js'
	),
	'position' => 'top',
	'dependencies' => array(
	),
	'remoteBasePath' => &$GLOBALS['wgStylePath'],
	'localBasePath' => &$GLOBALS['wgStyleDirectory'],
);
