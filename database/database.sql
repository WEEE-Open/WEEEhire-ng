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
values ('expiry', null), ('rolesAvailable', null), ('notifyEmail', 0);

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

-- Be careful not to change this line other than the number of the version, this is parsed with regex by the updater
insert into config (id, value) values ('SchemaVersion', '0');