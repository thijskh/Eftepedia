<?php
/* *********************************************************************** **
Speciale adminonly en readeronly tags die inhoud alleen in de adminversie 
resp readerversie toont.
** *********************************************************************** */

$wgHooks['ParserFirstCallInit'][] = 'EftepediaAdminReaderTag::ParserFirstCallInit';
 
class EftepediaAdminReaderTag {
  // Hook our callback function into the parser
  public static function ParserFirstCallInit( Parser $parser ) {
          $parser->setHook( 'adminonly', 'EftepediaAdminReaderTag::adminonly' );
          $parser->setHook( 'readeronly', 'EftepediaAdminReaderTag::readeronly' );

          // Always return true from this function. The return value does not denote
          // success or otherwise have meaning - it just must always be true.
          return true;
  }
   
  public static function adminonly( $input, array $args, Parser $parser, PPFrame $frame ) {
    global $geelVersion;
    if ($geelVersion === VERSION_ADMIN)
      return $output = $parser->recursiveTagParse( $input, $frame );
    return '';
  }

  public static function readeronly( $input, array $args, Parser $parser, PPFrame $frame ) {
    global $geelVersion;
    if ($geelVersion === VERSION_READER)
      return $output = $parser->recursiveTagParse( $input, $frame );
    return '';
  }
}
