<?php /* @var $isAdmin bool */ ?>
<?php /* @var $authenticated bool */ ?>
<nav class="navbar navbar-expand-sm top">
	<div class="container">
	    <a class="navbar-brand mr-auto" href="/">
		    <img src="weee.png" height="69" class="img-responsive" alt="WEEE Open">
	    </a>
		<div>
			<a href="language.php?l=en-us&from=<?= rawurlencode($_SERVER['REQUEST_URI']) ?>">en&nbsp;🇺🇸</a>&nbsp;-&nbsp;<a href="language.php?l=it-it&from=<?= rawurlencode($_SERVER['REQUEST_URI']) ?>">it&nbsp;🇮🇹</a>
		</div>
	    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="navbar-toggler-icon"></span>
	    </button>
	</div>
</nav>
