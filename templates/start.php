<div id='raumbelegungen_edit'>
    <form action="" name="auswahlZeitRaum" id='raumbelegungen_formular'>
        <label for="gebaude">Geb&auml;ude:</label>
        <select id="gebaude" name="gebaude" onchange="">
            <? foreach ($gebaude as $gebaud) : ?>
                <option
                    value="<?= $gebaud["resource_id"] ?>" <? if ($gebaud["resource_id"] == $auswahl["gebaeude"]) : ?> selected <? endif ?> > <?= $gebaud["Name"] ?> </option>
            <? endforeach ?>
        </select>
        <label for="von">Von:</label>
        <input class="user_form" type="text" name="von" value="<?= $auswahl["von"] ?>" required id='raumbelegungen_von'>
        <label for="bis">Bis:</label>
        <input class="user_form" type="text" name="bis" value="<?= $auswahl["bis"] ?>" required id='raumbelegungen_bis'>
        <button id="raumbelegungen_anzeigen">Raumbelegungen anzeigen</button>
    </form>
    <div id="raumbelegungen_buttons">
        <button id="raumbelegungen_buttonwochenende">n&auml;chstes Wochenende eintragen</button>
        <button id="raumbelegungen_buttonhilfe">Hilfe</button>
    </div>
</div>
<div id="raumbelegungen_ergebnis">
    <div id="raumbelegungen_print">
        <a href="print?gebaude=<?= $auswahl["gebaeude"] ?>&von=<?= $auswahl["von"] ?>&bis=<?= $auswahl["bis"] ?>" target="_blank"><img alt="Print" src="<?=Assets::image_path("icons/16/blue/print.png")?>" />&nbsp;Druckansicht</a>
        <span>&nbsp;</span>
        <a href="pdf?gebaude=<?= $auswahl["gebaeude"] ?>&von=<?= $auswahl["von"] ?>&bis=<?= $auswahl["bis"] ?>" target="_blank"><img alt="Print" src="<?=Assets::image_path("icons/16/blue/print.png")?>" />&nbsp;Als&nbsp;PDF</a>
    </div>

    <table border="0" class="default" id="termine">
        <thead>
            <tr>
                <th>Von</th>
                <th>Bis</th>
                <th>Raum</th>
                <th>Vorlesungstitel</th>
                <th>Dozent</th>
            </tr>
        </thead>
        <tbody>
        <? IF (!empty($termine[0])) : ?>
            <? foreach ($termine as $termin) : ?>
                <tr id="termin">
                    <td><?= $termin["von"] ?>
                    <td> <?= $termin["bis"] ?>
                    <td> <?= $termin["raum"] ?>
                    <td> <?= $termin["titel"] ?>
                    <td> <? IF (is_array($termin["Dozent"][0])): ?><? foreach ($termin["Dozent"] as $dozenten) : ?><?= $dozenten["title_front"] ?> <?= $dozenten["vorname"] ?> <?= $dozenten["nachname"] ?>
                            <br/><? endforeach ?><? endif ?>


                </tr>
            <? endforeach ?>
        <? ENDIF ?>
        </tbody>
    </table>
</div>

<div id="raumbelegungen_hilfe" title="FAQ f&uuml;r die Raumbelegungen">
    <div class="raumbelegungen_hilfe_frage">
        Warum gibt es Termine bis zum 01.01.70?
    </div>
    <div class="raumbelegungen_hilfe_antwort">
        Das sind Termine die kein festes Ende haben. Festgelegt wurde das Datum 1970 mit der Vermutung das bis dahin
        keiner mehr einen PC braucht. Also nehmen Sie dieses Datum nicht ernst.
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
        Machen Sie einen Screenshot und schicken Sie diesen an support-neo@hfwu.de mit einer kleinen Beschreibung was
        Sie gemacht haben.
    </div>
</div>