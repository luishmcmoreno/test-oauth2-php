/*
Created		9/10/2018
Modified		16/10/2018
Project		
Model			
Company		
Author		
Version		
Database		PostgreSQL 8.1 
*/


/* Create Tables */


Create sequence id_seq start 1;

Create table "profile_userprofile"
(
	"id" Integer NOT NULL Default nextval('id_seq'::regclass),
	"id_user_type" Integer NOT NULL,
	"id_user_group" Integer NOT NULL,
	"email" Varchar(80),
	"password" Varchar(40),
 primary key ("id")
) Without Oids;

Alter Table "profile_userprofile" add UNIQUE ("id");


Create sequence id_application_third_part_seq start 1;

Create table "application_third_part"
(
	"id_application_third_part" Integer NOT NULL Default nextval('id_application_third_part_seq'::regclass),
	"client_id" Varchar(30) NOT NULL UNIQUE,
	"client_secret" Varchar(30) NOT NULL UNIQUE,
	"email" Varchar(80),
	"token" Text,
	"name" Varchar(80),
	"password" Varchar(36),
	"token_expire" Timestamp with time zone,
	"dt_create" Char(20),
	"old_password" Char(36),
	"status_active" Boolean NOT NULL Default true,
 primary key ("id_application_third_part")
) Without Oids;

Alter Table "application_third_part" add UNIQUE ("id_application_third_part");

Create table "application_third_part_user"
(
	"id_application_third_part" Integer NOT NULL,
	"user_id" Integer NOT NULL,
	"acess_token" Text NOT NULL,
	"expire_token" Timestamp with time zone NOT NULL,
	"dt_create" Timestamp with time zone NOT NULL,
	"token_id"  character varying not null,
	"is_revoked" boolean not null default false

 primary key ("id_application_third_part","user_id")
) Without Oids;


Create sequence id_user_type_seq start 1;

Create table "user_type"
(
	"id_user_type" Integer NOT NULL Default nextval('id_user_type_seq'::regclass),
 primary key ("id_user_type")
) Without Oids;

Alter Table "user_type" add UNIQUE ("id_user_type");

Create sequence id_user_group_seq start 1;

Create table "user_group"
(
	"id_user_group" Integer NOT NULL Default nextval('id_user_group_seq'::regclass),
 primary key ("id_user_group")
) Without Oids;

Alter Table "user_group" add UNIQUE ("id_user_group");

Create table "authorization_code"
(
	"id_application_third_part" Integer NOT NULL,
	"id" Integer NOT NULL,
	"authorization_code" Varchar(40),
	"expire_code" Timestamp with time zone,
	"redirect_uri" Varchar(2000),
	"state" Varchar(20),
	"dt_create" Timestamp with time zone NOT NULL,
 primary key ("id_application_third_part","id")
) Without Oids;

Create sequence id_refresh_token_seq start 1; 
Create table "refresh_token"
(
	"id_refresh_token" Integer NOT NULL Default nextval('id_refresh_token_seq'::regclass),
	"id_application_third_part" Integer NOT NULL,
	"user_id" Integer NOT NULL,
	"expire_token" Timestamp with time zone NOT NULL,
	"dt_create" Timestamp with time zone NOT NULL Default timezone('utc'::text, now()) ,
	"token_id"  character varying(100) not null,
	"is_revoked" boolean not null default false

 primary key ("id_refresh_token")
) Without Oids;

Alter table "refresh_token" add  foreign key ("id_application_third_part") references "client_api" ("id_application_third_part") on update restrict on delete restrict;
Alter table "refresh_token" add  foreign key ("user_id") references "profile_userprofile" ("id") on update restrict on delete restrict;

Create sequence id_access_token_seq start 1; 
Create table "access_token"
(
	"id_access_token" Integer NOT NULL Default nextval('id_access_token_seq'::regclass),
	"id_application_third_part" Integer NOT NULL,
	"user_id" Integer NOT NULL,
	"expire_token" Timestamp with time zone NOT NULL,
	"dt_create" Timestamp with time zone NOT NULL Default timezone('utc'::text, now()) ,
	"token_id"  character varying(100) not null,
	"is_revoked" boolean not null default false

 primary key ("id_access_token")
) Without Oids;

Alter table "access_token" add  foreign key ("id_application_third_part") references "client_api" ("id_application_third_part") on update restrict on delete restrict;
Alter table "access_token" add  foreign key ("user_id") references "profile_userprofile" ("id") on update restrict on delete restrict;

Create sequence id_auth_code_seq start 1; 
Create table "auth_code"
(
	"id_auth_code" Integer NOT NULL Default nextval('id_auth_code_seq'::regclass),
	"id_application_third_part" Integer NOT NULL,
	"user_id" Integer NOT NULL,
	"expire_token" Timestamp with time zone NOT NULL,
	"dt_create" Timestamp with time zone NOT NULL Default timezone('utc'::text, now()) ,
	"code_id"  character varying(100) not null,
	"is_revoked" boolean not null default false

 primary key ("id_auth_code")
) Without Oids;

Alter table "auth_code" add  foreign key ("id_application_third_part") references "client_api" ("id_application_third_part") on update restrict on delete restrict;
Alter table "auth_code" add  foreign key ("user_id") references "profile_userprofile" ("id") on update restrict on delete restrict;


/* Create Foreign Keys */

Alter table "application_third_part_user" add  foreign key ("user_id") references "profile_userprofile" ("id") on update restrict on delete restrict;

Alter table "authorization_code" add  foreign key ("id") references "profile_userprofile" ("id") on update restrict on delete restrict;

Alter table "application_third_part_user" add  foreign key ("id_application_third_part") references "application_third_part" ("id_application_third_part") on update restrict on delete restrict;

Alter table "authorization_code" add  foreign key ("id_application_third_part") references "application_third_part" ("id_application_third_part") on update restrict on delete restrict;

Alter table "profile_userprofile" add  foreign key ("id_user_type") references "user_type" ("id_user_type") on update restrict on delete restrict;

Alter table "profile_userprofile" add  foreign key ("id_user_group") references "user_group" ("id_user_group") on update restrict on delete restrict;

Create sequence id_redirect_uri_seq start 1;
Create table "redirect_uri"
(
	"id_redirect_uri" Integer NOT NULL  Default nextval('id_redirect_uri_seq'::regclass),
	"description" Varchar(2000),
	"id_application_third_part" Integer NOT NULL,
 primary key ("id_redirect_uri")
) Without Oids;

Alter Table "redirect_uri" add UNIQUE ("id_redirect_uri");


Alter table "refresh_token" add  foreign key ("id_application_third_part") references "client_api" ("id_application_third_part") on update restrict on delete restrict;
Alter table "refresh_token" add  foreign key ("user_id") references "profile_userprofile" ("id") on update restrict on delete restrict;


/* Create Foreign Keys */

Alter table "redirect_uri" add  foreign key ("id_application_third_part") references "application_third_part" ("id_application_third_part") on update restrict on delete restrict;



Create sequence id_grant_type_seq start 1;

Create table "grant_type"
(
	"id_grant_type" Integer NOT NULL Default nextval('id_grant_type_seq'::regclass),
	"description" Varchar(40),
	"code" Varchar(40),
 primary key ("id_grant_type")
) Without Oids;


insert into grant_type (description, code) values ('Refresh Token', 'refresh_token');
insert into grant_type (description, code) values ('Password', 'password');
insert into grant_type (description, code) values ('Authorization Code', 'authorization_code');


Create table "grant_type_client_api"
(
	"id_application_third_part" Integer NOT NULL,
	"id_grant_type" Integer NOT NULL,
 primary key ("id_grant_type","id_application_third_part")
) Without Oids;
