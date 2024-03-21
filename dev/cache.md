# For dev

```mysql
INSERT INTO `rootdb-api`.cache_jobs (id, report_id, frequency, at_minute, at_time, at_weekday, at_day) VALUES (1, 1, 'everyFifteenMinutes', null, null, null, null);
INSERT INTO `rootdb-api`.cache_jobs (id, report_id, frequency, at_minute, at_time, at_weekday, at_day) VALUES (2, 1, 'hourlyAt', 15, null, null, null);
INSERT INTO `rootdb-api`.cache_jobs (id, report_id, frequency, at_minute, at_time, at_weekday, at_day) VALUES (3, 1, 'dailyAt', null, '19:30:00', null, null);
INSERT INTO `rootdb-api`.cache_jobs (id, report_id, frequency, at_minute, at_time, at_weekday, at_day) VALUES (4, 1, 'weeklyOn', null, '01:00:00', 2, null);

INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, date_start_from_values) VALUES (1, 1, 3, '{"values":["default"]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, date_start_from_values) VALUES (2, 1, 4, '{"values":["default"]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, multi_select_values) VALUES (3, 1, 6, '{"values":[56,74]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, select_values) VALUES (4, 1, 14, '{"values":["EU"]}');

INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, date_start_from_values) VALUES (5, 2, 3, '{"values":["default", "1-month", "6-months"]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, date_start_from_values) VALUES (6, 2, 4, '{"values":["default"]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, multi_select_values) VALUES (7, 2, 6, '{"values":[56,74]}');
INSERT INTO `rootdb-api`.cache_job_parameter_set_configs (id, cache_job_id, report_parameter_id, select_values) VALUES (8, 2, 14, '{"values":["EU","AS","NA"]}');
```

