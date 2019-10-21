<?php
/** @var string $name */
/** @var string $user */
?>
<nav id="adminnavbar" class="navbar navbar-expand-md">
	<div class="navbar-brand"><small><?= sprintf(__('Ciao, %s (%s)'), $name, $user) ?></small></div>
	<div class="navbar-nav">
		<a class="nav-item nav-link" href="candidates.php"><?=__('Candidati')?></a>
		<a class="nav-item nav-link" href="interviews.php"><?=__('Colloqui')?></a>
		<a class="nav-item nav-link" href="interviews.php?byrecruiter=true"><?=__('Recruiter')?></a>
		<a class="nav-item nav-link" href="settings.php"><?=__('Impostazioni')?></a>
		<a class="nav-item nav-link" href="logout.php"><?=__('Logout')?></a>
    </div>
</nav>
