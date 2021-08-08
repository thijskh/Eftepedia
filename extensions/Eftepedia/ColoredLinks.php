<?php
/* *********************************************************************** **
Pagina's die nog niet final zijn krijgen andere attributen.
** *********************************************************************** */
define('TITLE_STATUS_UNKNOWN', 0);
define('TITLE_STATUS_INTERNAL', 1);
define('TITLE_STATUS_CATEGORY', 2);
define('TITLE_STATUS_APPROVED', 3);
define('TITLE_STATUS_OUTDATED', 4);
define('TITLE_STATUS_REJECTED', 5);
define('TITLE_STATUS_UNAPPROVED', 6);
define('TITLE_STATUS_CHANGESREJECTED', 7);

class EftepediaColoredLinks
{
  private static function getTitleStatus($title)
  {
    if ($title->getPartialURL() === '' && $title->getFragment() !== '')
    {
      return TITLE_STATUS_INTERNAL; // Interne link of anker.
    } 
    elseif ($title->mNamespace == 14 /*Category*/)
    {
      return TITLE_STATUS_CATEGORY; // Categorie-link
    }
    else
    {
      $qualityVersion = FlaggedRevision::determineStable($title, FR_MASTER, array(), 'quality');
      $qualityIsQuality = ($qualityVersion !== null && checkQuality($qualityVersion));
      if (!$qualityIsQuality ) $qualityVersion = null;


      $latestVersion = FlaggedRevision::determineStable($title, FR_MASTER, array(), 'latest');
      $latestIsQuality = ($latestVersion !== null && checkQuality($latestVersion));
      if ($qualityVersion !== null && 
        $latestVersion !== null && 
        $qualityVersion->getRevision()->getId() !== $latestVersion->getRevision()->getId())
      {
        return TITLE_STATUS_CHANGESREJECTED; // Eerder goedgekeurd, maar de laatste versie is afgekeurd.
      }
      elseif ($qualityVersion === null && $latestVersion !== null)
      {
        return TITLE_STATUS_REJECTED; // Wel gekeurd, maar nog niet goedgekeurd.
      }
      elseif ($latestVersion === null)
      {
        return TITLE_STATUS_UNAPPROVED; // Nog nooit gekeurd.
      }
      else
      {
        $article = new Article($title);
        if ($article->getRevIdFetched() !== $latestVersion->getRevision()->getId())
        {
          return TITLE_STATUS_OUTDATED; // Ongekeurde wijzigingen gedaan sinds laatste (goed)keuring.
        }
        else
        {
          if ($latestIsQuality)
          {
            return TITLE_STATUS_APPROVED; // Laatste versie is goedgekeurd.
          }
          return TITLE_STATUS_UNKNOWN; // Euh
        }
      }
    }
  }

  public static function colorLinks( $skin, $target, $options, &$text, &$attribs, &$ret )
  {
    $title = $target;
    
    global $wgUser;
    //if ($wgUser->getName() === 'Ramon')
    //	return true;

    if ($title->mNamespace !== 0)
    {
      return true; // Niet de main namespace, dus n.v.t.
    }

    $classes = explode(' ', (string)@$attribs['class']);
    $isNew = in_array('new', $classes);
    $isExternal = in_array('external', $classes);
    
    $hints = array();

    if ($isNew)
    {
      $hints[] = 'bestaat niet';
    }
    else
    {
      // Bij redirect kijken waar de pagina heen verwijst.
      if ( $title->isRedirect() ) {
        $article = new Article( $title, 0 );
        $title = $article->getRedirectTarget();
        $isRedirect = true;
        $hints[] = 'redirect';
      }
      
      switch (self::getTitleStatus($title))
      {
        case TITLE_STATUS_UNKNOWN: {
          if ($isExternal)
          {
            $hints[] = 'externe link';
          }
          break;
        }
        case TITLE_STATUS_INTERNAL: {
          $classes[] = 'internal';
          $hints[] = 'interne link';
          break;
        }
        case TITLE_STATUS_CATEGORY: {
          $classes[] = 'category';
          $hints[] = 'categorie';
          break;
        }
        case TITLE_STATUS_APPROVED: {
          $classes[] = 'quality';
          $hints[] = 'goedgekeurd';
          break;
        }
        case TITLE_STATUS_CHANGESREJECTED:
        case TITLE_STATUS_OUTDATED: {
          $classes[] = 'outdated';
          $hints[] = 'aangepast';
          break;
        }
        case TITLE_STATUS_REJECTED: {
          $classes[] = 'rejected';
          $hints[] = 'afgekeurd';
          break;
        }
        case TITLE_STATUS_UNAPPROVED: {
          $classes[] = 'unapproved';
          $hints[] = 'nog niet goedgekeurd';
          break;
        }
      }
    }
    
    $attribs['class'] = implode(' ', $classes);
    $hint = implode(', ', $hints);
    if ($hint !== '')
      $hint = "($hint)";
    $hint = trim((string)@$attribs['title'] . ' ' . $hint);
    $attribs['title'] = $hint;
    
    return true;
  }
}

$wgHooks['LinkEnd'][] = 'EftepediaColoredLinks::colorLinks';
