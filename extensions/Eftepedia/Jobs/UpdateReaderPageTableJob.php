<?php
class UpdateReaderPageTableJob extends Job {
  public function __construct( $title, $params ) {
    parent::__construct( 'updateReaderPageTable', $title, $params );
  }
 
  /**
   * Execute the job
   *
   * @return bool
   */
  public function run() {
		$dbr = wfGetDB( DB_SLAVE );
    
    //$title = $this->title->getDBkey();
    $title = $this->params['title'];
    $dbr->query("insert into jos_test(title) values ($title)");

    //$fullPageList = $dbr->query($this->getFullPageListSQL($namespace));
    //while ($row = $dbr->fetchObject($fullPageList))
    //{
    //  $lines[] = $row;
    //}
    
    /* // Load data from $this->params and $this->title
    $article = new Article( $this->title, 0 );
    $limit = $this->params['limit'];
    $cascade = $this->params['cascade'];

    // Perform your updates
    if ( $article ) {
            Threads::synchroniseArticleData( $article, $limit, $cascade );
    }*/

    return true;
  }
}

$wgJobClasses['updateReaderPageTable'] = 'UpdateReaderPageTableJob';

function efCheckGoedkeuren()
{
  if ( array_key_exists('rs', $_REQUEST) && 
       $REQUEST['rs'] = 'RevisionReview::AjaxReview' &&
       array_key_exists('rsargs', $_REQUEST) &&
       is_array($_REQUEST['rsargs']))
  {
    foreach($_REQUEST['rsargs'] as $arg)
    {
      $arga = explode('|', $arg, 2);
      if ($arga[0] == 'target')
      {
        $target = $arga[1];
        var_dump($target);
        //$title = Title::newFromText($target);
        //var_dump($title);
        $job = new UpdateReaderPageTableJob(null, array('title'=>$target));
        $job->insert();
        var_dump('job gemaakt');
      }
    }
  }
}

//efCheckGoedkeuren();