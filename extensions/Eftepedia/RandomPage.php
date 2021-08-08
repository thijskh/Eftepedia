<?php
/* *********************************************************************** **
Random page betere random volgorde (wel trager)
** *********************************************************************** */
class EftepediaRandomPage extends RandomPage{

	public function __construct( $name = 'Randompage' ){
		parent::__construct( $name );
	}
	
	protected function getQueryInfo( $randstr ) {
		$redirect = $this->isRedirect() ? 1 : 0;

		$params = array(
			'tables' => array( 'page'),
			'fields' => array( 'page_title', 'page_namespace' ),
			'conds' => array_merge( array(
				'page_namespace' => $this->getNamespaces(),
				'page_is_redirect' => $redirect,
				/*'page_random >= ' . $randstr,*/
			), $this->extra ),
			'options' => array(
				'ORDER BY' => 'rand()',
				//'ORDER BY' => 'page_random',
				/*'USE INDEX' => 'page_random',*/
				'LIMIT' => 1,
			),
			'join_conds' => array()
		);
		
		return $params;
	}
  
  public static function SpecialPage_initList( &$aSpecialPages ) 
  {
  	$aSpecialPages['Randompage'] = 'EftepediaRandomPage';
	  return true; 
  }
}

$wgHooks['SpecialPage_initList'][] = 'EftepediaRandomPage::SpecialPage_initList';