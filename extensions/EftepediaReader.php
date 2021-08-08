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
  /*
  // Stuk hieronder is niet meer nodig, omdat ongeldige pagina's al gefilterd zijn in de database.


	if ($target->getPartialURL() === '' && $target->getFragment() !== '')
	{
		// Interne link/anchor binnen de pagina.
		return true;
	}
	
	if ($target->mNamespace == 14 /*Category*\)
	{
    if ($target->isKnown() === false)
    {
		  $ret = redLink($text);
    }
		return $target->isKnown();
	}
  
	if ($target->mNamespace == NS_MAIN && isValidGeelVersion($target) === false)
	{
		// Versie is nog helemaal niet goedgekeurd, of heeft nog geen quality versie.
		$ret = redLink($text);
		return false;
	}

	if (in_array($target->mNamespace, array(NS_MAIN, NS_FILE, NS_SPECIAL)) === false)
	{
		// Links naar pagina's in andere name spaces dan toegestaan. 
    // Geen idee wat hier aan de hand is, maar om te voorkomen dat er ineens user names
    // online staan, wordt er helemaal niets geoutput.
		$ret = '';
		return false;
	}

	if ($target->isKnown())
  {
    return true;
  }
  
	//if ($target->getArticleId() > 0)
	//	return true;
	
	if (is_array($text))
		$ret = $text['title'];
	else
		$ret = $text;
		
	return false;*/
}

$wgHooks['LinkEnd'][] = 'hideRedLinks';

/* *********************************************************************** **
   Hook om ervoor te zorgen dat de stable versie van een artikel wordt bekeken, en 
   niet de laatste versie. FlaggedRevs lijkt niet niet helemaal goed te doen als het
   eerste controle-level nog geen quality-level is.
   Wordt gebruikt bij het laden van de pagina in 'title', niet voor links e.d.
** *********************************************************************** */
/*function efHooks_ArticleFromTitle( &$title, &$article ) {
	global $wgTitle;
	global $protection;
	global $wgReaderHomePage;
	
	// Als de pagina een redirect is, zoek dan de pagina op waarnaar deze doorlinkt. 
	if ( $title->isRedirect() ) {
    $redirectArticle = new Article( $title, 0 );
    //var_dump($redirectArticle->getContent()); exit;
		$newtitle = $redirectArticle->getRedirectTarget();
	} else {
		$newtitle = $title;
	}
	// Redirects to some special pages are not permitted
	if ( $newtitle instanceOf Title && $newtitle->isValidRedirectTarget() ) 
	{
		$sv = FlaggedRevision::determineStable($newtitle, FR_MASTER, array(), 'quality');

		// Geen stable versie gevonden. Dan gewoon doorgaan. Die pagina's worden 
		// tegengehouden door isAllowedPage
		if ($sv === null)
			return true;

		// Overrule de default versie door een specifiek acticle te laden met 
		// het id van de goedgekeurde versie.
		$article = new Article( $title, $sv->getRevision()->getId());
		return true;
	}
	return true;
}*/
//$wgHooks['ArticleFromTitle'][] = 'efHooks_ArticleFromTitle';

/* *********************************************************************** **
  Prefix-search proberen te beinvloeden, om zo de suggesties-lijst van het 
  zoekboxje aan te passen. Kan gebruikt worden om zinloze dingen te schrappen
  (bijv, redirects naar pagina's die al in de lijst staan).
** *********************************************************************** */
function efHooks_PrefixSearchBackend( $ns, $search, $limit, &$results ) {
	$results = array('Eftepedia');
	return false;
}

//$wgHooks['PrefixSearchBackend'][] = 'efHooks_PrefixSearchBackend';

/* *********************************************************************** **
Random page alleen goedgekeurde pagina's
** *********************************************************************** */
/* class EftepediaRandomPage extends RandomPage{
	public function __construct( $name = 'Randompage' ){
		parent::__construct( $name );
	}
	
	protected function getQueryInfo( $randstr ) {
		$redirect = $this->isRedirect() ? 1 : 0;

		$params = array(
			'tables' => array( 'page', 'flaggedpages' ),
			'fields' => array( 'page_title', 'page_namespace' ),
			'conds' => array_merge( array(
				'page_namespace' => $this->getNamespaces(),
				'page_is_redirect' => $redirect,
				// 'page_random >= ' . $randstr,
				'page_id = fp_page_id',
				'fp_quality >= 1'
			), $this->extra ),
			'options' => array(
				'ORDER BY' => 'rand()',
				//'ORDER BY' => 'page_random',
				//'USE INDEX' => 'page_random',
				'LIMIT' => 1,
			),
			'join_conds' => array()
		);
		
		return $params;
	}
}*/

function efHooks_SpecialPage_initList( &$aSpecialPages ) {
	//$aSpecialPages['Randompage'] = 'EftepediaRandomPage';
  //$aSpecialPages['Allpages'] = 'EftepediaSpecialAllPages';

	return true;
}

//$wgHooks['SpecialPage_initList'][] = 'efHooks_SpecialPage_initList';

/* *********************************************************************** **
Soort van eigen caching ging fout na upgrade naar 1.20. Nu maar even uitgeschakeld.
** *********************************************************************** */

function efHooks_ArticleViewHeader(&$article, &$outputDone, &$useParserCache)
{
/*	global $wgUseFileCache;
	if ($wgUseFileCache === false)
		return true;
		
	$called = true;
	
	if ( $article->isFileCacheable() ) {
		$cache = new HTMLFileCache( $article->getTitle() );
		if ( $cache->isFileCacheGood(  ) ) {
			wfDebug( "Article::tryFileCache(): about to load file\n" );
			$cache->loadFromFileCache();
			return true;
		} else {
			wfDebug( "Article::tryFileCache(): starting buffer\n" );
			ob_start( array( &$cache, 'saveToFileCache' ) );
		}
	} else {
		wfDebug( "Article::tryFileCache(): not cacheable\n" );
	}

	$useParserCache = true;
	return true;*/

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
