<?php
 
$wgHooks['ParserFirstCallInit'][] = 'EftepediaRecentChangesTag::ParserFirstCallInit';
 
class EftepediaRecentChangesTag {

  // Hook our callback function into the parser
  public static function ParserFirstCallInit( Parser $parser ) {
    // When the parser sees the <sample> tag, it executes 
    // the wfSampleRender function (see below)
    $parser->setHook( 'publishedpages', 'EftepediaRecentChangesTag::Render' );
          // Always return true from this function. The return value does not denote
          // success or otherwise have meaning - it just must always be true.
    return true;
  }
   
  // Execute 
  public static function Render( $input, array $args, Parser $parser, PPFrame $frame ) 
  {
    $html = '';
    // Parameters verwerken: new, modified, limit=N
    // Limiteren van aantal rijen.
    $limit = 10;
    if (array_key_exists('limit', $args))
      $limit = @(0 + $args['limit']);
    
    if ($limit > 50) $limit = 50;
    
    $modified = array_key_exists('modified', $args);
    $new = array_key_exists('new', $args);
    
    // Condities en sortering bepalen o.b.v. filter.
    $filtersort = '';
    if ($modified && !$new)
    {
      $filtersort = '
        and ps_first_published != ps_last_modified
      order by 
        ps_last_modified desc';
    }
    elseif ($new && !$modified)
    {
      $filtersort = '
      order by 
        ps_first_published desc';
    }
    else
    {
      $filtersort = '
      order by 
        ps_last_modified desc';
    }
    
    // Daadwerkelijke rijen ophalen.
    $dbr = wfGetDB( DB_REPLICA );  
    $sql = <<<sql
      select
        page_title,
        page_namespace,
        ps_first_published,
        ps_last_modified
      from
        gwl_page
        inner join gwl_page_stats on ps_page_id = page_id
      where
        page_is_redirect = 0 and
        page_namespace = 0
      $filtersort
      limit $limit
sql;
    $pageQuery = $dbr->query($sql);
    
    // Resultaten ophalen in loop, en tegelijk de laagste modified timestamp bepalen. 
    // Dat om te kunnen kijken of een pagina nieuw is binnen de context van de lijst. 
    // Als een pagina nieuw is, maar daarna ook direct weer gewijzigd, dan wil je 'm
    // nog wel laten zien als nieuw. Daarom: als een pagina nieuwer is dan de oudste 
    // wijziging in de weergegeven lijst, dan wordt de pagina als nieuw beschouwd.
    // In de eerste loop halen we de data op en bepalen we de oudste wijziging.
    $oldestModified = '99999999999999';
    $rows = array();
    while ($row = $dbr->fetchObject($pageQuery))
    {
      $rows[] = $row;
      if ($new)
        if ($row->ps_last_modified < $oldestModified)
          $oldestModified = $row->ps_last_modified;
    }
    
    // Tweede loop: Daadwerkelijke items genereren.
    $pages = array();
    foreach ($rows as $row)
    {
      $title = Title::newFromText($row->page_title, $row->page_namespace);
      
      $newText = '';
      $isNew = ($row->ps_first_published >= $oldestModified);

      $namespace = $title->getNsText();
      if ($namespace != '') 
        $namespace .= ':';
      
      $class = ($isNew?'new':'modified');
      $pages[] = "* <span class='page $class'><a href='" . $title->getLocalUrl() . "'>" . $title->getText() . "</a></span>";
    }
    
    // Inpakken in extra elementen voor meer mogelijkheid tot stijlen.
    $classes = ($new?'new ':'') . ($modified?'modified ':'') . (($new != $modified)?'only':'');
    $html = '';
    $html .= "<div class='publishedpages $classes'>\n";
    $html .= implode("\n", $pages);
    $html .= "\n</div>";
    return $html;
  }
}
