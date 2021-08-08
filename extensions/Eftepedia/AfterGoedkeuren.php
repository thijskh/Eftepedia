<?php

class EftepediaAPIHooks
{
  public static function onAPIAfterExecute( &$module ) 
  {
    var_dump($_REQUEST);
    var_dump($module);
    return true;
  }
}

$wgHooks['APIAfterExecute'][] = 'EftepediaAPIHooks::onAPIAfterExecute';