<?php
session_start();
include 'maildetails.php';
include 'db.php';

$mail->setFrom('thyssenkrupp@tkep.com', 'tkei');
$mail->addReplyTo(Email, 'Information');
$mail->isHTML(true);   
// $mail->SMTPDebug = 4;                               // Enable verbose debug output

$expdate = strtotime("+7 day");
$expdate = date("Y-m-d", $expdate);

$_SESSION['department'] = $_POST['dept'];
$ctr = 0;
$position = str_replace(' ', '%20', $_POST['position']);
$positionorg = $_POST['position'];
$cursor = $db->session->findOne(array("sid" => $_COOKIE['sid']));
$arr = [];
if($cursor)
{
    $count=0;
    foreach ($db->listCollections() as $collectionInfo) {
       // var_dump($collectionInfo['name']);
        if($collectionInfo['name']=='rounds')
        {
            $count=1;
        }
       
    }
    if($count==1) //if round collection is present
    {
                $i=0;
                $result = $db->rounds->find(array("prf"=>$_POST['prf'],"rg"=>$cursor["rg"],"dept"=>$_POST['dept'],"pos"=>$_POST['pos']),array('sort' => array('_id' => -1)));
            $c=0;
            foreach($result as $d)
            {
                $arr[$i]=$d;
                $i=$i+1;
            }
            if(count($arr)==0)
            {//bad case when collection is present but no data
              
                $ctr=0;
                $instanceid=$instanceId=(string)sprintf("%03s",1);
                foreach($_POST['emails'] as $d)
                {
                    $mail->addAddress($d);
                    $token=sha1($d);
                    $url='http://'.$_SERVER['SERVER_NAME'].'/hrms/applicationblank.php?token='.$token.'&position='.$position;

                    $mail->Subject = "Invitation to interview with thyssenkrupp for the ". $positionorg." position";
                    $mail->Body    =   nl2br('Dear Candidate,

                    Further to our discussion for the profile of '. $positionorg.' in department - '.$_POST['dept'].' You are required to provide your basic
                    details by accessing the below link so that your application could be processed further.
                   
                    To access the link, please click <a href='.$url.'>here</a>
                   
                    Thank you for your interest in working with us.
                   
                    In-case of any query, feel free to reach out to recruitment@tkeap.com
                   
                    tkEI Recruiting Team.');
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    if(!$mail->send()) 
                    {
                        $ctr = 1;
                    }
                    else
                    {
                        $db->tokens->insertOne(array("email"=>$d,"token"=>$token,"prf"=>$_POST['prf'],"dept"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position'],"rg"=>$cursor["rg"],"rid"=>"00","expiry"=>$expdate,"iid"=>$instanceid));
                    }

                    $mail->ClearAddresses();
                }
                if($ctr==0)
                {
                    //"poszone"=>$_POST['poszone']
                    $r = $db->prfs->updateOne(array("prf"=>$_POST['prf'],"department"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position']),array('$set'=>array("progress"=>"initiated")));
                    
                    $db->rounds->insertOne(array(
                        "status"=>"bstart",
                        "prf"=>$_POST['prf'],
                        "dept"=>$_POST['dept'],
                        "pos"=>$_POST['pos'],
                        "poszone"=>$_POST['poszone'],
                        "position"=>$_POST['position'],
                        "rg"=>$cursor["rg"],
                        "rid"=>"00",
                        "expiry"=>$expdate,
                        "iid"=>$instanceid,
                        "members"=>$_POST['emails'],
                        "selected"=>array(),
                        "rejected"=>array(),
                        "onhold"=>array())
                    );
                    $fp = fopen('prflogs.txt', 'a');
                    $d = date("Y/m/d");
                    $m = $cursor['mail'];
                    $prf = $_POST['prf'];
                    $dept = $_POST['dept'];
                    fwrite($fp, "\n".$d."\t".$prf."\t".$m."\t".$dept);
                    fclose($fp);  
                    echo "sent";

                    $date = date_default_timezone_set('Asia/Kolkata');
           
                    $today = date("Y-m-d H-i-s");

                     //Current user

                     $newData=array('$set' => array("status" => "initiated","init_time"=>$today));

                     $db->generalized->updateOne(array("prf"=>$_POST['prf']),$newData);

                }
                else
                {
                    echo "notsent";
                }
            }
            else
            {   //when there is collection + some data
                $instanceid=$arr[0]['iid'];
                $instanceid=$instanceid+1;
                $instanceid=(string)sprintf("%03s",$instanceid);
                $ctr=0;
                foreach($_POST['emails'] as $d)
                {
                    $mail->addAddress($d);
                    $token=sha1($d);
                    $url='http://'.$_SERVER['SERVER_NAME'].'/hrms/applicationblank.php?token='.$token.'&position='.$position;

                    $mail->Subject = "Update on your application at thyssenkrupp for ". $position." position";
                    $mail->Body    = nl2br('Dear Candidate,

                    Further to our discussion for the profile of '. $positionorg.' in department - '.$_POST['dept'].' You are required to provide your basic
                    details by accessing the below link so that your application could be processed further.
                   
                    To access the link, please click here <a href='.$url.'>here</a>
                   
                    Thank you for your interest in working with us.
                   
                    In-case of any query, feel free to reach out to recruitment@tkeap.com
                   
                    tkEI Recruiting Team.');
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    if(!$mail->send()) 
                    {
                        $ctr = 1;
                    }
                    else
                    {
                        $db->tokens->insertOne(array("email"=>$d,"token"=>$token,"prf"=>$_POST['prf'],"dept"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position'],"rg"=>$cursor["rg"],"rid"=>"00","expiry"=>$expdate,"iid"=>$instanceid));
                        $date = date_default_timezone_set('Asia/Kolkata');
           
                        $today = date("Y-m-d H-i-s");

                        //Current user

                        $newData=array('$set' => array("status" => "initiated","init_time"=>$today));

                        $db->generalized->updateOne(array("prf"=>$_POST['prf']),$newData);


                    }
                    
                    $mail->ClearAddresses();
                }
                if($ctr==0)
                {
                    $r = $db->prfs->updateOne(array("prf"=>$_POST['prf'],"department"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position']),array('$set'=>array("progress"=>"initiated")));

                    $db->rounds->insertOne(array(
                        "status"=>"bstart",
                        "prf"=>$_POST['prf'],
                        "dept"=>$_POST['dept'],
                        "pos"=>$_POST['pos'],
                        "poszone"=>$_POST['poszone'],
                        "position"=>$_POST['position'],
                        "rg"=>$cursor["rg"],
                        "rid"=>"00",
                        "expiry"=>$expdate,
                        "iid"=>$instanceid,
                        "members"=>$_POST['emails'],
                        "selected"=>array(),
                        "rejected"=>array(),
                        "onhold"=>array())
                    );    
                    $fp = fopen('prflogs.txt', 'a');
                    $d = date("Y/m/d");
                    $m = $cursor['mail'];
                    $prf = $_POST['prf'];
                    $dept = $_POST['dept'];
                    fwrite($fp, "\n".$d."\t".$prf."\t".$m."\t".$dept);
                    fclose($fp);  
                    echo "sent";

                    $date = date_default_timezone_set('Asia/Kolkata');
           
                    $today = date("Y-m-d H-i-s");

                    //Current user

                    $newData=array('$set' => array("status" => "initiated","init_time"=>$today));

                    $db->generalized->updateOne(array("prf"=>$_POST['prf']),$newData);


                }
                else
                {
                    echo "notsent";
                }
               
            
            }
      
    }
    else   
    {//when there is no collection
            $ctr=0;
            $instanceid=$instanceId=(string)sprintf("%03s",1);
                foreach($_POST['emails'] as $d)
                {
                    $mail->addAddress($d);
                    $token=sha1($d);
                    $url='http://'.$_SERVER['SERVER_NAME'].'/hrms/applicationblank.php?token='.$token.'&position='.$position;

                    $mail->Subject = "Update on your application at thyssenkrupp for ". $position." position";
                    $mail->Body    = nl2br('Dear Candidate,

                    Further to our discussion for the profile of '. $positionorg.' in department - '.$_POST['dept']. ' You are required to provide your basic
                    details by accessing the below link so that your application could be processed further.
                   
                    To access the link, please click <a href='.$url.'>here</a>
                   
                    Thank you for your interest in working with us.
                   
                    In-case of any query, feel free to reach out to recruitment@tkeap.com
                   
                    tkEI Recruiting Team.');
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    if(!$mail->send()) 
                    {
                        $ctr = 1;
                    }
                    else
                    {
                      
                        $db->tokens->insertOne(array("email"=>$d,"token"=>$token,"prf"=>$_POST['prf'],"dept"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position'],"rg"=>$cursor["rg"],"rid"=>"00","expiry"=>$expdate,"iid"=>$instanceid));
                        
                        
                    }

                    $mail->ClearAddresses();
                }
                if($ctr==0)
                {
                    $r = $db->prfs->updateOne(array("prf"=>$_POST['prf'],"department"=>$_POST['dept'],"pos"=>$_POST['pos'],"position"=>$_POST['position']),array('$set'=>array("progress"=>"initiated")));
                   
                    $db->rounds->insertOne(
                        array(
                            "status"=>"bstart",
                            "prf"=>$_POST['prf'],
                            "dept"=>$_POST['dept'],
                            "pos"=>$_POST['pos'],
                            "poszone"=>$_POST['poszone'],
                            "position"=>$_POST['position'],
                            "rg"=>$cursor["rg"],
                            "rid"=>"00",
                            "expiry"=>$expdate,
                            "iid"=>$instanceid,
                            "members"=>$_POST['emails'],
                            "selected"=>array(),
                            "rejected"=>array(),
                            "onhold"=>array()));
                    
                    $fp = fopen('prflogs.txt', 'a');
                    $d = date("Y/m/d");
                    $m = $cursor['mail'];
                    $prf = $_POST['prf'];
                    $dept = $_POST['dept'];
                    fwrite($fp, "\n".$d."\t".$prf."\t".$m."\t".$dept);
                    fclose($fp);  
                    echo "sent";

                    $date = date_default_timezone_set('Asia/Kolkata');
           
                    $today = date("Y-m-d H-i-s");

                    //Current user

                    $newData=array('$set' => array("status" => "initiated","init_time"=>$today));

                    $db->generalized->updateOne(array("prf"=>$_POST['prf']),$newData);


                }
                else
                {
                    echo "notsent";
                }
       

    }

}
else
{
    header("refresh:0;url=notfound.html");    
}

?>