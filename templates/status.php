<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var $user \WEEEOpen\WEEEHire\User */
/** @var \Psr\Http\Message\UriInterface $globalRequestUri */
$this->layout('base', ['title' => __('Stato della richiesta')])
?>
<div class="text-center">
	<h1><?=__('Stato della richiesta')?></h1>
	<?php if($user->published && $user->status === true): ?>
		<h2 class="status-approved"><?=__('Ammesso/a al colloquio')?></h2>
		<p><?=sprintf(__('Ti abbiamo mandato un\'email per informarti, ma se non fosse arrivata per favore contattata %1$s (<a href="https://t.me/%2$s">@%2$s</a>) su Telegram per passare al colloquio.'),
				$this->e($user->recruiter), $this->e($user->recruitertg))?></p>
	<?php elseif($user->published && $user->status === false): ?>
		<h2 class="status-rejected"><?=__('Domanda respinta')?></h2>
	<?php elseif($user->published && $user->hold === true): ?>
		<h2 class="status-postponed"><?=__('Richiesta sospesa')?></h2>
	<?php else: ?>
		<h2 class="status-waiting"><?=__('Valutazione in corso')?></h2>
		<p><?=__('Salva questa pagina nei preferiti e torna a controllare lo stato più avanti.')?></p>
		<?php
		$date = new DateTime();
		$date->setTimezone(new DateTimeZone('Europe/Rome'));
		$date->setTimestamp($user->submitted);
		?>
		<p><?=sprintf(__('Candidatura inviata il %1$s alle %2$s'), $date->format('Y-m-d'), $date->format('H:i'))?></p>
	<?php endif ?>
</div>
<?php if($user->published && $user->hold === true && $user->visiblenotes !== null): ?>
<div class="text-center" id="reason">
	<p><span><?=__('Motivazioni:')?> </span><?= $this->e($user->visiblenotes) ?></p>
</div>
<?php endif ?>
<div class="text-center space-above">
	<a id="remove" class="btn btn-danger mb-2"
			href="<?=$this->e(WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri,
				['delete' => 'true']))?>"><?=__('Elimina candidatura')?></a>
	<a id="download" class="btn btn-primary mb-2"
			href="<?=$this->e(WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri,
				['download' => 'true']))?>"><?=__('Scarica tutti i miei dati')?></a>
</div>
<div class="text-center">
	<small><?=__('Visto come siamo GDPR-compliant?')?></small>
</div>
