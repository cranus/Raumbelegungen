<?php
/**
 * Created by JetBrains PhpStorm.
 * User: johannesstichler
 * Date: 21.02.12
 * Time: 09:09
 * To change this template use File | Settings | File Templates.
 */
class dates
{
    private $building, $day;
    private $dates = array();
    public $daysec = 86400;


    public function __construct($day, $building) {
      $this->day = $day;
      $this->building = $building;
      $this->getDatesFromDb();


    }

    private function getDatesFromDb() {
        $db = DBManager::get();
        $dayend = $this->day + $this->daysec;
        $sql = "SELECT resources_objects.name AS 'Raum',
                resources_assign.assign_id,
		resources_assign.begin,
                resources_assign.end,
                resources_assign.repeat_interval,
                resources_assign.repeat_day_of_week,
                resources_assign.repeat_week_of_month,
                resources_assign.repeat_day_of_month,
                resources_assign.repeat_month_of_year,
                resources_assign.assign_user_id AS 'id',
                resources_assign.user_free_name AS 'titel',
                resources_assign.repeat_end,
                resources_assign.repeat_day_of_week
                FROM `resources_assign`
                INNER JOIN resources_objects ON resources_assign.resource_id = resources_objects.resource_id
                WHERE ((resources_assign.begin > ? AND resources_assign.begin < ?) OR (resources_assign.begin < ? AND resources_assign.repeat_end > ?))
                AND resources_assign.resource_id in (SELECT resource_id FROM `resources_objects` WHERE parent_id = ? ORDER BY Name)
                ORDER BY end ASC";

        $sqldebug = "SELECT resources_objects.name AS 'Raum',
                resources_assign.begin,
                resources_assign.end,
                resources_assign.repeat_interval,
                resources_assign.repeat_day_of_week,
                resources_assign.repeat_week_of_month,
                resources_assign.repeat_day_of_month,
                resources_assign.repeat_month_of_year,
                resources_assign.assign_user_id AS 'id',
                resources_assign.user_free_name AS 'titel',
                resources_assign.repeat_end,
                resources_assign.repeat_day_of_week
                FROM `resources_assign`
                INNER JOIN resources_objects ON resources_assign.resource_id = resources_objects.resource_id
                WHERE ((resources_assign.begin > $this->day AND resources_assign.begin < $dayend) OR (resources_assign.begin < $this->day AND resources_assign.repeat_end < $this->day))
                AND resources_assign.resource_id in (SELECT resource_id FROM `resources_objects` WHERE parent_id = '".$this->building."' ORDER BY Name)
                ORDER BY end ASC";
        //$this->debug($sqldebug);

        $db = DBManager::get()->prepare($sql);
        $db->execute(array($this->day, $dayend,$this->day, $this->day, $this->building)); //$dayend
	$result = $db->fetchAll();
        if(empty($result)) return false;
        foreach($result as $date) {
           if(!empty($date))
           {    if((date("w",$date['begin']) == date("w",$this->day))
                OR ($date['repeat_interval'] == "1" AND $date['repeat_day_of_week'] == "0")
                OR ($date['repeat_interval'] == "0" AND $date['repeat_day_of_week'] == "0" AND $date['repeat_week_of_month'] == "0" AND $date['repeat_day_of_month'] == "0"  AND $date['repeat_day_of_month'] == "0")

                 )
                {
                    If(date("j",$date['begin']) != date("j",$this->day))
                    {
                        $tempdate = mktime(date("H",$date['begin']), date("i",$date['begin']), date("s",$date['begin']), date("m",$this->day), date("j",$this->day), date("Y",$this->day));
                        $date['begin'] = $tempdate;
                    }

                    If(date("j",$date['end']) != date("j",$this->day))
                    {
                        $tempdate = mktime(date("H",$date['end']), date("i",$date['end']), date("s",$date['end']), date("m",$this->day), date("j",$this->day), date("Y",$this->day));
                        $date['end'] = $tempdate;
                    }

                    //$this->debug($date);
		    if($date['repeat_day_of_week'] != date("N",$this->day)) 
                    $this->dates[] = $date;
               }
           }
        }

        //$this->debug($this->dates);

    }

    public function debug($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    public function getDates()
    {
        return $this->dates;
    }


}
