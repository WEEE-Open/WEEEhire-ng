<?php
/** @var $interviews \DateTime[][][]|string[][][] */
/** @var $myname string */
/** @var $myuser string */
$this->layout('base', ['title' => __('Recruiter')]);

$later = [];
$prevdate = null;
?>

<?=$this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser])?>

<h1><?=__('Recruiter')?></h1>

<?php
foreach($interviews as $interviewer => $ints) {
	echo '<h4 class="col-md-9 col-lg-6 mx-auto">' . $this->e($interviewer) . '</h4><ul class="list-group list-group-flush col-md-9 col-lg-6 mx-auto mb-3">';
	$prevdate = null;
	foreach($ints as $int) {
		$date = $int['when']->format('Y-m-d');
		$time = $int['when']->format('H:i');
		if($int['status'] === null) {
			$statusClass = '';
		} else {
			if($int['status']) {
				$statusClass = 'list-group-item-success';
			} else {
				$statusClass = 'list-group-item-danger';
			}
		}
		if($date !== $prevdate) {
			$prevdate = $date;
			?>
			<li class="list-group-item list-group-item-secondary">
				<?=sprintf(__('Giorno %s (%s)'), $date, $this->fetch('day', ['day' => $int['when']->format('N')]))?>
			</li>
			<?php
		}
		?>
		<li class="list-group-item d-flex justify-content-between align-items-center <?=$statusClass?>">
			<span><?=sprintf(__('<a href="interviews.php?id=%d">%s</a> (%s)'), $int['id'], $this->e($int['name']),
					$this->e($int['area']))?></span>
			<span class="badge badge-secondary badge-pill"><?=$time?></span>
		</li>
		<?php
	}
	echo '</ul>';
}
?>
