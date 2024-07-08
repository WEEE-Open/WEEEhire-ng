<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $expiry String */
/** @var $sendMail String */
/** @var $positions	 String */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'logoHref' => 'settings.php']);

$currentFileName = basename(__FILE__);

?>

<?=$this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser, 'currentFileName' => $currentFileName])?>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger">
		<?=$this->e($error)?>
	</div>
<?php endif ?>
<div class="container-fluid">
	<h1><?=__('Opzioni WEEEHire')?></h1>
	<hr>
	<h4><i><?=__('Modifica scadenza candidature')?></i></h4>
	<form method="post">
		<div class="form-group row">
			<div class="<?=$expiry === null ? 'col-sm-12 col-lg-8' : 'col-sm-12 col-lg-6'?>">
				<label for="expiry">
					<?=sprintf(
						__('Scadenza Candidature <b>(%s)</b>'),
						$expiry === null ? __('nessuna scadenza') : $expiry->format('d-m-Y') . ' ' . __('alle 00:00')
					)?>
				</label>
			</div>
			<div class="col-sm-6 col-lg-3">
				<input type="date" class="form-control" name="expiry" id="expiry"
						value="<?=$expiry === null ? '' : $expiry->format('Y-m-d');?>">
			</div>
			<?php if ($expiry === null) : ?>
				<div class="col-sm-6 col-lg-1 mt-3 mt-sm-0">
					<button type="submit" class="btn btn-primary"><?=__('Conferma')?></button>
				</div>
			<?php else : ?>
				<div class="col-sm-6 col-lg-3 mt-3 mt-sm-0">
					<button type="submit" class="btn btn-primary"><?=__('Conferma')?></button>
					<button type="submit" class="btn btn-outline-danger" name="noexpiry"
							value="true">&#x274C;<?=__('Elimina')?></button>
				</div>
			<?php endif ?>
		</div>
	</form>
	<hr>
	<h4 class="mb-3"><i><?=__('Modifica i ruoli disponibili per i nuovi candidati')?></i></h4>
	<form method="post">
		<input type="hidden" name="positions" value="true">
		<div class="container-fluid">
			<?php foreach ($positions as $position) : ?>
				<div class="row align-items-center">
					<input class="col-md-auto" type="checkbox" name="position-<?=$position['id']?>" id="position-<?=$position['id']?>" <?=$position['available'] == 1 ? 'checked' : ''?>>
					<label class="col" for="position-<?=$position['id']?>"><?=$position['name']?> (<?=$position['id']?>)</label>
					<div class="col-md-auto btn">
						<a href="position.php?id=<?=$position['id']?>" class="btn btn-outline-primary"><?=__('Modifica')?></a>
					</div>
				</div>
			<?php endforeach ?>
		</div>
		<div class="form-group row">
			<div class="col-12">
				<button type="submit" class="btn btn-primary"><?=__('Salva')?></button>
			</div>
		</div>
	</form>
	<hr>
	<h4 class="mb-3"><i><?=__('Invia email a noi quando arriva una nuova candidatura')?></i></h4>
	<form method="post">
		<div class="form-group row">
			<div class="col-12">
			<?php if ($sendMail) : ?>
				<p><?=sprintf(__('Viene inviata un\'email a %s ogni volta che riceviamo una nuova candidatura.'), WEEEHIRE_EMAIL_FALLBACK) ?></p>
			<?php else : ?>
				<p><?=__('Non riceviamo notifiche per le nuove candidature.')?></p>
			<?php endif; ?>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-12">
			<?php if ($sendMail) : ?>
				<button type="submit" class="btn btn-outline-danger" name="notifyEmail" value="false"><?=__('Disattiva email')?></button>
			<?php else : ?>
				<button type="submit" class="btn btn-outline-success" name="notifyEmail" value="true"><?=__('Attiva email')?></button>
			<?php endif; ?>
			</div>
		</div>
	</form>
</div>
