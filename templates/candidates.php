<?php
/** @var $users array */
/** @var $myname string */
/** @var $myuser string */
$this->layout('base', ['title' => __('Candidati'), 'datatables' => true])
?>
<small><?= sprintf(__('Ciao, %s (%s)'), $myname, $myuser) ?></small>
<h1><?=__('Candidati')?></h1>
<table id="candidates" class="table" data-search="true">
	<thead class="thead-dark">
	<tr>
		<th><?=__('Nome')?></th>
		<th data-sortable="true"><?=__('Interesse')?></th>
		<th data-sortable="true"><?=__('Inviato')?></th>
		<th data-sortable="true"><?=__('Recruiter')?></th>
		<th data-sortable="true"><?=__('Stato')?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user):
		$date = date('Y-m-d H:i', $user['submitted']);
		if($user['status'] === null) {
			$statusCell = "<a href=\"/candidates.php?id=${user['id']}\">" . __('Da decidere') . '</a>';
		} else {
			if($user['status'] === true) {
				$statusCell = $user['published'] ? __('Approvata, pubblicata') : '<b>' . __('Approvata, da pubblicare') . '</b>';
			} else {
				$statusCell = $user['published'] ? __('Rifiutata, pubblicata') : '<b>' . __('Rifiutata, da pubblicare') . '</b>';
			}
		}
		if($user['notes']) {
			$statusCell .= ' 📝';
		}
		if($user['status'] === true) {
			$tdcolor = 'class="table-success"';
		} elseif($user['status'] === false) {
			$tdcolor = 'class="table-danger"';
		} else {
			$tdcolor = '';
		}
		if($user['published']) {
			$trcolor = $tdcolor;
		} else {
			$trcolor = '';
		}
		?>
		<tr <?= $trcolor ?>>
			<td><a href="/candidates.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></a></td>
			<td><?= htmlspecialchars($user['area']) ?></td>
			<td><?= $date ?></td>
			<td><?= htmlspecialchars($user['recruiter']) ?></td>
			<td <?= $tdcolor ?>><?= $statusCell ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<div class="btn-toolbar" role="toolbar" aria-label="<?= __('Candidates management toolbar') ?>">
	<div class="input-group mr-3">
		<button type="button" disabled class="btn btn-outline-dark"><?= __('Pubblica rifiutati') ?></button>
	</div>
	<div class="input-group" role="group" aria-label="<?= __('Button group to delete candidates') ?>">
		<button type="button" disabled class="btn btn-outline-danger mr-2"><?= __('Cancella più vecchi di...') ?></button>
		<input type="number" disabled min="0" class="form-control" aria-label="<?=__('Cancella più vecchi di questo numero di giorni')?>" aria-describedby="deletecandidateshelp">
		<div class="input-group-append mr-2">
			<div class="input-group-text"><?=__('giorni')?></div>
		</div>
		<small id="deletecandidateshelp" class="form-text text-muted"><?=__('Solo pubblicati')?></small>
	</div>
</div>
