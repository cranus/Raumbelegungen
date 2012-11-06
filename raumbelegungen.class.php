<?php
/**
 * User: Johannes Stichler
 * Date: 02.09.11
 * Time: 09:25
 * Was macht die Klasse:
 */

require_once($GLOBALS['STUDIP_BASE_PATH']."/lib/classes/exportdocument/ExportPDF.class.php");
require_once(dirname(__FILE__)."/classes/days.php");

class raumbelegungen extends StudIPPlugin implements SystemPlugin {

    /*
     * Allgemeine Funktionen
     *
     */
    public function __construct() {
        parent::__construct();

        //Navigationselement AutoNavigation 
        $navigation = new AutoNavigation($this->getDisplayName(), PluginEngine::getURL($this, array(), "show"));
        $tools = new AutoNavigation("Tools", PluginEngine::getURL($this, array(), "start"));
        //Punkt an dem das Elements eingesetzt werden soll
        Navigation::addItem('/start/Raumbelgungen/', clone $navigation);
    }
    /*
     * Standard Funktion beim Aufruf des Plugins
     */
    public function show_action() {
        PageLayout::addScript($this->getPluginURL() . '/assets/js/raumbelegungen.js');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/css/raumbelegung.css');
        $gebaude = $this->getGebaude();
        $template = $this->getTemplate("start.php");
        $template->set_attribute('gebaude', $gebaude);
        $auswahlgeb = Request::option('gebaude');
        $auswahl["gebaude"] = Request::option('gebaude');
        if(!isset($auswahl["gebaude"])) $auswahl["von"] = "a1f025b5d6f20990f6232f4a73840dd7";
        $auswahl["von"] = $_GET["von"];
        if(!isset($auswahl["von"])) $auswahl["von"] = "00.00.0000";
        $auswahl["bis"] = $_GET["bis"];
        if(!isset($auswahl["bis"])) $auswahl["bis"] = "00.00.0000";

        $wochenende = $this->getNextWeekEnd();

        $auswahl["wevon"] = $wochenende["wevon"];
        $auswahl["webis"] = $wochenende["webis"];

        $template->set_attribute('auswahl', $auswahl);

        if(isset($auswahlgeb)) {
            //echo $auswahlgeb;
            if(isset($_GET["von"]) AND $_GET["von"] != "00.00.0000") $von = $this->dateToUnix($_GET["von"]);
            else $von = time();
            if(isset($_GET["bis"]) AND $_GET["bis"] != "00.00.0000") $bis =  $this->dateToUnix($_GET["bis"],"24");
            else $bis = $von;
            $termine = $this->getTermine($von, $bis, $auswahlgeb);
            $template->set_attribute('termine', $termine);

        } else {
		$template->set_attribute('termine', "");
	}
         echo $template->render();
    }

    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        if ($layout) {
            if (method_exists($this, "getDisplayName")) {
                PageLayout::setTitle($this->getDisplayName());
            } else {
                PageLayout::setTitle(get_class($this));
            }
        }
        if (!$this->template_factory) {
            $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/templates");
        }
        $template = $this->template_factory->open($template_file_name);
        if ($layout) {
            $template->set_layout($GLOBALS['template_factory']->open($layout === "without_infobox" ? 'layouts/base_without_infobox' : 'layouts/base'));
        }
        return $template;
    }

    private function getGebaude($id = "") {
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

    private function getTermine($von, $bis, $gebaude) {
        $von_durch = $von + 1;
        $bis = $bis - 1;
        //$test = new days($von_durch, $gebaude);
        //$test->debug(array($von, $bis, $gebaude));
        //print_r(array($von, $bis, $gebaude));
	$termine = array();
	for($i = 0; $von_durch < $bis AND $i < 3;$i++) {                                        //date("j",$von_durch) < date("j",$bis)
            $dates = new days($von_durch, $gebaude);

            $tempdates = $dates->getDates();
            //$dates->debug($dates->getDates());
            $tempdates[]["lauf"] = $i;
            $termine = array_merge($termine, $tempdates);
            //ein Tag vor
	    //print_r(array($von_durch, $bis, $gebaude));
            $von_durch = $von_durch + $dates->daysec + 1;
           //$dates->debug($termine);
	    //print_r(array($von_durch, $bis, $gebaude));
        }

        $vorlesungen = array();
        $i = 0;
        $wochentage = $this->getwochentage($von, $bis);

        foreach($termine as $termin){

            if(!empty($termin["begin"])) {
                $vorlesungen[$i]["von"] = "(".$this->deutscherTag(date('D', $termin["begin"])).") ".date("d.m.y - H:i",$termin["begin"]);
                $vorlesungen[$i]["bis"] = "(".$this->deutscherTag(date('D', $termin["end"])).") ".date("d.m.y - H:i",$termin["end"]);
                $vorlesungen[$i]["raum"] = $termin["Raum"];
                if($termin["titel"] == "") {
                    $vlinfos = $this->getTitel($termin["id"]);
                    $vorlesungen[$i]["titel"] = $vlinfos["Name"];
                    $vorlesungen[$i]["Dozent"] = $this->getDozent($vlinfos["Seminar_id"]);
                }
                else {
                    if($termin["begin"] != $termin["repeat_end"] AND !empty($termin["repeat_end"])) $vorlesungen[$i]["titel"] = $termin["titel"]." (Regelm&auml;ssiger Termin bis zum "."(".$this->deutscherTag(date('D', $termin["repeat_end"])).") ".date("d.m.y",$termin["repeat_end"]).")";
                    else $vorlesungen[$i]["titel"] = $termin["titel"];
                    $vorlesungen[$i]["Dozent"] = " &times; ";
                }
                $i++;
            }

	    }
        return $vorlesungen;
    }

    private function getDatesForDay () {

    }


    public function print_action() {
        $gebaude = $this->getGebaude();
        $template = $this->getTemplate("start.php");
        $template->set_attribute('gebaude', $gebaude);
        $auswahlgeb = Request::option('gebaude');
        $auswahl["gebaude"] = Request::option('gebaude');
        $auswahl["von"] = $_GET["von"];
        if(!isset($auswahl["von"])) $auswahl["von"] = "00.00.0000";
        $auswahl["bis"] = $_GET["bis"];
         if(!isset($auswahl["bis"])) $auswahl["bis"] = "00.00.0000";
        $auswahl .= $this->getNextWeekEnd();
         $template->set_attribute('auswahl', $auswahl);

        if(isset($auswahlgeb)) {
            //echo $auswahlgeb;
            if(isset($_GET["von"]) AND $_GET["von"] != "00.00.0000") $von = $this->dateToUnix($_GET["von"]);
            else $von = time();
            if(isset($_GET["bis"]) AND $_GET["bis"] != "00.00.0000") $bis =  $this->dateToUnix($_GET["bis"],"24");
            else $bis = $von;
            $termine = $this->getTermine($von, $bis, $auswahlgeb);
            $ausgabe = "<h2>Raumbuchungen von ".$_GET["von"]." bis ".$_GET["bis"]." f&uumlr das Geb&auml;ude: ".$this->getGebaude($auswahlgeb)."</h2>";
            $ausgabe .= " <table border='1'>
                          <tr> <td> Von <td> Bis <td> Raum <td> Vorlesungstitel <td> Dozent </tr>
                          <tbody>";
            foreach($termine as $termin) {
               $ausgabe .= "<tr>
                            <td>".$termin["von"]."<td>".$termin["bis"]."<td>".$termin["raum"]."<td>".$termin["titel"]."<td>";
               foreach($termin["Dozent"] as $dozent) {
                   $ausgabe .= $dozent["title_front"]." ".$dozent["vorname"]." ".$dozent["nachname"];
               }
               $ausgabe .=  "</tr>";
            }
            $ausgabe .= " </table>";
            //<td>".$termin["dozent"]."
            echo $ausgabe;
        }
    }

    public function pdf_action(){
        $auswahlgeb = Request::option('gebaude');
        if(isset($auswahlgeb)) {
            //echo $auswahlgeb;
            $doc = new ExportPDF();
            $doc->addPage();

            if(isset($_GET["von"]) AND $_GET["von"] != "00.00.0000") $von = $this->dateToUnix($_GET["von"]);
            else $von = time();
            if(isset($_GET["bis"]) AND $_GET["bis"] != "00.00.0000") $bis =  $this->dateToUnix($_GET["bis"],"24");
            else $bis = $von;
            if($_GET["nextwe"] == "1") {
                $we = $this->getNextWeekEnd();
                $von = $this->dateToUnix($we["wevon"]);
                $bis = $this->dateToUnix($we["webis"]);
            }
            $termine = $this->getTermine($von, $bis, $auswahlgeb);
            $doc->SetFont('arial', '', 12, '', true);
            $doc->addContent("Raumbuchungen von ".$_GET["von"]." bis ".$_GET["bis"]." f�r das Geb�ude: ".$this->getGebaude($auswahlgeb));
            $doc->SetFont('arial', '', 8, '', true);
            $i = 0;
            foreach($termine as $termin) {
                if($i == 0) {
                    $doc->SetFont('arial', '', 11, '', true);
                    $doc->addContent("Von - Bis - Raum - Vorlesungstitel - Dozent");
                    }

                    $content = $termin["von"]." - ".$termin["bis"]." - ".$termin["raum"]." - ".$termin["titel"]." - ";
                    foreach($termin["Dozent"] as $dozent) {
                        $content .= $dozent["title_front"]." ".$dozent["vorname"]." ".$dozent["nachname"];
                    }
                    $doc->SetFont('arial', '', 8, '', true);
                    $doc->addContent($content);
                if($i >=35) {
                    $doc->AddPage();
                    $i = 0;
                } else {
                    $i++;
                }
            }
            $doc->dispatch($this->getGebaude($auswahlgeb)."-".$von."-".$bis);
        }
    }

    /*
     * Hilfs Klassen
     */

    private function dateToUnix($datum, $stunde="0"){
        $tag = $datum[0].$datum[1];
        $monat = $datum[3].$datum[4];
        $jahr = $datum[6].$datum[7].$datum[8].$datum[9];
        //echo "Tag ".$tag." Monat ".$monat." Jahr: ".$jahr;
        return mktime($stunde,"0","0",$monat,$tag,$jahr);
    }

    /*
     * Errechnet das n�chste Wochenende
     Ausgelagert in ajax.php Datei*/
    private function getNextWeekEnd() {
        $tagsec = 86400;
        $tag = date("w");
        $tagBisWe = 6 - $tag;
        $secBisWe = $tagBisWe*$tagsec; //Sekunden bis Wochenende
        $return["wevon"] = date("d.m.Y",time()+$secBisWe);
        $return["webis"] = date("d.m.Y",time()+$secBisWe+$tagsec);
        return $return;
    }

    private function getDozent($id){
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

    private function getTitel($id) {
       $db = DBManager::get();
       $sql = "SELECT seminare.Name, seminare.Seminar_id
               FROM seminare
               INNER JOIN termine ON seminare.Seminar_id = termine.range_id
               WHERE termine.termin_id = '".$id."'";

         $vlinfos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        //echo $vlinfos[0]["Name"];
       return $vlinfos[0];
    }

    protected function getDisplayName() {
        return "Raumbelegungen";
    }

	private function dazwischen($von, $bis, $datum) {
		$von = $von -1;
		$bis = $bis -1;
		if($von ===0) $von = 7;
		if($bis ===0) $bis = 7;

		for($i=$von; $i <= $bis; $i++) {
			//echo "I: ".$i." = ".$datum."<br>";

			if($i==8) $i = 0;
			if($i==$datum) {
				//echo "I: ".$i." = ".$datum."<br>";
				return true;
			}
		}
		//echo "nicht Gefunden";
		return false;
	}

    private function getwochentage($von, $bis) {
        $von = date("N", $von);
        $bis = date("N", $bis-1);
        $return = '';

        if($von <= $bis){
            for($i=0;$von<=$bis;$bis=$bis-1) {
                $return[] = $bis;

            }
        } elseif ($von >= $bis){
            for($i=0;$bis<=$von;$von=$von-1) {
                $return[] = $von;

            }
        }
        return $return;
    }

    private function deutscherTag($engName) {
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

