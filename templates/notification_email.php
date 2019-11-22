<?php
/** @var $link string */
/** @var $user \WEEEOpen\WEEEHire\User */

$saluti1 = ['Ciao persone,', 'Welà gente,', 'Hey persone,', 'Ciao,', 'Buongiornissimo,', 'Hi,'];
$saluti2 = ['- Il WEEEHire', "Cordiali saluti,\nWEEEHire", 'Statemi bene', 'Ciaone', "Cordialmente vostro,\nWEEEHire", 'Allego informali convenevoli [sic]'];

$rand1 = mt_rand(0, count($saluti1) - 1);
$rand2 = mt_rand(0, count($saluti2) - 1);
?>

<?= $saluti1[$rand1] ?>

c'è una nuova candidatura per l'area <?= $user->area ?>.
Andate a valutarla: <?= $link ?>

<?= $saluti2[$rand2] ?>
