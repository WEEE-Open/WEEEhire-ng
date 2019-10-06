<?php
/** @var $users array */
$this->layout('base', ['title' => __('Candidati'), 'datatables' => true])
?>
<h1><?=__('Candidati')?></h1>
<table id="candidates" class="table" data-toggle="table">
	<thead class="thead-dark">
	<tr>
		<th><?=__('Nome')?></th>
		<th data-sortable="true"><?=__('Interesse')?></th>
		<th data-sortable="true"><?=__('Inviato')?></th>
		<th data-sortable="true"><?=__('Recruiter')?></th>
		<th data-sortable="true"><?=__('Pubblicato')?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user):
		$date = date('Y-m-d H:i', $user['submitted']);
		if($user['status'] === null) {
			$published = "<a href=\"/candidates.php?id=${user['id']}\">" . __('Da decidere') . '</a>';
		} else {
			$published = $user['published'] ? __('Sì') : '<b>' . __('No') . '</b>';
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
			<td <?= $tdcolor ?>><?= $published ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
