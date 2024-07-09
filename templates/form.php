<?php

/**
 * @var string|null $positions
 */

use Michelf\Markdown;

$totalUnavailable = count($positions);
foreach ($positions as $position) {
	if ($position['available'] == 1) {
		$totalUnavailable--;
	}
}

$this->layout('base', ['title' => __('Compila il questionario')]) ?>

<div class="col-md-12">
	<?php if (isset($error)) : ?>
		<div class="alert alert-danger">
		<?php
		switch ($error) {
			case 'form':
				echo __('Riempi tutti i campi del form, per favore');
				break;
			case 'consent':
				echo __('È necessario prestare il consenso per procedere');
				break;
			case 'db':
				echo sprintf(
					__('Errore di connessione al database. Se il problema persiste, puoi contattarci all\'indirizzo %s'),
					WEEEHIRE_EMAIL_FALLBACK
				);
				break;
			case 'duplicate':
				echo sprintf(__('Hai già inviato una candidatura con questa matricola. Puoi controllare la pagina con lo stato dal link che ti abbiamo mandato via email. Per favore non inviare più di una candidatura a testa, se vuoi candidarti per più di un\'area puoi <a href="https://weeeopen.polito.it/contattaci/" target="_blank">inviarci un messaggio tramite il sito</a> o dircelo durante il colloquio.'));
				break;
			default:
				echo sprintf(
					__('Si è rotto qualcosa (o hai rotto qualcosa?) durante l\'invio. Puoi contattarci a %s e spiegarci cos\'è successo, se vuoi.'),
					WEEEHIRE_EMAIL_FALLBACK
				);
				break;
		}
		?>
		</div>
	<?php endif ?>

	<?php //$this->insert('covid') ?>

	<form method="post">
		<div class="form-group row">
			<label for="name" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Nome')?></label>
			<div class="col-md-4 col-lg-4">
				<input id="name" name="name" type="text" required="required" class="form-control">
			</div>
			<label for="surname" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Cognome')?></label>
			<div class="col-md-4 col-lg-6">
				<input id="surname" name="surname" type="text" required="required" class="form-control">
			</div>
		</div>

		<div class="form-group row">
			<label for="year" class="col-md-1 col-form-label"><?php echo __('Anno')?></label>
			<div class="col-md-2 col-lg-4">
				<select id="year" name="year" required="required" class="form-control">
					<option value selected disabled class="d-none"></option>
					<option value="1º Triennale"><?php echo __('1º Triennale')?></option>
					<option value="2º Triennale"><?php echo __('2º Triennale')?></option>
					<option value="3º Triennale"><?php echo __('3º Triennale')?></option>
					<option value="1º Magistrale"><?php echo __('1º Magistrale')?></option>
					<option value="2º Magistrale"><?php echo __('2º Magistrale')?></option>
					<option value="Dottorato"><?php echo __('Dottorato')?></option>
				</select>
			</div>
			<label for="degreecourse" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Corso di laurea')?></label>
			<div class="col-md-7 col-lg-6">
				<select id="degreecourse" name="degreecourse" required="required" class="form-control">
					<option hidden disabled class="default"></option>
					<optgroup label="<?php echo __('Ingegneria') ?>" data-level="bachelor">
						<option value="Automotive Engineering">Automotive Engineering</option>
						<option value="Computer Engineering">Computer Engineering</option>
						<option value="Electronic And Communications Engineering">Electronic And Communications Engineering</option>
						<option value="Ingegneria Aerospaziale">Ingegneria Aerospaziale</option>
						<option value="Ingegneria Biomedica">Ingegneria Biomedica</option>
						<option value="Ingegneria Chimica E Alimentare">Ingegneria Chimica E Alimentare</option>
						<option value="Ingegneria Civile">Ingegneria Civile</option>
						<option value="Ingegneria Dei Materiali">Ingegneria Dei Materiali</option>
						<option value="Ingegneria Del Cinema E Dei Mezzi Di Comunicazione">Ingegneria Del Cinema E Dei Mezzi Di Comunicazione</option>
						<option value="Ingegneria Dell'autoveicolo">Ingegneria Dell'autoveicolo</option>
						<option value="Ingegneria Della Produzione Industriale">Ingegneria Della Produzione Industriale</option>
						<option value="Ingegneria Edile">Ingegneria Edile</option>
						<option value="Ingegneria Elettrica">Ingegneria Elettrica</option>
						<option value="Ingegneria Elettronica">Ingegneria Elettronica</option>
						<option value="Ingegneria Energetica">Ingegneria Energetica</option>
						<option value="Ingegneria Fisica">Ingegneria Fisica</option>
						<option value="Ingegneria Gestionale L-8">Ingegneria Gestionale L-8</option>
						<option value="Ingegneria Gestionale L-9">Ingegneria Gestionale L-9</option>
						<option value="Ingegneria Informatica">Ingegneria Informatica</option>
						<option value="Ingegneria Meccanica">Ingegneria Meccanica</option>
						<option value="Ingegneria Per L'ambiente E Il Territorio">Ingegneria Per L'ambiente E Il Territorio</option>
						<option value="Matematica Per L'ingegneria">Matematica Per L'ingegneria</option>
						<option value="Mechanical Engineering">Mechanical Engineering</option>
					</optgroup>
					<optgroup label="<?php echo __('Design') ?>" data-level="bachelor">
						<option value="Design E Comunicazione Visiva">Design E Comunicazione Visiva</option>
					</optgroup>
					<optgroup label="<?php echo __('Architettura e pianificazione urbanistica') ?>" data-level="bachelor">
						<option value="Architettura">Architettura</option>
						<option value="Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale">Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale</option>
					</optgroup>
					<optgroup label="<?php echo __('Ingegneria') ?>" data-level="master">
						<option value="Building Engineering">Building Engineering</option>
						<option value="Civil Engineering">Civil Engineering</option>
						<option value="Computer Engineering">Computer Engineering</option>
						<option value="Electronic Engineering">Electronic Engineering</option>
						<option value="Energy And Nuclear Engineering">Energy And Nuclear Engineering</option>
						<option value="Engineering And Management">Engineering And Management</option>
						<option value="Environmental And Land Engineering">Environmental And Land Engineering</option>
						<option value="ICT For Smart Societies">ICT For Smart Societies</option>
						<option value="Ingegneria Aerospaziale">Ingegneria Aerospaziale</option>
						<option value="Ingegneria Biomedica">Ingegneria Biomedica</option>
						<option value="Ingegneria Chimica E Dei Processi Sostenibili">Ingegneria Chimica E Dei Processi Sostenibili</option>
						<option value="Ingegneria Civile">Ingegneria Civile</option>
						<option value="Ingegneria Dei Materiali">Ingegneria Dei Materiali</option>
						<option value="Ingegneria Del Cinema E Dei Mezzi Di Comunicazione">Ingegneria Del Cinema E Dei Mezzi Di Comunicazione</option>
						<option value="Ingegneria Della Produzione Industriale E Dell'innovazione Tecnologica">Ingegneria Della Produzione Industriale E Dell'innovazione Tecnologica</option>
						<option value="Ingegneria Edile">Ingegneria Edile</option>
						<option value="Ingegneria Elettrica">Ingegneria Elettrica</option>
						<option value="Ingegneria Elettronica (Electronic Engineering)">Ingegneria Elettronica (Electronic Engineering)</option>
						<option value="Ingegneria Energetica E Nucleare">Ingegneria Energetica E Nucleare</option>
						<option value="Ingegneria Gestionale">Ingegneria Gestionale</option>
						<option value="Ingegneria Informatica (Computer Engineering)">Ingegneria Informatica (Computer Engineering)</option>
						<option value="Ingegneria Matematica">Ingegneria Matematica</option>
						<option value="Ingegneria Meccanica">Ingegneria Meccanica</option>
						<option value="Ingegneria Per L'ambiente E Il Territorio">Ingegneria Per L'ambiente E Il Territorio</option>
						<option value="Mechanical Engineering">Mechanical Engineering</option>
						<option value="Mechatronic Engineering">Mechatronic Engineering</option>
						<option value="Nanotechnologies For Icts">Nanotechnologies For Icts</option>
						<option value="Petroleum And Mining Engineering">Petroleum And Mining Engineering</option>
						<option value="Physics Of Complex Systems (Fisica Dei Sistemi Complessi)">Physics Of Complex Systems (Fisica Dei Sistemi Complessi)</option>
						<option value="Physics Of Complex Systems">Physics Of Complex Systems</option>
					</optgroup>
					<optgroup label="<?php echo __('Design') ?>" data-level="master">
						<option value="Design Sistemico">Design Sistemico</option>
					</optgroup>
					<optgroup label="<?php echo __('Architettura e pianificazione urbanistica') ?>" data-level="master">
						<option value="Architecture Construction City">Architecture Construction City</option>
						<option value="Architecture For Heritage">Architecture For Heritage</option>
						<option value="Architecture For Sustainability">Architecture For Sustainability</option>
						<option value="Architettura Costruzione Citta'">Architettura Costruzione Citta'</option>
						<option value="Architettura Per Il Patrimonio">Architettura Per Il Patrimonio</option>
						<option value="Architettura Per La Sostenibilita'">Architettura Per La Sostenibilita'</option>
						<option value="Automotive Engineering">Automotive Engineering</option>
						<option value="Communications And Computer Networks Engineering">Communications And Computer Networks Engineering</option>
						<option value="Data Science And Engineering">Data Science And Engineering</option>
						<option value="Digital Skills For Sustainable Societal Transitions">Digital Skills For Sustainable Societal Transitions</option>
						<option value="Economia Dell'ambiente, Della Cultura E Del Territorio">Economia Dell'ambiente, Della Cultura E Del Territorio</option>
						<option value="Geografia E Scienze Territoriali">Geografia E Scienze Territoriali</option>
						<option value="Pianificazione Territoriale, Urbanistica E Paesaggistico-Ambientale">Pianificazione Territoriale, Urbanistica E Paesaggistico-Ambientale</option>
						<option value="Progettazione Delle Aree Verdi E Del Paesaggio">Progettazione Delle Aree Verdi E Del Paesaggio</option>
						<option value="Territorial, Urban, Environmental And Landscape Planning">Territorial, Urban, Environmental And Landscape Planning</option>
					</optgroup>
					<optgroup label="<?php echo __('Dottorato')?>" data-level="phd">
						<option value="Dottorato in Ambiente E Territorio">Dottorato in Ambiente E Territorio</option>
						<option value="Dottorato in Architettura. Storia E Progetto">Dottorato in Architettura. Storia E Progetto</option>
						<option value="Dottorato in Beni Architettonici E Paesaggistici">Dottorato in Beni Architettonici E Paesaggistici</option>
						<option value="Dottorato in Beni Culturali">Dottorato in Beni Culturali</option>
						<option value="Dottorato in Bioingegneria E Scienze Medico-chirurgiche">Dottorato in Bioingegneria E Scienze Medico-chirurgiche</option>
						<option value="Dottorato in Energetica">Dottorato in Energetica</option>
						<option value="Dottorato in Fisica">Dottorato in Fisica</option>
						<option value="Dottorato in Gestione, Produzione E Design">Dottorato in Gestione, Produzione E Design</option>
						<option value="Dottorato in Ingegneria Aerospaziale">Dottorato in Ingegneria Aerospaziale</option>
						<option value="Dottorato in Ingegneria Biomedica">Dottorato in Ingegneria Biomedica</option>
						<option value="Dottorato in Ingegneria Chimica">Dottorato in Ingegneria Chimica</option>
						<option value="Dottorato in Ingegneria Civile E Ambientale">Dottorato in Ingegneria Civile E Ambientale</option>
						<option value="Dottorato in Ingegneria Elettrica, Elettronica E Delle Comunicazioni">Dottorato in Ingegneria Elettrica, Elettronica E Delle Comunicazioni</option>
						<option value="Dottorato in Ingegneria Informatica E Dei Sistemi">Dottorato in Ingegneria Informatica E Dei Sistemi</option>
						<option value="Dottorato in Ingegneria Meccanica">Dottorato in Ingegneria Meccanica</option>
						<option value="Dottorato in Ingegneria Per La Gestione Delle Acque E Del Territorio">Dottorato in Ingegneria Per La Gestione Delle Acque E Del Territorio</option>
						<option value="Dottorato in Matematica Pura E Applicata">Dottorato in Matematica Pura E Applicata</option>
						<option value="Dottorato in Metrologia">Dottorato in Metrologia</option>
						<option value="Dottorato in Scienza E Tecnologia Dei Materiali">Dottorato in Scienza E Tecnologia Dei Materiali</option>
						<option value="Dottorato in Storia Dell'architettura E Dell'urbanistica">Dottorato in Storia Dell'architettura E Dell'urbanistica</option>
						<option value="Dottorato in Urban And Regional Development">Dottorato in Urban And Regional Development</option>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<label for="matricola" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Matricola')?></label>
			<div class="col-md-3 col-lg-4">
				<input id="matricola" name="matricola" placeholder="s123456" type="text" required="required"
						class="form-control">
			</div>
			<label for="area" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Interesse')?></label>
			<div class="col-md-5 col-lg-6">
				<select id="area" name="area" required="required" class="form-control">
					<option value selected disabled class="d-none"></option>
					<?php foreach ($positions as $position) : ?>
						<option <?php echo $position['available'] == 1 ? '' : 'disabled' ?> value="<?php echo $this->e($position['id']) ?>"><?php echo $this->e($position['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php if ($totalUnavailable != 0) : ?>
		<div class="form-group">
			<p><small><?php echo __('Al momento alcune aree del team sono al completo, è possibile candidarsi solo nelle aree selezionabili dall\'elenco. In futuro le aree disponibili potrebbero cambiare senza preavviso.') ?></small></p>
		</div>
		<?php endif; ?>
		<div class="form-group">
			<label for="letter"><b><?php echo __('Lettera motivazionale')?></b></label>
			<div id="mlet-explain">
				<div class="form-text" id="mlet-explain-">
					<p><?php echo __('Seleziona l\'area del team che più ti interessa e qui compariranno delle linee guida su cosa scrivere.') ?></p>
				</div>
				<?php foreach ($positions as $position) : ?>
					<div class="form-text d-none" id="mlet-explain-<?php echo $position['id'] ?>">
					<?php echo Markdown::defaultTransform($position['description'] ?? '') ?>
					</div>
				<?php endforeach; ?>
			</div>
			<textarea id="letter" name="letter" cols="40" rows="8" required="required" class="form-control"></textarea>
		</div>
		<div class="form-group">
			<div>
				<div id="checkboxesHelpBlock" class="form-text text-muted">
					<span class="form-text text-muted" id="generatedEmailAddrText" class="hidden"> <?php echo __('Una conferma della tua candidatura verrà inviata all\'indirizzo <span id="generatedEmailAddr"></span>.') ?></span>
					<span><?php echo sprintf(__('Visualizza le <a href="%s">Informazioni sul trattamento dei dati personali</a>.'), 'privacy.php')?></span>
				</div>
				<div class="form-check">
					<input name="mandatorycheckbox_0" id="mandatorycheckbox_0" type="checkbox" class="form-check-input"
							value="true" aria-describedby="checkboxesHelpBlock" required="required">
					<label for="mandatorycheckbox_0"
							class="form-check-label"><?php echo __('Ho letto le Informazioni sul trattamento dei dati personali e accetto le condizioni lì delineate')?></label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary"><?php echo __('Invia')?></button>
		</div>
	</form>
</div>
<script type="text/javascript">
(function(){
	let area = document.getElementById("area");
	let explanations = document.getElementById('mlet-explain').children;

	function updateHints() {
		for(let i = 0; explanations.length > i; i++) {
			explanations[i].classList.add('d-none');
		}
		let explain = document.getElementById('mlet-explain-' + area.value.replace(/\s+/g, '-'));
		if(explain !== null) {
			explain.classList.remove('d-none');
		}
	}

	updateHints();
	area.addEventListener("change", updateHints);

	let yearSelector = document.getElementById("year");
	let matricolaSelector = document.getElementById('matricola');

	function dottorandize() {
		if(yearSelector.value === 'Dottorato') {
			matricolaSelector.placeholder = 'd123456';
		} else {
			matricolaSelector.placeholder = 's123456';
		}
	}
	yearSelector.addEventListener('change', dottorandize);
	dottorandize();

	let generatedEmailAddrText = document.getElementById("generatedEmailAddrText");
	let generatedEmailAddr = document.getElementById("generatedEmailAddr");
	function matricolize() {
		generatedEmailAddrText.classList.toggle("hidden", matricolaSelector.value === "");
		let email = matricolaSelector.value;
		if(email.startsWith('s')) {
			email += "@studenti.polito.it";
		} else {
			email += "@polito.it";
		}
		generatedEmailAddr.innerText = email;
	}
	matricolaSelector.addEventListener('change', matricolize);
	matricolize();

	let courseSelector = document.getElementById("degreecourse");
	function coursize() {
		let year = yearSelector.value;

		let level = null;
		if(year.includes("Triennale")) {
			level = "bachelor";
		} else if(year.includes("Magistrale")) {
			level = "master";
		} else if(year.includes("Dottorato")) {
			level = "phd";
		}

		for(let el of courseSelector.childNodes) {
			if(el.nodeName === 'OPTGROUP') {
				if(level) {
					if(el.dataset.level) {
						el.style.display = el.dataset.level === level ? '' : 'none';
					}
				} else {
					el.style.display = '';
				}
			}
		}

		let currentGroup = courseSelector.options[courseSelector.selectedIndex].parentElement;
		if(currentGroup.style.display !== '') {
			courseSelector.querySelector('.default').selected = 'selected';
		}
	}
	yearSelector.addEventListener('change', coursize);
	coursize();
}());
</script>
