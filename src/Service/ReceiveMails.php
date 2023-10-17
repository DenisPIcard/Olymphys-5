<?php
namespace App\Service;




use DateTime;

class ReceiveMails
{

    /**
     * @throws \Exception
     */
    public function receiveMail()//doc : https://www.geeksforgeeks.org/how-to-get-emails-using-php-and-imap/ et https://stackoverflow.com/questions/9654453/fatal-error-call-to-undefined-function-imap-open-in-php
    {
        $host = '{mail.olymphys.fr:993/imap/ssl}INBOX';
        $user = 'info@olymphys.fr';
        $password = 'lqH6Q582me';

        $conn=imap_open($host, $user, $password);
        $mails = imap_search($conn, 'SUBJECT "Mail delivery failed: returning message to sender"');
        $failedMail=[];
        $i=0;
        foreach ($mails as $email_number) {

            /* Retrieve specific email information*/
            $headers = imap_fetch_overview($conn, $email_number, 0);
            $message = imap_fetchbody($conn, $email_number, '1');
            $subMessage = substr($message, 0, 150);
            $finalMessage = trim(quoted_printable_decode($subMessage));
            $texte1=explode('<',$message);


            $dateMail= new DateTime($headers[0]->date);
            $date=new DateTime('now');
            if ($dateMail->format('d-m-Y H:i') == $date->format('d-m-Y H:i')){
                if(array_key_exists(1,$texte1)) {
                    $failedMail[$i] = explode('>', explode('<', $message)[1])[0];
                    $i++;
                }
            }

        }
        imap_close($conn);
        return $failedMail;

    }


}