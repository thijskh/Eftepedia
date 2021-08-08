<?php
// Eigengeschreven reader extensions

/* *********************************************************************** **
Hook voor het niet genereren van login links
** *********************************************************************** */
function NoLoginLinkOnMainPage( &$personal_urls ){
	$personal_urls = array();
	//unset( $personal_urls['login'] );
	//unset( $personal_urls['anonlogin'] );
	return true;
}
$wgHooks['PersonalUrls'][]='NoLoginLinkOnMainPage';
	
/* *********************************************************************** **
Beperken van toegestane pagina's
** *********************************************************************** */
function isAllowedSpecialPage($title)
{
	// Pagina's die toegestaan zijn.
	$allowed = array(
		'Zoeken'=>true, 
		'SphinxSearch'=>true, 
		'AllePaginas'=>true,
		'Willekeurig'=>true,
    'Categorieën'=>true,
    'Categorie%C3%ABn'=>true,
		'VerwijzingenNaarHier'=>true);
	$p = strpos($title, '/');
	if ($p !== false)
	{
		$title = substr($title, 0, $p);
	};
	return array_key_exists($title, $allowed);
}

function isAllowedPage( &$title, $article, &$output, &$user, $request, $mediaWiki ) 
{

	global $wgReaderHomePage;

	if (
		($title->mNamespace == 0 /*Main*/ && isValidGeelVersion($title)) || 
		($title->mNamespace == 4 /*Project*/ && isValidGeelVersion($title)) || 
		$title->mNamespace == 6 /*File*/ || 
		($title->mNamespace == 14 /*Category*/ && $title->isKnown()) ||
		($title->mNamespace == -1 /*Special*/ && isAllowedSpecialPage($title->mUrlform)) ||
    ($title->mNamespace == NS_TEMPLATE /*Main*/ && isValidGeelVersion($title)) 
	)
	{
		// Toegestaan
	}
	else {
		// Niet toegestaan. Redirecten
		if ($title->getText() !== $wgReaderHomePage)
		{
			//global $wgScriptPath;
			header("Location:https://www.eftepedia.nl".articlePath($wgReaderHomePage));
		}
		exit;
	}

	return true;
}
$wgHooks['BeforeInitialize'][] = 'isAllowedPage';	

/* *********************************************************************** **
Overriden van speciale pagina's. Ze moeten ook in de lijst met uitzonderingen staan.
** *********************************************************************** */
$pageOverrides = array('SpecialRecentChanges', 'SpecialAllPages', 'ImagePage');
foreach ($pageOverrides as $pageKey)
{
	$wgAutoloadLocalClasses[$pageKey] = 'eftepedia_reader/includes/' . $pageKey . '.php';
}

/* *********************************************************************** **
Pagina's die niet getoond kunnen/mogen worden, worden ook niet gelinkt.
** *********************************************************************** */
function pageExistsInAdminVersion($title)
{
  $result = false;
  
  if ($title->getNamespace() == NS_MAIN) // Alleen doen voor lemma's in main.
  {
    global $wgSharedTables;
    $pageTable = 'page';
    // Viespeukerij: Even terugswitchen naar de CMS-tabel, en daarin de titel zoeken.
    // Alleen doen als `page` bij de uitzonderingen staat, anders heeft het toch geen zin
    // en levert het alleen maar rare situaties op.
    
    if(($key = array_search($pageTable, $wgSharedTables)) !== false)
    {
      unset($wgSharedTables[$key]);

      $dbr = wfGetDB( DB_REPLICA );
      $dbr->setTableAliases($wgSharedTables);

      $res = $dbr->select(
        array( 'page', 'redirect' ),
        array( 'page_title' ),
        array(
          'page_title' => $title->getDBkey(),
          'page_namespace' => NS_MAIN,
          // Arbitraire grootte gekozen om bestaande pagina's met een kleine notitie te weren.
          // Dit heeft wel als nadeel dat redirects niet gecheckt worden.
          'page_len > 500'),
        __METHOD__
      );
      $count = $dbr->numRows( $res );
      
      if ( $count > 0 ) {
        $result = true;
      }
      $wgSharedTables[] = $pageTable; // Belangrijk?: shared tabels herstellen!
      $dbr->setTableAliases($wgSharedTables);

    }
  }
  
  return $result;
}

function redLink($text)
{
  return '<span class="redlink">' . $text . '</span>';
}

function hideRedLinks( $skin, $target, $options, &$text, &$attribs, &$ret )
{
  // Known lemma's altijd gewoon linken.
	if ($target->isKnown())
  {
    return true;
  }
	
  // Niet known? Dan geen link. Geef alleen de titel terug.
	if (is_array($text)) {
		$ret = $text['title'];
	} else {
		$ret = $text;
	}
    
  // Als de titel wel al bekend is in CMS, dan de tekst rood maken als aankondiging voor aanstaande publicatie.
  if (pageExistsInAdminVersion($target)) {
    $ret = redLink($ret);
  }
		
	return false;
}

$wgHooks['LinkEnd'][] = 'hideRedLinks';


/* *********************************************************************** **
  Prefix-search proberen te beinvloeden, om zo de suggesties-lijst van het 
  zoekboxje aan te passen. Kan gebruikt worden om zinloze dingen te schrappen
  (bijv, redirects naar pagina's die al in de lijst staan).
** *********************************************************************** */
function efHooks_PrefixSearchBackend( $ns, $search, $limit, &$results ) {
	$results = array('Eftepedia');
	return false;
}

function efHooks_ArticleViewHeader(&$article, &$outputDone, &$useParserCache)
{

  global $wgUseFileCache;
	if ($wgUseFileCache)
  {
    $cache = new HTMLFileCache( $article->getTitle(), 'view' );
    ob_start( array( &$cache, 'saveToFileCache' ) );
  }
  return true;
}
$wgHooks['ArticleViewHeader'][] = 'efHooks_ArticleViewHeader';

function efHooks_DisplayOldSubtitle( $article, $oldid ) {
  // Voorkomen dat de info over de huidige versies en de navigatielinks 
  // voor eerdere versies worden getoond.
  return false;
}
$wgHooks['DisplayOldSubtitle'][] = 'efHooks_DisplayOldSubtitle';

function efHooks_MediaWikiPerformAction( $output, $article, $title, $user, $request, $wiki ) 
{
  // Voorkomen dat actions uitgevoerd worden in de read-versie.
  if ($request->getVal('action') === null || $request->getVal('action') === 'redirect')
  {
    return true;
  }

  return false;
}
$wgHooks['MediaWikiPerformAction'][] = 'efHooks_MediaWikiPerformAction';

function efHooks_onSpecialSearchProfiles( &$profiles ) 
{
  $profiles = array();
  return true;
}

$wgHooks['SpecialSearchProfiles'][] = 'efHooks_onSpecialSearchProfiles';
