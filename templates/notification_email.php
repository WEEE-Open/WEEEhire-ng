<?php

/**
 * @var $link string
 */
/**
 * @var $user \WEEEOpen\WEEEHire\User
 */

$saluti1 = [
	'Ciao persone,',
	'Welà gente,',
	'Hey persone,',
	'Carissimi,',
	'Ciao,',
	'Buongiornissimo,',
	'Hi,',
	'Uelà bella gente,',
	'Salve,',
	'Buonsalve,',
];
$saluti2 = [
	'- Il WEEEHire',
	"Cordiali saluti,\nWEEEHire",
	"Cordiali ciaoni,\nWEEEHire",
	'Statemi bene',
	'Ciaone',
	"Cordialmente vostro,\nWEEEHire",
	'Allego informali convenevoli [sic]'
];

$rand1 = mt_rand(0, count($saluti1) - 1);
$rand2 = mt_rand(0, count($saluti2) - 1);

echo "$saluti1[$rand1]\r\n\r\n";
echo "c'è una nuova candidatura per l'area $user->area.\r\n";
echo "Andate a valutarla: $link\r\n\r\n";
echo $saluti2[$rand2];
