<?php
// Eigengeschreven extensions voor beide versies van Eftepedia



/* *********************************************************************
 Controlefuncties om te kijken of een versie is goedgekeurd. Wordt door
 beide versies van Eftepedia op verschillende manieren gebruiken, nl
 om links weg te halen (reader), of om links een andere kleur te geven (admin)
********************************************************************* */
function checkQuality($sv)
{
	if ($sv === null)
		return false;
	// Geeft terug of een stable version ook een qualiteitsversie is.
	// De flag quality is niet waterdicht. Deze is een momentopname en afhankelijk 
	// van de instellingen op het moment van flaggen. Het veranderen van de definitie van 'quality'
	// heeft geen invloed meer op de flag van eerdere revisies. 
	// Daarom zelf de tags bekijken en de waarden controleren: 
	// $sv->getQuality() < FR_QUALITY
	
	$tags = $sv->getTags();
	
	// Is het een quality versie? Deze check zou het mooist zijn, maar in de gaten houden of hij altijd werkt.
	return FlaggedRevs::isQuality($tags);
	
	// Als FlaggedRevs::isQuality niet werkt, werkt dit waarschijnlijk ook niet:
	//return $sv->getQuality() >= FR_QUALITY;
	
	// Elke flag controleren. Ook leuk.
	//return $tags['accuracy'] >= 1 && $tags['depth'] >= 1 && $tags['style'] >= 1;
}

function isValidGeelVersion(&$title)
{
	global $wgTitle;
	global $wgReaderHomePage;
	
	// Als de pagina een redirect is, zoek dan de pagina op waarnaar deze doorlinkt. 
	if ( $title->isRedirect() ) {
		$article = new Article( $title, 0 );
		$newtitle = $article->getRedirectTarget();
  } else {
		$newtitle = $title;
	}

	// Redirects to some special pages are not permitted
	if ( $newtitle instanceOf Title && $newtitle->isValidRedirectTarget() ) 
	{
    if (in_array($newtitle->getNamespace(), array(NS_MAIN, NS_PROJECT, NS_CATEGORY)))
    {
      return true;
    }
		/*$sv = FlaggedRevision::determineStable($newtitle, FR_MASTER, array(), 'quality');

		if (!checkQuality($sv) /*$sv->getQuality() < FR_QUALITY)*\)
		{
			// Versie is nog helemaal niet goedgekeurd, of heeft nog geen quality versie.
			return $title->getText() === $wgReaderHomePage;
		}
		return true;*/
	}
	
	return false;
}



