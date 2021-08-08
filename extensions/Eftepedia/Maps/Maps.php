<?php
/* *********************************************************************** **
Class voor het tonen van het onderwerp op een kaart.
** *********************************************************************** */

$wgHooks['ParserFirstCallInit'][] = 'EftepediaMaps::ParserFirstCallInit';
 
class EftepediaMaps {
  // Hook our callback function into the parser
  public static function ParserFirstCallInit( Parser $parser ) {
          $parser->setHook( 'map', 'EftepediaMaps::map' );

          // Always return true from this function. The return value does not denote
          // success or otherwise have meaning - it just must always be true.
          return true;
  }
  
  private static function getLandmarks($title) {
    $landmarks = array();
  
    $dbr = wfGetDB( DB_REPLICA );         

    $result = $dbr->select(
      array('t'=>'geo_tags', 'p'=>'page'), // table
      array(
        'gt_page_id', 'gt_primary', 'gt_lat', 'gt_lon', 'gt_dim', 'gt_type', 'gt_name', 
        'page_title'), // fields
      array('gt_page_id=page_id',
      "page_title='" . $title->getDBKey() . "'") // conditions + joins
    );
    while ($landmark = $result->fetchObject()) {
      $landmarks[] = $landmark;
    }
    return $landmarks;
  }
  
  public static function map( $input, array $args, Parser $parser, PPFrame $frame ) {
    global $geelVersion;
    
    $parser->disableCache();
    $output = '';
    if ($geelVersion === VERSION_ADMIN) {
      if ($frame->isTemplate()) {
        $title = $frame->parent->getTitle();
      } else {
        $title = $frame->getTitle();
      }
      
      if ($title && 
          ($landmarks = self::getLandmarks($title))) {
        
        $primary = reset($landmarks);
        foreach($landmarks as $landmark) {
          if ($landmark->gt_primary) {
            $primary = $landmark;
            break;
          }
        }
        
        $landmarksList = '';
        
        foreach ($landmarks as $landmark) {
          $classPrimary = $landmark->gt_primary?'primary':'secondary';
          $landmarkTitle = $landmark->gt_name;
          
          if ($landmarkTitle === null && $landmark->gt_primary) {
            $landmarkTitle = $title->getFullText();
          }
          
          $landmarksList .= <<<LANDMARK
        <div class="landmark {$landmark->gt_type} {$classPrimary}" data-lat="{$landmark->gt_lat}" data-lon="{$landmark->gt_lon}" data-title="{$landmarkTitle}">
        </div>
LANDMARK;
        }

        $output = <<<MAP
<div class="mapapp">
  <div class="map" data-center-lat="{$primary->gt_lat}" data-center-lon="{$primary->gt_lon}" data-size="{$primary->gt_dim}">
    <div class="layers">
      <div class="layer">
        {$landmarksList}
      </div>
    </div>
  </div>
</div>      
MAP;
      }
      return $output;
    }
    //return $output = $parser->recursiveTagParse( $input, $frame );
    return '';
  }

}
