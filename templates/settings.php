<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $expiry DateTime */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'datatables' => true]);
?>

<?= $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser]) ?>

<?php if(isset($error)): ?>
	<div class="alert alert-danger">
		<?= $this->e($error) ?>
	</div>
<?php endif ?>

<div class="container-fluid">
    <h1><?= __('Opzioni WEEEHire') ?></h1>
    <p><i><?= __('Modifica scadenza candidature e altri parametri di configurazione WEEEHire') ?></i></p>
    <form method="post">
	    <div class="form-group row">
	        <div class="col-md-9">
		        <label for="expiry">
	            <?= sprintf(__('Scadenza Candidature <b>(%s)</b>'), $expiry === null ? __('Nessuna Scadenza') : $expiry->format('d-m-Y')) ?>
		        </label>
	        </div>
		    <div class="col-md-2">
			    <input type="date" class="form-control" name="expiry" id="expiry" value="<?= $expiry === null ? '' : $expiry->format('Y-m-d'); ?>">
		    </div>
		    <div class="col-md-1">
			    <?php if($expiry === null): ?>
				    <button type="submit" class="btn btn-primary"><?= __('Conferma') ?></button>
			    <?php else: ?>
				    <button type="submit" class="btn btn-outline-danger" name="noexpiry" value="true">&#x274C;<?= __('Elimina') ?></button>
			    <?php endif ?>
		    </div>
	    </div>
    </form>
</div>