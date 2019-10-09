<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
/** @var $recruiters string[][] */
$titleShort = sprintf(__('%s %s (%s)'), htmlspecialchars($user->name), htmlspecialchars($user->surname), htmlspecialchars($user->matricola));
$title = sprintf(__('Candidatura di %s'), $titleShort);
$this->layout('base', ['title' => $title]);
if(isset($edit) && $edit) {
	$readonly = '';
} else {
	$readonly = 'readonly';
}
?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="candidates.php"><?= __('Candidati') ?></a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= $titleShort ?></li>
	</ol>
</nav>

<?php if($user->status === true): ?>
<div class="alert alert-success" role="alert">
	<?= __('Candidatura approvata') ?>
</div>
<?php elseif($user->status === false): ?>
	<div class="alert alert-danger" role="alert">
		<?= __('Candidatura rifiutata') ?>
	</div>
<?php endif ?>
<?php if($user->published): ?>
<div class="alert alert-info" role="alert">
	<?= __('Risultati pubblicati, ti consiglio di non modificarli') ?>
</div>
<?php endif ?>

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
		<button type="submit" class="btn btn-primary"><?=__('Aggiorna dati')?></button>
		<a class="btn btn-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => null])) ?>"><?=__('Annulla')?></a>
	</div>
	<?php endif ?>
</form>
<?php if(!$edit): ?>
<form method="post">
	<div class="form-group">
		<label for="notes"><b><?= __('Note') ?></b></label>
		<textarea id="notes" name="notes" cols="40" rows="3" class="form-control"><?= htmlspecialchars($user->notes) ?></textarea>
	</div>
	<div class="form-group text-center">
		<?php if(!$user->published): ?>
			<?php if($user->status !== null): ?>
				<button name="limbo" value="true" type="submit" class="btn btn-warning"><?=__('Rimanda nel limbo')?></button>
				<?php if($user->status === false): ?>
					<button name="publishnow" value="true" type="submit" class="btn btn-primary"><?=__('Pubblica')?></button>
				<?php endif ?>
				<?php else: ?>
				<button name="approve" value="true" type="submit" class="btn btn-success"><?=__('Approva candidatura')?></button>
				<button name="reject" value="true" type="submit" class="btn btn-danger"><?=__('Rifiuta candidatura')?></button>
			<?php endif ?>
		<?php endif ?>
		<button name="save" value="true" type="submit" class="btn btn-outline-primary"><?=__('Salva note')?></button>
		<a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'], ['edit' => 'true'])) ?>"><?=__('Modifica dati')?></a>
	</div>
</form>
<?php endif ?>
<?php if(!$edit && !$user->emailed && $user->status === true): ?>
	<form method="post">
		<div class="form-group">
			<label for="recruiter"><?= __('Recruiter') ?></label>
			<select id="recruiter" name="recruiter" required="required" class="form-control">
				<?php
				$hit = false;
				foreach($recruiters as $recruiter):
					if($user->recruiter === $recruiter[0]):
						$hit = true;
					?>
						<option value="<?= htmlspecialchars($recruiter[1]) . '|' . htmlspecialchars($recruiter[0]) ?>" selected><?= htmlspecialchars($recruiter[0]) ?> (@<?= htmlspecialchars($recruiter[1]) ?>)</option>
					<?php else:	?>
						<option value="<?= htmlspecialchars($recruiter[1]) . '|' . htmlspecialchars($recruiter[0]) ?>"><?= htmlspecialchars($recruiter[0]) ?> (@<?= htmlspecialchars($recruiter[1]) ?>)</option>
					<?php endif; endforeach; ?>
				<?php if(!$hit): ?>
				<option disabled hidden selected class="d-none"></option>
				<?php endif ?>
			</select>
		</div>
		<div class="form-group row">
			<label class="col-md-2 col-lg-1 col-form-label" for="subject"><b><?=__('Oggetto')?></b></label>
			<div class="col-md-8 col-lg-9">
				<input type="text" id="subject" name="subject" class="form-control" required>
			</div>
			<div class="col-md-2 text-right">
				<button class="btn btn-outline-secondary" id="email-it-btn">it-IT</button>
				<button class="btn btn-outline-secondary" id="email-en-btn">en-US</button>
			</div>
		</div>
		<div class="form-group">
			<label for="email"><b><?= __('Email') ?></b></label>
			<textarea id="email" name="email" rows="10" class="form-control" required></textarea>
		</div>
		<div class="form-group text-center">
			<button name="publishnow" value="true" type="submit" class="btn btn-primary"><?=__('Pubblica e manda email')?></button>
		</div>
	</form>
	<script>
		let recruiter = document.getElementById('recruiter');
		let mail = document.getElementById('email');
		let subject = document.getElementById('subject');
		let firstname = document.getElementById('name').value;
		let lang = 'it-IT';
		document.getElementById('email-it-btn').addEventListener('click', (e) => {e.preventDefault(); lang = 'it-IT'; templatize();});
		document.getElementById('email-en-btn').addEventListener('click', (e) => {e.preventDefault(); lang = 'en-US'; templatize();});
		function templatize() {
			if(recruiter.value === '') {
				mail.value = '';
				return;
			}
			let recruiter_split = recruiter.value.split('|', 2);
			let name = recruiter_split[1];
			let telegram = recruiter_split[0];
			if(lang === 'it-IT') {
				subject.value = 'Colloquio per Team WEEE Open';
				mail.value = `Ciao ${firstname},

Ci fa piacere il tuo interesse per il nostro progetto!
Abbiamo valutato la tua candidatura e ora vorremmo scambiare due parole in maniera più diretta con te, sia per discutere delle attività che potresti svolgere nel Team, sia in modo che tu possa farci domande, se vuoi.
Poiché utilizziamo Telegram per coordinare tutte le attività del team, ti chiedo di contattarmi lì: il mio username è @${telegram}, scrivimi pure.

A presto,
${name}
Team WEEE Open
`;
			} else if(lang === 'en-US') {
				subject.value = 'Interview for WEEE Open Team';
				mail.value = `Hi ${firstname},

We are glad that you are interested in our project!
We read your application and we would like to meet you in person to discuss about the activities that you could do within the Team, and to let you ask some questions if you have any.
Since we use Telegram for all the communications between team members, I'd like you to contact me there: my username is @${telegram}.

See you soon,
${name}
Team WEEE Open
`;
			}}
		recruiter.addEventListener('change', templatize.bind(null));
		templatize();
	</script>
<?php elseif($user->emailed && $user->published && $user->status === true): ?>
	<div class="alert alert-info" role="alert">
		<?= sprintf(__('Mail inviata da %s'), $user->recruiter); ?>
	</div>
<?php endif ?>
<?php if(!$edit && $user->status === true): ?>
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
