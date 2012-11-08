<?php
/**
 * Description of raumbelegungen
 *
 * @author johannesstichler
 */
class raumbelegung {
    private $days;
    
    /*
     * Sammelt alle Informationen für die erste Seite
     * @parm nichts
     * @return array $retun mit Gebaude
     */
    public function getStart() {
        $return = array();
        $return["gebaeude"] = $this->getGebaeude();
        //Ueberpruefen ob eine Gebaeude ID uebergeben wurde
        if(isset($_REQUEST['gebaude'])) {
            $return["auswahl"]["gebaeude"] = $_REQUEST['gebaude'];
        }
        //Ueberpruefen ob ein Von Tag uebergeben wurde
        if(!isset($_REQUEST['von'])) { //Falls nicht dann Aktuelle Tag
            $return["auswahl"]["von"] = date("d.m.Y");
        } else { //Falls ja dann diesen Wert eintragen
            $return["auswahl"]["von"] = $_REQUEST['von'];
        }
        //Ueberpruefen ob ein bis Tag uebergeben wurde
        if(!isset($_REQUEST['bis'])) { //Falls nicht dann Aktuelle Tag
            $return["auswahl"]["bis"] = date("d.m.Y");
        } else {//Falls ja dann diesen Wert eintragen
            $return["auswahl"]["bis"] = $_REQUEST['bis'];
        }
        
        return $return;
        
    }
    
    /*
     * Gibt alle Gebaude aus
     * @parm string $id uber die ID kann gefiltert werden bzw. ein Name zu einem Gebaeude ausgegeben werden
     */
    public function getGebaeude($id = "") {
        $db = DBManager::get();
        if($id == "") {
        $gebaude = $db->query("SELECT Name, resource_id
                               FROM `resources_objects`
                               WHERE `category_id` LIKE '3cbcc99c39476b8e2c8eef5381687461' ORDER BY Name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
         $gebaude = $db->query("SELECT Name
                               FROM resources_objects
                               WHERE resource_id = '".$id."' ORDER BY Name ASC")->fetchAll(PDO::FETCH_ASSOC);
         $gebaude = $gebaude[0]["Name"];
        }
        return $gebaude;
    }
    
    /*
     * Wandelt ein Datum in ein Unixtimestamp um. Benoetigt die Deutsche Form des Datums
     * @parm string $datum  Datum im Form von tt.mm.YYYY
     * @parm string $stunde Falls der Timestamp nicht in der Stunde 1 anfangen soll sondern in einer anderen Stunde kann das hier manipuliert werden
     */
    public function dateToUnix($datum, $stunde="1"){
        $tag = $datum[0].$datum[1];
        $monat = $datum[3].$datum[4];
        $jahr = $datum[6].$datum[7].$datum[8].$datum[9];
        return mktime($stunde,"0","0",$monat,$tag,$jahr);
    }

    /*
     * Gibt den Titel sowie den Vor- und Nachnamen der Dozenten aus
     * @parm string $id ID des Seminars
     * @return  array $dozenten mit title_front, vorname, nachname
     */
    public function getDozent($id){
        if(isset($id)) {
            $db = DBManager::get();
            $sql = "SELECT user_info.title_front ,auth_user_md5.vorname, auth_user_md5.nachname
                    FROM seminar_user
                    INNER JOIN auth_user_md5 ON auth_user_md5.user_id = seminar_user.user_id
                    INNER JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                    WHERE seminar_user.status = 'dozent' AND seminar_user.Seminar_id = '".$id."'";
            $dozenten = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            return $dozenten;
        }

    }

    /*
     * Gibt den Titel eines Seminars aus anhand des Termin_IDs
     * @parm string $id ID 
     * @return string   $vlinfos Name der Vorlesung 
     */
    public function getTitel($id) {
       $db = DBManager::get();
       $sql = "SELECT seminare.Name, seminare.Seminar_id
               FROM seminare
               INNER JOIN termine ON seminare.Seminar_id = termine.range_id
               WHERE termine.termin_id = '".$id."'";

         $vlinfos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
       return $vlinfos[0];
    }

    
    /*
     * Uebersetzt Englische Tage in Deutsche Abkuerzungen
     * @perm string $engName    Englische Abkuerzung
     * @return string $return   Deutsche Abkuerzung
     */
    public function deutscherTag($engName) {
        switch($engName) {
            case 'Mon': return "Mo" ;break;
            case 'Tue': return "Di" ;break;
            case 'Wed': return "Mi" ;break;
            case 'Thu': return "Do" ;break;
            case 'Fri': return "Fr" ;break;
            case 'Sat': return "Sa" ;break;
            case 'Sun': return "So" ;break;
            default: return $engName;
        }
    }
}

?>
