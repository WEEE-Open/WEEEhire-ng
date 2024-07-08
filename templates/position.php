<?php
/** @var $myname string */
/** @var $myuser string */
/** @var $position */
/** @var $nameTranslations */
/** @var $descriptionTranslations */
use WEEEOpen\WEEEHire\Template;

$this->layout('base', ['title' => __('Modifica posizione'), 'logoHref' => 'settings.php']);

$currentFileName = basename(__FILE__);

?>

<?=$this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser, 'currentFileName' => $currentFileName])?>

<?php if (isset($error)) : ?>
	<div class="alert alert-danger">
		<?=$this->e($error)?>
	</div>
<?php endif ?>
<div class="container-fluid">
	<h1><?=__('Modifica posizione')?></h1>
	<hr>
	<h4><i><?=__('Modifica id')?></i></h4>
	<form method="post">
		<div class="form-group row">
			<div class="col-sm-12 col-lg-8">
				<label for="id">
					Id
				</label>
			</div>
			<div class="col-sm-6 col-lg-3">
				<input type="text" class="form-control" name="id" id="id" value="<?=$position["id"]?>">
			</div>
			<div class="col-sm-6 col-lg-1 mt-3 mt-sm-0">
				<button type="submit" class="btn btn-primary"><?=__('Modifica')?></button>
			</div>
		</div>
	</form>
	<hr>
	<form method="post">
		<h4 class="mb-3"><i><?=__('Modifica nome')?></i></h4>
		<input type="hidden" name="translation" value="true">
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="form-group row">
				<div class="col-sm-12 col-lg-8">
					<label for="name-<?=$locale?>">
						<?=__('Nome')?> (<?=$locale?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-3">
					<input type="text" class="form-control" name="name-<?=$locale?>" id="name-<?=$locale?>" value="<?=$nameTranslations[$locale]?>">
				</div>
			</div>
		<?php endforeach; ?>
		<hr>
		<h4 class="mb-3"><i><?=__('Modifica descrizione')?></i></h4>
		<p><i><?=__('Ricorda che puoi usare il Markdown per formattare il testo')?></i></p>
		<?php foreach (Template::SUPPORTED_LOCALES as $locale) : ?>
			<div class="form-group row">
				<div class="col-sm-12 col-lg-6">
					<label for="description-<?=$locale?>">
						<?=__('Descrizione')?> (<?=$locale?>)
					</label>
				</div>
				<div class="col-sm-6 col-lg-6">
					<textarea class="form-control autoresize" name="description-<?=$locale?>" id="description-<?=$locale?>"><?=$descriptionTranslations[$locale]?></textarea>
				</div>
			</div>
		<?php endforeach; ?>
		<hr>
		<div class="form-group row">
			<div class="col-sm-12 col-lg-6">
				<button type="submit" class="btn btn-primary"><?=__('Modifica')?></button>
			</div>
		</div>
	</form>
</div>
