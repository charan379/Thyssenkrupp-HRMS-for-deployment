<?php
error_reporting(0);
include 'db.php';

$cursor = $db->session->findOne(array("sid" => $_COOKIE['sid']));

if($cursor)
{
    $digit13 = preg_split('/[-]/', $_POST['id']);
    $cursor2 =  $db->rounds->findOne(array("prf"=>$digit13[0],"pos"=>$digit13[1],"iid"=>$digit13[2],"rid"=>"00"));
    $memcount = count(iterator_to_array($cursor2['members']));
    $selectednames=$cursor2['selectedremove'];

    $date1 = $cursor2["expiry"];
    $date2 = date("Y-m-d");
    
    $date1Timestamp = strtotime($date1);
    $date2Timestamp = strtotime($date2);
    
    $difference = $date1Timestamp - $date2Timestamp;
    
    $days = floor($difference / (60*60*24) );

    if($days <= 0)
    {
        $nodays = "expired";
    }
    else
    {
        $nodays = $days;
    }
 
    $i=0;
    foreach($selectednames as $d)
    {
        $getselectednames =  $db->tokens->findOne(array("prf"=>$digit13[0],"pos"=>$digit13[1],"email"=>$d));
        $arr[$i]=array($getselectednames['full_name'],$d);
        $i++;
    }
    $variable = array($arr,$memcount,$cursor2['members'],$nodays);
    echo json_encode($variable);
}
else
{
    header("refresh:0;url=notfound.html");
}

?>