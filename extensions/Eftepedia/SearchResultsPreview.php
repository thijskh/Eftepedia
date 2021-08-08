<?php

class EftepediaSearchResultsPreview
{
	public static function onShowSearchHitTitle( &$title, &$text, $result, $terms, $page ) 
	{
    //if ($result->getTitle()->isRedirect())
    //  $xtext = 'DINGES';
		return true;
	}

	public static function onSpecialSearchResults( $term, &$titleMatches, &$textMatches ) 
	{
    // Eerst een lijst samenstellen van alle resultaten.
    // Als een resultaat een redirect is, dan wordt er een Title van de doelpagina aan gekoppeld.
    $results = array();
    $i = 0;
    foreach($textMatches->mResultSet as $match)
    {
      $result = new SphinxMWSearchResult($match, null);
      $title = $result->getTitle();
      if ($title->isRedirect())
      {
        $target = Title::newFromRedirectRecurse('#REDIRECT[[' . $title->getText() . ']]');
        if ($target !== null)
        {
          $match->target = $target;
          $results[] = $match;
        }
      }
      else
      {
        $results[] = $match;
      }
    }
    
    // Daarna kijken of het doel van die redirect al in de zoekresultaten staat.
    // Zo ja, dan de redirect niet opnemen in de zoekresultaten.
    $filteredResults = array();
    $compare = $results;
    foreach ($results as $checkTitle)
    {
      if (isset($checkTitle->target))
      {
        $exists = false;
        foreach ($compare as $compareTitle)
        {
          $title1 = $checkTitle->target;
          if ($title1->getDBKey() == $compareTitle->page_title &&
              $title1->getNamespace() == $compareTitle->page_namespace)
          {
            $exists = true;
            break;
          }
        }
        if ($exists == false)
        {
          $filteredResults[] = $checkTitle;
        }
      }
      else
      {
        $filteredResults[] = $checkTitle;
      }
    }
    // Gefilterde zoekresultaten terugzetten.
    $textMatches->mResultSet = $filteredResults;
    

		return true;
	}
}

if ( isJos()) 
{

  $wgHooks['ShowSearchHitTitle'][] = 'EftepediaSearchResultsPreview::onShowSearchHitTitle';
  $wgHooks['SpecialSearchResults'][] = 'EftepediaSearchResultsPreview::onSpecialSearchResults';
}
