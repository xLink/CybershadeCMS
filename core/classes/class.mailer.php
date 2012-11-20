<?php


class Mailer extends coreObj{


    protected $contentType  = 'text/plain',
              $charSet      = 'iso-8859-1',
              $from         = 'noreply@cybershade.org',
              $fromName     = 'No Reply',
              $to           = array(),
              $cc           = array(),
              $bcc          = array(),
              $replyTo      = array(),
              $subject      = '',
              $body         = '',
              $wordWrap     = false;


    public function __construct(){

    }

    public function setHtml(){
        $this->contentType = "text/html";
        return true;
    }


}

?>