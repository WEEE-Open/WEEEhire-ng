<?php

/**
 * @var string $name
 */
/**
 * @var string $user
 */
/**
 * @var string $currentFileName
 */
?>
<nav id="adminnavbar" class="navbar navbar-expand-md navbar-light">
	<div class="navbar-brand">
		<small><?php echo sprintf(__('Ciao, %s (%s)'), $name, $user)?></small>
	</div>
	<ul  class="navbar-nav">
		<li class="nav-item <?php echo $currentFileName === 'candidates.php' ? 'active' : '' ?>">
			<a class="nav-link" href="candidates.php"><?php echo __('Candidati')?></a>
		</li>
		<li class="nav-item <?php echo $currentFileName === 'interviews.php' ? 'active' : '' ?>">
			<a class="nav-link" href="interviews.php"><?php echo __('Colloqui')?></a>
		</li>
		<li class="nav-item <?php echo $currentFileName === 'interviewsbyrecruiter.php' ? 'active' : '' ?>">
			<a class="nav-link" href="interviews.php?byrecruiter=true"><?php echo __('Recruiter')?></a>
		</li>
		<li class="nav-item <?php echo $currentFileName === 'settings.php' ? 'active' : '' ?>">
			<a class="nav-link" href="settings.php"><?php echo __('Impostazioni')?></a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="logout.php"><?php echo __('Logout')?></a>
		</li>
	</ul >
</nav>
