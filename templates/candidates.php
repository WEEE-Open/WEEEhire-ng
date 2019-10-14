<?php
/** @var $users array */
/** @var $myname string */
/** @var $myuser string */
$this->layout('base', ['title' => __('Candidati'), 'datatables' => true]);
$total = 0;
$approved = 0;
$rejected = 0;
$tobe = 0;
$topublish = 0;
$published = 0;
?>

<?= $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser]) ?>

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
		$total++;
		if($user['status'] === null) {
			$tobe++;
			$statusCell = "<a href=\"/candidates.php?id=${user['id']}\">" . __('Da decidere') . '</a>';
		} else {
			if($user['status'] === true) {
				$approved++;
				$statusCell = $user['published'] ? __('Approvata, pubblicata') : '<b>' . __('Approvata, da pubblicare') . '</b>';
			} else {
				$rejected++;
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
			$published++;
			$trcolor = $tdcolor;
		} else {
			if($user['status'] !== null) {
				$topublish++;
			}
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

<form method="post">
	<div class="form-row mt-3">
		<div class="form-group col-md-2">
			<button type="submit" value="true" name="publishallrejected" class="btn btn-outline-dark"><?= __('Pubblica rifiutati') ?></button>
		</div>
	</div>
</form>

<form method="post">
	<div class="form-row">
		<div class="form-group col-md-2">
			<input name="days" id="deleteolderthandays" type="number" min="0" value="30" class="form-control" required>
		</div>
		<div class="form-group col-md-10">
			<button type="submit" value="true" name="deleteolderthan" class="btn btn-outline-danger mr-2"><?= __('Cancella') ?></button>
			<label for="deleteolderthandays"><?=__('Cancella candidati più vecchi di tot giorni (solo pubblicati)')?></label>
		</div>
	</div>
</form>
<ul class="list-group mt-3">
	<li class="list-group-item"><?= sprintf(_ngettext('%d candidato in totale', '%d candidati totali', $total), $total); ?></li>
	<li class="list-group-item list-group-item-primary"><?= sprintf(_ngettext('%d da valutare', '%d da valutare', $tobe), $tobe); ?></li>
	<li class="list-group-item list-group-item-success"><?= sprintf(_ngettext('%d approvato', '%d approvati', $approved), $approved); ?></li>
	<li class="list-group-item list-group-item-danger"><?= sprintf(_ngettext('%d rifiutato', '%d rifiutati', $rejected), $rejected); ?></li>
	<li class="list-group-item"><?= sprintf(_ngettext('%d da pubblicare', '%d da pubblicare', $topublish), $topublish); ?></li>
	<li class="list-group-item"><?= sprintf(_ngettext('%d pubblicato', '%d pubblicati', $published), $published); ?></li>
</ul>