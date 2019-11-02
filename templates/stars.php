<?php
function stars(float $vote): string {
	$rounded = round($vote * 2) / 2; // Rounds to .5, for the half star
	$result = '';
	for($i = 0; $i < floor($rounded); $i++) {
		$result .= '<span class="fas fa-star star-color"></span>';
	}
	if($rounded !== floor($rounded)) {
		$result .= '<span class="fas fa-star-half star-color"></span>';
	}

	return $result;
}