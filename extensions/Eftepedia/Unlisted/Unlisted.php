<?php 
/**
 * Extension om van pagina's (met name redirects) aan te kunnen geven of ze 
 * getoond of verborgen moeten worden in het overzicht.
 */
 
 

// Registreert het magic keyword '__UNLISTED__' dat ervoor moet zorgen dat de betreffende pagina niet
// zichtbaar is in de categorie-lijsten en het overzicht van alle pagina's. 
MagicWord::$mDoubleUnderscoreIDs[] = 'unlisted';
// Registreert het magic keyword '__LISTED__' dat ervoor moet zorgen dat de 
MagicWord::$mDoubleUnderscoreIDs[] = 'listed';

$wgExtensionMessagesFiles['EftepediaUnlisted'] = dirname(__FILE__) . '/Unlisted.i18n.php';

/**
 * Automatisch detecteren of een pagina listed of unlisted moet zijn.
 * Vuistregel: Als de pagina een redirect is, die behalve de redirect ook nog 
 * content heeft (meestal een categorie), dan is de pagina listed. 
 * Redirects zonder content zijn waarschijnlijk alleen alternatieve schrijfwijzen.
/*

// Hook om automatisch doorverwijspagina's te tonen of te verbergen.
function Eftepedia_ArticlePageDataAfter( $article, $row  ) 
{
  //echo '<span style="font-size: 500%">Ik zit even te debuggen - Jos</span>';
  return true;
}

$wgHooks['ArticlePageDataAfter'][] = 'Eftepedia_ArticlePageDataAfter';


function Eftepedia_ParserBeforeStrip( &$parser, &$text, &$strip_state )
{
    //var_dump($text);
  $x = strtoupper($text);
  if (strpos($x, 'DOORVERWIJZING') !== false ||
      strpos($x, 'REDIRECT') !== false)
  {
    //var_dump($text);
    //exit;
    //$text .= "\n__LISTED__";
  }
  return true;
}
$wgHooks['ParserBeforeStrip'][] = 'Eftepedia_ParserBeforeStrip';
*/