<?php
/**
 * User: Johannes Stichler
 * Date: 16.11.11
 * Time: 11:22
 * 
 */

 function getNextWeekEnd() {
        $tagsec = 86400;
        $tag = date("w");
        $tagBisWe = 6 - $tag;
        $secBisWe = $tagBisWe*$tagsec; //Sekunden bis Wochenende
        $return["wevon"] = date("d.m.Y",time()+$secBisWe);
        $return["webis"] = date("d.m.Y",time()+$secBisWe+$tagsec);
        return $return;
    }

$hash = $_REQUEST["id"];

if($hash == "we") {
    $we = getNextWeekEnd();
    $json_array = array(
        "von" => $we["wevon"],
        "bis" => $we["webis"],
        );
}

echo json_encode($json_array);