<?php

/**
 *
 *
 * @var string $myname
 */
/**
 *
 *
 * @var string $myuser
 */
/**
 *
 *
 * @var string $expiry
 */
/**
 *
 *
 * @var string $sendMail
 */
/**
 *
 *
 * @var string $positions
 */
/**
 *
 *
 * @var int $ghostdays
 */

use WEEEOpen\WEEEHire\Template;

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'logoHref' => 'settings.php']);

$currentFileName = basename(__FILE__);

?>

<?php echo $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser, 'currentFileName' => $currentFileName])?>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger">
	<?php echo $this->e($error)?>
	</div>
<?php endif ?>
<div class="container-fluid">
	<h1><?php echo __('Opzioni WEEEHire')?></h1>
	<hr>
	<h4><i><?php echo __('Modifica scadenza candidature')?></i></h4>
	<form method="post">
		<div class="mb-3 row align-items-center">
			<div class="col-12 col-md mb-2 mb-md-0">
				<label for="expiry">
					<?php echo    __('Scadenza Candidature')?>
					<b>(<?php echo $expiry === null ? __('nessuna scadenza') : $expiry->format('d-m-Y') . ' ' . __('alle 23:59') ?>)</b>
				</label>
			</div>
			<div class="col-12 col-md-auto d-flex gap-2">
				<input type="date" class="form-control" name="expiry" id="expiry"
						value="<?php echo $expiry === null ? '' : $expiry->format('Y-m-d');?>">
				<?php if ($expiry === null) : ?>
					<button type="submit" class="btn btn-primary"><?php echo __('Conferma')?></button>
				<?php else : ?>
					<button type="submit" class="btn btn-primary"><?php echo __('Conferma')?></button>
					<button type="submit" class="btn btn-outline-danger" name="noexpiry"
						value="true">&#x274C;<?php echo __('Elimina')?></button>
				<?php endif ?>
			</div>
		</div>
	</form>
	<h4><i><?php echo __('Modifica Giorni Ghost')?></i></h4>
	<form method="post">
		<div class="mb-3 row align-items-center">
			<div class="col-12 col-md mb-2 mb-md-0">
				<label for="ghostdays">
					<?php echo __('Impostazione Giorni Ghost: ')?>
					<b>
						<?php
						if ($ghostdays === null || $ghostdays === '') {
							echo __('Non Definito');
						} elseif ($ghostdays === 0) {
							echo __('Disattivato');
						} else {
							echo __($ghostdays);
						}
						?>
					</b>
				</label>
			</div>
			<div class="col-12 col-md-auto d-flex gap-2">
				<input type="number" class="form-control" name="ghostdays" id="ghostdays" min="0"
					value="<?php echo $ghostdays === null ? '' : $ghostdays;?>">
				<button type="submit" class="btn btn-primary"><?php echo __('Conferma')?></button>
			</div>
		</div>
	</form>
	<hr>
	<h4 class="mb-3"><i><?php echo __('Modifica i ruoli disponibili per i nuovi candidati')?></i></h4>
	<form method="post">
		<input type="hidden" name="positions" value="true">
		<div class="container-fluid">
			<?php foreach ($positions as $position) : ?>
				<div class="row align-items-center">
					<input class="col-md-auto" type="checkbox" name="position-<?php echo $position['id']?>" id="position-<?php echo $position['id']?>" <?php echo $position['available'] == 1 ? 'checked' : ''?>>
					<label class="col" for="position-<?php echo $position['id']?>"><?php echo $position['name']?> (<?php echo $position['id']?>)</label>
					<div class="col-md-auto btn">
						<a href="position.php?id=<?php echo $position['id']?>" class="btn btn-outline-primary"><?php echo __('Modifica')?></a>
					</div>
				</div>
			<?php endforeach ?>
		</div>
		<div class="mb-3 row">
			<div class="col">
				<button type="submit" class="btn btn-primary"><?php echo __('Salva')?></button>
			</div>
			<div class="col-md-auto">
				<div class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#newPositionModal">
					<div><?php echo __('Crea nuova posizione')?></div>
				</div>
			</div>
		</div>
	</form>
	<div class="modal fade" id="newPositionModal" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<form method="post" class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo __('Nuova posizione')?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3 row">
						<div class="col-sm-12 col-lg-4">
							<label for="newPositionName">
								<?php echo __('Nome')?>
							</label>
						</div>
						<div class="col-sm-6 col-lg-8">
							<input type="text" class="form-control" name="newPositionName" id="newPositionName">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?php echo __('Annulla')?></button>
					<input type="submit" class="btn btn-primary" value="<?php echo __('Crea')?>"></input>
				</div>
			</div>
		</form>
	</div>
	<hr>
	<h4 class="mb-3"><i><?php echo __('Invia email a noi quando arriva una nuova candidatura')?></i></h4>
	<form method="post">
		<div class="mb-3 row">
			<div class="col-12">
			<?php if ($sendMail) : ?>
				<p><?php echo sprintf(__('Viene inviata un\'email a %s ogni volta che riceviamo una nuova candidatura.'), WEEEHIRE_EMAIL_FALLBACK) ?></p>
			<?php else : ?>
				<p><?php echo __('Non riceviamo notifiche per le nuove candidature.')?></p>
			<?php endif; ?>
			</div>
		</div>
		<div class="mb-3 row">
			<div class="col-12">
			<?php if ($sendMail) : ?>
				<button type="submit" class="btn btn-outline-danger" name="notifyEmail" value="false"><?php echo __('Disattiva email')?></button>
			<?php else : ?>
				<button type="submit" class="btn btn-outline-success" name="notifyEmail" value="true"><?php echo __('Attiva email')?></button>
			<?php endif; ?>
			</div>
		</div>
	</form>
	<hr>
	<h4 class="mb-3"><i><?php echo __('Messaggi precompilati')?></i></h4>
	<div class="mb-3 row">
		<div class="col-12">
			<p>
				<?php echo __('Puoi usare ${parametro}, per riempire automaticamente i campi. I parametri disponibili sono:') ?>
				<ul>
					<li>inviteLink - <?php echo __('Link d\'invito (solo per in caso il membro sia stato approvato)') ?></li>
					<li>firstname - <?php echo __('Nome del candidato') ?></li>
					<li>interviewerName - <?php echo __('Nome del recruiter') ?></li>
				</ul>
			</p>
		</div>
	</div>
	<p><i><?php echo __('Ricorda che puoi usare il Markdown per formattare il testo') ?></i></p>
	<form method="post">
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="mb-3 row">
				<div class="col-sm-12 col-lg-6">
					<label for="accepted-message-<?php echo $locale ?>">
			<?php echo __('Messaggio accettato') ?> (<?php echo $locale ?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-6">
					<textarea class="form-control autoresize" name="accepted-message-<?php echo $locale ?>" id="accepted-message-<?php echo $locale ?>"><?php echo $acceptedMessage[$locale] ?></textarea>
				</div>
			</div>
		<?php endforeach; ?>
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="mb-3 row">
				<div class="col-sm-12 col-lg-6">
					<label for="rejected-message-<?php echo $locale ?>">
			<?php echo __('Messaggio rifiutato') ?> (<?php echo $locale ?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-6">
					<textarea class="form-control autoresize" name="rejected-message-<?php echo $locale ?>" id="rejected-message-<?php echo $locale ?>"><?php echo $rejectedMessage[$locale] ?></textarea>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="col-md-auto">
			<button type="submit" class="btn btn-primary" name="messages" value="true"><?php echo __('Modifica') ?></button>
		</div>
	</form>
</div>
