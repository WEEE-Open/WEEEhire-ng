<?php $this->layout('base', ['title' => 'WEEElcome']) ?>

<h1><?= __('Entra in WEEE Open') ?></h1>
<p><?= __('Compila il questionario per fare richiesta di ammissione in team. Premi il bottone qui sotto per iniziare.') ?></p>
<div class="col-md-12 text-center">
	// Inserire timer 
</div>
<div class="col-md-12 text-center">
	<a class="btn btn-lg btn-primary the-button" href="candidate_close.php"><?= __('Inizia') ?></a>
</div>
<?php if($_SESSION['locale'] === 'en-us'): ?>
	<p>Il questionario è anche disponibile <a href="language.php?l=it-it&from=<?= rawurlencode('/form.php') ?>">in Italiano</a></p>
<?php else: ?>
	<p>The form is also available <a href="language.php?l=en-us&from=<?= rawurlencode('/form.php') ?>">in English</a></p>
<?php endif ?>