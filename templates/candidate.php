<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
/** @var $status bool|null */
/** @var $published bool */
$this->layout('base', ['title' => sprintf(__('Candidatura di %s %s (%s)'), htmlspecialchars($user->name), htmlspecialchars($user->surname), htmlspecialchars($user->matricola))]);
if(isset($edit) && $edit) {
	$readonly = '';
} else {
	$readonly = 'readonly';
}
?>

<div class="col-md-12">
<form method="post">
	<div class="form-group row">
		<label for="name" class="col-md-2 col-lg-1 col-form-label"><?= __('Nome') ?></label>
		<div class="col-md-4 col-lg-5">
			<input <?= $readonly ?> id="name" name="name" type="text" required="required" class="form-control" value="<?= htmlspecialchars($user->name) ?>">
		</div>
		<label for="surname" class="col-md-2 col-lg-1 col-form-label"><?=__('Cognome')?></label>
		<div class="col-md-4 col-lg-5">
			<input <?= $readonly ?> id="surname" name="surname" type="text" required="required" class="form-control" value="<?= htmlspecialchars($user->surname) ?>">
		</div>
	</div>
	<div class="form-group row">
		<label for="degreecourse" class="col-md-2 col-lg-1 col-form-label"><?=__('Corso di laurea')?></label>
		<div class="col-md-7 col-lg-6">
			<input <?= $readonly ?> type="text" id="degreecourse" name="degreecourse" required="required" class="form-control" value="<?= htmlspecialchars($user->degreecourse) ?>">
		</div>
		<label for="year" class="col-md-1 col-form-label"><?=__('Anno')?></label>
		<div class="col-md-2 col-lg-4">
			<input <?= $readonly ?> type="text" id="year" name="year" required="required" class="form-control" value="<?= htmlspecialchars($user->year) ?>">
		</div>
	</div>
	<div class="form-group row">
		<label for="matricola" class="col-md-2 col-lg-1 col-form-label"><?=__('Matricola')?></label>
		<div class="col-md-3 col-lg-4">
			<input <?= $readonly ?> id="matricola" name="matricola" type="text" required="required" class="form-control" value="<?= htmlspecialchars($user->matricola) ?>">
		</div>
		<label for="area" class="col-md-2 col-lg-1 col-form-label"><?=__('Interesse')?></label>
		<div class="col-md-5 col-lg-6">
			<input <?= $readonly ?> type="text" name="area" id="area" required="required" class="form-control" value="<?= htmlspecialchars($user->area) ?>">
		</div>
	</div>
	<div class="form-group">
		<label for="letter"><?= __('Lettera motivazionale') ?></label>
		<textarea <?= $readonly ?> id="letter" name="letter" cols="40" rows="5" required="required" class="form-control"><?= htmlspecialchars($user->letter) ?></textarea>
	</div>
	<?php if($edit): ?>
	<div class="form-group">
		<button type="submit" class="btn btn-primary"><?=__('Aggiorna dati')?></button>
		<a class="btn btn-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null])) ?>"><?=__('Annulla')?></a>
	</div>
	<?php endif ?>
</form>
<?php if(!$edit): ?>
<form method="post">
	<div class="form-group">
		<label for="notes"><b><?= __('Note') ?></b></label>
		<textarea id="notes" name="notes" cols="40" rows="3" class="form-control"><?= htmlspecialchars($user->notes) ?></textarea>
	</div>
	<div class="form-group text-center">
		<?php if($published): ?>
			<a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['mail' => 'true'])) ?>"><?=__('Modifica dati')?></a>
		<?php else: ?>
			<button name="approve" value="true" type="submit" class="btn btn-success"><?=__('Approva candidatura')?></button>
			<button name="reject" value="true" type="submit" class="btn btn-danger"><?=__('Rifiuta candidatura')?></button>
			<?php if($status !== null): ?>
				<button name="publishnow" value="true" type="submit" class="btn btn-primary"><?=__('Pubblica')?></button>
				<button name="limbo" value="true" type="submit" class="btn btn-warning"><?=__('Rimanda nel limbo')?></button>
			<?php endif ?>
		<?php endif ?>
		<button name="save" value="true" type="submit" class="btn btn-outline-primary"><?=__('Salva note')?></button>
		<a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => 'true'])) ?>"><?=__('Modifica dati')?></a>
	</div>
</form>
<?php endif ?>
</div>
