<?php $this->layout('base', ['title' => 'WEEElcome']) ?>

<h1><?= __('Entra in WEEE Open') ?></h1>
<p><?= __('Compila il questionario per fare richiesta di ammissione in team. Premi il bottone qui sotto per iniziare.') ?></p>
<div class="col-md-12 text-center">
	<button type="button" class="btn btn-lg btn-primary"><?= __('Inizia') ?></button>
</div>
<?php if($_SESSION['locale'] === 'en_US'): ?>
	<p>Il questionario è anche disponibile <a href="language.php?l=it_IT&from=<?= rawurlencode($_SERVER['REQUEST_URI']) ?>">in Italiano</a></p>
<?php else: ?>
	<p>The form is also available <a href="language.php?l=en_US&from=<?= rawurlencode($_SERVER['REQUEST_URI']) ?>">in English</a></p>
<?php endif ?>
