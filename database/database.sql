create table users (
	id integer
		constraint users_id_pk
			primary key autoincrement,
	name text not null,
	surname text not null,
	degreecourse text not null,
	year text not null,
	matricola text not null,
	area text not null,
	letter text not null,
	published integer default 0 not null,
	emailed int default 0 not null,
	status int,
	recruiter text,
	submitted int not null,
	token text not null,
	recruitertg text,
	invitelink text,
	visiblenotes text default NULL,
	interview int,
	hold boolean not null default 0,
	interviewer text,
	interviewertg text,
	interviewstatus int,
	answers int
--	safetytestdate int
);

create unique index users_matricola_uindex
	on users(matricola);

create table config (
	id varchar not null primary key,
	value varchar
);

insert into config (id, value)
values ('expiry', null), ('notifyEmail', 0);

create table evaluation (
	id_evaluation integer primary key autoincrement,
	ref_user_id integer,
	id_evaluator varchar not null,
	desc_evaluator varchar not null,
	date integer,
	vote integer not null,
	foreign key (ref_user_id) references users (id) on delete cascade on update cascade
);

create index if not exists ref_user_id_index on evaluation(ref_user_id);

create table notes (
	uid text not null,
	candidate_id integer,
	note text not null,
	created_at datetime default current_timestamp not null,
	updated_at datetime default current_timestamp not null,
	foreign key (candidate_id) references users (id) on delete cascade on update cascade,
	primary key (uid, candidate_id)
);

create table if not exists positions (
	id varchar not null primary key,
	idx integer not null,
	available integer not null
);

-- this is generic, but actually used only for the positions aka anywhere the text can be dynamically changed
create table if not exists translations (
	id varchar not null,
	lang varchar not null,
	value varchar not null,
	unique(id, lang)
);

insert into positions (id, idx, available) values 
	('hardware-repair', 0, 1),
	('electronics', 1, 1),
	('python-software-dev', 2, 1),
	('php-software-dev', 3, 1),
	('javascript-software-dev', 4, 1),
	('vuejs-software-dev', 5, 1),
	('machine-learning-engineer', 6, 1),
	('sysadmin', 7, 1),
	('communication-and-social', 8, 1),
	('digital-content-creation', 9, 1),
	('creative-reuse', 10, 1),
	('other', 11, 1);

insert into translations (id, lang, value) values 
	('position.hardware-repair.name', 'it-IT', 'Riparazione hardware'),
	('position.electronics.name', 'it-IT', 'Elettronica'),
	('position.python-software-dev.name', 'it-IT', 'Sviluppo software Python'),
	('position.php-software-dev.name', 'it-IT', 'Sviluppo software PHP'),
	('position.javascript-software-dev.name', 'it-IT', 'Sviluppo software JavaScript'),
	('position.vuejs-software-dev.name', 'it-IT', 'Sviluppo software Vue.js'),
	('position.machine-learning-engineer.name', 'it-IT', 'Machine Learning Engineer'),
	('position.sysadmin.name', 'it-IT', 'Sysadmin'),
	('position.communication-and-social.name', 'it-IT', 'Comunicazione e social'),
	('position.digital-content-creation.name', 'it-IT', 'Creazione di contenuti digitali'),
	('position.creative-reuse.name', 'it-IT', 'Riuso creativo'),
	('position.other.name', 'it-IT', 'Altro'),
	('position.hardware-repair.name', 'en-US', 'Hardware repair'),
	('position.electronics.name', 'en-US', 'Electronics'),
	('position.python-software-dev.name', 'en-US', 'Python software development'),
	('position.php-software-dev.name', 'en-US', 'PHP software development'),
	('position.javascript-software-dev.name', 'en-US', 'JavaScript software development'),
	('position.vuejs-software-dev.name', 'en-US', 'Vue.js software development'),
	('position.machine-learning-engineer.name', 'en-US', 'Machine Learning Engineer'),
	('position.sysadmin.name', 'en-US', 'Sysadmin'),
	('position.communication-and-social.name', 'en-US', 'Communication and social'),
	('position.digital-content-creation.name', 'en-US', 'Digital content creation'),
	('position.creative-reuse.name', 'en-US', 'Creative reuse'),
	('position.other.name', 'en-US', 'Other'),
	('position.hardware-repair.description', 'it-IT', 'Descrivi qualsiasi tua esperienza di riparazione di computer (fissi o portatili), o assemblaggio, o saldatura di componenti elettronici.

Se non sai qualcosa, cosa fai per imparare in autonomia? Puoi anche fornire degli esempi.

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a riparare computer.'),
	('position.electronics.description', 'it-IT', 'Uno degli obiettivi del team è la progettazione di strumenti elettronici per la diagnostica a basso livello e il riuso dell''hardware recuperato.

Qual è il tuo rapporto con il mondo dell''elettronica? Ti interessa di più l''elettronica digitale o analogica (specialmente di potenza) o ti interessano entrambe?

Se hai mai realizzato qualche circuito o progetto oltre a quelli nei laboratori didattici, parlane con riferimento anche al metodo con cui è stato realizzato (breadboard, millefori, circuito stampato, componenti through-hole o SMD, etc...).

Indica anche se hai dimestichezza con qualche software di Electronic Design Automation (progettazione, simulazione, test e verifica, etc...).

Menziona anche quanto tempo potresti dedicare al team e se fai qualcos''altro di interessante nel tempo libero oltre a progettare circuiti.'),
	('position.python-software-dev.description', 'it-IT', 'Descrivi qualsiasi tua esperienza nel programmare in Python, e se hai un account su Github non esitare a condividerlo nella tua lettera!

Molti degli strumenti interni del team sono stati creati con python, per esempio la [Peracotta](https://github.com/WEEE-Open/peracotta) ed il [Pesto](https://github.com/WEEE-Open/pesto). Se conosci come lavorare con qt ed interagire con API, sei la persona che stiamo cercando!

Potresti menzionare se hai mai usato virtual environments, collaborato con qualcuno su un progetto software, o se sai scrivere in altri linguaggi che usiamo nel team, come JavaScript, PHP e Bash o altri ancora.

Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.'),
	('position.php-software-dev.description', 'it-IT', 'I principali progetti PHP del team sono [Tarallo](https://github.com/WEEE-Open/tarallo), [WEEEHire](https://github.com/WEEE-Open/WEEEHire-ng) e [crauto](https://github.com/WEEE-Open/crauto), puoi darci già un''occhiata per sapere a cosa vai incontro.

Descrivi qualsiasi tua esperienza nel programmare in PHP, e se hai un account su Github non esitare a condividerlo nella tua lettera!

Va bene anche "per l''esame di ... ho creato un programma che fa ..." o "ho fatto il sito web per la panetteria all''angolo".

Se conosci anche altri linguaggi non esitare a condividere la tua esperienza.

Puoi anche menzionare se conosci altri linguaggi di programmazione o hai mai partecipato ad altri progetti collaborativi.

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.'),
	('position.javascript-software-dev.description', 'it-IT', 'Da un paio d''anni abbiamo iniziato a migrare alcuni dei nostri progetti a Node.js. Cerchiamo persone capaci di lavorare con express, MySQL e creare API.

Parla di qualsiasi tua esperienza riguardante l''utilizzo di JavaScript (backend, app NodeJS, e frontend, sia vanilla JS, sia framework, in particolare Vue.js). Conosci altri linguaggi che usiamo in team, come Python, PHP e Bash, o altri ancora? Menzionali pure! E se hai un account su Github non esitare a condividerlo nella tua lettera!

Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.'),
	('position.vuejs-software-dev.description', 'it-IT', 'Da un paio d''anni abbiamo iniziato a migrare alcuni dei nostri progetti a Vue.js. Cerchiamo persone capaci di creare SPA e PWA con Vue.js integrate con servizi backend.

Parla di qualsiasi tua esperienza riguardante l''utilizzo di JavaScript (frontend, vanilla JS, Vue.js or any other framework, e backend, NodeJS). Conosci altri linguaggi che usiamo in team, come Python, PHP e Bash, o altri ancora? Menzionali pure! E se hai un account su Github non esitare a condividerlo nella tua lettera!

Oltre a seguire le lezioni, che metodo usi per imparare (e.g. seguire tutorial su internet, iniziare a scrivere codice e cercare man mano su Stack Overflow, etc...)?

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a digitare codice.'),
	('position.machine-learning-engineer.description', 'it-IT', 'Dal 2021-2022, grazie all''esperienza acquisita da alcuni membri del team in materia, e al lancio del nostro progetto di software per studenti, cerchiamo una figura che possa occuparsi della creazione di alcuni modelli volti a migliorare l''esperienza utente delle piattaforme web che abbiamo intenzione di sviluppare, in particolare nell''ambito del Natural Language Processing.

Se hai delle conoscenze riguardo a qualcuno tra Python, PyTorch, Tensorflow, Keras, Jupyter Notebook e GitHub, stiamo cercando proprio te!

Se in più sai ricercare paper scientifici su nuove tecnologie su ArXiv o simili, o hai intenzione di imparare a farlo, fantastico!

Se hai mai usato Linux, parlane liberamente: su tutti i computer che ripariamo installiamo Linux.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre a progettare intelligenze artificiali.'),
	('position.sysadmin.description', 'it-IT', 'Il compito dei sysadmin è assicurarsi che gli strumenti informatici a cui il team si appoggia per le sue operazioni siano al massimo della loro efficienza.

Per fare questo gestiscono i server che ospitano i nostri servizi, collaborano con gli sviluppatori per deployare e aggiornare i software che creiamo, monitorano lo stato di salute della nostra infrastruttura e si assicurano che i nostri dati siano protetti da attacchi e perdite.

I sysadmin seguono l''intero ciclo di vita dei servizi, dalla configurazione del server tramite Ansible, passando per la containerizzazione e gestione dei servizi, alle normali procedure di manutenzione.

Le competenze esercitate sono una generale conoscenza di Ansible e della containerizzazione, familiarità con la gestione e configurazione di software per server comuni come Nginx, PHP e MariaDB/PostgreSQL e maneggevolezza con il terminale Linux.

Se hai esperienza con alcune di queste cose, parlane liberamente.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero oltre ad amministrare server.'),
	('position.communication-and-social.description', 'it-IT', 'Hai buone capacità di comunicazione e organizzazione, ti piace il nostro team e vuoi aiutarci a migliorare la nostra immagine? Questo è il posto che fa per te!

Cerchiamo qualcuno che possa svolgere le seguenti mansioni:

- Scrittura e pubblicazione di post e storie per i nostri social
- Programmazione temporale dei contenuti
- Definizione della strategia di comunicazione in generale
- Brainstorming di idee per podcast e video tematici
- Intrattenimento di rapporti con terzi, altre associazioni e ospiti

Ti troverai a lavorare in sinergia con i creatori di contenuti digitali, che si occuperanno di produrre materiale grafico e video (o potrai occupartene tu stesso se ti va e ne sei capace).

Ora parlaci di te.

Descrivi qualsiasi tua esperienza nel gestire pagine o profili (e.g. personali, di attività commerciali, di meme nonsense, etc...) sui social network, in particolare Facebook e Instagram.

Menziona anche i risultati raggiunti con tali attività promozionali, se possibile.

Se dovessi spiegare in due righe cosa fa il team, cosa diresti?

Indica anche quanto tempo potresti dedicare a queste attività e se hai qualche altro interesse.'),
	('position.digital-content-creation.description', 'it-IT', 'Sprigiona la tua vena creativa entrando nel nostro team! Abbiamo bisogno di figure che si occupino di realizzare:

- Design di manifesti, infografiche, biglietti da visita
- Elementi grafici per pagine web e social network
- Sfondi, icone e immagini personalizzate per i nostri software
- Redesign e modding dei case dei computer riparati
- Jingle e motivi musicali per i nostri video

Se almeno una, o più di una, di queste attività di interessano, questo è il ruolo adatto.

Parla di qualsiasi esperienza artistica, inclusi progetti personali (anche piccoli) o esami sostenuti.

Se vuoi mostrarci alcuni dei tuoi lavori passati, abbozzi, concepts o hai idee su come migliorare il volto del team, non esitare!

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero.'),
	('position.creative-reuse.description', 'it-IT', 'Non tutti i computer che ci arrivano sono riparabili, ma vorremmo comunque minimizzare la quantità di materiale che finisce nel bidone.

Se hai manualità e/o esperienze nel riuso creativo e/o making è il momento di dirlo.

Puoi anche aggiungere se hai idee su come potremmo riutilizzare case vuoti, schede madri dall''estetica peculiare o i piatti a specchio di hard disk rotti.

Accenna anche a che metodo seguiresti per progettare queste cose.

Menziona anche quanto tempo potresti dedicare alle attività in team e se fai altro di interessante nel tempo libero.'),
	('position.other.description', 'it-IT', 'Stupiscici.'),
	('position.hardware-repair.description', 'en-US', 'Describe anything about your experience in computer repairs (desktop or laptops), or building computers, or soldering of electronic components.

If you don''t know something, what do you do to learn it yourself, usually? You can give us some examples as well.

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than repairing computers.'),
	('position.electronics.description', 'en-US', 'One of the goals of the team is to develop electronic tools for low-level hardware diagnostics and the reuse of recovered hardware.

What''s your relationship with electronics? Are you more interested in digital or analog (especially power) electronics do you like both equally?

If you ever made any electronic circuit or project other than those for academic courses, tell us. Explain also the method you''ve used to build them (breadboard, prototype board, PCB, through-hole components or SMD, etc...)

Tell us if you have experience with any Electronic Design Automation software (project, simulation, test and verification, etc...).

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than designing circuits.'),
	('position.python-software-dev.description', 'en-US', 'Describe any prior experience you have in coding in Python, and don''t hesitate sharing your Github account in your letter if you have one!

Many of the internal tools of the team have been created with python, for example [Peracotta](https://github.com/WEEE-Open/peracotta) and [Pesto](https://github.com/WEEE-Open/pesto). If you know how to work with qt and interact with APIs, you are the person we are looking for!

You could mention if you have ever used virtual environments, collaborated with someone else on a software project, or if you can code in other programming languages.

Besides attending lectures, what do you do to learn? E.g. watching or reading tutorials online, starting to write code and looking things up on Stack Overflow as you go, etc...

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than typing code.'),
	('position.php-software-dev.description', 'en-US', 'Our main PHP projects are [Tarallo](https://github.com/WEEE-Open/tarallo), [WEEEHire](https://github.com/WEEE-Open/WEEEHire-ng) and [crauto](https://github.com/WEEE-Open/crauto), you can have a look so you know what you''re going to work on.

Describe any prior experience you have in coding in PHP, and don''t hesitate sharing your Github account in your letter if you have one!

Even "for the exam of ... I created a program that does ..." or "I made the website for the bakery around the corner" are fine.

If you know other programming languages, don''t hesitate to share your experience.

You can also mention whether you know other programming languages or have ever participated to other collaborative projects.

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than typing code.'),
	('position.javascript-software-dev.description', 'en-US', 'Since a couple of years we started migrating some of our projects to Node.js. We are looking for people capable of working with express, MySQL and creating APIs.

Talk about any experience you have with JavaScript (backend, NodeJS apps, and frontend, vanilla JS, or any framework, especially Vue.js). Do you know other languages we use in the team, like Python, PHP and Bash, or others? Mention them! 

And if you have a Github account don''t hesitate to share it in your letter!

Besides attending lectures, what do you do to learn? E.g. watching or reading tutorials online, starting to write code and looking things up on Stack Overflow as you go, etc...

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than typing code.'),
	('position.vuejs-software-dev.description', 'en-US', 'Since a couple of years we started migrating some of our projects to Vue.js. We are looking for people capable of creating SPAs and PWAs with Vue.js integrated with backend services.

Talk about any experience you have with JavaScript (frontend, vanilla JS, Vue.js or any other framework, and backend, NodeJS). Do you know other languages we use in the team, like Python, PHP and Bash, or others? Mention them! And if you have a Github account don''t hesitate to share it in your letter!

Besides attending lectures, what do you do to learn? E.g. watching or reading tutorials online, starting to write code and looking things up on Stack Overflow as you go, etc...

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than typing code.'),
	('position.machine-learning-engineer.description', 'en-US', 'From 2021-2022, thanks to the experience gained by some team members on the subject, and the launch of our project of software for students, we are looking for a figure who can take care of the creation of some models aimed at improving the user experience of the web platforms we intend to develop, in particular in the field of Natural Language Processing.

If you know any of Python, PyTorch, Tensorflow, Keras, Jupyter Notebook and GitHub, we are looking for you!

If you can also research scientific papers on new technologies on ArXiv or similar platforms, or you want to learn how to do it, great!

If you ever used Linux, feel free to talk about it: we install Linux on every computer that we repair.

Mention how much time you have for team activities, too, and if you have any other hobby or interests besides designing AIs.'),
	('position.sysadmin.description', 'en-US', 'Our sysadmins ensure that IT tools - which are essential for the team - are operating at peak efficiency.

To do this, they manage our servers, collaborate with developers to deploy and update the software we made, monitor the health of our infrastructure and ensure that our data is safe from attacks and other disasters.

Sysadmins oversee the entire service life cycle, from server configuration through Ansible, to containerization and service management, to routine maintenance procedures.

The competences you will use are general knowledge of Ansible and containes, familiarity with managing and configuring common server software like Nginx, PHP and MariaDB/PostgreSQL, and familiarity with the Linux terminal.

If you have any experience with this stuff, it''s time to talk about it.

Mention how much time you have for team activities, too, and if you have any other hobby or interests other than managing servers.'),
	('position.communication-and-social.description', 'en-US', 'Do you have good communication and organizational skills, you like our team and you want to help us improve our public image? This is the right place!

We are looking for someone to perform the following tasks:

- Write and publish posts/stories for our social media
- Organize temporal scheduling of contents
- Define the communication strategy in general
- Come up with new ideas for podcasts and videos
- Maintain relations with third parties, other associations and guests

You will work together with digital content creators, who will provide graphic and video resources (or you can make them yourself, if you have the skills).

Now tell us about yourself.

Describe any experience you have in managing pages or profiles (e.g. personal, business, of random memes, etc...) on social networks, in particular Facebook and Instagram.

Mention also the results you achieved with such promotional activities, if you can.

If you had to explain in a few lines what does the team do, what would you write?

Mention how much time you have for team activities, too, and if you have any other hobby or interests.'),
	('position.digital-content-creation.description', 'en-US', 'Unleash your inner creativity by joining our team! We are looking for people that will produce:

- Designs for posters, infographics, business cards
- Graphic elements for web pages and social networks
- Backgrounds, icons and custom images for our software
-Redesigns and modding of repaired computer cases
- Jingles and music for our videos

If you''re interested in at least one, or more than one, of these activities, this is the role for you.

Tell us about any experience you have with arts, including personal projects (even small ones) or exams.

If you want to show us some of your past works, sketches, concepts or you have any idea on how to improve the teams public "face", do not hesitate and tell us!

Mention how much time you have for team activities, too, and if you have any other hobby or interests.'),
	('position.creative-reuse.description', 'en-US', 'Not all the computers that we get are repairable, but we''d still like to minimize the quantity of stuff that goes into the trash can.

If you have any DIY skills and/or experiences in creative reuse and/or "making" this is the moment to tell us.

You can also add some ideas (if you have any) on how we could reuse empty cases, particularly beautiful motherboards or shiny plates from broken hard disk drives.

Point out which method you''d follow to develop these projects, too.

Mention how much time you have for team activities, too, and if you have any other hobby or interests.'),
	('position.other.description', 'en-US', 'Surprise us.');

create trigger if not exists delete_positions_translation
	after delete on positions
	begin
		delete from translations where id = 'position.' || old.id || '.name';
		delete from translations where id = 'position.' || old.id || '.description';
	end;

create trigger if not exists update_positions_translation
	after update on positions
	begin
		update translations set id = 'position.' || new.id || '.name' where id = 'position.' || old.id || '.name';
		update translations set id = 'position.' || new.id || '.description' where id = 'position.' || old.id || '.description';
	end;

-- Be careful not to change this line other than the number of the version, this is parsed with regex by the updater
insert into config (id, value) values ('SchemaVersion', '1');