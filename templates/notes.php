<?php
/**
 * @var array $notes
 */
?>
<div class="row mx-0 border-bottom">
	<div class="col col-md-6 d-none d-md-block">
		<p class="font-weight-bold"><?php echo __('Testo') ?></p>
	</div>
	<div class="col col-md-2">
		<p class="font-weight-bold"><?php echo __('Autore') ?></p>
	</div>
	<div class="col col-md-2">
		<p class="font-weight-bold"><?php echo __('Data') ?></p>
	</div>
	<div class="col col-md-2 text-right text-md-left">
		<p class="font-weight-bold"><?php echo __('Azioni') ?></p>
	</div>
</div>

<?php $currentUserNoted = false; if (count($notes) > 0) : ?>
	<?php foreach ($notes as $note) :
		$currentUserNoted = $_SESSION['uid'] === $note['uid']; ?>
		<form method="post">
			<div class="row mx-0 mb-4">
				<div class="col-12 col-md-6 mb-1" style="padding-left: 0; padding-right: 0;">
		<?php if ($currentUserNoted) : ?>
						<textarea class="form-control" name="note" cols="40"><?php echo htmlspecialchars($note['note']) ?></textarea>
		<?php else : ?>
						<div class="p-2"><?php echo htmlspecialchars($note['note']) ?></div>
		<?php endif; ?>
				</div>
				<div class="col pt-1 col-md-2"><?php echo htmlspecialchars($note['uid']) ?></div>
				<div class="col pt-1 col-md-2"><?php echo $note['updated_at']->format('Y-m-d') ?> <small><?php echo $note['updated_at']->format('H:i') ?></small></div>
				<div class="col col-md-2 pr-0 text-right text-md-left">
		<?php if ($currentUserNoted) : ?>
						<button class="btn btn-outline-primary my-1" name="updateNote" value="true"><?php echo __('Modifica') ?></button>
		<?php endif; ?>
				</div>
			</div>
		</form>
	<?php endforeach; ?>
<?php else : ?>
	<div class="row mx-0">
		<div class="col my-3 text-center"><?php echo __('Nessuna nota') ?></div>
	</div>
<?php endif; ?>