<?php
/**
 * Eftepedia 004 skin
 *
 * @file
 * @ingroup Skins
 * @version 2.0.0
 * @author Jos Visser (www.eftepedia.nl / goleztrol@hotmail.com)
 * @license Afblijve met je fikke
 */
 
// initialize
if( !defined( 'MEDIAWIKI' ) ){
	die( -1 );
}
 
define('GEELCSSVERSION', '031');

class EftepediaHackOutput
{
  
  function __construct($output)
  {
    $this->output = $output;
  }
  
  function checkValidLink($link)
  { 
    return substr($link, 0, 3) === '<a ';
  }
  
  function getCategoryLinks()
  {
    $sourceLinks = $this->output->getCategoryLinks();
    $filteredLinks = array();
    foreach ($sourceLinks as $linkType => $links)
    {
      $links = array_filter($links, 
          function($link){ 
            return substr($link, 0, 3) === '<a ';
          });
      if (count($links) > 0)
      {
        $filteredLinks[$linkType] = $links;
      }
    }
    return $filteredLinks;
  }
}
// inherit main code from SkinTemplate, set the CSS and template filter
class SkinEftepedia004 extends SkinTemplate {
  
  private $hackOutput = null;
  
  function initPage( OutputPage $out ) {
    parent::initPage( $out );
    $this->skinname  = 'eftepedia004';
    $this->stylename = 'eftepedia004';
    $this->template  = 'Eftepedia004Template';

    $out->addModules( 'skins.eftepedia004.js' );
  }
  
  function printSource()
  {
    $url = htmlspecialchars( wfExpandIRI( $this->getTitle()->getCanonicalURL() ) );
    return $this->msg( 'retrievedfrom', '<a href="' . $url . '">' . $url . '</a>' )->text();
  }
  
  function setupSkinUserCss( OutputPage $out ) {
    parent::setupSkinUserCss( $out );
    // Append to the default screen common & print styles...
    $out->addStyle( 'eftepedia004/main.css', 'screen' );
  }
  
  // Dit is een beetje een vieze hack. Het samenstellen van de links gebeurt diep
  // in de krochten van Mediawiki. Daardoor komen de category links er al als 
  // voorbewerkte HTML uit. Deze hack verandert dat, door tijdelijk de 'output'
  // te vervangen door een dummy object (hackOutput). Dat object roept de 
  // oorspronkelijke 'output' aan om de ruwe category links te verkrijgen.
  // Deze worden daarna gefilterd en teruggegeven. Mediawiki rendert dan de HTML
  // op basis van de gefilterde categorieën, en geeft deze op de gebruikelijke 
  // manier terug.
  function getOutput() // override
  {
    if ($this->hackOutput)
      return $this->hackOutput;
    return parent::getOutput();
  }
  
  function getCategoryLinks() // override
  {
    $this->hackOutput = new EftepediaHackOutput($this->getOutput());
    $links = parent::getCategoryLinks();
    $this->hackOutput = null;
    return $links;
  }
}

class Eftepedia004Template 
  extends BaseTemplate {
  /**
   * Template filter callback for this skin.
   * Takes an associative array of data set from a SkinTemplate-based
   * class, and a wrapper for MediaWiki's localization database, and
   * outputs a formatted page.
   */
   
  function editLink()
  {
    global $geelVersion;
    if ($geelVersion !== VERSION_ADMIN)
      return '';
    $title = $this->getSkin()->getTitle();
    
    if (!in_array($title->getNameSpace(), array(NS_MAIN, NS_TALK, NS_CATEGORY, NS_CATEGORY_TALK)))
      return '';
    
    $url = htmlspecialchars( wfExpandIRI( $title->getCanonicalURL() ) );
    return '<span class="mw-editsection"><a href="' . $url . '?action=edit">Bewerken</a></span>';
  }

  /**
   * Classes samenstellen om pagina's specifiek te kunnen stijlen
   */
  private function getHtmlClasses($title)
  {
    global $geelVersion;
    
    $htmlClasses = '';
    
    // Classes voor paginatype en speciale pagina's.
    if ($title->isSpecialPage()){
      $specialPageName = $title->getDBkey();
      if ($specialPageName === 'Categorieën') 
        $specialPageName = 'Categories';
      
      $htmlClasses .= ' special-' . $specialPageName;
    }
    
    // Elke contentpagina, alles behalve specials.
    if ($title->canExist()){
      $htmlClasses .= ' contentpage';
      
      $titlelink = wfUrlencode( $title->getPrefixedDBkey() );
    }
    
    // Namespace van de pagina
    $nameSpace = $title->getNameSpace();
    if ($nameSpace > -1)
    {
      // Strip de laatste bit, wat schijnbaar de 'Overleg'-bit is.
      $nameSpace = ($nameSpace & 0xFFFFFEE);
    }
    $nameSpaces = array(
      NS_SPECIAL => 'special', 
      NS_MAIN => 'main', 
      NS_USER => 'user',
      NS_PROJECT => 'project',
      NS_FILE => 'file',
      NS_MEDIAWIKI => 'mediawiki',
      NS_TEMPLATE => 'template',
      NS_HELP => 'help',
      NS_CATEGORY => 'category');
      
    if (array_key_exists($nameSpace, $nameSpaces) === true)
    {
      $htmlClasses .= ' ns-' . $nameSpaces[$nameSpace];
    }
    else
    {
      $htmlClasses .= ' ns-' . $nameSpace;
    }
     
    // Overlegpagina's over voornoemde namespace
    if ($title->isTalkPage()){
      $htmlClasses .= ' talk';
    }
 
    // Classes voor admin of reader. 
    if ($geelVersion === VERSION_ADMIN)
    {
      $htmlClasses .= ' admin';
    }
    if ($geelVersion === VERSION_READER)
    {
      $htmlClasses .= ' reader';
    }
    
    return $htmlClasses;
  }
  
  public function execute() {
    global $wgRequest;
    global $wgArticlePath;
    global $wgArticle;
    global $geelVersion;
    
    $isAdmin = $geelVersion === VERSION_ADMIN;
    
    $baseUrl = str_replace('/$1', '', $wgArticlePath);
    
    $title = $this->getSkin()->getTitle();
    
    $skin = $this->data['skin'];

    $titlelink = '';
    // suppress warnings to prevent notices about missing indexes in $this->data
    wfSuppressWarnings();
    
    //Compile the page title:
    $pageTitle[] = $this->data['title'];
    $pageTitle[] = $this->data['sitename'];
    $pageTitle[] = 'alles over de Efteling';
    $pageTitle = implode(' - ', $pageTitle);
    
    // Classes samenstellen om specifieke styling te kunnen doen.
    $htmlClasses = $this->getHtmlClasses($title);
   
    // Elke contentpagina, alles behalve specials.
    if ($title->canExist()){
      $titlelink = wfUrlencode( $title->getPrefixedDBkey() );
    }
   
?><!DOCTYPE html>
<html lang="nl" dir="ltr" class="client-nojs<?=$htmlClasses?>">
<head>
<!--Sjömelen-->
<title><?=$pageTitle?>
</title>
<?php /*<meta name="description" content="<?=htmlspecialchars($pageTitle)?>"/>*/ ?>
<meta name="keywords" content="Efteling,<?=htmlspecialchars($this->data['title'])?>">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="search" type="application/opensearchdescription+xml" href="/opensearch.php" title="Eftepedia">
<?php	
    if ($isAdmin) {
?>
<link href="/css/css/css.php?source=eftepedia&amp;v=<?=GEELCSSVERSION?>" rel="stylesheet" type="text/css">
<?php
    } else {
?>  
<link href="/css/css/cache/eftepedia.css?v=<?=GEELCSSVERSION?>" rel="stylesheet" type="text/css">
<?php
    }
  
    $canonicalUrl = wfExpandIRI($title->getCanonicalURL());
?>
<link href="<?=htmlspecialchars($canonicalUrl)?>" rel="canonical">
<?= $this->getSkin()->getOutput()->getRlClient()->getHeadHtml() ?>
</head>
<body class="<?=$bodyClasses?>">
<div id="globalWrapper">
<header id="topbar">
  <div class="topbar">
    <div id="logo"><a href="<?=$baseUrl?>" title="Eftepedia hoofdpagina" rel="home" class="u-home">Eftepedia hoofdpagina</a></div>
    <?php if($isAdmin): ?>
    <div class="menubutton" onclick='$("html").toggleClass("menu");'></div>
    <?php endif; ?>
    <a class="skiplink" href="#firstHeading">Naar de inhoud</a>
    <div id="sitename"><a href="<?=$baseUrl?>" title="Eftepedia hoofdpagina" rel="home" class="u-home"><span>Eftepedia</span></a></div>
    <div id="searchbar">
      <div id="p-search" class="portlet">
        <div id="searchBody" class="pBody">
          <form action="<?php $this->text('searchaction') ?>" id="searchform">
            <input type="hidden" name="title" value="Speciaal:Zoeken">
            <input id="searchInput" title="Zoeken in Eftepedia" accesskey="f" type="search" name="search" autocomplete="off" placeholder="Eftepedia doorzoeken">
            <input type="submit" name="go" class="searchButton" id="searchGoButton" value="OK">&nbsp;
            <input type="submit" name="fulltext" class="searchButton" id="mw-searchButton" value="Zoeken">
          </form>
        </div>
      </div>
    </div>
  
    <nav id="mainmenu" class="mainmenu">
      <div id="navbar" class="navbar">
        <?php if ($isAdmin){?>
        <span id="watishier"></span>
        <?php }?>
        <a href="<?=$this->makeUrl('Categorieën')?>" title="Secties en categorieën">Categorieën</a>
        <a href="<?=$this->makeUrl('Speciaal:AllePaginas')?>" class="secondary" title="Alle pagina's op alfabetische volgorde">Index</a>
        <a href="<?=$this->makeUrl('Speciaal:Willekeurig')?>" title="Spring naar een willekeurige pagina">Willekeurige pagina</a>
        <a href="<?=$this->makeUrl('Eftepedia')?>" class="secondary nav-homepage" title="Over Eftepedia">Eftepedia</a>
        <?php /*if($titlelink) { ?>
        <a href="<?=$baseUrl?>/Speciaal:VerwijzingenNaarHier/<?=$titlelink?>">Verwijzingen naar deze pagina</a>
        <?php }*/ ?>
      </div>
    </nav>
  </div>
</header>
  
<?php	
  if ($isAdmin) {
?>
  <nav class="portals">
<?php
  
    $this->renderPortals( $this->data['sidebar'] );
    $this->cactions(); 
    $this->pactions();  
?>
  </nav>
<?php
  }
?>  
<article id="column-content" itemscope itemtype="http://schema.org/Article">
  <header>
    <h1 id="firstHeading" tabindex="-1" class="firstHeading"><span itemprop="name"><?php $this->html('title'); ?></span><?=$this->editLink()?></h1>
    <div id="siteSub" itemprop="author"><?php $this->msg('tagline') ?></div>
  </header>
    <div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php if ($isAdmin) { $this->html( 'subtitle' ); } ?></div>
<?php if($this->data['undelete']) { ?>
		<div id="contentSub2"><?php $this->html('undelete') ?></div>
<?php } ?><?php if($this->data['newtalk'] ) { ?>
		<div class="usermessage"><?php $this->html('newtalk')  ?></div>
<?php } ?><?php /*if($this->data['showjumplinks']) { ?>
		<div id="jump-to-nav" class="mw-jump"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a><?php $this->msg( 'comma-separator' ) ?><a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div>
<?php }*/ ?>  
  
	<div id="bodyContent">
		<div role="main" lang="nl" dir="ltr" class="mainContent" itemprop="articleBody">

<?php $this->html('bodytext') ?>

		</div>
<?php
  if( strpos($this->data['catlinks'], 'catlinks-allhidden') === false ) { 
?>
    <nav id="categories" class="categories">
      <?= $this->html('catlinks'); ?>
    </nav>
<?php 
  }
?>
	</div>
  <?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
  <div class="visualClear"></div>
</article>
<footer class="pagefooter vcard h-card">
  &copy; <?=date('Y')?> 
  <a href="<?=$baseUrl?>/Eftepedia" title="Eftepedia" class="fn org p-org">Eftepedia</a> &bull;
  <a href="<?=$baseUrl?>/Colofon" title="Colofon" rel="author" class="p-author">Colofon</a> &bull;
  <a href="mailto:feedback@HAALDITWEG.eftepedia.nl" title="E-mail ons" class="email u-email">E-mail</a> &bull;
  <script>var el = document.querySelector('a.email.u-email'); var m = el.href.replace('HAALDITWEG.', ''); el.href = m;</script>
  <a href="https://twitter.com/eftepedia" target="_blank" title="Eftepedia op Twitter" class="u-twitter">Twitter</a> &bull;
  <span class="note p-note">Eftepedia is niet gelieerd aan de Efteling</span>
</footer>

<?php $this->printTrail();?>

  </div>
  </body>
</html>
<?php
  }
  
	function renderPortals( $sidebar ) {
		//if ( !isset( $sidebar['SEARCH'] ) ) $sidebar['SEARCH'] = true;
		if ( !isset( $sidebar['TOOLBOX'] ) ) $sidebar['TOOLBOX'] = true;
		//if ( !isset( $sidebar['LANGUAGES'] ) ) $sidebar['LANGUAGES'] = true;

		foreach( $sidebar as $boxName => $content ) {
			if ( $content === false )
				continue;

			if ( $boxName == 'SEARCH' ) {
				//$this->searchBox();
			} elseif ( $boxName == 'TOOLBOX' ) {
				$this->toolbox();
			} elseif ( $boxName == 'LANGUAGES' ) {
				//$this->languageBox();
			} else {
				$this->customBox( $boxName, $content );
			}
		}
	}

	function toolbox() {
?>
	<div class="portlet" id="p-tb">
		<h5><?php $this->msg('toolbox') ?></h5>
		<div class="pBody">
			<ul>
<?php
		foreach ( $this->getToolbox() as $key => $tbitem ) { ?>
				<?php echo $this->makeListItem($key, $tbitem); ?>

<?php
		}
		wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
		wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this, true ) );
?>
			</ul>
		</div>
	</div>
<?php
	}  
  
	function cactions() {
?>
	<div id="p-cactions" class="portlet">
		<h5><?php $this->msg('views') ?></h5>
		<div class="pBody">
			<ul><?php
				foreach($this->data['content_actions'] as $key => $tab) {
					echo '
				' . $this->makeListItem( $key, $tab );
				} ?>

			</ul>
		</div>
	</div>
<?php
	}
  
  function pactions()
  {
?>
  <div class="portlet" id="p-personal">
		<h5><?php $this->msg('personaltools') ?></h5>
		<div class="pBody">
			<ul<?php $this->html('userlangattributes') ?>>
<?php		foreach($this->getPersonalTools() as $key => $item) { ?>
				<?php echo $this->makeListItem($key, $item); ?>

<?php		} ?>
			</ul>
		</div>
	</div>
<?php
  }
  
	function customBox( $bar, $cont ) {
		$portletAttribs = array( 'class' => 'generated-sidebar portlet', 'id' => Sanitizer::escapeId( "p-$bar" ) );
		$tooltip = Linker::titleAttrib( "p-$bar" );
		if ( $tooltip !== false ) {
			$portletAttribs['title'] = $tooltip;
		}
		echo '	' . Html::openElement( 'div', $portletAttribs );
?>

		<h5><?php $msg = wfMessage( $bar ); echo htmlspecialchars( $msg->exists() ? $msg->text() : $bar ); ?></h5>
		<div class='pBody'>
<?php   if ( is_array( $cont ) ) { ?>
			<ul>
<?php 			foreach($cont as $key => $val) { ?>
				<?php echo $this->makeListItem($key, $val); ?>

<?php			} ?>
			</ul>
<?php   } else {
			# allow raw HTML block to be defined by extensions
			print $cont;
		}
?>
		</div>
	</div>
<?php
	}
  
  function makeUrl($key)
  {
    $title = Title::newFromText($key);
    return $title->getLocalURL();
  }
}

