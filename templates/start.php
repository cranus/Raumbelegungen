<div id='raumbelegungen_edit'>
<form action="" name="auswahlZeitRaum" id='raumbelegungen_formular'>

<br>
<label for="gebaude">Bitte w&auml;hlen Sie das Geb&auml;ude aus</label><br />
 <select id="gebaude" name="gebaude" onchange="">

    <? foreach ($gebaude as $gebaud) : ?>
     <option value="<?= $gebaud["resource_id"] ?>" <? if($gebaud["resource_id"] == $auswahl["gebaude"]) : ?> selected <? endif ?> > <?= $gebaud["Name"] ?> </option>
    <? endforeach ?>
 </select>

<br>
Bitte w&auml;hlen Sie den Zeit Raum aus
<br>
Von:<br>
<input class="user_form" type="text" name="von" value="<?= $auswahl["von"] ?>" required id='raumbelegungen_von'><br>

Bis: <br>
<input class="user_form" type="text" name="bis" value="<?= $auswahl["bis"] ?>" required id='raumbelegungen_bis'> <br>
<br>

<button id="raumbelegungen_anzeigen">Raumbelegungen anzeigen</button></p>
</form>
<strong>
    <div id="raumbelegungen_buttons">
        <button id="raumbelegungen_buttonwochenende">n&auml;chstes Wochenende eintragen</button>
        <button id="raumbelegungen_buttonhilfe">Hilfe</button>
    </div>
</div>
    <div id="raumbelegungen_ergebnis">
    <img alt="Print" src="/assets/images/icons/16/black/print.png">
        <a href="print?gebaude=<?= $auswahl["gebaude"] ?>&von=<?= $auswahl["von"] ?>&bis=<?= $auswahl["bis"] ?>" target="_blank">Druckansicht</a>
    <img alt="Print" src="/assets/images/icons/16/black/print.png">
        <a href="pdf?gebaude=<?= $auswahl["gebaude"] ?>&von=<?= $auswahl["von"] ?>&bis=<?= $auswahl["bis"] ?>" target="_blank">Als PDF</a>

 <table border="0" id="termine">
  <tr> <td> Von <td> Bis <td> Raum <td> Vorlesungstitel <td> Dozent </tr>
  <tbody>
  <? foreach ($termine as $termin) : ?>
    <tr id="termin">
        <td><?=  $termin["von"] ?><td> <?=  $termin["bis"] ?> <td> <?=  $termin["raum"] ?> <td> <?=  $termin["titel"] ?> <td> <? foreach ($termin["Dozent"]  as $dozenten) : ?><?= $dozenten["title_front"]  ?> <?= $dozenten["vorname"]  ?> <?= $dozenten["nachname"]  ?> <br /><? endforeach ?>


     </tr>
 <? endforeach ?>
 </table>
</div>

<div id="raumbelegungen_hilfe" title="FAQ f&uuml;r die Raumbelegungen">
    <div class="raumbelegungen_hilfe_frage">
        Warum gibt es Termine bis zum 01.01.70?
    </div>
    <div class="raumbelegungen_hilfe_antwort">
        Das sind Termine die kein festes Ende haben. Festgelegt wurde das Datum 1970 mit der Vermutung das bis dahin keiner mehr einen PC braucht. Also nehmen Sie dieses Datum nicht ernst.
    </div>
    <div class="raumbelegungen_hilfe_frage">
            Warum stehen Termine im Ergebnis, die an einem anderen Tag anf&auml;ngen?
    </div>
    <div class="raumbelegungen_hilfe_antwort">
        Weil diese Termine in dem von Ihnen ausgw&auml;hlten Zeitraum schneidet. Sehen k&ouml;nnen Sie es am Enddatum.
    </div>
    <div class="raumbelegungen_hilfe_frage">
            Ich habe einen Fehler gefunden. Was muss ich tun?
    </div>
    <div class="raumbelegungen_hilfe_antwort">
        Machen Sie einen Screenshot und schicken Sie diesen an johannes.stichler@hfwu.de mit einer kleinen Beschreibung was Sie gemacht haben.
    </div>
</div>