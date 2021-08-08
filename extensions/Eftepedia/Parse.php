<?php
class EftepediaParse
{
  // Hook our callback function into the parser
  static function ParserInit( Parser $parser ) {
    $parser->setHook( 'parse', 'EftepediaParse::Render' );
    return true;
  }

  function Render( $input, array $args, Parser $parser, PPFrame $frame ) {
    $output = $parser->recursiveTagParse( $input, $frame );
    var_dump($output);
    return $output;
  }
}
$wgHooks['ParserFirstCallInit'][] = 'EftepediaParse::ParserInit';
