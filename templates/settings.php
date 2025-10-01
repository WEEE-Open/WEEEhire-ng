<?php
/**
 * @var $myname string
 */
/**
 * @var $myuser string
 */
/**
 * @var $expiry String
 */
/**
 * @var $sendMail String
 */
/**
 * @var $positions     String
 */

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
					<?=	__('Scadenza Candidature')?>
					<b>(<?= $expiry === null ? __('nessuna scadenza') : $expiry->format('d-m-Y') . ' ' . __('alle 00:00') ?>)</b>
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
				<div class="btn btn-secondary" data-toggle="modal" data-target="#newPositionModal">
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
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
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
</div>
