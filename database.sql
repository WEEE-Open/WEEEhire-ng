create table users
(
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
	interview int,
	interviewer text,
	interviewertg text,
	interviewstatus int,
	answers int
);

create unique index users_matricola_uindex
	on users (matricola);

CREATE TABLE config 
(
	id VARCHAR not NULL primary key,
	value VARCHAR
);
INSERT INTO config (id, value) VALUES ('expiry', NULL);
INSERT INTO config (id, value) VALUES ('rolesUnavailable', NULL);


CREATE TABLE evaluation
(
    id_evaluation integer primary key autoincrement,
    ref_user_id     integer,
    id_evaluator    varchar,
    desc_evaluator  varchar not null,
    date            timestamp,
    vote            integer not null,

    foreign key (ref_user_id) references users (id)
)