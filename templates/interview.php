<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
/** @var $recruiters string[][] */
$titleShort = sprintf(__('%s %s (%s)'), htmlspecialchars($user->name), htmlspecialchars($user->surname), htmlspecialchars($user->matricola));
$title = sprintf(__('%s - Colloquio'), $titleShort);
$this->layout('base', ['title' => $title]);
?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="interviews.php"><?= __('Colloqui') ?></a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= $titleShort ?></li>
	</ol>
</nav>

<?php if($user->published): ?>
<div class="alert alert-info" role="alert">
	<?= __('Colloqui pianificati per il ...') ?>
</div>
<?php endif ?>

<?= $this->fetch('userinfo', ['user' => $user, 'edit' => $edit]) ?>

<?php if(!$edit): ?>
	<a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => 'true'])) ?>"><?=__('Modifica dati')?></a>
	<form method="post">
		<?php if($user->invitelink !== null): ?>
			<div class="alert alert-info" role="alert">
				<?= sprintf(__('Link d\'invito: %s'), $user->invitelink); ?>
			</div>
		<?php endif ?>
		<div class="form-group text-center">
			<button name="invite" value="true" type="submit" class="btn btn-primary"><?=__('Genera link d\'invito')?></button>
		</div>
	</form>
<?php endif ?>


<script src="resize.js"></script>
