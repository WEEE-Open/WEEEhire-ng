<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
/** @var $recruiters string[][] */
/** @var $evaluations string[][] */
/** @var $uid string */
/** @var $cn string */

$titleShort = sprintf(__('%s %s (%s)'), $this->e($user->name), $this->e($user->surname), $this->e($user->matricola));
$title = sprintf(__('%s - Candidatura'), $titleShort);
$this->layout('base', ['title' => $title, 'fontAwesome' => true]);
require_once 'stars.php';
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="candidates.php"><?=__('Candidati')?></a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$titleShort?></li>
	</ol>
</nav>

<?php if($user->status === true): ?>
	<div class="alert alert-success" role="alert">
		<?=sprintf(__('Candidatura approvata, <a href="%s">passa al colloquio</a>'),
			'interviews.php?id=' . $user->id) // It's an int, no risks here ?>
	</div>
<?php elseif($user->status === false): ?>
	<div class="alert alert-danger" role="alert">
		<?=__('Candidatura rifiutata')?>
	</div>
<?php endif ?>
<?php if($user->published): ?>
	<div class="alert alert-info" role="alert">
		<?=__('Risultati pubblicati, ti consiglio di non modificarli')?>
	</div>
<?php endif ?>

<?=$this->fetch('userinfo', ['user' => $user, 'edit' => $edit, 'evaluations' => $evaluations, 'uid' => $uid])?>

<?php if(!$edit):
	$total = 0;
	foreach($evaluations as $evaluation) {
		$total += $evaluation['vote'];
	}
	if(count($evaluations) > 0) {
		$avg = round($total / count($evaluations), 2);
	}
	$voted = false;
	foreach($evaluations as $evaluation) {
		if($evaluation['id_evaluator'] === $uid) {
			$voted = true;
			break;
		}
	}
	?>
	<div class="row">
		<div class="col"><h4><?=__('Valutazioni')?></h4></div>
		<?php if(count($evaluations) === 0): ?>
			<div class="col"></div>
		<?php else: ?>
			<div class="col"><p class="text-right"><?=sprintf(__('Valutazione:&nbsp;%s&nbsp;%s'), $avg, stars($avg)) ?></p></div>
		<?php endif ?>
	</div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th scope="col"><?=__('Nome valutatore')?></th>
			<th scope="col"><?=__('Voto')?></th>
			<th scope="col"><?=__('Data')?></th>
			<th scope="col"><?=__('Azioni')?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($evaluations as $evaluation): ?>
			<tr>
				<td><?= sprintf(__('%s (%s)'), $evaluation['name_evaluator'], $evaluation['id_evaluator']) ?></td>
				<td class="align-middle"><?= $evaluation['vote'] ?>&nbsp;</td>
				<td><?= date('Y-m-d H:i', $evaluation['date']) ?></td>
				<td>
					<form method="post">
						<input type="hidden" name="id_evaluation" value="<?= $evaluation['id_evaluation'] ?>" />
						<button type="submit" name="deleted" class="btn btn-outline-danger btn-sm"><?= __('Elimina 🗑') ?></button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php if(!$voted): ?>
		<tr>
			<td><?= sprintf(__('%s (%s)'), $cn, $uid) ?></td>
			<td colspan="3">
				<form method="post">
					<div class="form-row row">
						<label for="FormControlVote" class="sr-only"><?=__('Voto')?></label>
						<div class="col-8">
							<select name="vote" class="form-control star-color" id="FormControlVote">
								<option value="1">1 ★</option>
								<option value="2">2 ★★</option>
								<option value="3">3 ★★★</option>
								<option value="4">4 ★★★★</option>
								<option value="5">5 ★★★★★</option>
							</select>
						</div>
						<div class="col-4">
							<button type="submit" name="voted" class="btn btn-outline-primary"><?=__('Vota')?></button>
						</div>
					</div>
				</form>
			</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<form method="post">
		<div class="form-group">
			<label for="notes"><b><?=__('Note')?></b></label>
			<textarea id="notes" name="notes" cols="40" rows="3"
					class="form-control"><?=$this->e($user->notes)?></textarea>
		</div>
		<div class="form-group text-center">
			<?php if(!$user->published): ?>
				<?php if($user->status !== null): ?>
					<button name="limbo" value="true" type="submit"
							class="btn btn-warning"><?=__('Rimanda nel limbo')?></button>
					<?php if($user->status === false): ?>
						<button name="publishnow" value="true" type="submit"
								class="btn btn-primary"><?=__('Pubblica')?></button>
					<?php endif ?>
				<?php else: ?>
					<button name="approve" value="true" type="submit"
							class="btn btn-success"><?=__('Approva candidatura')?></button>
					<button name="reject" value="true" type="submit"
							class="btn btn-danger"><?=__('Rifiuta candidatura')?></button>
				<?php endif ?>
			<?php elseif($user->published && $user->status === false): ?>
				<?php if($user->hold): ?>
					<button name="approvefromhold" value="true" type="submit"
							class="btn btn-success"><?=__('Approva candidatura')?></button>
					<button name="holdoff" value="true" type="submit"
							class="btn btn-secondary"><?=__('Togli dalla lista d\'attesa')?></button>
				<?php else: ?>
					<button name="holdon" value="true" type="submit"
							class="btn btn-secondary"><?=__('Metti in lista d\'attesa')?></button>
				<?php endif ?>
			<?php endif ?>
			<button name="save" value="true" type="submit"
					class="btn btn-outline-primary"><?=__('Salva note')?></button>
			<a class="btn btn-outline-secondary"
					href="<?=$this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($_SERVER['REQUEST_URI'],
						['edit' => 'true']))?>"><?=__('Modifica dati')?></a>
		</div>
	</form>
<?php endif ?>
<?php if(!$edit && !$user->emailed && $user->status === true): ?>
	<form method="post">
		<div class="form-group">
			<label for="recruiter"><?=__('Recruiter')?></label>
			<select id="recruiter" name="recruiter" required="required" class="form-control">
				<?php
				$hit = false;
				foreach($recruiters as $recruiter):
					if($user->recruiter === $recruiter[0]):
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
			<label for="email"><b><?=__('Email')?></b></label>
			<textarea id="email" name="email" rows="10" class="form-control" required></textarea>
		</div>
		<div class="form-group text-center">
			<button name="publishnow" value="true" type="submit"
					class="btn btn-primary"><?=__('Pubblica e manda email')?></button>
		</div>
	</form>
	<script>
		let recruiter = document.getElementById('recruiter');
		let mail = document.getElementById('email');
		let subject = document.getElementById('subject');
		let firstname = document.getElementById('name').value;
		let lang = 'it-IT';
		document.getElementById('email-it-btn').addEventListener('click', (e) => {
			e.preventDefault();
			lang = 'it-IT';
			templatize();
		});
		document.getElementById('email-en-btn').addEventListener('click', (e) => {
			e.preventDefault();
			lang = 'en-US';
			templatize();
		});

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
			}
			mail.dispatchEvent(new Event('input'));
		}

		recruiter.addEventListener('change', templatize.bind(null));
		templatize();
	</script>
<?php elseif($user->emailed && $user->published && $user->status === true): ?>
	<div class="alert alert-info" role="alert">
		<?=sprintf(__('Mail inviata da %s'), $user->recruiter);?>
	</div>
<?php endif ?>
<?php if(!$edit && $user->status === true): ?>
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