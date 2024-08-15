-- MariaDB dump 10.19  Distrib 10.11.8-MariaDB, for Linux (x86_64)
--
-- Host: 172.20.0.50    Database: rootdb-api
-- ------------------------------------------------------
-- Server version	11.4.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache_job_parameter_set_configs`
--

DROP TABLE IF EXISTS `cache_job_parameter_set_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_job_parameter_set_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cache_job_id` int(10) unsigned NOT NULL,
  `report_parameter_id` int(10) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `date_start_from_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: [default, 1-week, 2-weeks, 3-weeks, 4-weeks, 1-month, 2-months, 4-months, 5-months, 6-months, 1-year, 2-years, 3-years, 4-years, 5-years]}' CHECK (json_valid(`date_start_from_values`)),
  `select_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: []} - it will generate one query for each value.' CHECK (json_valid(`select_values`)),
  `multi_select_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: []} - generally used with IN (x,y,z) in WHERE statement.' CHECK (json_valid(`multi_select_values`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cjpsc_id` (`cache_job_id`),
  KEY `fk_cjpsc_rp_id` (`report_parameter_id`),
  CONSTRAINT `fk_cjpsc_id` FOREIGN KEY (`cache_job_id`) REFERENCES `cache_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cjpsc_rp_id` FOREIGN KEY (`report_parameter_id`) REFERENCES `report_parameters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_job_parameter_set_configs`
--

LOCK TABLES `cache_job_parameter_set_configs` WRITE;
/*!40000 ALTER TABLE `cache_job_parameter_set_configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_job_parameter_set_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_job_parameter_sets`
--

DROP TABLE IF EXISTS `cache_job_parameter_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_job_parameter_sets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cache_job_id` int(10) unsigned NOT NULL,
  `report_parameter_id` int(10) unsigned NOT NULL,
  `date_start_from_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: [default, 1-week, 2-weeks, 3-weeks, 4-weeks, 1-month, 2-months, 4-months, 5-months, 6-months, 1-year, 2-years, 3-years, 4-years, 5-years]}' CHECK (json_valid(`date_start_from_values`)),
  `select_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: []}' CHECK (json_valid(`select_values`)),
  `select_all_values` tinyint(1) NOT NULL DEFAULT 0,
  `multi_select_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{values: []}' CHECK (json_valid(`multi_select_values`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cj_id` (`cache_job_id`),
  KEY `fk_rp_id` (`report_parameter_id`),
  CONSTRAINT `fk_cj_id` FOREIGN KEY (`cache_job_id`) REFERENCES `cache_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_id` FOREIGN KEY (`report_parameter_id`) REFERENCES `report_parameters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_job_parameter_sets`
--

LOCK TABLES `cache_job_parameter_sets` WRITE;
/*!40000 ALTER TABLE `cache_job_parameter_sets` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_job_parameter_sets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_jobs`
--

DROP TABLE IF EXISTS `cache_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `periodicity` enum('every_minute','hourly','daily','weekly') NOT NULL DEFAULT 'hourly',
  `frequency` enum('everyFifteenMinutes','everyThirtyMinutes','hourlyAt','dailyAt','weeklyOn','monthlyOn') NOT NULL DEFAULT 'hourlyAt',
  `at_minute` tinyint(3) unsigned DEFAULT 0,
  `at_time` time DEFAULT '00:00:01',
  `at_weekday` enum('1','2','3','4','5','6','7') DEFAULT '1',
  `at_day` tinyint(3) unsigned DEFAULT 1,
  `ttl` int(10) unsigned NOT NULL DEFAULT 3600 COMMENT 'In seconds',
  `last_run` datetime DEFAULT NULL,
  `last_run_duration` int(10) unsigned DEFAULT NULL COMMENT 'In seconds',
  `last_num_parameter_sets` int(10) unsigned DEFAULT NULL,
  `last_cache_size_b` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT 1,
  `running` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cache_jobs_report_id_foreign` (`report_id`),
  CONSTRAINT `cache_jobs_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_jobs`
--

LOCK TABLES `cache_jobs` WRITE;
/*!40000 ALTER TABLE `cache_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `color_hex` varchar(6) NOT NULL DEFAULT 'd0d1d2',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_organization_id_foreign` (`organization_id`),
  CONSTRAINT `categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,1,'Category 1','7cb3df','Category description','2022-07-21 10:48:45',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conf_connectors`
--

DROP TABLE IF EXISTS `conf_connectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conf_connectors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Local connexion',
  `connector_database_id` int(10) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `host` varchar(255) NOT NULL DEFAULT 'localhost',
  `port` int(10) unsigned NOT NULL DEFAULT 3306,
  `database` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(512) NOT NULL,
  `timeout` int(11) NOT NULL DEFAULT 15,
  `use_ssl` tinyint(1) NOT NULL DEFAULT 0,
  `ssl_ca` mediumtext DEFAULT NULL,
  `ssl_cert` mediumtext DEFAULT NULL,
  `ssl_key` mediumtext DEFAULT NULL,
  `ssl_cipher` varchar(255) DEFAULT NULL,
  `global` tinyint(1) NOT NULL DEFAULT 0,
  `mysql_ssl_verify_server_cert` tinyint(1) NOT NULL DEFAULT 0,
  `pgsql_ssl_mode` enum('disable','allow','prefer','require','verify-ca','verify-full') NOT NULL DEFAULT 'disable',
  PRIMARY KEY (`id`),
  KEY `conf_connectors_connector_database_id_foreign` (`connector_database_id`),
  KEY `conf_connectors_organization_id_foreign` (`organization_id`),
  CONSTRAINT `conf_connectors_connector_database_id_foreign` FOREIGN KEY (`connector_database_id`) REFERENCES `connector_databases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conf_connectors_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conf_connectors`
--

LOCK TABLES `conf_connectors` WRITE;
/*!40000 ALTER TABLE `conf_connectors` DISABLE KEYS */;
/*!40000 ALTER TABLE `conf_connectors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conf_global`
--

DROP TABLE IF EXISTS `conf_global`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conf_global` (
  `default_connector_id` int(10) unsigned NOT NULL,
  KEY `conf_global_default_connector_id_foreign` (`default_connector_id`),
  CONSTRAINT `conf_global_default_connector_id_foreign` FOREIGN KEY (`default_connector_id`) REFERENCES `conf_connectors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conf_global`
--

LOCK TABLES `conf_global` WRITE;
/*!40000 ALTER TABLE `conf_global` DISABLE KEYS */;
/*!40000 ALTER TABLE `conf_global` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connector_databases`
--

DROP TABLE IF EXISTS `connector_databases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `connector_databases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connector_databases`
--

LOCK TABLES `connector_databases` WRITE;
/*!40000 ALTER TABLE `connector_databases` DISABLE KEYS */;
INSERT INTO `connector_databases` VALUES
(1,'MySQL',1),
(2,'PostgreSQL',1);
/*!40000 ALTER TABLE `connector_databases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directories`
--

DROP TABLE IF EXISTS `directories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `directories_organization_id_foreign` (`organization_id`),
  KEY `directories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `directories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `directories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `directories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directories`
--

LOCK TABLES `directories` WRITE;
/*!40000 ALTER TABLE `directories` DISABLE KEYS */;
INSERT INTO `directories` VALUES
(1,1,NULL,'Directory 1','Directory description','2022-07-21 10:48:45',NULL);
/*!40000 ALTER TABLE `directories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draft_queries`
--

DROP TABLE IF EXISTS `draft_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_queries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(10) unsigned NOT NULL,
  `queries` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `draft_queries_draft_id_foreign` (`draft_id`),
  CONSTRAINT `draft_queries_draft_id_foreign` FOREIGN KEY (`draft_id`) REFERENCES `drafts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draft_queries`
--

LOCK TABLES `draft_queries` WRITE;
/*!40000 ALTER TABLE `draft_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `draft_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drafts`
--

DROP TABLE IF EXISTS `drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drafts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conf_connector_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `drafts_user_id_foreign` (`user_id`),
  KEY `drafts_conf_connector_id_foreign` (`conf_connector_id`),
  CONSTRAINT `drafts_conf_connector_id_foreign` FOREIGN KEY (`conf_connector_id`) REFERENCES `conf_connectors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `drafts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drafts`
--

LOCK TABLES `drafts` WRITE;
/*!40000 ALTER TABLE `drafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `drafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groups_organization_id_foreign` (`organization_id`),
  CONSTRAINT `groups_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0000_00_00_000000_create_websockets_statistics_entries_table',1),
(2,'0000_00_00_000000_rename_statistics_counters',1),
(3,'2014_10_10_000000_create_organizations_table',1),
(4,'2014_10_12_000000_create_users_table',1),
(5,'2014_11_05_152740_create_jobs_table',1),
(6,'2018_08_08_100000_create_telescope_entries_table',1),
(7,'2019_08_19_000000_create_failed_jobs_table',1),
(8,'2019_12_14_000001_create_personal_access_tokens_table',1),
(9,'2020_09_17_145722_create_roles_table',1),
(10,'2020_09_17_145723_create_role_grants_table',1),
(11,'2020_09_17_145822_create_organization_users_table',1),
(12,'2020_09_17_145823_create_user_preferences',1),
(13,'2020_09_17_145824_create_organization_user_role_table',1),
(14,'2020_09_17_152149_create_groups_table',1),
(15,'2020_09_17_152542_create_organization_user_group_table',1),
(16,'2020_09_28_091048_create_connector_databases_table',1),
(17,'2020_09_28_091049_create_tables_conf_global_and_conf_connectors',1),
(18,'2020_10_26_092330_create_categories_table',1),
(19,'2020_10_26_102330_create_directories_table',1),
(20,'2020_10_26_112330_create_reports_table',1),
(21,'2020_10_26_112530_create_report_user_favorite_table',1),
(22,'2020_10_26_113055_create_report_user_table',1),
(23,'2020_10_26_113132_create_report_group_table',1),
(24,'2020_10_26_114112_create_report_data_views_table',1),
(25,'2020_10_26_135607_create_report_parameter_input_data_types_table',1),
(26,'2020_10_26_135617_create_report_parameter_input_types',1),
(27,'2020_10_26_135618_create_report_parameter_inputs_table',1),
(28,'2020_10_26_135856_create_report_parameters_table',1),
(29,'2021_02_11_092708_create_report_data_view_libs_table',1),
(30,'2021_02_11_092804_create_report_data_view_lib_versions_table',1),
(31,'2021_02_11_093349_create_report_data_view_js_table',1),
(32,'2021_02_11_120538_update_report_data_views_table',1),
(33,'2021_10_06_092401_create_report_data_view_lib_types_table',1),
(34,'2021_11_26_111559_update_report_data_view_table',1),
(35,'2022_03_11_135555_create_drafts_table',1),
(36,'2022_03_11_162959_create_draft_queries_table',1),
(39,'2022_07_12_193915_report_data_views_update',1),
(40,'2022_07_15_111416_update_report_dataview_lib_types.php',1),
(42,'2022_07_15_111416_update_report_dataview_lib_types',2),
(44,'2022_08_10_155952_update_report_parameters',4),
(45,'2022_08_19_114355_update_report_parameter_input_data_types',5),
(46,'2022_09_08_101606_update_report_parameters_tables',5),
(47,'2022_09_08_140555_update_report_parameter_inputs',6),
(49,'2022_09_16_120448_update_report_data_view_lib_versions',8),
(50,'2022_10_14_154634_udate_role_grants',9),
(51,'2022_10_22_224235_create_services_messages',10),
(52,'2022_10_23_103838_create_organization_service_message',10),
(53,'2022_10_23_111426_update_role_grants',10),
(54,'2022_11_18_143524_update_reports',11),
(55,'2023_01_05_093525_update_report_data_view_lib_versions',12),
(56,'2023_02_09_210544_update_user',12),
(57,'2023_02_14_193829_update_data_view_positions',12),
(58,'2023_02_16_184354_update_data_view_js_init',12),
(59,'2023_03_01_00000_update_report_data_view_lib_versions',13),
(60,'2023_03_02_163915_update_role_grants',14),
(61,'2023_05_19_101939_update_connector_databases',15),
(62,'2023_05_26_103807_update_conf_connectors',15),
(63,'2023_05_27_094847_update_reports_and_report_data_views',16),
(64,'2023_05_31_154709_create_cache_jobs',16),
(65,'2023_05_31_154727_create_cache_job_parameter_sets',16),
(66,'2023_08_25_154727_report_data_views_and_libs',16),
(67,'2023_09_20_154727_text_dataview_type',16),
(68,'2023_05_31_154727_create_cache_job_parameter_set_configs',17),
(69,'2023_06_17_102242_update_role_grants',17),
(70,'2023_06_17_175854_update_cache_jobs',17),
(72,'2023_06_21_225321_cache_job_parameter_set_configs',17),
(73,'2023_06_22_195215_update_cache_jobs',17),
(74,'2023_07_06_144532_update_reports',17),
(75,'2023_07_18_174814_report_caches',17),
(76,'2023_07_26_153825_update_reports',17),
(77,'2023_07_26_191437_update_report_caches',17),
(78,'2023_10_20_161358_update_report_caches',17),
(79,'2023_10_26_114241_update_report_caches',17),
(80,'2024_02_09_094710_update_reports',18),
(81,'2024_03_07_085853_update_report_data_view_lib_versions',19),
(82,'2024_08_01_113128_apache_echarts_',20),
(83,'2024_08_08_074929_update_report_data_view_lib_versions',21);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_service_message`
--

DROP TABLE IF EXISTS `organization_service_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_service_message` (
  `service_message_id` int(10) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  KEY `organization_service_message_service_message_id_foreign` (`service_message_id`),
  KEY `organization_service_message_organization_id_foreign` (`organization_id`),
  CONSTRAINT `organization_service_message_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_service_message_service_message_id_foreign` FOREIGN KEY (`service_message_id`) REFERENCES `service_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_service_message`
--

LOCK TABLES `organization_service_message` WRITE;
/*!40000 ALTER TABLE `organization_service_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `organization_service_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_user`
--

DROP TABLE IF EXISTS `organization_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_user_organization_id_user_id_unique` (`organization_id`,`user_id`),
  UNIQUE KEY `organization_user_id_unique` (`id`),
  KEY `organization_user_user_id_foreign` (`user_id`),
  CONSTRAINT `organization_user_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_user`
--

LOCK TABLES `organization_user` WRITE;
/*!40000 ALTER TABLE `organization_user` DISABLE KEYS */;
INSERT INTO `organization_user` VALUES
(1,1,1);
/*!40000 ALTER TABLE `organization_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_user_group`
--

DROP TABLE IF EXISTS `organization_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_user_id` bigint(20) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_user_group_organization_user_id_group_id_unique` (`organization_user_id`,`group_id`),
  UNIQUE KEY `organization_user_group_id_unique` (`id`),
  KEY `organization_user_group_group_id_foreign` (`group_id`),
  CONSTRAINT `organization_user_group_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_user_group_organization_user_id_foreign` FOREIGN KEY (`organization_user_id`) REFERENCES `organization_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_user_group`
--

LOCK TABLES `organization_user_group` WRITE;
/*!40000 ALTER TABLE `organization_user_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `organization_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_user_role`
--

DROP TABLE IF EXISTS `organization_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_user_id` bigint(20) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_user_role_organization_user_id_role_id_unique` (`organization_user_id`,`role_id`),
  UNIQUE KEY `organization_user_role_id_unique` (`id`),
  KEY `organization_user_role_role_id_foreign` (`role_id`),
  CONSTRAINT `organization_user_role_organization_user_id_foreign` FOREIGN KEY (`organization_user_id`) REFERENCES `organization_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_user_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_user_role`
--

LOCK TABLES `organization_user_role` WRITE;
/*!40000 ALTER TABLE `organization_user_role` DISABLE KEYS */;
INSERT INTO `organization_user_role` VALUES
(83,1,1),
(84,1,2),
(85,1,3);
/*!40000 ALTER TABLE `organization_user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES
(1,'Organisation','2022-07-21 10:48:45',NULL);
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_caches`
--

DROP TABLE IF EXISTS `report_caches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_caches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cache_job_id` int(11) unsigned DEFAULT NULL,
  `cache_key` varchar(255) NOT NULL,
  `report_id` int(10) unsigned NOT NULL,
  `input_parameters_hash` varchar(255) NOT NULL,
  `report_data_view_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cache_type` enum('user','job') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_caches_id_unique` (`id`),
  KEY `fk__rcfu_report_id` (`report_id`),
  KEY `fk__rcfu_report_data_view_id` (`report_data_view_id`),
  KEY `fk_rc_cache_job_id` (`cache_job_id`),
  CONSTRAINT `fk__rcfu_report_data_view_id` FOREIGN KEY (`report_data_view_id`) REFERENCES `report_data_views` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk__rcfu_report_id` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rc_cache_job_id` FOREIGN KEY (`cache_job_id`) REFERENCES `cache_jobs` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_caches`
--

LOCK TABLES `report_caches` WRITE;
/*!40000 ALTER TABLE `report_caches` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_caches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_data_view_js`
--

DROP TABLE IF EXISTS `report_data_view_js`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_data_view_js` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_data_view_id` int(10) unsigned NOT NULL,
  `report_data_view_lib_version_id` int(10) unsigned NOT NULL,
  `json_form` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_form`)),
  `js_register` text DEFAULT NULL,
  `js_code` text DEFAULT NULL,
  `js_init` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_data_view_js_report_data_view_id_foreign` (`report_data_view_id`),
  KEY `report_data_view_js_report_data_view_lib_version_id_foreign` (`report_data_view_lib_version_id`),
  CONSTRAINT `report_data_view_js_report_data_view_id_foreign` FOREIGN KEY (`report_data_view_id`) REFERENCES `report_data_views` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_data_view_js_report_data_view_lib_version_id_foreign` FOREIGN KEY (`report_data_view_lib_version_id`) REFERENCES `report_data_view_lib_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_data_view_js`
--

LOCK TABLES `report_data_view_js` WRITE;
/*!40000 ALTER TABLE `report_data_view_js` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_data_view_js` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_data_view_lib_types`
--

DROP TABLE IF EXISTS `report_data_view_lib_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_data_view_lib_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_data_view_lib_version_id` int(10) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `data_view_lib_types_report_data_view_lib_version_id_foreign` (`report_data_view_lib_version_id`),
  CONSTRAINT `data_view_lib_types_report_data_view_lib_version_id_foreign` FOREIGN KEY (`report_data_view_lib_version_id`) REFERENCES `report_data_view_lib_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_data_view_lib_types`
--

LOCK TABLES `report_data_view_lib_types` WRITE;
/*!40000 ALTER TABLE `report_data_view_lib_types` DISABLE KEYS */;
INSERT INTO `report_data_view_lib_types` VALUES
(21,3,'default','Default'),
(22,3,'bar','Bar'),
(23,3,'doughnut','Doughnut'),
(24,3,'line','Line'),
(25,3,'multi_lines','Multi-lines'),
(26,3,'multi_axis','Multi Axis'),
(27,3,'pie','Pie'),
(28,3,'stacked_bar','Stacked bar'),
(29,4,'default','Default'),
(30,4,'bar','Bar'),
(31,4,'line','Line'),
(32,9,'bar','Bar'),
(33,9,'line','Line'),
(34,9,'scatter','Scatter'),
(35,9,'pie','Pie'),
(36,9,'candlestick','Candlestick'),
(37,9,'radar','Radar'),
(38,9,'default','Default'),
(39,9,'stacked_line','Stacked line');
/*!40000 ALTER TABLE `report_data_view_lib_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_data_view_lib_versions`
--

DROP TABLE IF EXISTS `report_data_view_lib_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_data_view_lib_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_data_view_lib_id` int(10) unsigned NOT NULL,
  `major_version` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `url_documentation` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `report_data_view_lib_versions_report_data_view_lib_id_foreign` (`report_data_view_lib_id`),
  CONSTRAINT `report_data_view_lib_versions_report_data_view_lib_id_foreign` FOREIGN KEY (`report_data_view_lib_id`) REFERENCES `report_data_view_libs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_data_view_lib_versions`
--

LOCK TABLES `report_data_view_lib_versions` WRITE;
/*!40000 ALTER TABLE `report_data_view_lib_versions` DISABLE KEYS */;
INSERT INTO `report_data_view_lib_versions` VALUES
(1,1,'8.x','8.13.2','https://react-table.tanstack.com/docs/overview',1),
(3,2,'4.x','4.4.3','https://www.chartjs.org/docs/master/',0),
(4,3,'7.x','7.8.5','https://github.com/d3/d3/wiki',0),
(5,4,'1.x','1.0.4','https://documentation.rootdb.fr',0),
(6,5,'1.x','1.0.4','https://documentation.rootdb.fr',0),
(7,6,'1.x','1.0.4','https://documentation.rootdb.fr',0),
(8,7,'1.x','1.0.4','https://documentation.rootdb.fr',0),
(9,8,'5.x','5.5.1','https://echarts.apache.org/handbook/en/get-started/',0);
/*!40000 ALTER TABLE `report_data_view_lib_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_data_view_libs`
--

DROP TABLE IF EXISTS `report_data_view_libs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_data_view_libs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('1','2','3','4','5','6') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend, 6: text',
  `name` varchar(255) NOT NULL,
  `url_website` varchar(255) DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_data_view_libs`
--

LOCK TABLES `report_data_view_libs` WRITE;
/*!40000 ALTER TABLE `report_data_view_libs` DISABLE KEYS */;
INSERT INTO `report_data_view_libs` VALUES
(1,'1','React Table','https://react-table.tanstack.com/',1),
(2,'2','Chart.js','https://www.chartjs.org/',1),
(3,'2','D3.js','https://d3js.org/',0),
(4,'3','RootDB','https://www.rootdb.fr/',1),
(5,'4','RootDB','https://www.rootdb.fr/',1),
(6,'5','RootDB','https://www.rootdb.fr/',1),
(7,'6','RootDB','https://www.rootdb.fr/',1),
(8,'2','Apache ECharts','https://echarts.apache.org',0);
/*!40000 ALTER TABLE `report_data_view_libs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_data_views`
--

DROP TABLE IF EXISTS `report_data_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_data_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `type` enum('1','2','3','4','5','6') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend, 6: text',
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_display_type` tinyint(3) unsigned NOT NULL DEFAULT 1 COMMENT '0: when no description, 1: overlay, 2: text',
  `by_chunk` tinyint(1) NOT NULL DEFAULT 0,
  `chunk_size` int(10) unsigned NOT NULL DEFAULT 500,
  `query` text DEFAULT NULL,
  `position` varchar(255) DEFAULT '',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `on_queue` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `report_data_view_lib_version_id` int(10) unsigned NOT NULL,
  `max_width` int(10) unsigned DEFAULT NULL,
  `num_runs` double(8,2) NOT NULL DEFAULT 0.00,
  `num_seconds_all_run` double(8,2) NOT NULL DEFAULT 0.00,
  `avg_seconds_by_run` double(8,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `report_data_views_report_id_foreign` (`report_id`),
  KEY `report_data_views_report_data_view_lib_version_id_foreign` (`report_data_view_lib_version_id`),
  CONSTRAINT `report_data_views_report_data_view_lib_version_id_foreign` FOREIGN KEY (`report_data_view_lib_version_id`) REFERENCES `report_data_view_lib_versions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_data_views_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_data_views`
--

LOCK TABLES `report_data_views` WRITE;
/*!40000 ALTER TABLE `report_data_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_data_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_groups`
--

DROP TABLE IF EXISTS `report_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `report_groups_report_id_foreign` (`report_id`),
  KEY `report_groups_group_id_foreign` (`group_id`),
  CONSTRAINT `report_groups_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_groups_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_groups`
--

LOCK TABLES `report_groups` WRITE;
/*!40000 ALTER TABLE `report_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_parameter_input_data_types`
--

DROP TABLE IF EXISTS `report_parameter_input_data_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_parameter_input_data_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connector_database_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `custom_entry` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `report_parameter_input_data_types_connector_database_id_foreign` (`connector_database_id`),
  CONSTRAINT `report_parameter_input_data_types_connector_database_id_foreign` FOREIGN KEY (`connector_database_id`) REFERENCES `connector_databases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_parameter_input_data_types`
--

LOCK TABLES `report_parameter_input_data_types` WRITE;
/*!40000 ALTER TABLE `report_parameter_input_data_types` DISABLE KEYS */;
INSERT INTO `report_parameter_input_data_types` VALUES
(1,1,'integer','int',0),
(2,1,'varchar','varchar',0),
(3,1,'date','date',0),
(4,1,'char','char',0),
(5,1,'double','double',0),
(6,1,'float','float',0),
(7,1,'year','year',0);
/*!40000 ALTER TABLE `report_parameter_input_data_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_parameter_input_types`
--

DROP TABLE IF EXISTS `report_parameter_input_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_parameter_input_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `query` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_parameter_input_types`
--

LOCK TABLES `report_parameter_input_types` WRITE;
/*!40000 ALTER TABLE `report_parameter_input_types` DISABLE KEYS */;
INSERT INTO `report_parameter_input_types` VALUES
(1,'text',''),
(2,'select','SELECT 0 as value, \'option 1\' as name\nUNION\nSELECT 1 as value, \'option 2\' as name\nUNION\nSELECT 2 as value, \'option 3\' as name\nUNION\nSELECT 3 as value, \'option 4\' as name'),
(3,'checkbox','SELECT 0 as value, \'option 1\' as name\nUNION\nSELECT 1 as value, \'option 2\' as name\nUNION\nSELECT 2 as value, \'option 3\' as name\nUNION\nSELECT 3 as value, \'option 4\' as name'),
(4,'radio','SELECT 1 as value, \"Yes\" as name UNION SELECT 0 as value, \"No\" as name'),
(5,'auto-complete',''),
(6,'date',''),
(7,'multi-select','SELECT 0 as value, \'option 1\' as name\nUNION\nSELECT 1 as value, \'option 2\' as name\nUNION\nSELECT 2 as value, \'option 3\' as name\nUNION\nSELECT 3 as value, \'option 4\' as name');
/*!40000 ALTER TABLE `report_parameter_input_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_parameter_inputs`
--

DROP TABLE IF EXISTS `report_parameter_inputs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_parameter_inputs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conf_connector_id` int(10) unsigned NOT NULL,
  `parameter_input_type_id` int(10) unsigned NOT NULL,
  `parameter_input_data_type_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `query` text DEFAULT NULL,
  `query_default_value` text DEFAULT NULL,
  `default_value` varchar(2500) DEFAULT NULL,
  `custom_entry` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `report_parameter_inputs_conf_connector_id_foreign` (`conf_connector_id`),
  KEY `report_parameter_inputs_parameter_input_type_id_foreign` (`parameter_input_type_id`),
  KEY `report_parameter_inputs_parameter_input_data_type_id_foreign` (`parameter_input_data_type_id`),
  CONSTRAINT `report_parameter_inputs_conf_connector_id_foreign` FOREIGN KEY (`conf_connector_id`) REFERENCES `conf_connectors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_parameter_inputs_parameter_input_data_type_id_foreign` FOREIGN KEY (`parameter_input_data_type_id`) REFERENCES `report_parameter_input_data_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_parameter_inputs_parameter_input_type_id_foreign` FOREIGN KEY (`parameter_input_type_id`) REFERENCES `report_parameter_input_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_parameter_inputs`
--

LOCK TABLES `report_parameter_inputs` WRITE;
/*!40000 ALTER TABLE `report_parameter_inputs` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_parameter_inputs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_parameters`
--

DROP TABLE IF EXISTS `report_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_parameters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `parameter_input_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `variable_name` varchar(255) NOT NULL,
  `following_parameter_next_to_this_one` tinyint(1) NOT NULL DEFAULT 0,
  `forced_default_value` varchar(2500) DEFAULT NULL,
  `available_public_access` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `report_parameters_report_id_foreign` (`report_id`),
  KEY `report_parameters_parameter_input_id_foreign` (`parameter_input_id`),
  CONSTRAINT `report_parameters_parameter_input_id_foreign` FOREIGN KEY (`parameter_input_id`) REFERENCES `report_parameter_inputs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_parameters_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_parameters`
--

LOCK TABLES `report_parameters` WRITE;
/*!40000 ALTER TABLE `report_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_user_favorites`
--

DROP TABLE IF EXISTS `report_user_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_user_favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_use_unique` (`report_id`,`user_id`),
  KEY `report_user_favorites_user_id_foreign` (`user_id`),
  CONSTRAINT `report_user_favorites_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_user_favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_user_favorites`
--

LOCK TABLES `report_user_favorites` WRITE;
/*!40000 ALTER TABLE `report_user_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_user_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_users`
--

DROP TABLE IF EXISTS `report_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `report_users_report_id_foreign` (`report_id`),
  KEY `report_users_user_id_foreign` (`user_id`),
  CONSTRAINT `report_users_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_users`
--

LOCK TABLES `report_users` WRITE;
/*!40000 ALTER TABLE `report_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conf_connector_id` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `directory_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_listing` text DEFAULT NULL,
  `query_init` text DEFAULT NULL,
  `query_cleanup` text DEFAULT NULL,
  `public_access` tinyint(1) NOT NULL DEFAULT 0,
  `public_security_hash` varchar(40) NOT NULL,
  `public_authorized_referers` tinytext DEFAULT NULL COMMENT 'list of referer hosts, coma separated',
  `has_parameters` tinyint(1) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 0,
  `auto_refresh` tinyint(1) NOT NULL DEFAULT 0,
  `on_queue` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `has_data_views` tinyint(1) NOT NULL DEFAULT 0,
  `num_runs` double(8,2) NOT NULL DEFAULT 0.00,
  `num_seconds_all_run` double(8,2) NOT NULL DEFAULT 0.00,
  `avg_seconds_by_run` double(8,2) NOT NULL DEFAULT 0.00,
  `has_cache` tinyint(1) NOT NULL DEFAULT 0,
  `has_user_cache` tinyint(1) NOT NULL DEFAULT 0,
  `has_job_cache` tinyint(1) NOT NULL DEFAULT 0,
  `num_parameter_sets_cached_by_users` int(11) unsigned DEFAULT NULL,
  `num_parameter_sets_cached_by_jobs` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_conf_connector_id_foreign` (`conf_connector_id`),
  KEY `reports_user_id_foreign` (`user_id`),
  KEY `reports_organization_id_foreign` (`organization_id`),
  KEY `reports_directory_id_foreign` (`directory_id`),
  KEY `reports_category_id_foreign` (`category_id`),
  CONSTRAINT `reports_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_conf_connector_id_foreign` FOREIGN KEY (`conf_connector_id`) REFERENCES `conf_connectors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_directory_id_foreign` FOREIGN KEY (`directory_id`) REFERENCES `directories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_grants`
--

DROP TABLE IF EXISTS `role_grants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `route_label` varchar(255) NOT NULL,
  `index` tinyint(1) NOT NULL DEFAULT 0,
  `store` tinyint(1) NOT NULL DEFAULT 0,
  `show` tinyint(1) NOT NULL DEFAULT 0,
  `update` tinyint(1) NOT NULL DEFAULT 0,
  `destroy` tinyint(1) NOT NULL DEFAULT 0,
  `ui_edit` tinyint(1) NOT NULL DEFAULT 0,
  `organization_user_bound` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role_grants_role_id_foreign` (`role_id`),
  CONSTRAINT `role_grants_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_grants`
--

LOCK TABLES `role_grants` WRITE;
/*!40000 ALTER TABLE `role_grants` DISABLE KEYS */;
INSERT INTO `role_grants` VALUES
(113,1,'cache','cache',1,0,0,0,0,0,1),
(114,2,'cache','cache',1,0,0,0,0,0,1),
(115,3,'cache','cache',1,0,0,0,0,0,1),
(116,1,'category','category',1,1,1,1,1,1,1),
(117,2,'category','category',1,1,1,1,1,1,1),
(118,3,'category','category',1,0,1,0,0,0,1),
(119,1,'conf-connector','conf-connector',0,0,0,0,0,0,1),
(120,2,'conf-connector','conf-connector',1,1,1,1,1,1,1),
(121,3,'conf-connector','conf-connector',0,0,0,0,0,0,1),
(122,1,'draft','draft',0,0,0,0,0,1,1),
(123,2,'draft','draft',1,1,1,1,1,1,1),
(124,3,'draft','draft',0,0,1,0,0,0,1),
(125,1,'draft-queries','draft-queries',0,0,0,0,0,1,1),
(126,2,'draft-queries','draft-queries',1,1,1,1,1,1,1),
(127,3,'draft-queries','draft-queries',0,0,1,0,0,0,1),
(128,1,'directory','directory',1,1,1,1,1,1,1),
(129,2,'directory','directory',1,1,1,1,1,1,1),
(130,3,'directory','directory',1,0,1,0,0,0,1),
(131,1,'group','group',1,1,1,1,1,1,1),
(132,2,'group','group',1,1,1,1,1,1,1),
(133,3,'group','group',1,0,1,0,0,0,1),
(134,1,'license','license',1,1,1,1,1,1,0),
(135,2,'license','license',0,0,1,0,0,0,0),
(136,3,'license','license',0,0,1,0,0,0,0),
(137,1,'organization','organization',1,1,1,1,1,1,0),
(138,2,'organization','organization',1,0,1,0,0,0,0),
(139,3,'organization','organization',1,0,1,0,0,0,0),
(140,1,'report','report',1,0,1,0,0,0,1),
(141,2,'report','report',1,1,1,1,1,1,1),
(142,3,'report','report',1,0,1,0,0,0,1),
(143,1,'report-data-view','report-data-view',1,0,1,0,0,0,1),
(144,2,'report-data-view','report-data-view',1,1,1,1,1,1,1),
(145,3,'report-data-view','report-data-view',1,0,1,0,0,0,1),
(146,1,'report-data-view-js','report-data-view-js',1,0,1,0,0,0,1),
(147,2,'report-data-view-js','report-data-view-js',1,1,1,1,1,1,1),
(148,3,'report-data-view-js','report-data-view-js',1,0,1,0,0,0,1),
(149,1,'report-parameter-input','report-parameter-input',0,0,0,0,0,0,1),
(150,2,'report-parameter-input','report-parameter-input',1,1,1,1,1,1,1),
(151,3,'report-parameter-input','report-parameter-input',0,0,0,0,0,0,1),
(152,1,'report-parameter','report-parameter',1,0,1,0,0,0,1),
(153,2,'report-parameter','report-parameter',1,1,1,1,1,1,1),
(154,3,'report-parameter','report-parameter',1,0,1,0,0,0,1),
(155,1,'user','user',1,1,1,1,1,1,1),
(156,2,'user','user',1,0,1,0,0,0,1),
(157,3,'user','user',1,0,1,0,0,0,1),
(158,1,'user-preferences','user-preferences',0,0,1,1,0,1,1),
(159,2,'user-preferences','user-preferences',0,0,1,1,0,1,1),
(160,3,'user-preferences','user-preferences',0,0,1,1,0,1,1),
(161,1,'system-info','system-info',1,0,1,0,0,1,0),
(162,2,'system-info','system-info',1,0,1,0,0,1,0),
(163,3,'system-info','system-info',0,0,0,0,0,1,0),
(164,1,'service-message','service-message',1,1,1,1,1,1,0),
(165,2,'service-message','service-message',1,1,1,1,1,1,0),
(166,3,'service-message','service-message',1,0,1,0,0,0,0),
(167,1,'report-user-favorite','report-user-favorite',1,1,1,1,1,0,0),
(168,2,'report-user-favorite','report-user-favorite',1,1,1,1,1,0,0),
(169,3,'report-user-favorite','report-user-favorite',1,1,1,1,1,0,0),
(170,1,'cache-job','cache-job',1,0,1,0,0,0,0),
(171,2,'cache-job','cache-job',1,1,1,1,1,1,0),
(172,3,'cache-job','cache-job',1,0,1,0,0,0,0);
/*!40000 ALTER TABLE `role_grants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'Administrator','2022-07-21 10:48:45',NULL),
(2,'Developer','2022-07-21 10:48:45',NULL),
(3,'Viewer','2022-07-21 10:48:45',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_messages`
--

DROP TABLE IF EXISTS `service_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `contents` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_messages`
--

LOCK TABLES `service_messages` WRITE;
/*!40000 ALTER TABLE `service_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_user_id` bigint(20) unsigned NOT NULL,
  `lang` varchar(2) DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_preferences_organization_user_id_foreign` (`organization_user_id`),
  CONSTRAINT `user_preferences_organization_user_id_foreign` FOREIGN KEY (`organization_user_id`) REFERENCES `organization_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES
(53,1,'fr','saga-blue');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_password` tinyint(1) NOT NULL DEFAULT 1,
  `first_connection` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'super-admin','super-admin@domain','2022-07-21 10:48:45','super-admin','super-admin','$2y$10$3sdY0U20n45LHMazwcJmhuNu4C6c8AXWaHYmNzzkOzy6drBOP5fhi',1,1,1,0,NULL,'2022-07-21 10:48:45',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `websockets_statistics_entries`
--

DROP TABLE IF EXISTS `websockets_statistics_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `websockets_statistics_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(255) NOT NULL,
  `peak_connections_count` int(11) NOT NULL,
  `websocket_messages_count` int(11) NOT NULL,
  `api_messages_count` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `websockets_statistics_entries`
--

LOCK TABLES `websockets_statistics_entries` WRITE;
/*!40000 ALTER TABLE `websockets_statistics_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `websockets_statistics_entries` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-08-08  9:57:06
