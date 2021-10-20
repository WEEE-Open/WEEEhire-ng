<?php
/** @var $link string */
/** @var $subject bool */
if($subject) {
	echo __('Candidatura in WEEE Open');
} else {
	echo __("Ciao!") . "\r\n";
	echo sprintf(__("Abbiamo ricevuto la tua candidatura per il team WEEE Open, questa è la pagina da cui potrai verificare lo stato della tua domanda: %s"), $link) . "\r\n";
	echo __("Se la domanda sarà approvata, riceverai un'email sempre a questo indirizzo con scritto chi contattare per passare al colloquio. Le stesse informazioni saranno visibili anche alla pagina di cui sopra.") . "\r\n";
	echo __("Buona fortuna ;)") . "\r\n\r\n";
	echo sprintf(__("Se non hai inviato tu questa candidatura e presumi che questa e-mail ti sia stata recapitata per errore, puoi pure ignorarla. Se il problema persiste, per favore, contattaci al seguente indirizzo e-mail: %s"), WEEEHIRE_EMAIL_FALLBACK) . "\r\n\r\n";
	echo __("Il software WEEEHire per conto del team WEEE Open") . "\r\n";
}
