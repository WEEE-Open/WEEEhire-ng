<?php
/** @var $expiry int */

$this->layout('base', ['title' => 'WEEElcome']) ?>

<h1><?=__('Entra in WEEE Open')?></h1>
<p><?=__('Compila il questionario per fare richiesta di ammissione in team. Premi il bottone qui sotto per iniziare.')?></p>
<div class="col-md-12 text-center">
	<a class="btn btn-lg btn-primary the-button" href="form.php"><?=__('Inizia')?></a>
</div>
<?php if($_SESSION['locale'] === 'en-us'): ?>
	<p>Il questionario è anche disponibile <a
				href="language.php?l=it-it&from=<?=rawurlencode('/form.php')?>">in Italiano</a></p>
<?php else: ?>
	<p>The form is also available <a href="language.php?l=en-us&from=<?=rawurlencode('/form.php')?>">in English</a></p>
<?php endif ?>
<?php if($expiry !== null): ?>
	<p id="timer"></p>
	<script>
		(function() {
			let expiry = "<?php echo $expiry ?>";
			// Set the date we're counting down to
			let timer = document.getElementById("timer");
			let countDownDate = new Date(expiry * 1000).getTime();

			function countdown() {
				// Get today's date and time
				let now = new Date().getTime();

				// Find the distance between now and the count down date
				let distance = countDownDate - now;

				// Time calculations for days, hours, minutes and seconds
				let days = Math.floor(distance / (1000 * 60 * 60 * 24));
				let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				//let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				//let seconds = Math.floor((distance % (1000 * 60)) / 1000);

				// Display the result in the element with id="demo"
				if(days === 0) {
					if(hours === 0) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra meno di un\'ora, affrettati!') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra 1 ora.') ?>`;
					} else {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra ${hours} ore.') ?>`;
					}
				} else if(days === 1) {
					if(hours === 0) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra 1 giorno.') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra 1 giorno e 1 ora.') ?>`;
					} else {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra 1 giorno e ${hours} ore.') ?>`;
					}
				} else {
					if(hours === 0) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra ${days} giorni.') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra ${days} giorni e 1 ora.') ?>`;
					} else {
						timer.textContent = `<?= __('Le candidature si chiuderanno tra ${days} giorni e ${hours} ore.') ?>`;
					}
				}

				if(distance < 0) {
					clearInterval(x);
				}
			}

			// Update the count down every 10 seconds
			let x = setInterval(countdown, 10000);
			countdown();
		})();
	</script>
<?php endif; ?>
