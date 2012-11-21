<?php

/**
 * Todo: Return $this on functions so I can link methods together
 * =>   $mail = $objMailer->setHtml()
 *                        ->useSMTP()
 *                        ->addAddress('test@test.com')
 *                        ->setSubject('testing email')
 *                        ->send();
 */
class Mailer extends coreObj{

    protected   $contentType  = 'text/plain',
                $charSet      = 'iso-8859-1',
                $from         = 'noreply@cybershade.org',
                $fromName     = 'NoReply',
                $to           = array(),
                $cc           = array(),
                $bcc          = array(),
                $replyTo      = array(),
                $subject      = '',
                $body         = '',
                $wordWrap     = false,
                $mailType     = 'mail',
                $attachments  = array();


    public function __construct(){

    }

    public function setHtml(){
        $this->setVar('contentType', 'text/html');
        return $this;
    }

    public function useSMTP(){
        $this->setVar('mailType', 'smtp');
        return $this;
    }

    public function addAddress( $address, $name = '' ){
        $currentCount = count($this->to);

        // Stripslashes to prevent CRLF injection
        $this->to[$currentCount][0] = trim( stripslashes( $address ) );
        $this->to[$currentCount][1] = stripslashes($name);

        return $this;
    }

    public function addCC( $address, $name = '' ){
        $currentCount = count($this->cc);

        // Stripslashes to prevent CRLF injection
        $this->cc[$currentCount][0] = trim( stripslashes( $address ) );
        $this->cc[$currentCount][1] = stripslashes( $name );

        return $this;
    }

    public function setSubject( $subject = '' ){
        $this->setVar('subject', $subject);
        return $this;
    }

    public function addReplyTo( $address, $name = '' ){
        $currentCount = count($this->replyTo);

        // Stripslashes to prevent CRLF injection
        $this->replyTo[$currentCount][0] = trim( stripslashes( $address ) );
        $this->replyTo[$currentCount][1] = stripslashes( $name );

        return $this;
    }

    public function send(){
        if( count( $this->to ) < 1 ){
            trigger_error('You must provide at least one recipient email address');
            return false;
        }

        $header   = $this->createHeader();
        $body     = $this->createBody();
        $mailType = $this->getVar('mailType');
        $mail     = $this->sendMail( $header, $body, ( !$mailType ? 'mail' : $mailType ) );

        return $mail;
    }

    protected function sendMail( $headers, $body, $type = 'mail' ){
        if( is_empty( $mail ) ){
            trigger_error('You must specify a valid send mode');
            return false;
        }

        // Switch on the mail type
        switch( strtolower($mail) ){
            case 'smtp':
                // Do later
                break;

            // Default mail type
            case 'mail':
            default:
                $to       = $this->getVar('to');
                if( $to && count( $to ) < 1 ){
                    trigger_error('You must specify valid addresses to send the mail to');
                    return false;
                }

                $subject  = $this->getVar('subject');
                $from     = $this->getVar('from');
                $fromName = $this->getVar('fromName');

                $sendTo = $to[0][0];

                /**
                 * Todo:
                 * Fix this loop and make it generate a correct $sendTo string
                 */
                foreach($to as $name => $addrTo){
                    $sendTo .= sprintf(',%s', $addrTo);
                }
                break;
        }
    }
}

?>