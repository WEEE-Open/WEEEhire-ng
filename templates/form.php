<?php

/** @var string|null $rolesUnavailable */

if ($rolesUnavailable === null) {
	$roles = [];
} else {
	$roles = explode('|', $rolesUnavailable);
	$roles = array_combine($roles, $roles);
}
require_once 'roles.php';
$allRoles = getRoles();
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
					echo sprintf(__('Hai già inviato una candidatura con questa matricola. Puoi controllare la pagina con lo stato dal link che ti abbiamo mandato via email.'));
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
			<label for="name" class="col-md-2 col-lg-1 col-form-label"><?=__('Nome')?></label>
			<div class="col-md-4 col-lg-4">
				<input id="name" name="name" type="text" required="required" class="form-control">
			</div>
			<label for="surname" class="col-md-2 col-lg-1 col-form-label"><?=__('Cognome')?></label>
			<div class="col-md-4 col-lg-5">
				<input id="surname" name="surname" type="text" required="required" class="form-control">
			</div>
		</div>

		<div class="form-group row">
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
					<?php foreach ($allRoles as $value => $role) : ?>
						<option <?= isset($roles[$value]) ? 'disabled' : '' ?> value="<?= $this->e($value) ?>"><?= $this->e($role) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php if (count($roles) > 0) : ?>
		<div class="form-group">
			<p><small><?= __('Al momento alcune aree del team sono al completo, è possibile candidarsi solo nelle aree selezionabili dall\'elenco. In futuro le aree disponibili potrebbero cambiare senza preavviso.') ?></small></p>
		</div>
		<?php endif; ?>
		<div class="form-group">
			<label for="letter"><b><?=__('Lettera motivazionale')?></b></label>
			<div id="mlet-explain">
				<div class="form-text" id="mlet-explain-">
					<p><?=__('Seleziona l\'area del team che più ti interessa e qui compariranno delle linee guida su cosa scrivere.') ?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Riparazione-hardware">
					<p><?=__('Descrivi qualsiasi tua esperienza di riparazione di computer (fissi o portatili), o assemblaggio, o saldatura di componenti elettronici.')?></p>
					<p><?=__('Se non sai qualcosa, cosa fai per imparare in autonomia? Puoi anche fornire degli esempi.')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a riparare computer.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Elettronica">
					<p><?=__('Uno degli obiettivi del team è la progettazione di strumenti elettronici per la diagnostica a basso livello e il riuso dell\'hardware recuperato.') ?></p>
					<p><?=__('Qual è il tuo rapporto con il mondo dell\'elettronica? Ti interessa di più l\'elettronica digitale o analogica (specialmente di potenza) o ti interessano entrambe?') ?></p>
					<p><?=__('Se hai mai realizzato qualche circuito o progetto oltre a quelli nei laboratori didattici, parlane con riferimento anche al metodo con cui è stato realizzato (breadboard, millefori, circuito stampato, componenti through-hole o SMD, etc...).')?></p>
					<p><?=__('Indica anche se hai dimestichezza con qualche software di Electronic Design Automation (progettazione, simulazione, test e verifica, etc...).') ?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare al team e se fai qualcos\'altro di interessante nel tempo libero oltre a progettare circuiti.') ?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sysadmin">
					<p><?=__('Il team ha alcuni server Linux con delle web app e dei software di SSO. La configurazione è automatizzata in parte con Ansible e in parte con container Docker/Podman.') ?></p>
					<p><?=__('Tra nuovi software da installare e configurare, container da migliorare e ordinaria manutenzione da eseguire (installazione di aggiornamenti), il lavoro non manca mai.')?></p>
					<p><?=__('Se hai già esperienza con queste cose, ottimo, ma il minimo richiesto è non spaventarsi davanti a un terminale Linux.')?></p>
					<p><?=__('È piuttosto importante che tu sia una persona cauta e attenta, che legga con cura il manuale e i messaggi prima di lanciare comandi. Se ti ritieni tale e vuoi mettere le mani in pasta su dei server, dicci qualcosa su di te e che esperienza hai con Linux, con i server o con l\'informatica in generale.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre ad amministrare server.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sviluppo-software-Python">
					<p><?=__('Descrivi qualsiasi tua esperienza nel programmare in Python, e se hai un account su Github non esitare a condividerlo nella tua lettera!')?></p>
					<p><?=__('Dal 2021-2022 abbiamo lanciato un programma per il miglioramento della qualità della vita degli studenti (<a href="https://weeeopen.polito.it/skeeell" target="_blank">skeeell</a>). Stiamo lavorando per creare <a href="https://github.com/weee-open/skeeelled" target="_blank">skeeelled</a>, una piattaforma per alzare il livello di e-learning fornito a tutti i 40\'000 studenti del Poli. Se hai già lavorato su backend e/o REST API in Python (es. Django, Flask), o in altri linguaggi, ti stiamo cercando! Noi in particolare stiamo usando <a href="https://fastapi.tiangolo.com" target="_blank">FastAPI</a>.')?></p>
					<p><?=__('Potresti menzionare se hai mai usato virtual environments, collaborato con qualcuno su un progetto software, o se sai scrivere in altri linguaggi che usiamo nel team, come JavaScript, PHP e Bash o altri ancora.')?></p>
					<p><?=__('Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sviluppo-software-PHP">
					<p><?=__('I principali progetti PHP del team sono <a href="https://github.com/WEEE-Open/tarallo" target="_blank">Tarallo</a>, <a href="https://github.com/WEEE-Open/WEEEHire-ng" target="_blank">WEEEHire</a> e <a href="https://github.com/WEEE-Open/crauto" target="_blank">crauto</a>, puoi darci già un\'occhiata per sapere a cosa vai incontro.')?></p>
					<p><?=__('Dal 2021-2022 abbiamo anche instaurato una collaborazione con gli sviluppatori del bot Telegram <a href="https://t.me/inginf_bot" target="_blank">inginf_bot</a>, per il nostro progetto di miglioramento della qualità della vita degli studenti.')?></p>
					<p><?=__('Parla di qualsiasi tua esperienza nello scrivere software in PHP o per il web in generale. Va bene anche "per l\'esame di ... ho creato un programma che fa ..." o "ho fatto il sito web per la panetteria all\'angolo".') ?></p>
					<p><?=__('Puoi anche menzionare se conosci altri linguaggi di programmazione o hai mai partecipato ad altri progetti collaborativi.')?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Sviluppo-software-JavaScript">
					<p><?=__('Dal 2021-2022 abbiamo lanciato un nuovo progetto per migliorare la qualità della vita degli studenti (<a href="https://weeeopen.polito.it/skeeell" target="_blank">skeeell</a>). Abbiamo creato <a href="https://weee.link/skeeep" target="_blank">skeeep</a>, stiamo lavorando per creare <a href="https://github.com/weee-open/skeeelled" target="_blank">skeeelled</a> (migliore e-learning per tutti i 40\'000 studenti del Poli), e abbiamo instaurato una collaborazione con gli sviluppatori dell\'estensione per browser <a href="https://chrome.google.com/webstore/detail/politools/fbbjhoaakfhbggkegckmjafkffaofnkd?hl=it" target="_blank">PoliTools</a>.')?></p>
					<p><?=__('Parla di qualsiasi tua esperienza riguardante l\'utilizzo di JavaScript (frontend - in particolare ReactJS e Angular -, backend, app NodeJS). Conosci altri linguaggi che usiamo in team, come Python, PHP e Bash, o altri ancora? Menzionali pure! E se hai un account su Github non esitare a condividerlo nella tua lettera!')?></p>
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
				<div class="form-text d-none" id="mlet-explain-Machine-Learning-Engineer">
					<p><?=__('Dal 2021-2022, grazie all\'esperienza acquisita da alcuni membri del team in materia, e al lancio del nostro progetto di software per studenti, cerchiamo una figura che possa occuparsi della creazione di alcuni modelli volti a migliorare l\'esperienza utente delle piattaforme web che abbiamo intenzione di sviluppare, in particolare nell\'ambito del Natural Language Processing.')?></p>
					<p><?=__('Se hai delle conoscenze riguardo a qualcuno tra Python, PyTorch, Tensorflow, Keras, Jupyter Notebook e GitHub, stiamo cercando proprio te!')?></p>
					<p><?=__('Se in più sai ricercare paper scientifici su nuove tecnologie su ArXiv o simili, o hai intenzione di imparare a farlo, fantastico!') ?></p>
					<p><?=__('Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.')?></p>
					<p><?=__('Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a progettare intelligenze artificiali.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire, e se hai già lavorato su qualche progetto non esitare a condividerne il link nella tua lettera!.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Comunicazione-e-social">
					<p><?=__('Hai buone capacità di comunicazione e organizzazione, ti piace il nostro team e vuoi aiutarci a migliorare la nostra immagine? Questo è il posto che fa per te!')?></p>
					<p><?=__('Cerchiamo qualcuno che possa svolgere le seguenti mansioni:')?></p>
					<ul>
						<li><?=__('Scrittura e pubblicazione di post e storie per i nostri social')?></li>
						<li><?=__('Programmazione temporale dei contenuti')?></li>
						<li><?=__('Definizione della strategia di comunicazione in generale')?></li>
						<li><?=__('Brainstorming di idee per podcast e video tematici')?></li>
						<li><?=__('Intrattenimento di rapporti con terzi, altre associazioni e ospiti')?></li>
					</ul>
					<p><?=__('Ti troverai a lavorare in sinergia con i creatori di contenuti digitali, che si occuperanno di produrre materiale grafico e video (o potrai occupartene tu stesso se ti va e ne sei capace).')?></p>
					<p><?=__('Ora parlaci di te.')?></p>
					<p><?=__('Descrivi qualsiasi tua esperienza nel gestire pagine o profili (e.g. personali, di attività commerciali, di <i>meme</i> nonsense, etc...) sui social network, in particolare Facebook e Instagram.')?></p>
					<p><?=__('Menziona anche i risultati raggiunti con tali attività promozionali, se possibile.')?></p>
					<p><?=__('Se dovessi spiegare in due righe cosa fa il team, cosa diresti?')?></p>
					<p><?=__('Indica anche quanto tempo potresti dedicare a queste attività e se hai qualche altro interesse.')?></p>
					<p><?=__('Queste <strong>sono solo linee guida</strong>, scrivi tutto ciò che ti sembra rilevante dire.')?></p>
				</div>
				<div class="form-text d-none" id="mlet-explain-Creazione-di-contenuti-digitali">
					<p><?=__('Sprigiona la tua vena creativa entrando nel nostro team! Abbiamo bisogno di figure che si occupino di realizzare:')?></p>
					<ul>
						<li><?=__('Design di manifesti, infografiche, biglietti da visita')?></li>
						<li><?=__('Elementi grafici per pagine web e social network')?></li>
						<li><?=__('Sfondi, icone e immagini personalizzate per i nostri software')?></li>
						<li><?=__('Redesign e modding dei case dei computer riparati')?></li>
						<li><?=__('Jingle e motivi musicali per i nostri video')?></li>
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
						class="form-text text-muted"><?=sprintf(
							__('Dovresti leggere le <a href="%s">Informazioni sul trattamento dei dati personali</a> e almeno dare un\'occhiata alla pagina <a href="%s">Attività</a> sul nostro sito prima di candidarti.'),
							'privacy.php',
							__('https://weeeopen.polito.it/attivita/')
						)?></a></span>
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

	let yearSelector = document.getElementById("year");
	let courseSelector = document.getElementById("degreecourse");
	// array of objects for each courses
	let selectOptions = [
		{
			bachelor: [
				{
					optgroup: "<?= __('Architettura e pianificazione urbanistica (L, LM)') ?>",
					options: ["Architettura", "Pianificazione Territoriale, Urbanistica E Paesaggistico-ambientale"]
				},
				{
					optgroup: "<?= __('Design (L, LM)') ?>",
					options: ['Design E Comunicazione Visiva']
				},
				{
					optgroup: "<?= __('Ingegneria (L, LM)') ?>",
					options: ["Electronic And Communications Engineering", "Ingegneria Aerospaziale", "Ingegneria Biomedica", "Ingegneria Chimica E Alimentare", "Ingegneria Civile",
						"Ingegneria Dei Materiali", "Ingegneria Del Cinema E Dei Mezzi Di Comunicazione", "Ingegneria Dell'autoveicolo", "Automotive Engineering", "Ingegneria Della Produzione Industriale",
						"Ingegneria Edile", "Ingegneria Elettrica", "Ingegneria Elettronica", "Ingegneria Energetica", "Ingegneria Fisica", "Ingegneria Gestionale L-9",
							"Ingegneria Gestionale L-8", "Ingegneria Informatica", "Computer Engineering", "Ingegneria Meccanica", "Mechanical Engineering",
						"Ingegneria Per L'ambiente E Il Territorio", "Matematica Per L'ingegneria"]
				}]
		},
		{
			master:[
				{
					optgroup: "<?=__('Architettura e pianificazione urbanistica (L, LM)')?>",
					options: ["Architettura Costruzione Citta'", "Architecture Construction City", "Architettura Per Il Patrimonio", "Architecture For Heritage",
							"Architettura Per La Sostenibilita'", "Architecture For Sustainability", "Automotive Engineering", "Communications And Computer Networks Engineering", "Data Science And Engineering",
							"Digital Skills For Sustainable Societal Transitions", "Economia Dell'ambiente, Della Cultura E Del Territorio", "Geografia E Scienze Territoriali",
							"Pianificazione Territoriale, Urbanistica E Paesaggistico-Ambientale", "Territorial, Urban, Environmental And Landscape Planning", "Progettazione Delle Aree Verdi E Del Paesaggio"]
				},
				{
					optgroup: "<?=__('Design (L, LM)')?>",
					options: ["Design Sistemico"]
				},
				{
					optgroup: "<?=__('Ingegneria (L, LM)')?>",
					options: ["ICT For Smart Societies", "Ingegneria Aerospaziale", "Ingegneria Biomedica", "Ingegneria Chimica E Dei Processi Sostenibili", "Ingegneria Civile",
							  "Civil Engineering", "Ingegneria Dei Materiali", "Ingegneria Del Cinema E Dei Mezzi Di Comunicazione", "Ingegneria Della Produzione Industriale E Dell'innovazione Tecnologica",
							  "Ingegneria Edile", "Building Engineering", "Ingegneria Elettrica", "Ingegneria Elettronica (Electronic Engineering)", "Electronic Engineering", "Ingegneria Energetica E Nucleare",
							  "Energy And Nuclear Engineering", "Ingegneria Gestionale", "Engineering And Management", "Ingegneria Informatica (Computer Engineering)", "Computer Engineering",
							  "Ingegneria Matematica", "Ingegneria Meccanica", "Mechanical Engineering", "Ingegneria Per L'ambiente E Il Territorio", "Environmental And Land Engineering",
							  "Mechatronic Engineering", "Nanotechnologies For Icts", "Petroleum And Mining Engineering", "Physics Of Complex Systems (Fisica Dei Sistemi Complessi)",
							  "Physics Of Complex Systems"]
				}]
		}
	]

	yearSelector.onchange = () => {
		let val = yearSelector.options[yearSelector.selectedIndex].value;

		// remove all child
		courseSelector.textContent = '';

		// to show first element blank
		let option = document.createElement('option');
		option.setAttribute('value', '');
		option.appendChild(document.createTextNode(''));
		courseSelector.appendChild(option);

		// build inner HTML first iterate two courses then optgroup put every option to optgroup
		selectOptions.forEach((degree, index) => {
			if ( (val === '1º Triennale' || val === '2º Triennale' || val === '3º Triennale') && index === 0) {
				degree.bachelor.forEach((singleOptgroup) => {
					buildOptions(singleOptgroup);
				});
			} else if ( (val === '1º Magistrale' || val === '2º Magistrale') && index === 1) {
				degree.master.forEach((singleOptgroup) => {
					buildOptions(singleOptgroup);
				});
			}
		});

	};

	// build inside of optgroup
	function buildOptions( singleOptgroup ) {
		let newOptgroup = document.createElement('optgroup');
		newOptgroup.setAttribute('label', singleOptgroup.optgroup);

		singleOptgroup.options.forEach((value) => {

			let option = document.createElement('option');
			option.setAttribute('value', value);
			option.appendChild(document.createTextNode(value));
			newOptgroup.appendChild(option);
		});

		courseSelector.appendChild(newOptgroup);
	}




</script>
