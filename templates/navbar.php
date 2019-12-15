<?php
/* @var $isAdmin bool */
/* @var $authenticated bool */
/** @var string $globalRequestUri */
?>
<nav class="navbar navbar-expand-sm top">
	<div class="container">
		<a class="navbar-brand mr-3" href="/">
			<img src="weee.png" height="69" class="img-fluid" alt="WEEE Open">
		</a>
		<div class="ml-auto">
			<a href="language.php?l=en-us&from=<?=rawurlencode($globalRequestUri)?>">en</a>&nbsp;-&nbsp;<a
					href="language.php?l=it-it&from=<?=rawurlencode($globalRequestUri)?>">it</a>
		</div>
	</div>
</nav>
