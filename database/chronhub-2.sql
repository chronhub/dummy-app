-- transactional sequence number
CREATE TABLE IF NOT EXISTS PositionCounter
(
    Position bigint NOT NULL
);

INSERT INTO PositionCounter VALUES (0);

-- prevent removal / additional rows
CREATE RULE rule_positioncounter_noinsert AS
    ON INSERT TO PositionCounter DO INSTEAD NOTHING;
CREATE RULE rule_positioncounter_nodelete AS
    ON DELETE TO PositionCounter DO INSTEAD NOTHING;

-- function to get next sequence number
CREATE FUNCTION NextPosition() RETURNS bigint AS $$
DECLARE
    nextPos bigint;
BEGIN
    UPDATE PositionCounter
    SET Position = Position + 1;
    SELECT INTO nextPos Position FROM PositionCounter;
    RETURN nextPos;
END;
$$ LANGUAGE plpgsql;

CREATE TABLE stream_event
(
    position bigint NOT NULL,
    stream_name varchar NOT NULL,
    type varchar NOT NULL,
    id uuid NOT NULL,
    version bigint NOT NULL,
    metadata jsonb NOT NULL,
    content jsonb NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now()
);

ALTER TABLE stream_event ADD CONSTRAINT pk_stream_event PRIMARY KEY (position, stream_name);
ALTER TABLE stream_event ADD CONSTRAINT uk_stream_event UNIQUE (type, id, version);

CREATE SEQUENCE IF NOT EXISTS event_stream_no_seq;
CREATE TABLE event_stream
(
    no BIGINT NOT NULL DEFAULT nextval('event_stream_no_seq'),
    stream_name varchar NOT NULL,
    real_stream_name varchar NOT NULL,
    partition varchar DEFAULT NULL,
    event_created_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (no, stream_name)
);

ALTER TABLE event_stream ADD CONSTRAINT uk_event_stream UNIQUE (stream_name);

CREATE OR REPLACE FUNCTION stream_event_insert_trigger() RETURNS TRIGGER AS $$
DECLARE
    stream_name varchar(255);
    base_table_name varchar(255);
    derived_table_name varchar(255);
    uk_stream_name varchar(255);
    uk_base_table_name varchar(255);
    uk_derived_table_name varchar(255);
    next_position bigint;
BEGIN
    stream_name := NEW.stream_name;
    base_table_name := nullif(split_part(stream_name, '-', 1), '');
    derived_table_name := nullif(split_part(stream_name, '-', 2), '');
    uk_stream_name := 'uk_' || stream_name;
    uk_base_table_name := 'uk_' || base_table_name;
    uk_derived_table_name := 'uk_' || stream_name;

    IF NOT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = stream_name AND schemaname = 'public') THEN

        IF POSITION('-' IN stream_name) = 0 THEN
            EXECUTE format('CREATE TABLE %I (CHECK (stream_name LIKE %L)) INHERITS (stream_event)', stream_name, stream_name);
            EXECUTE format('ALTER TABLE %I ADD CONSTRAINT %I UNIQUE (type, id, version)', stream_name, uk_stream_name);
            INSERT INTO event_stream (stream_name, real_stream_name, partition) VALUES (stream_name, stream_name, null);
        END IF;

        IF POSITION ('-' IN stream_name) > 0 THEN

            IF base_table_name IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = base_table_name) THEN
                EXECUTE format('CREATE TABLE %I (CHECK (stream_name LIKE %L)) INHERITS (stream_event)', base_table_name, base_table_name || '%');
                EXECUTE format('ALTER TABLE %I ADD CONSTRAINT %I UNIQUE (type, id, version)', base_table_name, uk_base_table_name);
                INSERT INTO event_stream (stream_name, real_stream_name, partition) VALUES (base_table_name, base_table_name, null);
            END IF;

            IF derived_table_name IS NOT NULL THEN
                EXECUTE format('CREATE TABLE %I (CHECK (stream_name = %L)) INHERITS (%I)', stream_name, stream_name, base_table_name);
                EXECUTE format('ALTER TABLE %I ADD CONSTRAINT %I UNIQUE (type, id, version)', stream_name, uk_derived_table_name);
                INSERT INTO event_stream (stream_name, real_stream_name, partition) VALUES (stream_name, stream_name, base_table_name);
            END IF;

        END IF;

    END IF;

    -- Get the next position using the NextPosition() function
    SELECT NextPosition() INTO next_position;

    -- insert record into the base table
    EXECUTE format('INSERT INTO %I (position, stream_name, type, id, version, metadata, content) VALUES ($1, $2, $3, $4, $5, $6, $7)', stream_name)
        USING next_position, NEW.stream_name, NEW.type, NEW.id, NEW.version, NEW.metadata, NEW.content;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER stream_event_insert_trigger
    BEFORE INSERT ON stream_event
    FOR EACH ROW EXECUTE FUNCTION stream_event_insert_trigger();


