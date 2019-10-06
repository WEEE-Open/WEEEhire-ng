<?php
/** @var $users array */
$this->layout('base', ['title' => __('Candidati'), 'datatables' => true])
?>
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
				$statusCell = $user['published'] ? __('Rifiutata, pubblicata') : '<b>' . __('Approvata, da pubblicare') . '</b>';
			}
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
