<?php
/**
 * @var $user WEEEOpen\WEEEhire\User
 */
/**
 * @var $edit bool
 */
/**
 * @var \Psr\Http\Message\UriInterface $globalRequestUri
 */
if (isset($edit) && $edit) {
	$readonly = '';
} else {
	$readonly = 'readonly';
}
?>

<form method="post">
	<div class="row">
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="name" class="form-label"><?php echo __('Nome')?></label>
			<input <?php echo $readonly?> id="name" name="name" type="text" required="required" class="form-control"
					value="<?php echo $this->e($user->name)?>">
		</div>
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="surname" class="form-label"><?php echo __('Cognome')?></label>
			<input <?php echo $readonly?> id="surname" name="surname" type="text" required="required" class="form-control"
					value="<?php echo $this->e($user->surname)?>">
		</div>
	</div>
	<div class="row">
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="degreecourse" class="form-label"><?php echo __('Corso di laurea')?></label>
			<input <?php echo $readonly?> type="text" id="degreecourse" name="degreecourse" required="required"
					class="form-control" value="<?php echo $this->e($user->degreecourse)?>">
		</div>
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="year" class="form-label"><?php echo __('Anno')?></label>
			<input <?php echo $readonly?> type="text" id="year" name="year" required="required" class="form-control"
					value="<?php echo $this->e($user->year)?>">
		</div>
	</div>
	<div class="row">
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="matricola" class="form-label"><?php echo __('Matricola')?></label>
			<input <?php echo $readonly?> id="matricola" name="matricola" type="text" required="required" class="form-control"
					value="<?php echo $this->e($user->matricola)?>">
		</div>
		<div class="mb-3 col-md-12 col-lg-6">
			<label for="area" class="form-label"><?php echo __('Interesse')?></label>
			<input <?php echo $readonly?> type="text" name="area" id="area" required="required" class="form-control"
					value="<?php echo $this->e($user->area)?>">
		</div>
	</div>
	<div class="mb-3">
		<label for="letter"><?php echo __('Lettera motivazionale')?></label>
		<textarea <?php echo $readonly?> id="letter" name="letter" cols="40" rows="5" required="required"
				class="form-control"><?php echo $this->e($user->letter)?></textarea>
	</div>
	<?php if ($edit) : ?>
		<div class="mb-3">
			<button type="submit" name="edit" value="true" class="btn btn-primary"><?php echo __('Aggiorna dati')?></button>
			<a class="btn btn-secondary"
					href="<?=$this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri, ['edit' => null]))?>"><?=__('Annulla')?></a>
		</div>
	<?php endif ?>
</form>
