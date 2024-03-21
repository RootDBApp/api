
-- Get primary and foreign keys
SELECT GROUP_CONCAT(COLUMN_NAME) AS COLUMN_NAMES, TABLE_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA LIKE 'sakila'
GROUP BY TABLE_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME

-- Gel all indexes
SELECT  GROUP_CONCAT(COLUMN_NAME) AS COLUMN_NAMES, TABLE_NAME, INDEX_NAME, COMMENT, INDEX_TYPE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'sakila'
GROUP BY TABLE_NAME, INDEX_NAME, COMMENT, INDEX_TYPE
ORDER BY TABLE_NAME;

-- List all views
SELECT v.TABLE_SCHEMA, v.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_COMMENT, c.COLUMN_TYPE
FROM INFORMATION_SCHEMA.VIEWS v
         LEFT JOIN INFORMATION_SCHEMA.COLUMNS c ON v.TABLE_NAME = c.TABLE_NAME
WHERE v.TABLE_SCHEMA = 'sakila'
ORDER BY v.TABLE_NAME, c.COLUMN_NAME;


-- CLI OUTPUT
--
-- mysql -u up_test_db -pthootohPhah9OoWu6SooMo3iquai9i -h 5.135.30.182 test-db -e 'SELECT * FROM `sakila`.`actor` LIMIT 5;'
--
-- +----------+------------+--------------+---------------------+
-- | actor_id | first_name | last_name    | last_update         |
-- +----------+------------+--------------+---------------------+
-- |        1 | PENELOPE   | GUINESS      | 2006-02-15 04:34:33 |
-- |        2 | NICK       | WAHLBERG     | 2006-02-15 04:34:33 |
-- |        3 | ED         | CHASE        | 2006-02-15 04:34:33 |
-- |        4 | JENNIFER   | DAVIS        | 2006-02-15 04:34:33 |
-- |        5 | JOHNNY     | LOLLOBRIGIDA | 2006-02-15 04:34:33 |
-- +----------+------------+--------------+---------------------+
--
--
-- MySQLConnectorService::parseCliResults()
--
--
-- :0-0 > ["actor_id\tfirst_name\tlast_name\tlast_update"]
-- COLUMNS >  [["actor_id","first_name","last_name","last_update"]]
-- :0-1 > ["1\tPENELOPE\tGUINESS\t2006-02-15 04:34:33"]
-- VALUES > [["1","PENELOPE","GUINESS","2006-02-15 04:34:33"]]
-- column -> value [0,"1"]
-- column -> value [1,"PENELOPE"]
-- column -> value [2,"GUINESS"]
-- column -> value [3,"2006-02-15 04:34:33"]
-- :0-2 > ["2\tNICK\tWAHLBERG\t2006-02-15 04:34:33"]
-- VALUES > [["2","NICK","WAHLBERG","2006-02-15 04:34:33"]]
-- column -> value [0,"2"]
-- column -> value [1,"NICK"]
-- column -> value [2,"WAHLBERG"]
-- column -> value [3,"2006-02-15 04:34:33"]
-- :0-3 > ["3\tED\tCHASE\t2006-02-15 04:34:33"]
-- VALUES > [["3","ED","CHASE","2006-02-15 04:34:33"]]
-- column -> value [0,"3"]
-- column -> value [1,"ED"]
-- column -> value [2,"CHASE"]
-- column -> value [3,"2006-02-15 04:34:33"]
-- :0-4 > ["4\tJENNIFER\tDAVIS\t2006-02-15 04:34:33"]
-- VALUES > [["4","JENNIFER","DAVIS","2006-02-15 04:34:33"]]
-- column -> value [0,"4"]
-- column -> value [1,"JENNIFER"]
-- column -> value [2,"DAVIS"]
-- column -> value [3,"2006-02-15 04:34:33"]
-- :0-5 > ["5\tJOHNNY\tLOLLOBRIGIDA\t2006-02-15 04:34:33"]
-- VALUES > [["5","JOHNNY","LOLLOBRIGIDA","2006-02-15 04:34:33"]]
-- column -> value [0,"5"]
-- column -> value [1,"JOHNNY"]
-- column -> value [2,"LOLLOBRIGIDA"]
-- column -> value [3,"2006-02-15 04:34:33"]
-- :0-6 > [""]
