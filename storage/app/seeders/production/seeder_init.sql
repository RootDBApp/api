-- MySQL dump 10.13  Distrib 8.0.37, for Linux (x86_64)
--
-- Host: 172.20.0.50    Database: rootdb-api
-- ------------------------------------------------------
-- Server version	11.4.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
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
