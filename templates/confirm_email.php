<?php
/** @var $link string */
/** @var $subject bool */
if($subject) {
	echo __('Candidatura in WEEE Open');
} else {
	echo sprintf(__("Ciao!
Abbiamo ricevuto la tua candidatura per il team WEEE Open, questa è la pagina da cui potrai verificare lo stato della tua domanda:
%s
Se la domanda sarà approvata, riceverai un'email sempre a questo indirizzo con scritto chi contattare per passare al colloquio. Le stesse informazioni saranno visibili anche alla pagina di cui sopra.
Buona fortuna ;)
Il software WEEEHire per conto del team WEEE Open"), $link);
}