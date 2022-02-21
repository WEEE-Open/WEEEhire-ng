<?php
/** @var $user WEEEOpen\WEEEhire\User */
/** @var $edit bool */
/** @var $recruiters string[][] */
/** @var $evaluations string[][] */
/** @var $uid string */
/** @var $cn string */
/** @var \Psr\Http\Message\UriInterface $globalRequestUri */
/** @var array $notes */

$titleShort = sprintf(__('%s %s (%s)'), $this->e($user->name), $this->e($user->surname), $this->e($user->matricola));
$title = sprintf(__('%s - Candidatura'), $titleShort);
$this->layout('base', ['title' => $title, 'fontAwesome' => true, 'logoHref' => 'candidates.php']);
require_once 'stars.php';
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="candidates.php"><?=__('Candidati')?></a></li>
		<li class="breadcrumb-item active" aria-current="page"><?=$titleShort?></li>
	</ol>
</nav>

<?php if ($user->status === true) : ?>
	<div class="alert alert-success" role="alert">
		<?=sprintf(
			__('Candidatura approvata, <a href="%s">passa al colloquio</a>'),
			'interviews.php?id=' . $user->id
		) // It's an int, no risks here ?>
	</div>
<?php elseif ($user->status === false) : ?>
	<div class="alert alert-danger" role="alert">
		<?=__('Candidatura rifiutata')?>
	</div>
<?php elseif ($user->status === null && $user->hold === true) : ?>
	<div class="alert alert-warning" role="alert">
		<?=__('Candidatura rimandata')?>
	</div>
<?php endif ?>
<?php if ($user->published) : ?>
	<div class="alert alert-info" role="alert">
		<?=__('Risultati pubblicati, ti consiglio di non modificarli')?>
	</div>
<?php endif ?>

<?=$this->fetch('userinfo', ['user' => $user, 'edit' => $edit, 'evaluations' => $evaluations, 'uid' => $uid])?>

<?php if (!$edit) :
	$total = 0;
	foreach ($evaluations as $evaluation) {
		$total += $evaluation['vote'];
	}
	if (count($evaluations) > 0) {
		$avg = round($total / count($evaluations), 2);
	}
	$voted = false;
	foreach ($evaluations as $evaluation) {
		if ($evaluation['id_evaluator'] === $uid) {
			$voted = true;
			break;
		}
	}
	?>
<div class="row">
	<div class="col"><h4><?=__('Valutazioni')?></h4></div>
	<?php if (count($evaluations) === 0) : ?>
		<div id="votesresult" class="col"></div>
	<?php else : ?>
		<div id="votesresult" class="col"><p class="text-right"><?=sprintf(__('Valutazione:&nbsp;%s&nbsp;%s'), $avg, stars($avg))?></p>
		</div>
	<?php endif ?>
</div>
<table id="votestable" class="table table-striped">
	<thead>
	<tr>
		<th scope="col"><?=__('Nome valutatore')?></th>
		<th scope="col"><?=__('Voto')?></th>
		<th scope="col" class="d-none d-md-table-cell"><?=__('Data')?></th>
		<th scope="col"><?=__('Azioni')?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($evaluations as $evaluation) : ?>
		<tr class="voterow">
			<td><?=sprintf(__('%s (%s)'), $evaluation['name_evaluator'], $evaluation['id_evaluator'])?></td>
			<td class="align-middle"><?=$evaluation['vote']?>&nbsp;<?=stars($evaluation['vote'])?></td>
			<td class="d-none d-md-table-cell"><?=date('Y-m-d H:i', $evaluation['date'])?></td>
			<td>
				<form method="post">
					<input type="hidden" name="id_evaluation" value="<?=$evaluation['id_evaluation']?>" />
					<button type="submit" name="unvote"
							class="btn btn-outline-danger btn-sm"><?=__('Elimina 🗑')?></button>
				</form>
			</td>
		</tr>
	<?php endforeach; ?>
	<?php if (!$voted) : ?>
		<tr id="showvotesbuttonrow">
			<td colspan="4" class="text-center"><button id="showvotesbutton" class="btn btn-secondary"><?= __('Mostra (spoiler!)') ?></button></td>
		</tr>
		<script>
			"use strict";
			let vt = document.getElementById("votestable");
			let vr = document.getElementById("votesresult");
			for(let el of vt.querySelectorAll(".voterow")) {
				el.classList.add("d-none");
			}
			vr.classList.add("d-none");

			document.getElementById("showvotesbutton").onclick = function() {
				for(let el of vt.querySelectorAll(".voterow")) {
					el.classList.remove("d-none");
				}
				vr.classList.remove("d-none");
				let deleteme = document.getElementById("showvotesbuttonrow");
				deleteme.parentNode.removeChild(deleteme);
			}
		</script>
		<tr class="myvoterow">
			<td><?=sprintf(__('%s (%s)'), $cn, $uid)?></td>
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
							<button type="submit" name="voteButton" value="true"
									class="btn btn-outline-primary"><?=__('Vota')?></button>
						</div>
					</div>
				</form>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>

<h4 class="mt-5"><?= __('Note') ?></h4>
	<?= $this->fetch('notes', ['notes' => $notes]); ?>
	<?php
	$userNoted = false;
	foreach ($notes as $note) {
		$userNoted = $_SESSION['uid'] === $note['uid'];
		if ($userNoted) {
			break;
		}
	}
	?>

<form method="post" class="mt-3">
	<?php if (!$userNoted) : ?>
	<div class="form-group">
		<label for="notes"><b><?= __('Aggiungi nota') ?></b></label>
		<textarea id="notes" name="note" cols="40" rows="3"
				class="form-control" required></textarea>
	</div>
	<div class="form-group text-center">
		<button name="saveNote" value="true" type="submit"
				class="btn btn-outline-primary my-1 mx-1"><?=__('Aggiungi nota')?></button>
	<?php else : ?>
		<!-- open div bacause there is close div tag in below -->
		<div class="form-group text-center">
	<?php endif; ?>
		<a class="btn btn-outline-secondary my-1 mx-1"
				href="<?=$this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl(
					$globalRequestUri,
					['edit' => 'true']
				))?>"><?=__('Modifica dati')?></a>
	</div>
	<div class="form-group text-center justify-content-center row">
		<div class="btn-toolbar">
			<a href="/candidates.php?id=<?= (int) $user->prev_not_evaluated_user ?>" class="btn btn-outline-primary mr-1 ml-1 <?= $user->prev_not_evaluated_user == null ? 'disabled' : '' ?>"><span class="fas fa-arrow-circle-left"></span>&nbsp;<?=__('Precedente da valutare')?></a>
			<a href="/candidates.php?id=<?= (int) $user->prev_user ?>" class="btn btn-outline-secondary mr-1 ml-1 <?= $user->prev_user == null ? 'disabled' : '' ?>"><span class="fas fa-arrow-circle-left"></span>&nbsp;<?=__('Precedente')?></a>
			<a href="/candidates.php?id=<?= (int) $user->next_user ?>" class="btn btn-outline-secondary mr-1 ml-1 <?= $user->next_user == null ? 'disabled' : '' ?>"><?= __('Successivo') ?>&nbsp;<span class="fas fa-arrow-circle-right"></span></a>
			<a href="/candidates.php?id=<?= (int) $user->next_not_evaluated_user ?>" class="btn btn-outline-primary mr-1 ml-1 <?= $user->next_not_evaluated_user == null ? 'disabled' : '' ?>"><?= __('Successivo da valutare') ?>&nbsp;<span class="fas fa-arrow-circle-right"></span></a>
		</div>

	</div>
	<?php $status = $user->getCandidateStatus(); ?>
	<?php if ($status === \WEEEOpen\WEEEHire\User::STATUS_NEW_HOLD || $status === \WEEEOpen\WEEEHire\User::STATUS_PUBLISHED_HOLD) : ?>
	<div class="form-group">
		<label for="visiblenotes"><b><?=__('Motivazioni (visibili alla persona interessata)')?></b></label>
		<textarea id="visiblenotes" name="visiblenotes" cols="40" rows="3"
				class="form-control"><?=$this->e($user->visiblenotes)?></textarea>
	</div>
	<?php endif ?>
	<div class="form-group text-center">
		<?php switch ($status) :
			default:
			case \WEEEOpen\WEEEHire\User::STATUS_NEW:
				?>
			<button name="approve" value="true" type="submit"
					class="btn btn-success my-1 mx-1"><?=__('Approva candidatura')?></button>
			<button name="reject" value="true" type="submit"
					class="btn btn-danger my-1 mx-1"><?=__('Rifiuta candidatura')?></button>
			<button name="holdon" value="true" type="submit"
					class="btn btn-secondary my-1 mx-1"><?=__('Metti in lista d\'attesa')?></button>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_NEW_APPROVED:
				?>
			<button name="limbo" value="true" type="submit"
					class="btn btn-warning my-1 mx-1"><?=__('Rimanda nel limbo')?></button>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_NEW_REJECTED:
				?>
			<button name="publishnow" value="true" type="submit"
					class="btn btn-primary my-1 mx-1"><?=__('Pubblica')?></button>
			<button name="limbo" value="true" type="submit"
					class="btn btn-warning my-1 mx-1"><?=__('Rimanda nel limbo')?></button>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_NEW_HOLD:
				?>
		<!-- TODO: add stuff for email, remove "publish now" (or leave it to skip the email) -->
			<button name="publishnow" value="true" type="submit"
					class="btn btn-primary my-1 mx-1"><?=__('Pubblica')?></button>
			<button name="holdoff" value="true" type="submit"
					class="btn btn-secondary my-1 mx-1"><?=__('Togli dalla lista d\'attesa')?></button>
			<button name="savevisiblenotes" value="true" type="submit"
					class="btn btn-outline-primary my-1 mx-1"><?=__('Salva motivazioni')?></button>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_PUBLISHED_APPROVED:
				?>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_PUBLISHED_REJECTED:
				?>
			<button name="holdon" value="true" type="submit"
					class="btn btn-secondary my-1 mx-1"><?=__('Metti in lista d\'attesa')?></button>
				<?php
				break;
			case \WEEEOpen\WEEEHire\User::STATUS_PUBLISHED_HOLD:
				?>
			<button name="approvefromhold" value="true" type="submit"
					class="btn btn-success my-1 mx-1"><?=__('Approva candidatura')?></button>
			<button name="reject" value="true" type="submit"
						class="btn btn-danger my-1 mx-1"><?=__('Rifiuta candidatura definitivamente')?></button>
			<button name="savevisiblenotes" value="true" type="submit"
					class="btn btn-outline-warning my-1 mx-1"><?=__('Salva motivazioni')?></button>
				<?php
				break;
		endswitch; ?>
	</div>
</form>
<?php endif ?>
<?php if (!$edit && !$user->emailed && $user->status === true) : ?>
	<form method="post">
		<div class="form-group">
			<label for="recruiter"><?=__('Recruiter')?></label>
			<select id="recruiter" name="recruiter" required="required" class="form-control">
				<?php
				$hit = false;
				foreach ($recruiters as $recruiter) :
					if ($user->recruiter === $recruiter[0]) :
						$hit = true;
						?>
						<option value="<?=$this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"
								selected><?=$this->e($recruiter[0])?> (@<?=$this->e($recruiter[1])?>)
						</option>
					<?php else : ?>
						<option value="<?=$this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"><?=$this->e($recruiter[0])?> (@<?=$this->e($recruiter[1])?>)</option>
					<?php endif;
				endforeach; ?>
				<?php if (!$hit) : ?>
					<option disabled hidden selected class="d-none"></option>
				<?php endif ?>
			</select>
		</div>
		<div class="form-group">
			<label><?=__('Lingua e template')?></label>
			<div class="row">
				<div class="col-md-3 col-lg-2">
					<button class="btn btn-outline-secondary mr-1 mb-2 mr-md-0 mb-md-0" id="email-it-btn">it-IT</button>
					<button class="btn btn-outline-secondary mb-2 mb-md-0" id="email-en-btn">en-US</button>
				</div>
				<div class="col-md-9 col-lg-10">
				<select aria-label="<?=__('Template')?>" class="custom-select" id="email-custom-select" onchange="templatize()">
					<option value="default" selected>Email standard</option>
					<option value="programmer">Programmatore</option>
					<option value="sysadmin">Sysadmin</option>
					<!--                  <option value="repairs" disabled>Riparatore</option>-->
					<!--                  <option value="electronics" disabled>Elettronico</option>-->
					<!--                  <option value="digital-creator" disabled>Crezione contenuti digitali</option>-->
					<!--                  <option value="creative-reuse" disabled>Riuso creativo</option>-->
				</select>
				</div>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-md-2 col-lg-1 col-form-label" for="subject"><b><?=__('Oggetto')?></b></label>
			<div class="col-md-10 col-lg-11">
				<input type="text" id="subject" name="subject" class="form-control" required>
			</div>
		</div>
		<div class="form-group">
			<label for="email"><b><?=__('Email')?></b></label>
			<textarea id="email" name="email" rows="10" class="form-control" required></textarea>
		</div>
		<div class="form-group text-center">
			<button name="publishnow" value="true" type="submit"
					class="btn btn-primary my-1 mx-1"><?=__('Pubblica e manda email')?></button>
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
			// remove nickname between "" from email signature
			let name = recruiter_split[1].split(' ').filter(str => !str.includes('"')).join(" ");
			let telegram = recruiter_split[0];
			let emailVariant = document.getElementById('email-custom-select').value;

			// awful code formatting --> good email formatting
			const defaultEmailText = {
				'it-IT': {
					'subject': 'Colloquio per Team WEEE Open',
					'beginning': `Ciao ${firstname},

Ci fa piacere il tuo interesse per il nostro progetto!
Abbiamo valutato la tua candidatura e ora vorremmo scambiare due parole in maniera più diretta con te, sia per discutere delle attività che potresti svolgere nel Team, sia in modo che tu possa farci domande, se vuoi.
Poiché utilizziamo Telegram per coordinare tutte le attività del team, ti chiedo di contattarmi lì: il mio username è @${telegram}, scrivimi pure.`,
					'end': `

A presto,
${name}
Team WEEE Open`
				},
				'en-US': {
					'subject': 'Interview for WEEE Open Team',
					'beginning': `Hi ${firstname},

We are glad that you are interested in our project!
We read your application and we would like to meet you in person to discuss about the activities that you could do within the Team, and to let you ask some questions if you have any.
Since we use Telegram for all the communications between team members, I'd like you to contact me there: my username is @${telegram}.`,
					'end': `

See you soon,
${name}
Team WEEE Open`
				},
			};

			const customEmailText = {
				'it-IT': {
					'default': "",
					'programmer': `
Potremmo chiederti di fare un breve esercizio di programmazione. Prepara il tuo ambiente di sviluppo o editor di testo preferito!`,
					'sysadmin': `
Potremmo chiederti di svolgere qualche esercizio da terminale, ricorda di averne uno pronto!`
				},
				'en-US': {
					'default': "",
					'programmer': `
We may ask you to do some live coding. Prepare your own favourite IDE or text editor!`,
					'sysadmin': `
We may ask you to do some terminal exercises, remember to have one ready!`
				},
			};

			subject.value = defaultEmailText[lang]['subject'];
			mail.value = defaultEmailText[lang]['beginning'];
			mail.value += customEmailText[lang][emailVariant];
			mail.value += defaultEmailText[lang]['end'];

			mail.dispatchEvent(new Event('input'));
		}

		recruiter.addEventListener('change', templatize.bind(null));
		templatize();
	</script>
<?php elseif ($user->emailed && $user->published && $user->status === true) : ?>
	<div class="alert alert-info" role="alert">
		<?=sprintf(__('Mail inviata da %s'), $user->recruiter);?>
	</div>
<?php endif ?>
<?php if (!$edit && $user->status === true) : ?>
	<?php if ($user->invitelink !== null) : ?>
		<div class="alert alert-info" role="alert">
			<?=sprintf(__('Link d\'invito: %s'), $user->invitelink);?>
		</div>
	<?php endif ?>
<?php endif ?>
<script src="resize.js"></script>
