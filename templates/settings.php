<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $expiry String */
/** @var $sendMail String */
/** @var $rolesUnavailable String */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'logoHref' => 'settings.php']);
require_once 'roles.php';
$allRoles = getRoles();
if ($rolesUnavailable === null) {
	$roles = [];
} else {
	$roles = explode('|', $rolesUnavailable);
	$roles = array_combine($roles, $roles);
}
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
						$expiry === null ? __('nessuna scadenza') : $expiry->format('d-m-Y')
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
		<div class="row justify-content-between">
			<div class="col">
				<div class="row">
					<div class="col border-right">
						<h5 class="text-success"><?=__('Ruoli disponibili')?></h5>
						<?php foreach ($allRoles as $value => $role) : ?>
							<?php if (!isset($roles[$value])) : ?>
								<p><?= $this->e($role) ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="col border-right">
						<h5 class="text-danger"><?=__('Ruoli non disponibili')?></h5>
						<?php foreach ($allRoles as $value => $role) : ?>
							<?php if (isset($roles[$value])) : ?>
								<p><?= $this->e($role) ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="col ml-3">
				<div class="row">
					<label for="roles"><?= __('Seleziona i ruoli da rendere non disponibili') ?></label>
					<select size="<?= count($allRoles) ?>" class="custom-select mb-2" multiple name="roles[]" id="roles">
						<?php foreach ($allRoles as $value => $role) : ?>
							<option <?= isset($roles[$value]) ? 'selected' : '' ?> value="<?= $this->e($value) ?>"><?= $this->e($role) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="row justify-content-between mt-3">
					<button type="submit" class="btn btn-primary mb-md-0 mb-2"><?=__('Conferma')?></button>
					<button type="submit" class="btn btn-warning" name="rolesReset" value="true"><?=__('Rendi tutti disponibili')?></button>
				</div>
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
