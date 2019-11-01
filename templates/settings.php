<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $expiry String */
/** @var $rolesUnavailable String */


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
	<hr>
	<h4><i><?=__('Modifica scadenza candidature')?></i></h4>
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
	</form>
	<hr>
	<h4 class="mb-3"><i><?=__('Modifica i ruoli disponibili per i nuovi candidati')?></i></h4>
	<?php
	$roles = explode(',', $rolesUnavailable);
	?>
	<form method="post">
		<div class="row justify-content-between">
			<div class="col">
				<div class="row">
					<div class="col" style="border-right:1px solid #d7d7d7;">
						<h5 style="color: limegreen">Ruoli disponibili</h5>
						<?php if(!in_array('hardware', $roles)): ?>
							<p><?=__('Riparazione Hardware')?></p> <?php endif; ?>
						<?php if(!in_array('electronic', $roles)): ?> <p><?=__('Elettronica')?></p> <?php endif; ?>
						<?php if(!in_array('development', $roles)): ?>
							<p><?=__('Sviluppo Software')?></p> <?php endif; ?>
						<?php if(!in_array('fun', $roles)): ?> <p><?=__('Riuso Creativo')?></p> <?php endif; ?>
						<?php if(!in_array('relationship', $roles)): ?>
							<p><?=__('Pubbliche Relazioni')?></p> <?php endif; ?>
					</div>
					<div class="col" style="border-right:1px solid #d7d7d7;">
						<h5 style="color: red">Ruoli non disponibili</h5>
						<?php if(in_array('hardware', $roles)): ?>
							<p><?=__('Riparazione Hardware')?></p> <?php endif; ?>
						<?php if(in_array('electronic', $roles)): ?> <p><?=__('Elettronica')?></p> <?php endif; ?>
						<?php if(in_array('development', $roles)): ?>
							<p><?=__('Sviluppo Software')?></p> <?php endif; ?>
						<?php if(in_array('fun', $roles)): ?> <p><?=__('Riuso Creativo')?></p> <?php endif; ?>
						<?php if(in_array('relationship', $roles)): ?>
							<p><?=__('Pubbliche Relazioni')?></p> <?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col ml-3">
				<div class="row">
					<select size="5" class="custom-select mb-2" multiple name="roles[]">
						<option value="hardware"><?=__('Riparazione Hardware')?></option>
						<option value="electronic"><?=__('Elettronica')?></option>
						<option value="development"><?=__('Sviluppo Software')?></option>
						<option value="fun"><?=__('Riuso Creativo')?></option>
						<option value="relationship"><?=__('Pubbliche Relazioni')?></option>
					</select>
				</div>
				<div class="row justify-content-between mt-3">
					<button type="submit" class="btn btn-warning" name="rolesReset"
							value="1"><?=__('Rendi tutti disponibili')?></button>
					<button type="submit" class="btn btn-primary"><?=__('Conferma')?></button>
				</div>
			</div>
		</div>
	</form>
</div>