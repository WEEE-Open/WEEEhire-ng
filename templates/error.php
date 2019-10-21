<?php
/** @var $message string */
$this->layout('base', ['title' => __('Errore')])
?>

<h1><?=__('Errore')?></h1>
<p class="alert alert-danger"><?=$message?></p>
