<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $interview WEEEOpen\WEEEhire\Interview */
/** @var $edit bool */
/** @var $recruiters string[][] */
/** @var \Psr\Http\Message\UriInterface $globalRequestUri */

$titleShort = sprintf(__('%s %s (%s)'), $this->e($user->name), $this->e($user->surname), $this->e($user->matricola));
$title = sprintf(__('%s - Colloquio'), $titleShort);
$this->layout('base', ['title' => $title, 'logoHref' => 'interviews.php']);
?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="interviews.php"><?=__('Colloqui')?></a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$titleShort?></li>
	</ol>
</nav>

<?php if($interview->status === true): ?>
	<div class="alert alert-success" role="alert">
		<?=sprintf(__('Colloquio superato secondo %s'), $interview->recruiter)?>
	</div>
<?php elseif($interview->status === false): ?>
	<div class="alert alert-danger" role="alert">
		<?=sprintf(__('Colloquio fallito secondo %s'), $interview->recruiter)?>
	</div>
<?php endif ?>

<?php if($interview->when === null): ?>
	<div class="alert alert-warning" role="alert">
		<?=__('Colloquio da fissare')?>
	</div>
<?php else: ?>
	<div class="alert alert-info" role="alert">
		<?=sprintf(__('Colloquio fissato per il %s alle %s con <a href="https://t.me/%s">%s</a>. <a href="%s">🗓 Aggiungi al calendario.</a>'),
			$interview->when->format('Y-m-d'), $interview->when->format('H:i'), $interview->recruitertg,
			$interview->recruiter, $this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri, ['download' => 'true'])))?>
	</div>
<?php endif ?>

<?php if($interview->status === null && !$edit): ?>
	<form method="post">
		<div class="form-group row">
			<label for="recruiter" class="col-md-1"><?=__('Recruiter')?></label>
			<div class="col-md-5">
				<select id="recruiter" name="recruiter" required="required" class="form-control">
					<?php
					$hit = false;
					$therecruiter = $interview->recruiter ?? $user->recruiter;
					foreach($recruiters as $recruiter):
						if($therecruiter === $recruiter[0]):
							$hit = true;
							?>
							<option value="<?=$this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"
									selected><?=$this->e($recruiter[0])?> (@<?=$this->e($recruiter[1])?>)
							</option>
						<?php else: ?>
							<option value="<?=$this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"><?=$this->e($recruiter[0])?> (@<?=$this->e($recruiter[1])?>)</option>
						<?php endif; endforeach; ?>
					<?php if(!$hit): ?>
						<option disabled hidden selected class="d-none"></option>
					<?php endif ?>
				</select>
			</div>
			<label for="when1" class="col-md-1 col-form-label"><?=__('Data')?></label>
			<div class="col-md-2">
				<input type="date" id="when1" name="when1" required="required" class="form-control"
						placeholder="YYYY-MM-DD"
						value="<?=$interview->when === null ? '' : $interview->when->format('Y-m-d')?>">
			</div>
			<label for="when2" class="col-md-1 col-form-label"><?=__('Ora')?></label>
			<div class="col-md-2">
				<input type="time" id="when2" name="when2" required="required" class="form-control" placeholder="HH:MM"
						value="<?=$interview->when === null ? '' : $interview->when->format('H:i')?>">
			</div>
		</div>
		<div class="form-group text-center">
			<button name="setinterview" value="true" type="submit"
					class="btn btn-primary"><?=__('Fissa colloquio')?></button>
			<button name="unsetinterview" value="true" type="submit"
					class="btn btn-outline-danger"><?=__('Annulla colloquio')?></button>
		</div>
	</form>
<?php endif ?>

<?=$this->fetch('userinfo', ['user' => $user, 'edit' => $edit])?>

<?php if(!$edit): ?>
	<div class="form-group">
		<a class="btn btn-outline-secondary"
				href="<?=$this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri,
					['edit' => 'true']))?>"><?=__('Modifica dati')?></a>
	</div>
	<form method="post">
		<div class="form-group">
			<label for="questions"><?=__('Note e domande per il colloquio')?></label>
			<textarea id="questions" name="questions" cols="40" rows="5"
					class="form-control"><?=$this->e($interview->questions)?></textarea>
		</div>
		<div class="form-group">
			<label for="answers"><?=__('Risposte e commenti vari post-colloquio')?></label>
			<textarea id="answers" name="answers" cols="40" rows="10"
					class="form-control"><?=$this->e($interview->answers)?></textarea>
		</div>
		<div class="form-group text-center">
			<?php if($interview->status === null && $interview->recruiter !== null && $interview->when !== null): ?>
				<?php if($interview->hold): ?>
					<button name="popHold" value="true" type="submit"
							class="btn btn-info"><?=__('Togli dalla lista d\'attesa')?></button>
				<?php else: ?>
					<button name="pushHold" value="true" type="submit"
							class="btn btn-info"><?=__('Metti in lista d\'attesa')?></button>
				<?php endif; ?>
				<button name="approve" value="true" type="submit"
						class="btn btn-success"><?=__('Colloquio passato')?></button>
				<button name="reject" value="true" type="submit"
						class="btn btn-danger"><?=__('Colloquio fallito')?></button>
			<?php elseif($interview->recruiter !== null && $interview->when !== null): ?>
				<button name="limbo" value="true" type="submit"
						class="btn btn-warning"><?=__('Rimanda nel limbo')?></button>
			<?php endif ?>
			<button name="save" value="true" type="submit" class="btn btn-outline-primary"><?=__('Salva')?></button>
		</div>
	</form>
	<form method="post">
		<?php if($user->invitelink !== null): ?>
			<div class="alert alert-info" role="alert">
				<?=sprintf(__('Link d\'invito: %s'), $user->invitelink);?>
			</div>
		<?php endif ?>
		<div class="form-group text-center">
			<button name="invite" value="true" type="submit"
					class="btn btn-primary"><?=__('Genera link d\'invito')?></button>
		</div>
	</form>
<?php endif ?>


<script src="resize.js"></script>
