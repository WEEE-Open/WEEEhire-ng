<?php /* @var $isAdmin bool */ ?>
<?php /* @var $authenticated bool */ ?>
<nav class="navbar navbar-expand-sm top">
	<div class="container">
		<a class="navbar-brand mr-3" href="/">
			<img src="weee.png" height="69" class="img-fluid" alt="WEEE Open">
		</a>
		<div class="ml-auto">
			<a href="language.php?l=en-us&from=<?=rawurlencode($_SERVER['REQUEST_URI'])?>">en&nbsp;🇺🇸</a>&nbsp;-&nbsp;<a
					href="language.php?l=it-it&from=<?=rawurlencode($_SERVER['REQUEST_URI'])?>">it&nbsp;🇮🇹</a>
		</div>
	</div>
</nav>
