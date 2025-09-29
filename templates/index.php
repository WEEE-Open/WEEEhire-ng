<?php
/**
 * @var $expiry int
 * @var $positions array
 */

use Michelf\Markdown;

$this->layout('base', ['title' => 'WEEElcome']) ?>

<h1><?php echo __('Entra in WEEE Open')?></h1>
<p><?php echo __('Compila il questionario per fare richiesta di ammissione in team. Premi il bottone qui sotto per iniziare.')?></p>
<?php //$this->insert('covid') ?>
<div class="col-md-12 d-flex flex-column flex-md-row mb-2 justify-content-center">
	<?php if ($_SESSION['locale'] === 'en-US') : ?>
		<a class="btn btn-lg btn-primary mx-2 mb-2 ms-md-2" href="form.php">Begin</a><a class="btn btn-lg btn-outline-secondary mx-2 mb-2 ms-md-2" href="language.php?l=it-IT&from=<?= rawurlencode('/form.php')?>">Inizia</a>
	<?php else : ?>
		<a class="btn btn-lg btn-primary mx-2 mb-2 ms-md-2" href="form.php">Inizia</a><a class="btn btn-lg btn-outline-secondary mx-2 mb-2 ms-md-2" href="language.php?l=en-US&from=<?= rawurlencode('/form.php')?>">Begin</a>
	<?php endif; ?>
</div>
<?php if ($expiry !== null) : ?>
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
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra meno di un\'ora, affrettati!') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra 1 ora.') ?>`;
					} else {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra ${hours} ore.') ?>`;
					}
				} else if(days === 1) {
					if(hours === 0) {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra 1 giorno.') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra 1 giorno e 1 ora.') ?>`;
					} else {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra 1 giorno e ${hours} ore.') ?>`;
					}
				} else {
					if(hours === 0) {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra ${days} giorni.') ?>`;
					} else if(hours === 1) {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra ${days} giorni e 1 ora.') ?>`;
					} else {
						timer.textContent = `<?php echo __('Le candidature si chiuderanno tra ${days} giorni e ${hours} ore.') ?>`;
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
<h2 class="pb-3"><?=__('Posizioni disponibili:')?></h2>
<dl>
	<?php foreach ($positions as $position) : ?>
		<dt>
			<h4><?=$position['name']?></h4>
		</dt>
		<dd>
			<p>
				<?= Markdown::defaultTransform($position['summary'] ?? '') ?>
			</p>
			<div class="col-md-12 d-flex flex-column flex-md-row justify-content-md-end">
				<?php if ($_SESSION['locale'] === 'en-US') : ?>
					<a class="btn btn-primary mx-2 mb-2 mb-md-0" href="form.php?position=<?=$this->e($position['id'])?>">Apply</a><a class="btn btn-outline-secondary mx-2 ms-md-2" href="language.php?l=it-IT&from=<?= rawurlencode('/form.php?position=' . $this->e($position['id']))?>">Candidati</a>
				<?php else : ?>
					<a class="btn btn-primary mx-2 ms-md-2" href="form.php?position=<?=$this->e($position['id'])?>"">Candidati</a><a class="btn btn-outline-secondary mx-2 ms-md-2" href="language.php?l=en-US&from=<?= rawurlencode('/form.php?position=' . $this->e($position['id']))?>">Apply</a>
				<?php endif; ?>
			</div>
		</dd>
		<hr>
	<?php endforeach; ?>
</dl>
<div class="py-4"></div>