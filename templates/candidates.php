<?php

/**
 * @var $users array
 */
/**
 * @var $myname string
 */
/**
 * @var $myuser string
 */

use WEEEOpen\WEEEHire\User;

$this->layout('base', ['title' => __('Candidati'), 'datatables' => true, 'fontAwesome' => true, 'logoHref' => 'candidates.php']);
$total = 0;
$approved = 0;
$rejected = 0;
$tobe = 0;
$topublish = 0;
$published = 0;
$hold = 0;
$currentFileName = basename(__FILE__);
require_once 'stars.php';
?>

<?php echo $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser, 'currentFileName' => $currentFileName])?>

<h1><?php echo __('Candidati')?></h1>
<table id="candidates" class="table" data-search="true">
	<thead class="thead-dark">
	<tr>
		<th><?php echo __('Nome')?></th>
		<th data-sortable="true"><?php echo __('Interesse')?></th>
		<th data-sortable="true"><?php echo __('Voto')?></th>
		<th data-sortable="true"><?php echo __('Inviato')?></th>
		<th data-sortable="true"><?php echo __('Recruiter')?></th>
		<th data-sortable="true"><?php echo __('Stato')?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user) :
		/**
 * @var User $user
*/
		$date = date('Y-m-d H:i', $user['submitted']);
		$total++;
		$status = WEEEOpen\WEEEHire\User::computeCandidateStatus($user['published'], $user['status'], $user['hold']);
		$trcolor = '';
		$tdcolor = '';
		$statusCell = '';
		switch ($status) {
			case User::STATUS_NEW:
				$tobe++;
				$statusCell = "<a href=\"/candidates.php?id={$user['id']}\">" . __('Da decidere') . '</a>';
				$tdcolor = '';
				break;
			case User::STATUS_NEW_APPROVED:
				$approved++;
				$topublish++;
				$statusCell = '<b>' . __('Approvata, da pubblicare') . '</b>';
				$tdcolor = 'class="candidates-approved"';
				break;
			case User::STATUS_NEW_REJECTED:
				$rejected++;
				$topublish++;
				$statusCell = '<b>' . __('Rifiutata, da pubblicare') . '</b>';
				$tdcolor = 'class="candidates-rejected"';
				break;
			case User::STATUS_NEW_HOLD:
				$hold++;
				$topublish++;
				$statusCell = "<a href=\"/candidates.php?id={$user['id']}\">" . __('In lista d\'attesa') . '</a>';
				$tdcolor = 'class="candidates-hold"';
				break;
			case User::STATUS_PUBLISHED_APPROVED:
				$approved++;
				$published++;
				$statusCell = __('Approvata, pubblicata');
				$tdcolor = $trcolor = 'class="candidates-approved"';
				break;
			case User::STATUS_PUBLISHED_REJECTED:
				$rejected++;
				$published++;
				$statusCell = __('Rifiutata, pubblicata');
				$tdcolor = $trcolor = 'class="candidates-rejected"';
				break;
			case User::STATUS_PUBLISHED_HOLD:
				$hold++;
				$published++;
				$statusCell = "<a href=\"/candidates.php?id={$user['id']}\">" . __('In lista d\'attesa, pubblicata') . '</a>';
				$tdcolor = $trcolor = 'class="candidates-hold"';
				break;
		}
		$statusCellIcons = '';
		if ($user['myvote'] !== null) {
			$statusCellIcons .= '<span class="fas fa-star text-dark"></span>';
		}
		if ($user['hold']) {
			$statusCellIcons .= '<span class="fas fa-lock text-dark"></span>';
		}

		// if user has note by admin add note icon
		if (!is_null($user['has_note'])) {
			$statusCellIcons .= '<span class="fas fa-sticky-note"></span>';
		}

		if ($statusCellIcons !== '') {
			$statusCell .= '&nbsp;' . $statusCellIcons;
		}
		?>
		<tr <?php echo $trcolor?>>
			<td><a href="/candidates.php?id=<?php echo $user['id'] ?>"><?php echo $this->e($user['name']) ?></a></td>
			<td><?php echo $this->e($user['area'])?></td>
			<td class="stars <?php echo $user['myvote'] === null && $user['evaluation'] !== null ? 'notmine' : '' ?>"><?php echo $user['evaluation'] === null ? '' : sprintf('%3.1f', $user['evaluation']) . '&nbsp;' . stars($user['evaluation'])?></td>
			<td><?php echo $date?></td>
			<td><?php echo $this->e($user['recruiter'])?></td>
			<td <?php echo $tdcolor?>><?php echo $statusCell?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<script>
	(function(){
		"use strict";
		let table = document.getElementById("candidates");

		let found = false;
		let spoilers = [];
		for(let stars of table.querySelectorAll("td.notmine")) {
			let td = document.createElement("td");
			let button = document.createElement("button");
			button.classList.add("btn");
			button.classList.add("btn-secondary");
			button.classList.add("p-1");
			button.classList.add("spoiler");
			button.textContent = "<?php echo __('Mostra (spoiler!)')?>";
			td.appendChild(button);

			spoilers.push(stars);
			button.dataset.spoilerId = (spoilers.length - 1).toString();
			stars.parentNode.insertBefore(td, stars);
			stars.parentNode.removeChild(stars);

			found = true;
		}
		if(found) {
			table.addEventListener("click", function(ev) {
				if(ev.target.tagName === "BUTTON" && ev.target.classList.contains("spoiler")) {
					if(ev.target.dataset.spoilerId) {
						let id = parseInt(ev.target.dataset.spoilerId)
						if(!isNaN(id)) {
							console.log(spoilers);
							let stars = spoilers[id];
							let td = ev.target.parentNode;
							td.parentNode.insertBefore(stars, td);
							td.parentNode.removeChild(td);
						}
					}
				}
			}, false);
		}
	})();
</script>


<form method="post">
	<div class="form-row mt-3">
		<div class="form-group col-md-2">
			<button type="submit" value="true" name="publishallrejected"
					class="btn btn-outline-dark"><?php echo __('Pubblica rifiutati')?></button>
		</div>
	</div>
</form>

<form method="post">
	<div class="form-row">
		<div class="form-group col-md-2">
			<input name="days" id="deleteolderthandays" type="number" min="0" value="30" class="form-control" required>
		</div>
		<div class="form-group col-md-10">
			<button type="submit" value="true" name="deleteolderthan"
					class="btn btn-outline-danger mr-2"><?php echo __('Cancella')?></button>
			<label for="deleteolderthandays"><?php echo __('Cancella candidati più vecchi di tot giorni (solo pubblicati)')?></label>
		</div>
	</div>
</form>
<ul class="list-group mt-3">
	<li class="list-group-item"><?php echo sprintf(_ngettext('%d candidato in totale', '%d candidati totali', $total), $total);?></li>
	<li class="list-group-item list-group-item-primary"><?php echo sprintf(_ngettext('%d da valutare', '%d da valutare', $tobe), $tobe);?></li>
	<li class="list-group-item list-group-item-success"><?php echo sprintf(_ngettext('%d approvato', '%d approvati', $approved), $approved);?></li>
	<li class="list-group-item list-group-item-danger"><?php echo sprintf(_ngettext('%d rifiutato', '%d rifiutati', $rejected), $rejected);?></li>
	<li class="list-group-item"><?php echo sprintf(_ngettext('%d da pubblicare', '%d da pubblicare', $topublish), $topublish);?></li>
	<li class="list-group-item"><?php echo sprintf(_ngettext('%d pubblicato', '%d pubblicati', $published), $published);?></li>
</ul>
