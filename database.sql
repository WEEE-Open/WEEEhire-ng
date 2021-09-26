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
	notes text,
	visiblenotes text default NULL,
	interview int,
	hold boolean not null default 0,
	interviewer text,
	interviewertg text,
	interviewstatus int,
	answers int
);

create unique index users_matricola_uindex
	on users(matricola);

CREATE TABLE config (
	id varchar not null primary key,
	value varchar
);

INSERT INTO config (id, value)
VALUES ('expiry', null);

INSERT INTO config (id, value)
VALUES ('rolesUnavailable', null), ('notifyEmail', 0);

create table evaluation (
	id_evaluation integer primary key autoincrement,
	ref_user_id integer
		references users,
	id_evaluator varchar not null,
	desc_evaluator varchar not null,
	date integer,
	vote integer not null,
	foreign key (ref_user_id) references users (id)
);
