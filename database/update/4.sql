ALTER TABLE users ADD COLUMN timeofapproval TEXT;
INSERT INTO config(id, value) VALUES ('ghostdays', NULL)