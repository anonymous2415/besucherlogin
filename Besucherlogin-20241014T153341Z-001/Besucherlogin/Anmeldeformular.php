<form method="post" action="">
    <label class="labels" for="firmenname">Firmenname:</label>
    <input type="text" id="firmenname" name="firmenname" class="felder">

    <label class="labels" for="vorname">Vorname:</label>
    <input type="text" id="vorname" name="vorname" required class="felder">

    <label class="labels" for="nachname">Nachname:</label>
    <input type="text" id="nachname" name="nachname" required class="felder">

    <label class="labels" for="ansprechpartner">Ansprechpartner bei der abat AG:</label>
    <input type="text" id="ansprechpartner" name="ansprechpartner" required class="felder">
<br><br>
    <div>
        <input type="checkbox" id="datenschutz" name="datenschutz" required style="transform: scale(1.5);">
        <label for="datenschutz">Hiermit akzeptiere ich die <a href="http://link_zum_datenschutz" target="_blank">
            Datenschutzerklärung</a> und stimme der Verarbeitung meiner personenbezogenen Daten zu.</label>
    </div>
            <br>
    <input type="submit" name="submit_visitor" value="Eintragen" class="form-submit-button">
</form>