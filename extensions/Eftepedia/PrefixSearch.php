<?php
/** 
 * Doel: Een 'betere' auto-suggest, die doorverwijzingen naar andere pagina's niet laat zien als het doel 
 * van de doorverwijzing zelf ook al in de lijst staat.
 */
$wgExtensionFunctions[] = 'efEftepediaPrefixSearchSetup';

function efEftepediaPrefixSearchSetup() {
  global $wgHooks;
	$wgHooks['PrefixSearchBackend'][] = 'EftepediaPrefixSearch::prefixSearchBackend';
}
  
class EftepediaPrefixSearch
{
	/**
	 * Override the default OpenSearch backend...
	 * @param string $search term
	 * @param int $limit max number of items to return
	 * @param array &$results out param -- list of title strings
	 */
	static function prefixSearchBackend( $ns, $search, $limit, &$results ) {
    $results = self::prefixSearch( $ns, $search, $limit );
    return false;
	}
	
	static function prefixSearch( $namespaces, $search, $limit ) {
		// Basis van deze implementatie is gejat uit de TitleKey extensie.
		$ns = array_shift( $namespaces ); // support only one namespace
		if( in_array( NS_MAIN, $namespaces ) )
			$ns = NS_MAIN; // if searching on many always default to main 
		
		$key = TitleKey::normalize( $search );
		
		// Selecteer de pagina's om voor te stellen. Extra: Join de redirect target erbij, en
		// selecteer wat meer items (20 ipv 10), om de lijst uit te kunnen dunnen.
		$dbr = wfGetDB( DB_REPLICA );

		$result = $dbr->select(
			array( 'titlekey', 'page', 'redirect' ),
			array( 'page_namespace', 'page_title', 'rd_namespace', 'rd_title' ),
			array(
				'tk_page=page_id',
				'tk_namespace' => $ns,
				'tk_key ' . $dbr->buildLike( $dbr->anyString(), $key, $dbr->anyString() ), // Full like %X%
			),
			__METHOD__,
			array(
				'ORDER BY' => array(
          // Als title matcht met X% heeft deze voorrang op %X%
          'case when tk_key ' . $dbr->buildLike($key, $dbr->anyString()) . ' then 0 else 1 end', 
          // Als title matcht met % X % heeft deze voorrang op %X%
          'case when CONCAT(\' \', tk_key, \' \') ' . $dbr->buildLike($dbr->anyString(), ' ', $key, ' ', $dbr->anyString()) . ' then 0 else 1 end', 
          // Als title matcht met % X% heeft deze voorrang op %X%
          'case when CONCAT(\' \', tk_key) ' . $dbr->buildLike($dbr->anyString(), ' ', $key, $dbr->anyString()) . ' then 0 else 1 end', 
          'tk_key'), 
				'LIMIT' => $limit * 2 ),
			array('redirect' => array('LEFT JOIN', 'rd_from=page_id'))
			);
		
		// Reformat useful data for future printing by JSON engine
		$rows = array();
		foreach( $result as $row ) {
			$rows[] = $row;
		}
		$result->free();
	
		// Alle items controleren.
		$srchres = array();
		foreach($rows as $row)
		{
			$found = false;
			if ($row->rd_title != "")
			{
				// Het item is een doorverwijzing. Loop nog een keer door de array en kijk
				// of het doel van de redirect ook in de lijst zit.
				foreach($rows as $check)
				{
					if ($row->rd_title == $check->page_title && $row->rd_namespace == $check->page_namespace)
					{
						$found = true;
						break;
					}
				}
			}
			if (!$found)
			{
				// Pagina is geen redirect of redirect target staat niet bij de suggesties: 
				// Toevoegen aan uiteindelijke resultaat.
				$title = Title::makeTitle( $row->page_namespace, $row->page_title );
				$srchres[] = $title->getPrefixedText();
			}
		}
		
		// Eindresultaat terugbrengen tot de gewenste lengte.
		$srchres = array_slice($srchres, 0, $limit);
		return $srchres;
	}
}
