<?php

/** @var string $name */
/** @var string $user */
/** @var string $currentFileName */
?>
<nav id="adminnavbar" class="navbar navbar-expand-md navbar-light">
	<div class="navbar-brand">
		<small><?=sprintf(__('Ciao, %s (%s)'), $name, $user)?></small>
	</div>
	<ul  class="navbar-nav">
		<li class="nav-item <?= $currentFileName === 'candidates.php' ? 'active' : '' ?>">
			<a class="nav-link" href="candidates.php"><?=__('Candidati')?></a>
		</li>
		<li class="nav-item <?= $currentFileName === 'interviews.php' ? 'active' : '' ?>">
			<a class="nav-link" href="interviews.php"><?=__('Colloqui')?></a>
		</li>
		<li class="nav-item <?= $currentFileName === 'interviewsbyrecruiter.php' ? 'active' : '' ?>">
			<a class="nav-link" href="interviews.php?byrecruiter=true"><?=__('Recruiter')?></a>
		</li>
		<li class="nav-item <?= $currentFileName === 'settings.php' ? 'active' : '' ?>">
			<a class="nav-link" href="settings.php"><?=__('Impostazioni')?></a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="logout.php"><?=__('Logout')?></a>
		</li>
	</ul >
</nav>
