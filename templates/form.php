<?php $this->layout('base', ['title' => __('Compila il questionario')]) ?>

<div class="col-md-12">
<form method="post">
	<div class="form-group row">
		<label for="name" class="col-md-2 col-lg-1 col-form-label"><?= __('Nome') ?></label>
		<div class="col-md-4 col-lg-5">
			<input id="name" name="name" type="text" required="required" class="form-control">
		</div>
		<label for="surname" class="col-md-2 col-lg-1 col-form-label"><?=__('Cognome')?></label>
		<div class="col-md-4 col-lg-5">
			<input id="surname" name="surname" type="text" required="required" class="form-control">
		</div>
	</div>
	<div class="form-group row">
		<label for="cdl" class="col-md-2 col-lg-1 col-form-label"><?=__('Corso di laurea')?></label>
		<div class="col-md-7 col-lg-6">
			<select id="cdl" name="cdl" required="required" class="form-control">
				<option value selected disabled class="d-none"></option>
				<option value="Asd">asd</option>
			</select>
		</div>
		<label for="year" class="col-md-1 col-form-label"><?=__('Anno')?></label>
		<div class="col-md-2 col-lg-4">
			<select id="year" name="year" required="required" class="form-control" onchange="dottorandize()">
				<option value selected disabled class="d-none"></option>
				<option value="1° Triennale"><?=__('1° Triennale')?></option>
				<option value="2° Triennale"><?=__('2° Triennale')?></option>
				<option value="3° Triennale"><?=__('3° Triennale')?></option>
				<option value="1° Magistrale"><?=__('1° Magistrale')?></option>
				<option value="2° Magistrale"><?=__('2° Magistrale')?></option>
				<option value="Dottorato"><?=__('Dottorato')?></option>
			</select>
		</div>
	</div>
	<div class="form-group row">
		<label for="matricola" class="col-md-2 col-lg-1 col-form-label"><?=__('Matricola')?></label>
		<div class="col-md-3 col-lg-4">
			<input id="matricola" name="matricola" placeholder="s123456" type="text" required="required" class="form-control">
		</div>
		<label for="interest" class="col-md-2 col-lg-1 col-form-label"><?=__('Interesse')?></label>
		<div class="col-md-5 col-lg-6">
			<select id="interest" name="interest" required="required" class="form-control" onchange="updateHints()">
				<option value selected disabled class="d-none"></option>
				<option value="Riparazione Hardware"><?=__('Riparazione Hardware')?></option>
				<option value="Elettronica"><?=__('Elettronica')?></option>
				<option value="Sviluppo Software"><?=__('Sviluppo Software')?></option>
				<option value="Riuso-creativo"><?=__('Riuso creativo')?></option>
				<option value="Pubbliche-relazioni"><?=__('Pubbliche relazioni')?></option>
				<option value="Altro"><?=__('Altro')?></option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label for="motivational"><b>Lettera motivazionale</b></label>
		<div id="mlet-explain">
			<div class="form-text" id="mlet-explain-">
<?= __(<<<EOT
				<p>Seleziona l'area del team che più ti interessa e qui compariranno delle linee guida su cosa scrivere.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Riparazione-Hardware">
<?= __(<<<EOT
				<p>Descrivi qualsiasi tua esperienza di riparazione di computer (fissi o portatili), o assemblaggio, o saldatura di componenti elettronici.</p>
				<p>Se non sai qualcosa, cosa fai per imparare in autonomia? Puoi anche fornire degli esempi.</p>
				<p>Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.</p>
				<p>Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a riparare computer.</p>
				<p>Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Elettronica">
<?= __(<<<EOT
				<p>Uno degli obiettivi del team è la progettazione di strumenti elettronici per la diagnostica a basso livello e il riuso dell'hardware recuperato.</p>
				<p>Qual è il tuo rapporto con il mondo dell'elettronica? Ti interessa di più l'elettronica digitale o analogica (specialmente di potenza) o ti interessano entrambe?</p>
				<p>Se hai mai realizzato qualche circuito o progetto oltre a quelli nei laboratori didattici, parlane con riferimento anche al metodo con cui è stato realizzato (breadboard, millefori, circuito stampato, componenti through-hole o SMD, etc...).</p>
				<p>Indica anche se hai dimestichezza con qualche software di Electronic Design Automation (progettazione, simulazione, test e verifica, etc...).</p>
				<p>Menziona anche quanto tempo potresti dedicare al team e se fai qualcos'altro di interessante nel tempo libero oltre a progettare circuiti.</p>
				<p>Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Sviluppo-Software">
<?= __(<<<EOT
				<p>Parla di qualsiasi tua esperienza nello scrivere software. Va bene anche "per l'esame di ... ho creato un programma che fa ..." o "ho fatto il sito web per la panetteria all'angolo".</p>
				<p>Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?</p>
				<p>La <a href="https://github.com/weee-open" target="_blank">maggior parte del software realizzato in team</a> (osserva in particolare Tarallo, turbofresa, bot e weeelab) è in PHP o Python. Cosa ne pensi di quei linguaggi e del codice di quei progetti?</p>
				<p>Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.</p>
				<p>Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.</p>
				<p>Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Riuso-creativo">
<?= __(<<<EOT
				<p>Non tutti i computer che ci arrivano sono riparabili, ma vorremmo comunque minimizzare la quantità di materiale che finisce nel bidone.</p>
				<p>Se hai manualità e/o esperienze nel riuso creativo e/o making è il momento di dirlo.</p>
				<p>Puoi anche aggiungere se hai idee su come potremmo riutilizzare <i>case</i> vuoti, schede madri dall'estetica peculiare o i piatti a specchio di hard disk rotti.</p>
				<p>Accenna anche a che metodo seguiresti per progettare queste cose.</p>
				<p>Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero.</p>
				<p>Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Pubbliche-relazioni">
<?= __(<<<EOT
				<p>Descrivi qualsiasi tua esperienza nel gestire pagine o profili (e.g. personali, di attività commerciali, di <i>meme</i> nonsense, etc...) sui social network, in particolare Facebook e Instagram.</p>
				<p>Menziona anche i risultati raggiunti con tali attività promozionali, se possibile.</p>
				<p>Se dovessi spiegare in due righe cosa fa il team, cosa diresti?</p>
				<p>Indica anche quanto tempo potresti dedicare a queste attività e se hai qualche altro interesse.</p>
				<p>Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.</p>
EOT
); ?>
			</div>
			<div class="form-text d-none" id="mlet-explain-Altro">
<?= __(<<<EOT
				<p>Stupiscici.</p>
EOT
); ?>
			</div>
		</div>
		<textarea id="motivational" name="motivational" cols="40" rows="5" required="required" class="form-control"></textarea>
	</div>
	<div class="form-group">
		<div>
			<span id="checkboxesHelpBlock" class="form-text text-muted"><?= sprintf(__('Dovresti leggere la nostra <a href="%s">Privacy Policy</a> e almeno dare un\'occhiata alla pagina <a href="%s">Entra nel team</a> sul nostro sito prima di candidarti.'), 'privacy.php', 'http://weeeopen.polito.it/entra-nel-team.html') ?></a></span>
			<div class="form-check">
				<input name="checkbox" id="checkbox_0" type="checkbox" class="form-check-input" value="read" aria-describedby="checkboxesHelpBlock" required="required">
				<label for="checkbox_0" class="form-check-label"><?= __('Ho letto la Privacy Policy e accetto le condizioni lì delineate') ?></label>
			</div>
			<div class="form-check">
				<input name="checkbox" id="checkbox_1" type="checkbox" aria-describedby="checkboxesHelpBlock" required="required" class="form-check-input" value="policy">
				<label for="checkbox_1" class="form-check-label"><?= __('Dichiaro che tutte le informazioni inserite sono corrette e ho dato un\'occhiata alla pagina "Entra nel team" sul sito') ?></label>
			</div>
			
		</div>
	</div>
	<div class="form-group">
		<button name="submit" type="submit" class="btn btn-primary">Submit</button>
	</div>
</form>
</div>
<script type="text/javascript">
	let interest = document.getElementById("interest");
	let explanations = document.getElementById('mlet-explain').children;
	function updateHints() {
		for(let i = 0; explanations.length > i; i++) {
			explanations[i].classList.add('d-none');
		}
		let explain = document.getElementById('mlet-explain-' + interest.value.replace(/\s+/g, '-'));
		if(explain !== null) {
			explain.classList.remove('d-none');
		}
	}
	updateHints();
	let checkboxes = document.querySelectorAll(".mandatory-checkbox");
	let submit = document.getElementById("submit-btn");

	function dottorandize() {
		if(document.getElementById('year').value === 'Dottorato') {
			document.getElementById('matricola').placeholder = 'd123456';
		} else {
			document.getElementById('matricola').placeholder = 's123456';
		}
	}
	dottorandize();
</script>