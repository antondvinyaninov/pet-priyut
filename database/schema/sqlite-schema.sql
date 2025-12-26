CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "osvv_requests"(
  "id" integer primary key autoincrement not null,
  "contact_name" varchar not null,
  "contact_phone" varchar not null,
  "contact_email" varchar,
  "animal_type" varchar check("animal_type" in('cat', 'dog', 'other')) not null default 'cat',
  "animal_type_other" varchar,
  "animal_gender" varchar check("animal_gender" in('male', 'female', 'unknown')) not null default 'unknown',
  "animal_age" varchar,
  "animal_description" text,
  "location_address" text not null,
  "location_landmark" varchar,
  "status" varchar check("status" in('new', 'processing', 'capture_scheduled', 'captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled')) not null default 'new',
  "notes" text,
  "user_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "animals_count" integer not null default '1',
  "district" varchar,
  "has_bite" tinyint(1) not null default '0',
  "is_pregnant" tinyint(1) not null default '0',
  "departure_date" date,
  "deadline_date" date,
  "capture_result" text,
  "latitude" numeric,
  "longitude" numeric,
  "departures_count" integer not null default '0',
  "next_departure_date" datetime,
  "max_departures" integer,
  "departure_notes" text,
  "requires_video" tinyint(1) not null default '0',
  foreign key("user_id") references "users"("id")
);
CREATE TABLE IF NOT EXISTS "osvv_comments"(
  "id" integer primary key autoincrement not null,
  "osvv_request_id" integer not null,
  "user_id" integer not null,
  "comment" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("osvv_request_id") references "osvv_requests"("id") on delete cascade,
  foreign key("user_id") references "users"("id")
);
CREATE INDEX "osvv_requests_district_index" on "osvv_requests"("district");
CREATE INDEX "osvv_requests_has_bite_index" on "osvv_requests"("has_bite");
CREATE INDEX "osvv_requests_deadline_date_index" on "osvv_requests"(
  "deadline_date"
);
CREATE TABLE IF NOT EXISTS "tasks"(
  "id" integer primary key autoincrement not null,
  "osvv_request_id" integer not null,
  "user_id" integer,
  "scheduled_date" date not null,
  "scheduled_time" time,
  "status" varchar check("status" in('pending', 'assigned', 'in_progress', 'completed', 'cancelled')) not null default 'pending',
  "notes" text,
  "requires_video" tinyint(1) not null default '0',
  "is_priority" tinyint(1) not null default '0',
  "route_position" text,
  "route_data" text,
  "completed_at" datetime,
  "cancelled_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("osvv_request_id") references "osvv_requests"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_05_21_172342_create_osvv_requests_table',2);
INSERT INTO migrations VALUES(5,'2025_05_21_172407_create_osvv_comments_table',2);
INSERT INTO migrations VALUES(6,'2025_05_21_180500_add_additional_fields_to_osvv_requests_table',3);
INSERT INTO migrations VALUES(7,'2025_05_22_095431_add_coordinates_to_osvv_requests_table',4);
INSERT INTO migrations VALUES(8,'2025_05_22_141206_add_departures_fields_to_osvv_requests_table',5);
INSERT INTO migrations VALUES(9,'2025_05_22_151054_create_tasks_table',6);
