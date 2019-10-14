<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
if(isset($edit) && $edit) {
	$readonly = '';
} else {
	$readonly = 'readonly';
}
?>

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
		<button type="submit" name="edit" value="true" class="btn btn-primary"><?=__('Aggiorna dati')?></button>
		<a class="btn btn-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null])) ?>"><?=__('Annulla')?></a>
	</div>
<?php endif ?>
</form>
