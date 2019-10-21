<?php
/** @var $candidatureEta int */

$this->layout('base', ['title' => 'WEEElcome']) ?>

<h1><?= __('Entra in WEEE Open') ?></h1>
<p><?= __('Compila il questionario per fare richiesta di ammissione in team. Premi il bottone qui sotto per iniziare.') ?></p>
<div class="col-md-12 text-center">
	<!-- TODO:Inserire timer -->
    <p id="timer"></p>
    <script>
        var scadenza = <?php echo $candidatureEta ?>
        // Set the date we're counting down to
        var countDownDate = new Date(scadenza*1000).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="demo"
            document.getElementById("timer").innerHTML ='Le candidature si chiuderanno tra: ' +  days + " Giorni " + hours + " Ore "
                + minutes + " Minuti " + seconds + " Secondi ";

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("demo").innerHTML = "EXPIRED";
            }
        }, 1000);
    </script>
</div>
<div class="col-md-12 text-center">
	<a class="btn btn-lg btn-primary the-button" href="form.php"><?= __('Inizia') ?></a>
</div>
<?php if($_SESSION['locale'] === 'en-us'): ?>
	<p>Il questionario è anche disponibile <a href="language.php?l=it-it&from=<?= rawurlencode('/form.php') ?>">in Italiano</a></p>
<?php else: ?>
	<p>The form is also available <a href="language.php?l=en-us&from=<?= rawurlencode('/form.php') ?>">in English</a></p>
<?php endif ?>