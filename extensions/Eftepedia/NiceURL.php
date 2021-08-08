<?php

class EftepediaLocalUrl
{
  public static function onGetLocalURL( $title, $url, $query ) {
    /** @var $title Title */  
    $namespace = $title->getNamespace();
    
    
    if ($namespace <> 0)
    {
      $url = '/' . lcfirst($title->getNsText()) . '/' . wfUrlencode($title->getDBkey());
      if ($query)
      {
        $url .= '?' . $query;
      }
      /*if (GEELVERSION == VERSION_ADMIN) {
        $url = '/cms' . $url;
      }*/
    }
    return true;  
  }

}
$wgHooks['GetLocalURL'][] = 'EftepediaLocalUrl::onGetLocalURL';

?>
