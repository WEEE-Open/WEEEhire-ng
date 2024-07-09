<?php

/**
 * @var $message string
 */
$this->layout('base', ['title' => __('Errore')])
?>

<h1><?php echo __('Errore')?></h1>
<p class="alert alert-danger"><?php echo $message?></p>
