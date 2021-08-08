<?php

class EftepediaDefaultMinorEdit
{
  public static function onAlternateEdit( $editpage ) {
    global $wgRequest;
    if (!$wgRequest->wasPosted())
    {
      $wgRequest->setVal('minor', 'on');
    }
    return true;
  }
}

$wgHooks['AlternateEdit'][] = 'EftepediaDefaultMinorEdit::onAlternateEdit';