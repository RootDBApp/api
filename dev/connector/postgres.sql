-- Get grants
SELECT concat(table_schema, '.', table_name) as table_name, string_agg(privilege_type, ', ')
FROM information_schema.role_table_grants
WHERE grantee = 'redmine'
AND table_schema !~ 'information_schema'
AND table_schema !~ '^pg_'
GROUP BY table_schema, table_name
ORDER BY table_name;

-- Get all databases
SELECT datname
FROM pg_catalog.pg_database
WHERE datname !~ 'postgres'
  AND datname !~ 'template';

-- Get all database's tables
SELECT schema_name
FROM information_schema.schemata;

-- Get all table's columns
SELECT c.column_name, c.data_type, c.table_name, pd.description
FROM information_schema.columns c
         JOIN information_schema.tables t ON c.table_name = t.table_name AND t.table_type = 'BASE TABLE'
         JOIN pg_catalog.pg_namespace pn ON pn.nspname = 'public'
         JOIN pg_catalog.pg_class pc ON pc.relkind = 'r' AND pn.oid = pc.relnamespace AND t.table_name = pc.relname
         LEFT JOIN pg_catalog.pg_description pd ON pc.oid = pd.objoid AND c.ordinal_position = pd.objsubid
WHERE c.table_schema = 'public'
ORDER BY c.table_schema, c.table_name;

-- Get primary and foreign keys
SELECT string_agg(kc.column_name, ',') AS column_names, tc.table_name, tc.constraint_name, ccu.column_name as referenced_column_name, ccu.table_name as referenced_table_name
FROM information_schema.table_constraints tc
         JOIN information_schema.key_column_usage kc ON kc.table_name = tc.table_name AND kc.table_schema = tc.table_schema AND kc.constraint_name = tc.constraint_name
         LEFT JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema AND tc.constraint_type = 'FOREIGN KEY'
WHERE tc.table_schema = 'public'
  AND tc.constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY')
  AND kc.ordinal_position IS NOT NULL
GROUP BY tc.table_schema, tc.table_name, tc.constraint_name, referenced_column_name, referenced_table_name
;

-- Gel all indexes
SELECT pc.relname         AS index_name,
       pi.indrelid::regclass as table_name, pa.amname AS index_type,
       ARRAY(SELECT pg_get_indexdef(pi.indexrelid, k + 1, true)
             FROM generate_subscripts(pi.indkey, 1) AS k
             ORDER BY k
           ) AS column_names,
       ''                 as comment
FROM pg_index AS pi
         JOIN pg_class as pc ON pc.oid = pi.indexrelid
         JOIN pg_am as pa ON pc.relam = pa.oid
         JOIN pg_namespace as pn ON pn.oid = pc.relnamespace AND pn.nspname = ANY (current_schemas(false))
WHERE pn.nspname = 'public'
;


-- SELECT v.TABLE_SCHEMA, v.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_COMMENT, c.COLUMN_TYPE
-- List all views
SELECT v.table_schema, v.table_name, pa.attname AS column_name, pd.description as column_comment, format_type(atttypid, atttypmod) AS column_type
FROM information_schema.views v
         JOIN pg_catalog.pg_attribute pa ON pa.attrelid = CONCAT('public.', v.table_name)::regclass
JOIN information_schema.columns c
ON v.table_schema = c.table_schema AND v.table_name = c.table_name AND pa.attname = c.column_name
    JOIN pg_catalog.pg_namespace pn ON pn.nspname = 'public'
    JOIN pg_catalog.pg_class pc ON pc.relkind = 'v' AND pn.oid = pc.relnamespace AND v.table_name = pc.relname
    LEFT JOIN pg_catalog.pg_description pd ON pc.oid = pd.objoid
WHERE v.table_schema NOT IN ('information_schema'
    , 'pg_catalog')
  AND v.table_schema = 'public'
  AND v.table_name = 'film_list'
ORDER BY v.table_schema, v.table_name;

-- Variable declaration
-- Create a function that uses variables
DROP FUNCTION my_function();
CREATE OR REPLACE FUNCTION my_function()  RETURNS TABLE (actor_id INT, film_id INT, last_update timestamp) AS $$
DECLARE
    myActorId INT;
    result_data public.film_actor%; -- Define a variable to hold the result data
BEGIN
    -- Assign a value to the variable
    myActorId := 3;


    -- Use the variable in a SELECT statement
    RETURN QUERY
    SELECT film_actor.actor_id, film_actor.film_id, film_actor.last_update
    INTO result_data
    FROM public.film_actor
    WHERE film_actor.actor_id = myActorId
    ;

    -- Other statements using the variable...

END;
$$ LANGUAGE plpgsql;


SELECT * FROM my_function();


-- CLI OUTPUT
--
-- PGPASSWORD=thee1uuWiechieneiyieZ0aif3aefe psql -U localuser -p 5432 -h 172.20.0.70 dvdrental -c "SELECT * FROM public.actor LIMIT 5;"
--
--  actor_id | first_name |  last_name   |     last_update
-- ----------+------------+--------------+---------------------
--         1 | PENELOPE   | GUINESS      | 2006-02-15 04:34:33
--         2 | NICK       | WAHLBERG     | 2006-02-15 04:34:33
--         3 | ED         | CHASE        | 2006-02-15 04:34:33
--         4 | JENNIFER   | DAVIS        | 2006-02-15 04:34:33
--         5 | JOHNNY     | LOLLOBRIGIDA | 2006-02-15 04:34:33
-- (5 rows)
--
--
-- PostgreSQLConnectorService::parseCliResults()
--

