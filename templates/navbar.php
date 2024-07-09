<?php

/**
 * @var $isAdmin bool
 */
/**
 * @var $authenticated bool
 */
/**
 * @var string $logoHref
 */
/**
 * @var \Psr\Http\Message\UriInterface $globalRequestUri
 */
?>
<nav class="navbar navbar-expand-sm top">
	<div class="container">
		<a class="navbar-brand mr-3" href="<?php echo $this->e($logoHref) ?>">
			<img src="weee.png" height="69" class="img-fluid" alt="WEEE Open">
		</a>
		<div class="ml-auto">
			<a href="language.php?l=en-US&amp;from=<?php echo rawurlencode($globalRequestUri->getPath())?>">en</a>&nbsp;-&nbsp;<a
					href="language.php?l=it-IT&amp;from=<?php echo rawurlencode($globalRequestUri->getPath())?>">it</a>
		</div>
	</div>
</nav>
