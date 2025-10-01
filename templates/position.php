<?php

/**
 * @var $myname string
 */

/**
 * @var $myuser string
 */

/**
 * @var $position
 */

/**
 * @var $nameTranslations
 */

/**
 * @var $summaryTranslations
 */

/**
 * @var $descriptionTranslations
 */

use WEEEOpen\WEEEHire\Template;

$this->layout('base', ['title' => __('Modifica posizione'), 'logoHref' => 'settings.php']);

$currentFileName = basename(__FILE__);

?>

<?php echo $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser, 'currentFileName' => $currentFileName]) ?>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger">
	<?php echo $this->e($error) ?>
	</div>
<?php endif ?>
<div class="container-fluid">
	<h1><?php echo __('Modifica posizione') ?></h1>
	<hr>
	<h4><i><?php echo __('Modifica id') ?></i></h4>
	<form method="post">
		<div class="mb-3 row">
			<div class="col-sm-12 col-lg-7">
				<label for="id">
					Id
				</label>
			</div>
			<div class="col-sm-6 col-lg-3">
				<input type="text" class="form-control" name="id" id="id" value="<?php echo $position['id'] ?>">
			</div>
			<div class="col-sm-6 col-lg-1 mt-3 mt-sm-0">
				<button type="submit" class="btn btn-primary"><?php echo __('Modifica') ?></button>
			</div>
		</div>
	</form>
	<h4><i><?php echo __('Modifica index') ?></i></h4>
	<form method="post">
		<div class="mb-3 row">
			<div class="col-sm-12 col-lg-7">
				<label for="index">
					Index
				</label>
			</div>
			<div class="col-sm-6 col-lg-3">
				<input type="text" class="form-control" name="index" id="index" value="<?php echo $position['idx'] ?>">
			</div>
			<div class="col-sm-6 col-lg-1 mt-3 mt-sm-0">
				<button type="submit" class="btn btn-primary"><?php echo __('Modifica') ?></button>
			</div>
		</div>
	<hr>
	<form method="post">
		<h4 class="mb-3"><i><?php echo __('Modifica nome') ?></i></h4>
		<input type="hidden" name="translation" value="true">
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="mb-3 row">
				<div class="col-sm-12 col-lg-7">
					<label for="name-<?php echo $locale ?>">
			<?php echo __('Nome') ?> (<?php echo $locale ?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-3">
					<input type="text" class="form-control" name="name-<?php echo $locale ?>" id="name-<?php echo $locale ?>" value="<?php echo $nameTranslations[$locale] ?>">
				</div>
			</div>
		<?php endforeach; ?>
		<hr>
		<h4 class="mb-3"><i><?php echo __('Modifica sintesi') ?></i></h4>
		<p><i><?php echo __('Ricorda che puoi usare il Markdown per formattare il testo') ?></i></p>
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="mb-3 row">
				<div class="col-sm-12 col-lg-6">
					<label for="summary-<?php echo $locale ?>">
			<?php echo __('Sintesi') ?> (<?php echo $locale ?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-6">
					<textarea class="form-control autoresize" name="summary-<?php echo $locale ?>" id="summary-<?php echo $locale ?>"><?php echo $summaryTranslations[$locale] ?></textarea>
				</div>
			</div>
		<?php endforeach; ?>
		<hr>
		<h4 class="mb-3"><i><?php echo __('Modifica descrizione') ?></i></h4>
		<p><i><?php echo __('Ricorda che puoi usare il Markdown per formattare il testo') ?></i></p>
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="mb-3 row">
				<div class="col-sm-12 col-lg-6">
					<label for="description-<?php echo $locale ?>">
			<?php echo __('Descrizione') ?> (<?php echo $locale ?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-6">
					<textarea class="form-control autoresize" name="description-<?php echo $locale ?>" id="description-<?php echo $locale ?>"><?php echo $descriptionTranslations[$locale] ?></textarea>
				</div>
			</div>
		<?php endforeach; ?>
		<hr>
		<div class="mb-3 row">
			<div class="col">
				<div class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteModal"><?php echo __('Elimina') ?></div>
			</div>
			<div class="col-md-auto">
				<button type="submit" class="btn btn-primary"><?php echo __('Modifica') ?></button>
			</div>
		</div>
	</form>
	<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo __('Conferma')?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p><?php echo __('Sei sicuro di voler eliminare questa posizione?')?></p>
					<p><b><?php echo __('Questa azione non può essere annullata.')?></b></p>
				</div>
				<form method="post" class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Annulla')?></button>
					<input type="hidden" name="delete" value="true">
					<input type="submit" class="btn btn-outline-danger" value="<?php echo __('Elimina')?>"></input>
				</form>
			</div>
		</div>
	</div>
</div>
