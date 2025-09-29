
drop trigger if exists delete_positions_translation;
drop trigger if exists update_positions_translation;

create trigger if not exists delete_positions_translation
	after delete on positions
	begin
		delete from translations where id = 'position.' || old.id || '.name';
		delete from translations where id = 'position.' || old.id || '.description';
		delete from translations where id = 'position.' || old.id || '.summary';
	end;

create trigger if not exists update_positions_translation
	after update on positions
	begin
		update translations set id = 'position.' || new.id || '.name' where id = 'position.' || old.id || '.name';
		update translations set id = 'position.' || new.id || '.description' where id = 'position.' || old.id || '.description';
        update translations set id = 'position.' || new.id || '.summary' where id = 'position.' || old.id || '.summary';
	end;