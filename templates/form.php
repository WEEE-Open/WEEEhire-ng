<?php

/** @var string|null $rolesUnavailable */

if($rolesUnavailable === null) {
	$roles = [];
} else {
	$roles = explode('|', $rolesUnavailable);
	$roles = array_combine($roles, $roles);
}
require_once 'roles.php';
$allRoles = getRoles();
$this->layout('base', ['title' => __('Compila il questionario')]) ?>

<div class="col-md-12">
	<?php if(isset($error)): ?>
		<div class="alert alert-danger">
			<?php
			switch($error) {
				case 'form':
					echo __('Riempi tutti i campi del form, per favore');
					break;
				case 'consent':
					echo __('È necessario prestare il consenso per procedere');
					break;
				case 'db':
					echo sprintf(__('Errore di connessione al database. Se il problema persiste, puoi contattarci all\'indirizzo %s'),
						WEEEHIRE_EMAIL_FALLBACK);
					break;
				case 'duplicate':
					echo sprintf(__('Hai già inviato una candidatura con questa matricola. Puoi controllare la pagina con lo stato dal link che ti abbiamo mandato via email.'));
					break;
				default:
					echo sprintf(__('Si è rotto qualcosa (o hai rotto qualcosa?) durante l\'invio. Puoi contattarci a %s e spiegarci cos\'è successo, se vuoi.'),
						WEEEHIRE_EMAIL_FALLBACK);
					break;
			}
			?>
		</div>
	<?php endif ?>

	<?php $this->insert('covid') ?>

	<form method="post">
		<div class="form-group row">
			<label for="name" class="col-md-2 col-lg-1 col-form-label"><?=__('Nome')?></label>
			<div class="col-md-4 col-lg-5">
				<input id="name" name="name" type="text" required="required" class="form-control">
			</div>
			<label for="surname" class="col-md-2 col-lg-1 col-form-label"><?=__('Cognome')?></label>
			<div class="col-md-4 col-lg-5">
				<input id="surname" name="surname" type="text" required="required" class="form-control">
			</div>
		</div>
		<div class="form-group row">
			<label for="degreecourse" class="col-md-2 col-lg-1 col-form-label"><?=__('Corso di laurea')?></label>
			<div class="col-md-7 col-lg-6">
				<select id="degreecourse" name="degreecourse" required="required" class="form-control">
					<optgroup label="<?=__('Ingegneria (L, LM)')?>">
						<option value selected disabled class="d-none"></option>
						<option value="Automotive Engineering">Automotive Engineering</option>
						<option value="Civil Engineering">Civil Engineering</option>
						<option value="Communications And Computer Networks Engineering">Communications And Computer Networks Engineering</option>
						<option value="Computer Engineering">Computer Engineering</option>
						<option value="Electronic And Communications Engineering">Electronic And Communications Engineering</option>
						<option value="Electronic Engineering">Electronic Engineering</option>
						<option value="Engineering And Management">Engineering And Management</option>
						<option value="ICT For Smart Societies">ICT For Smart Societies</option>
						<option value="Ingegneria Aerospaziale">Ingegneria Aerospaziale</option>
						<option value="Ingegneria Biomedica">Ingegneria Biomedica</option>
						<option value="Ingegneria Chimica E Alimentare">Ingegneria Chimica E Alimentare</option>
						<option value="Ingegneria Chimica E Dei Processi Sostenibili">Ingegneria Chimica E Dei Processi Sostenibili</option>
						<option value="Ingegneria Civile">Ingegneria Civile</option>
						<option value="Ingegneria Dei Materiali">Ingegneria Dei Materiali</option>
						<option value="Ingegneria Del Cinema E Dei Mezzi Di Comunicazione">Ingegneria Del Cinema E Dei Mezzi Di Comunicazione</option>
						<option value="Ingegneria Dell'autoveicolo">Ingegneria Dell'autoveicolo</option>
						<option value="Ingegneria Della Produzione Industriale">Ingegneria Della Produzione Industriale</option>
						<option value="Ingegneria Della Produzione Industriale E Dell'innovazione Tecnologica">Ingegneria Della Produzione Industriale E Dell'innovazione Tecnologica</option>
						<option value="Ingegneria Edile">Ingegneria Edile</option>
						<option value="Ingegneria Elettrica">Ingegneria Elettrica</option>
						<option value="Ingegneria Elettronica">Ingegneria Elettronica</option>
						<option value="Ingegneria Energetica">Ingegneria Energetica</option>
						<option value="Ingegneria Energetica E Nucleare">Ingegneria Energetica E Nucleare</option>
						<option value="Ingegneria Fisica">Ingegneria Fisica</option>
						<option value="Ingegneria Gestionale L-8">Ingegneria Gestionale L-8</option>
						<option value="Ingegneria Gestionale L-9">Ingegneria Gestionale L-9</option>
						<option value="Ingegneria Informatica">Ingegneria Informatica</option>
						<option value="Ingegneria Matematica">Ingegneria Matematica</option>
						<option value="Ingegneria Meccanica">Ingegneria Meccanica</option>
						<option value="Ingegneria Per L'ambiente E Il Territorio">Ingegneria Per L'ambiente E Il Territorio</option>
						<option value="Matematica Per L'ingegneria">Matematica Per L'ingegneria</option>
						<option value="Mechanical Engineering">Mechanical Engineering</option>
						<option value="Mechatronic Engineering">Mechatronic Engineering</option>
						<option value="Nanotechnologies For Icts">Nanotechnologies For Icts</option>
						<option value="Petroleum And Mining Engineering">Petroleum And Mining Engineering</option>
						<option value="Physics Of Complex Systems">Physics Of Complex Systems</option>
					</optgroup>
					<optgroup label="<?=__('Design (L, LM)')?>">
						<option value="Design E Comunicazione Visiva">Design E Comunicazione Visiva</option>
						<option value="Design Sistemico">Design Sistemico</option>
					</optgroup>
					<optgroup label="<?=__('Architettura e pianificazione urbanistica (L, LM)')?>">
						<option value="Architecture">Architecture</option>
						<option value="Architecture Construction City">Architecture Construction City</option>
						<option value="Architecture For The Sustainability Design">Architecture For The Sustainability Design</option>
						<option value="Architettura">Architettura</option>
						<option value="Architettura Costruzione Città">Architettura Costruzione Città</option>
						<option value="Architettura Per Il Progetto Sostenibile">Architettura Per Il Progetto Sostenibile</option>
						<option value="Architettura Per Il Restauro E Valorizzazione Del Patrimonio">Architettura Per Il Restauro E Valorizzazione Del Patrimonio</option>
						<option value="Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale">Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale</option>
						<option value="Progettazione Delle Aree Verdi E Del Paesaggio">Progettazione Delle Aree Verdi E Del Paesaggio</option>
						<option value="Territorial, Urban, Environmental And Landscape Planning">Territorial, Urban, Environmental And Landscape Planning</option>
					</optgroup>
					<optgroup label="<?=__('Dottorato (tutti i settori)')?>">
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
			<label for="year" class="col-md-1 col-form-label"><?=__('Anno')?></label>
			<div class="col-md-2 col-lg-4">
				<select id="year" name="year" required="required" class="form-control" onchange="dottorandize()">
					<option value selected disabled class="d-none"></option>
					<option value="1º Triennale"><?=__('1º Triennale')?></option>
					<option value="2º Triennale"><?=__('2º Triennale')?></option>
					<option value="3º Triennale"><?=__('3º Triennale')?></option>
					<option value="1º Magistrale"><?=__('1º Magistrale')?></option>
					<option value="2º Magistrale"><?=__('2º Magistrale')?></option>
					<option value="Dottorato"><?=__('Dottorato')?></option>
				</select>
			</div>
		</div>
		<div class="form-group row">
			<label for="matricola" class="col-md-2 col-lg-1 col-form-label"><?=__('Matricola')?></label>
			<div class="col-md-3 col-lg-4">
				<input id="matricola" name="matricola" placeholder="s123456" type="text" required="required"
						class="form-control">
			</div>
			<label for="area" class="col-md-2 col-lg-1 col-form-label"><?=__('Interesse')?></label>
			<div class="col-md-5 col-lg-6">
				<select id="area" name="area" required="required" class="form-control" onchange="updateHints()">
					<option value selected disabled class="d-none"></option>
					<?php foreach($allRoles as $value => $role): ?>
						<option <?= isset($roles[$value]) ? 'disabled' : '' ?> value="<?= $this->e($value) ?>"><?= $this->e($role) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php if(count($roles) > 0): ?>
		<div class="form-group">
			<p><small><?= __('Al momento alcune aree del team sono al completo, è possibile candidarsi solo nelle aree selezionabili dall\'elenco.') ?></small></p>
		</div>
		<?php endif; ?>
		<div class="form-group">
			<label for="letter"><b><?=__('Lettera motivazionale')?></b></label>
			<div id="mlet-explain">
				<div class="form-text" id="mlet-explain-">
					<p><?= __('Seleziona l\'area del team che più ti interessa e qui compariranno delle linee guida su cosa scrivere.') ?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Riparazione-hardware">
					<p><?=__('Descrivi qualsiasi tua esperienza di riparazione di computer (fissi o portatili), o assemblaggio, o saldatura di componenti elettronici.')?></p>
					<p><?=__('Se non sai qualcosa, cosa fai per imparare in autonomia? Puoi anche fornire degli esempi.')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a riparare computer.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Elettronica">
					<p><?= __('Uno degli obiettivi del team è la progettazione di strumenti elettronici per la diagnostica a basso livello e il riuso dell\'hardware recuperato.') ?></p>
					<p><?=__('Qual è il tuo rapporto con il mondo dell\'elettronica? Ti interessa di più l\'elettronica digitale o analogica (specialmente di potenza) o ti interessano entrambe?') ?></p>
					<p><?=__('Se hai mai realizzato qualche circuito o progetto oltre a quelli nei laboratori didattici, parlane con riferimento anche al metodo con cui è stato realizzato (breadboard, millefori, circuito stampato, componenti through-hole o SMD, etc...).')?></p>
					<p><?=__('Indica anche se hai dimestichezza con qualche software di Electronic Design Automation (progettazione, simulazione, test e verifica, etc...).') ?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare al team e se fai qualcos\'altro di interessante nel tempo libero oltre a progettare circuiti.') ?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sysadmin">
					<p><?=__('Il team ha alcuni server Linux con delle web app e dei software di SSO. La maggior parte della configurazione è automatizzata con Ansible.') ?></p>
					<p><?=__('I server hanno bisogno di ordinaria manutenzione (installare aggiornamenti e simili). Inoltre alcuni software andrebbero containerizzati.')?></p>
					<p><?=__('Se hai già esperienza con queste cose, ottimo, ma il minimo richiesto è non spaventarsi davanti a un terminale Linux.')?></p>
					<p><?=__('Ci serve più che altro una persona cauta e attenta, che legga con cura il manuale e i messaggi prima di fare danni. Se la sei e vuoi mettere le mani in pasta su dei server, dicci qualcosa su di te e che esperienza hai con Linux, con i server o con l\'informatica in generale.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre ad amministrare server.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sviluppo-software-Python">
					<p><?=__('Descrivi qualsiasi tua esperienza nel programmare in Python, specialmente Python 3, e se hai un account su Github non esitare a condividerlo nella tua lettera!')?></p>
					<p><?=__('Potresti menzionare se hai mai usato virtual environments, collaborato con qualcuno su un progetto software, o se sai scrivere in altri linguaggi.')?></p>
					<p><?=__('Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sviluppo-software-PHP">
					<p><?=__('I principali progetti PHP del team sono <a href="https://github.com/WEEE-Open/tarallo" target="_blank">Tarallo</a>, <a href="https://github.com/WEEE-Open/WEEEHire-ng" target="_blank">WEEEHire</a> e <a href="https://github.com/WEEE-Open/crauto" target="_blank">crauto</a>, puoi darci già un\'occhiata per sapere a cosa vai incontro.')?></p>
					<p><?=__('Parla di qualsiasi tua esperienza nello scrivere software in PHP. Va bene anche "per l\'esame di ... ho creato un programma che fa ..." o "ho fatto il sito web per la panetteria all\'angolo". Puoi anche menzionare se conosci altri linguaggi o hai mai lavorato a progetti collaborativi.') ?></p>
					<p><?=__('Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Riuso-creativo">
					<p><?=__('Non tutti i computer che ci arrivano sono riparabili, ma vorremmo comunque minimizzare la quantità di materiale che finisce nel bidone.')?></p>
					<p><?=__('Se hai manualità e/o esperienze nel riuso creativo e/o making è il momento di dirlo.')?></p>
					<p><?=__('Puoi anche aggiungere se hai idee su come potremmo riutilizzare <i>case</i> vuoti, schede madri dall\'estetica peculiare o i piatti a specchio di hard disk rotti.') ?></p>
					<p><?=__('Accenna anche a che metodo seguiresti per progettare queste cose.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Pubbliche-relazioni">
					<p><?=__('Descrivi qualsiasi tua esperienza nel gestire pagine o profili (e.g. personali, di attività commerciali, di <i>meme</i> nonsense, etc...) sui social network, in particolare Facebook e Instagram.')?></p>
					<p><?=__('Menziona anche i risultati raggiunti con tali attività promozionali, se possibile.')?></p>
					<p><?=__('Se dovessi spiegare in due righe cosa fa il team, cosa diresti?')?></p>
					<p><?=__('Indica anche quanto tempo potresti dedicare a queste attività e se hai qualche altro interesse.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Creazione-di-contenuti-digitali">
					<p><?=__('Come ogni team abbiamo un\'immagine e ci servono persone in gamba per curarla e mantenerla. In particolare, abbiamo bisogno di:')?></p>
					<ul>
						<li><?=__('Loghi e immagini personalizzate da usare come sfondi, icone e simili sui computer riparati')?></li>
						<li><?=__('Design grafici per manifesti, biglietti da visita e personalizzazioni dei case dei computer riparati')?>
						<li><?=__('Persone che abbiano dimestichezza con la videocamera, l\'editing video e la cinematografia per realizzare tutorial e vlog da caricare sul nuovo canale YouTube del team')?></li>
						<li><?=__('Persone che gestiscano le nostre pagine social (su cui carichiamo aggiornamenti inerenti alle nostre attività) o che curino i contenuti multimediali (foto e video) che carichiamo')?></li>
						<li><?=__('Un musicista o deejay che possa creare un jingle per il team ;)')?></li>
					</ul>
					<p><?=__('Se <strong>almeno una</strong>, o più di una, di queste attività di interessano, questo è il ruolo adatto.')?></p>
					<p><?=__('Parla di qualsiasi esperienza artistica, inclusi progetti personali (anche piccoli) o esami sostenuti.')?></p>
					<p><?=__('Se vuoi mostrarci alcuni dei tuoi lavori passati, abbozzi, concepts o hai idee su come migliorare il volto del team, non esitare!')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Altro">
					<p><?=__('Stupiscici.')?></p>
				</div>
			</div>
			<textarea id="letter" name="letter" cols="40" rows="5" required="required" class="form-control"></textarea>
		</div>
		<div class="form-group">
			<div>
				<span id="checkboxesHelpBlock"
						class="form-text text-muted"><?=sprintf(__('Dovresti leggere le <a href="%s">Informazioni sul trattamento dei dati personali</a> e almeno dare un\'occhiata alla pagina <a href="%s">Entra nel team</a> sul nostro sito prima di candidarti.'),
						'privacy.php', 'http://weeeopen.polito.it/entra-nel-team.html')?></a></span>
				<div class="form-check">
					<input name="mandatorycheckbox_1" id="mandatorycheckbox_0" type="checkbox" class="form-check-input"
							value="true" aria-describedby="checkboxesHelpBlock" required="required">
					<label for="mandatorycheckbox_0"
							class="form-check-label"><?=__('Ho letto le Informazioni sul trattamento dei dati personali e accetto le condizioni lì delineate')?></label>
				</div>
				<div class="form-check">
					<input name="mandatorycheckbox_0" id="mandatorycheckbox_1" type="checkbox" class="form-check-input"
							value="true" aria-describedby="checkboxesHelpBlock" required="required">
					<label for="mandatorycheckbox_1"
							class="form-check-label"><?=__('Dichiaro che tutte le informazioni inserite sono corrette e ho dato un\'occhiata alla pagina "Entra nel team" sul sito')?></label>
				</div>

			</div>
		</div>
		<div class="form-group">
			<button type="submit" class="btn btn-primary"><?=__('Invia')?></button>
		</div>
	</form>
</div>
<script type="text/javascript">
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

	function dottorandize() {
		if(document.getElementById('year').value === 'Dottorato') {
			document.getElementById('matricola').placeholder = 'd123456';
		} else {
			document.getElementById('matricola').placeholder = 's123456';
		}
	}

	dottorandize();
</script>
