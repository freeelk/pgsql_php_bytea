CREATE TABLE company_files (
     id        SERIAL PRIMARY KEY,
     stock_id  INTEGER NOT NULL,
     mime_type CHARACTER VARYING(255) NOT NULL,
     file_name CHARACTER VARYING(255) NOT NULL,
     file_data BYTEA NOT NULL
);
