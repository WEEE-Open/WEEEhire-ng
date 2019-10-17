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
