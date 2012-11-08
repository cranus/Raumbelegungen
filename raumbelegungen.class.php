<?php
/**
 * User: Johannes Stichler
 * Date: 02.09.11
 * Time: 09:25
 * Was macht die Klasse:
 */

require_once($GLOBALS['STUDIP_BASE_PATH']."/lib/classes/exportdocument/ExportPDF.class.php");
require_once(dirname(__FILE__)."/classes/dates.php");
require_once(dirname(__FILE__)."/classes/raumbelegung.php");

class raumbelegungen extends StudIPPlugin implements SystemPlugin {
    private $raumbelegung;
    /*
     * Allgemeine Funktionen
     *
     */
    public function __construct() {
        parent::__construct();
        $this->raumbelegung = new raumbelegung();

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
        //JavaScript Datei hinzufühen
        PageLayout::addScript($this->getPluginURL() . '/assets/js/raumbelegungen.js');
        //CSS Datei hinzufügen
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/css/raumbelegung.css');
        //Sammeln der Informationen die Auf der Startseite benoetigt werden
        $template = $this->getTemplate("start.php");
        
       $start = $this->raumbelegung->getStart();
       $template->set_attribute('gebaude', $start["gebaeude"]);

        $template->set_attribute('auswahl', $start["auswahl"]);

        if(isset($_REQUEST["gebaude"])) {
            //echo $auswahlgeb;
            if(isset($_REQUEST["von"]) AND $_REQUEST["von"] != "00.00.0000") $von = $this->raumbelegung->dateToUnix($_GET["von"]);
            else $von = time();
            if(isset($_REQUEST["bis"]) AND $_REQUEST["bis"] != "00.00.0000") {
                if($_REQUEST["bis"] != $_REQUEST["von"]) $bis = $this->raumbelegung->dateToUnix($_REQUEST["bis"],"24");
                else $bis = $this->raumbelegung->dateToUnix($_REQUEST["bis"],"22"); // Wenn gleicher Tag dann nur bis 22Uhr
            }
            else $bis = $von;
            $termine = $this->getTermine($von, $bis, $start["auswahl"]["gebaeude"]);
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

    

    private function getTermine($von, $bis, $gebaude) {
  
        print_r(array(
            "von" => $von,
            "bis" => $bis,
            
        ));
	$termine = array();
	for($i = 0; $von < $bis AND $i < 3;$i++) {                                        //date("j",$von_durch) < date("j",$bis)
            $dates = new dates($von, $gebaude);

            $tempdates = $dates->getDates();
            //$dates->debug($dates->getDates());
            $tempdates[]["lauf"] = $i;
            $termine = array_merge($termine, $tempdates);
            //ein Tag vor
            $von_durch = $von_durch + $dates->daysec + 1;
        }

        $vorlesungen = array();
        $i = 0;
        $wochentage = $this->getwochentage($von, $bis);

        foreach($termine as $termin){

            if(!empty($termin["begin"])) {
                $vorlesungen[$i]["von"] = "(".$this->raumbelegung->deutscherTag(date('D', $termin["begin"])).") ".date("d.m.y - H:i",$termin["begin"]);
                $vorlesungen[$i]["bis"] = "(".$this->raumbelegung->deutscherTag(date('D', $termin["end"])).") ".date("d.m.y - H:i",$termin["end"]);
                $vorlesungen[$i]["raum"] = $termin["Raum"];
                if($termin["titel"] == "") {
                    $vlinfos = raumbelegung::getTitel($termin["id"]);
                    $vorlesungen[$i]["titel"] = $vlinfos["Name"];
                    $vorlesungen[$i]["Dozent"] = raumbelegung::getDozent($vlinfos["Seminar_id"]);
                }
                else {
                    if($termin["begin"] != $termin["repeat_end"] AND !empty($termin["repeat_end"])) $vorlesungen[$i]["titel"] = $termin["titel"]." (Regelm&auml;ssiger Termin bis zum "."(".$this->raumbelegung->deutscherTag(date('D', $termin["repeat_end"])).") ".date("d.m.y",$termin["repeat_end"]).")";
                    else $vorlesungen[$i]["titel"] = $termin["titel"];
                    $vorlesungen[$i]["Dozent"] = " &times; ";
                }
                $i++;
            }

	    }
        return $vorlesungen;
    }

    public function print_action() {
        $gebaude = $this->raumbelegung->getGebaeude();
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
            $ausgabe = "<h2>Raumbuchungen von ".$_GET["von"]." bis ".$_GET["bis"]." f&uumlr das Geb&auml;ude: ".$this->getGebaeude($auswahlgeb)."</h2>";
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
            $doc->addContent("Raumbuchungen von ".$_GET["von"]." bis ".$_GET["bis"]." fï¿½r das Gebï¿½ude: ".$this->getGebaeude($auswahlgeb));
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
            $doc->dispatch($this->getGebaeude($auswahlgeb)."-".$von."-".$bis);
        }
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
                    

                    if($i==8) $i = 0;
                    if($i==$datum) {
                            return true;
                    }
            }
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

   

}

