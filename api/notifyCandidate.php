<?php
 include "db.php";
$cursor = $db->session->findOne(array("sid" => $_COOKIE['sid']));
if($cursor)
{
   
    include 'maildetails.php';
    $mail->setFrom('thyssenkrupp@tkep.com', 'tkei');
    $mail->addReplyTo(Email, 'Information');
    $mail->isHTML(true);
    $i=0;
    $prf13 = explode("*",$_POST['prf13']);
    echo json_encode($prf13);
    $cursor = $db->session->findOne(array("sid" => $_COOKIE['sid']));
    if($cursor)
    {
        $criteria = array("prf"=>$prf13[0],"rid"=>$prf13[1],"iid"=>$prf13[2],"intvmail"=>$prf13[3],"invname"=>$prf13[6],"accepted"=>$prf13[7],"ilocation"=>$prf13[8],"iperson"=>$prf13[9]);
        $result = $db->interviews->findOne($criteria);
        $res = $db->interviews->updateOne($criteria,array('$set'=>array("sent"=>"done","accepted"=>"yes")));




        $date = date_default_timezone_set('Asia/Kolkata');
           
        $today = date("Y-m-d H-i-s");

                        //Current user

        $newData=array('$set' => array("status" => "ongoing","accepted_time"=>$today));

        $db->generalized->updateOne(array("prf"=>$prf13[0]),$newData);

        foreach($result['members'] as $d)
        {
            $name = $db->tokens->findOne(array("email"=>$d));
            $name1 = $name['full_name'];
            $_SESSION['posi'] = $name['position'];
            $mail->addAddress($d);
            $mail->Subject = "Invitation to interview with thyssenkrupp for the ". $name['position']." position";
            $mail->Body    = nl2br('Dear '.$name1.',

            Thank you for the application for the role of '.$name['position'].'. Further to our discussion you are
            required to meet us as per the below details to have face to face interview round.


            Date : '.$result['dates'][$i].'

            Timings : '.$result['times'][$i].'

            Address : '.$prf13[8].'

            Contact Person : '.$prf13[9].'

            In-case of any query, feel free to reach out to recruitment@tkeap.com

            tkEI Recruiting Team.');
            $mail->AltBody = 'You are assigned for an interview. Please check your dashboard for further progress.';

            $mail->send(); 
            $mail->ClearAddresses();
            $i++;
        }

        $r = $db->prfs->findOne(array("prf"=>$prf13[0]));
        $mail->addAddress($result['intvmail']);
        $mail->Subject = 'Interview schedule for '.$r['department'].' - '.$r['position'].'';
        $mail->Body    = nl2br('Dear '.$result['invname'].',

        Thank you for confirmation, please find below the details for the interview for the post of '.$r['position'].'.

        Address : '.$prf13[8].'

        You will find date & time of each candidate on your dashboard. Please be available at the stipulated time.

        In-case of any query, feel free to reach out to recruitment@tkeap.com

        tkEI Recruiting Team.');
        $mail->AltBody = 'Thank You For Confirmation.';

        $mail->send();
        echo "done";

    }
}
else
{
    header("refresh:0;url=notfound.html");    
}


?>