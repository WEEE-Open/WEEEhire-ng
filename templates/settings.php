<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $expiry DateTime */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'datatables' => true]);
?>

<?=$this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser])?>

<?php if(isset($error)): ?>
	<div class="alert alert-danger">
		<?=$this->e($error)?>
	</div>
<?php endif ?>

<div class="container-fluid">
	<h1><?=__('Opzioni WEEEHire')?></h1>
	<p><i><?=__('Modifica scadenza candidature e altri parametri di configurazione WEEEHire')?></i></p>
	<form method="post">
		<div class="form-group row">
			<div class="<?=$expiry === null ? 'col-sm-12 col-lg-8' : 'col-sm-12 col-lg-6'?>">
				<label for="expiry">
					<?=sprintf(__('Scadenza Candidature <b>(%s)</b>'),
						$expiry === null ? __('Nessuna Scadenza') : $expiry->format('d-m-Y'))?>
				</label>
			</div>
			<div class="col-sm-6 col-lg-3">
				<input type="date" class="form-control" name="expiry" id="expiry"
						value="<?=$expiry === null ? '' : $expiry->format('Y-m-d');?>">
			</div>
			<?php if($expiry === null): ?>
				<div class="col-sm-6 col-lg-1 mt-3 mt-sm-0">
					<button type="submit" class="btn btn-primary"><?=__('Conferma')?></button>
				</div>
			<?php else: ?>
				<div class="col-sm-6 col-lg-3 mt-3 mt-sm-0">
					<button type="submit" class="btn btn-primary"><?=__('Conferma')?></button>
					<button type="submit" class="btn btn-outline-danger" name="noexpiry"
							value="true">&#x274C;<?=__('Elimina')?></button>
				</div>
			<?php endif ?>
		</div>
</div>
</form>
</div>