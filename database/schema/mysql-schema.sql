/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `activity_log_event_index` (`event`),
  KEY `activity_log_causer_index` (`causer_type`,`causer_id`),
  KEY `activity_log_created_at_index` (`created_at`),
  KEY `activity_log_subject_index` (`subject_type`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backup_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_backup_at` timestamp NULL DEFAULT NULL,
  `next_backup_at` timestamp NULL DEFAULT NULL,
  `retention_count` int NOT NULL DEFAULT '8',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `barangays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barangays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `has_scheduled_vaccination` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barangays_name_unique` (`name`),
  KEY `idx_barangays_name` (`name`),
  KEY `idx_barangays_active_schedulable` (`is_active`,`has_scheduled_vaccination`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `vaccination_schedule_id` bigint unsigned DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_parent_schedule_feedback` (`parent_id`,`vaccination_schedule_id`),
  KEY `patient_id` (`patient_id`),
  KEY `feedback_vaccination_schedule_id_foreign` (`vaccination_schedule_id`),
  KEY `idx_feedback_barangay` (`barangay`),
  CONSTRAINT `feedback_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_vaccination_schedule_id_foreign` FOREIGN KEY (`vaccination_schedule_id`) REFERENCES `vaccination_schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `health_workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `health_workers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_created_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_requested_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `health_workers_username_unique` (`username`),
  UNIQUE KEY `health_workers_email_unique` (`email`),
  KEY `health_workers_barangay_id_index` (`barangay_id`),
  KEY `health_workers_created_by_foreign` (`created_by`),
  CONSTRAINT `health_workers_barangay_id_foreign` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE SET NULL,
  CONSTRAINT `health_workers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_created_at` timestamp NULL DEFAULT NULL,
  `password_changed` tinyint(1) NOT NULL DEFAULT '1',
  `privacy_policy_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_policy_accepted_at` timestamp NULL DEFAULT NULL,
  `privacy_policy_version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parents_username_unique` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_parents_barangay` (`barangay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_growth_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_growth_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int unsigned NOT NULL,
  `height` decimal(5,2) DEFAULT NULL COMMENT 'Height in cm',
  `weight` decimal(5,2) DEFAULT NULL COMMENT 'Weight in kg',
  `recorded_date` date NOT NULL COMMENT 'When measurement was taken',
  `recorded_by` int unsigned DEFAULT NULL COMMENT 'Health worker ID',
  `measurement_type` enum('birth','routine_checkup','vaccination_visit','sick_visit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'routine_checkup',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_growth_records_patient_id_index` (`patient_id`),
  KEY `patient_growth_records_recorded_by_index` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patient_vaccine_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_vaccine_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `vaccine_id` int NOT NULL,
  `dose_1_date` date DEFAULT NULL,
  `dose_2_date` date DEFAULT NULL,
  `dose_3_date` date DEFAULT NULL,
  `dose_4_date` date DEFAULT NULL,
  `dose_5_date` date DEFAULT NULL,
  `dose_6_date` date DEFAULT NULL,
  `dose_7_date` date DEFAULT NULL,
  `dose_8_date` date DEFAULT NULL,
  `dose_9_date` date DEFAULT NULL,
  `dose_10_date` date DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_patient_vaccine` (`patient_id`,`vaccine_id`),
  KEY `patient_id` (`patient_id`),
  KEY `vaccine_id` (`vaccine_id`),
  KEY `idx_pvr_vaccine` (`vaccine_id`),
  KEY `idx_pvr_vaccine_dose1` (`vaccine_id`,`dose_1_date`),
  KEY `idx_pvr_vaccine_dose2` (`vaccine_id`,`dose_2_date`),
  KEY `idx_pvr_vaccine_dose3` (`vaccine_id`,`dose_3_date`),
  KEY `idx_pvr_patient_dose1` (`patient_id`,`dose_1_date`),
  CONSTRAINT `patient_vaccine_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `patient_vaccine_records_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `qr_uuid` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `mother_name` text NOT NULL,
  `father_name` text NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` text NOT NULL,
  `address` text NOT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `birth_height` text NOT NULL,
  `birth_weight` text NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `profile_image` varchar(100) DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `barangay` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_qr_uuid_unique` (`qr_uuid`),
  KEY `fk_parent_id` (`parent_id`),
  KEY `idx_patients_barangay` (`barangay`),
  KEY `idx_patients_barangay_dob` (`barangay`,`date_of_birth`),
  KEY `idx_patients_contact_no` (`contact_no`),
  KEY `idx_patients_dob` (`date_of_birth`),
  KEY `idx_patients_created_at` (`created_at`),
  KEY `idx_patients_name` (`name`),
  KEY `idx_patients_contact` (`contact_no`),
  KEY `idx_patients_created` (`created_at`),
  KEY `idx_patients_last_name` (`last_name`),
  KEY `idx_patients_qr_uuid` (`qr_uuid`),
  CONSTRAINT `fk_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_subscribable_morph_idx` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recipient_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `gateway_response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gateway_message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifiable_id` bigint unsigned DEFAULT NULL,
  `notification_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(8,2) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_logs_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `sms_logs_status_index` (`status`),
  KEY `sms_logs_notification_id_foreign` (`notification_id`),
  CONSTRAINT `sms_logs_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vaccination_report_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccination_report_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter_start` tinyint NOT NULL,
  `month_start` tinyint DEFAULT NULL,
  `quarter_end` tinyint NOT NULL,
  `month_end` tinyint DEFAULT NULL,
  `barangay` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vaccine_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `male_count` int NOT NULL DEFAULT '0',
  `female_count` int NOT NULL DEFAULT '0',
  `total_count` int NOT NULL DEFAULT '0',
  `percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `eligible_population` int NOT NULL DEFAULT '0',
  `eligible_population_under_1_year` int NOT NULL DEFAULT '0',
  `eligible_population_0_12_months` int NOT NULL DEFAULT '0',
  `eligible_population_13_23_months` int NOT NULL DEFAULT '0',
  `eligible_population_0_23_months` int NOT NULL DEFAULT '0',
  `data_source` enum('calculated','manual_edit','imported') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'calculated',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `saved_at` timestamp NULL DEFAULT NULL,
  `saved_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `deletion_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report_cell_with_version_and_months` (`year`,`quarter_start`,`quarter_end`,`month_start`,`month_end`,`barangay`,`vaccine_name`,`version`),
  KEY `idx_year_quarter` (`year`,`quarter_start`,`quarter_end`),
  KEY `idx_barangay` (`barangay`),
  KEY `vaccination_report_snapshots_created_by_foreign` (`created_by`),
  KEY `vaccination_report_snapshots_updated_by_foreign` (`updated_by`),
  KEY `vaccination_report_snapshots_saved_by_foreign` (`saved_by`),
  KEY `vaccination_report_snapshots_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `vaccination_report_snapshots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_report_snapshots_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_report_snapshots_saved_by_foreign` FOREIGN KEY (`saved_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_report_snapshots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vaccination_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccination_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vaccination_date` date NOT NULL,
  `barangay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('scheduled','active','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vaccination_schedules_vaccination_date_index` (`vaccination_date`),
  KEY `vaccination_schedules_barangay_index` (`barangay`),
  KEY `vaccination_schedules_status_index` (`status`),
  KEY `vaccination_schedules_created_by_foreign` (`created_by`),
  KEY `vaccination_schedules_cancelled_at_index` (`cancelled_at`),
  KEY `vaccination_schedules_cancelled_by_foreign` (`cancelled_by`),
  KEY `idx_vacc_schedules_barangay` (`barangay`),
  KEY `idx_vacc_schedules_date_status` (`vaccination_date`,`status`),
  CONSTRAINT `vaccination_schedules_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `health_workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vaccination_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccination_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vaccination_session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vaccination_schedule_id` bigint unsigned DEFAULT NULL,
  `administered_elsewhere` tinyint(1) NOT NULL DEFAULT '0',
  `stock_override` tinyint(1) NOT NULL DEFAULT '0',
  `patient_id` int NOT NULL,
  `vaccine_id` int NOT NULL,
  `patient_vaccine_record_id` int NOT NULL,
  `dose_number` int NOT NULL COMMENT 'Which dose: 1, 2, or 3',
  `doses_deducted` int NOT NULL DEFAULT '1' COMMENT 'Number of doses deducted (usually 1)',
  `vaccinated_at` date NOT NULL COMMENT 'Date of vaccination',
  `vaccinated_by` bigint unsigned DEFAULT NULL COMMENT 'Health worker who administered vaccine',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Additional information about this vaccination',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vaccination_transactions_patient_vaccine_record_id_foreign` (`patient_vaccine_record_id`),
  KEY `vaccination_transactions_vaccinated_by_foreign` (`vaccinated_by`),
  KEY `vaccination_transactions_patient_id_index` (`patient_id`),
  KEY `vaccination_transactions_vaccine_id_index` (`vaccine_id`),
  KEY `vaccination_transactions_vaccinated_at_index` (`vaccinated_at`),
  KEY `vaccination_transactions_patient_id_vaccine_id_dose_number_index` (`patient_id`,`vaccine_id`,`dose_number`),
  KEY `vaccination_transactions_vaccination_session_id_index` (`vaccination_session_id`),
  KEY `vaccination_transactions_created_at_index` (`created_at`),
  KEY `vaccination_transactions_vaccination_schedule_id_index` (`vaccination_schedule_id`),
  CONSTRAINT `vaccination_transactions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vaccination_transactions_patient_vaccine_record_id_foreign` FOREIGN KEY (`patient_vaccine_record_id`) REFERENCES `patient_vaccine_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vaccination_transactions_vaccinated_by_foreign` FOREIGN KEY (`vaccinated_by`) REFERENCES `health_workers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_transactions_vaccination_schedule_id_foreign` FOREIGN KEY (`vaccination_schedule_id`) REFERENCES `vaccination_schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vaccination_transactions_vaccine_id_foreign` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vaccine_dose_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccine_dose_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vaccine_id` int NOT NULL,
  `dose_number` tinyint unsigned NOT NULL,
  `min_age_months` decimal(8,2) NOT NULL DEFAULT '0.00',
  `max_age_months` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vaccine_dose_configs_vaccine_id_dose_number_unique` (`vaccine_id`,`dose_number`),
  CONSTRAINT `vaccine_dose_configs_vaccine_id_foreign` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vaccines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vaccine_name` varchar(100) NOT NULL,
  `doses_description` text,
  `total_doses` int unsigned NOT NULL DEFAULT '1',
  `target_age_group` varchar(50) NOT NULL DEFAULT 'under_1_year',
  `acronym` varchar(50) DEFAULT NULL,
  `is_standard` tinyint(1) NOT NULL DEFAULT '0',
  `is_active_in_reports` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '99',
  `stocks` int NOT NULL DEFAULT '0',
  `available_bottles` int NOT NULL DEFAULT '0',
  `doses_per_bottle` int NOT NULL DEFAULT '10',
  `doses_used_from_current_bottles` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vaccines_name` (`vaccine_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_01_08_180802_create_inventories_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_05_28_115109_create_telescope_entries_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_05_28_141828_update_encrypted_fields_to_text',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_09_01_091000_create_patient_growth_records_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_09_01_195732_add_stocks_to_vaccines_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_09_02_000001_add_unique_patient_vaccine_constraint',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_11_11_012913_create_vaccination_report_snapshots_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_11_11_110835_add_soft_delete_and_versioning_to_vaccination_report_snapshots',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_11_11_110845_add_soft_delete_and_versioning_to_vaccination_report_snapshots',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_11_11_113710_update_unique_constraint_to_include_version_in_vaccination_report_snapshots',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_11_14_010046_add_month_columns_to_vaccination_report_snapshots',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_11_14_040647_add_performance_indexes_to_tables',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_11_15_153042_decrypt_patient_searchable_fields',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_11_15_154151_add_indexes_to_patients_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_11_15_172227_add_vaccination_status_indexes_to_patients_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_11_16_171552_create_vaccine_inventory_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_11_16_171559_create_vaccination_transactions_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_11_17_120000_add_bottle_fields_to_vaccines_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_11_17_141205_add_doses_used_tracking_to_vaccines_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_11_17_150000_update_vaccination_transactions_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_11_17_223018_create_vaccination_schedules_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_11_18_add_cancellation_fields_to_vaccination_schedules',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_11_18_041858_add_vaccination_schedule_id_to_vaccination_transactions',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_11_18_061946_add_backdate_flags_to_vaccination_transactions_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_11_19_001523_update_unique_constraint_with_months_in_vaccination_report_snapshots',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_11_19_144654_add_age_group_eligible_populations_to_vaccination_report_snapshots',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_11_20_062946_add_parent_location_fields_to_parents_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_11_20_063006_update_feedback_table_for_parent_centric_submissions',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_11_20_000003_delete_patients_52_53_and_parents',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_11_20_000001_add_password_changed_to_parents_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_11_20_000004_add_privacy_policy_fields_to_parents_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_11_20_000005_delete_parent_44_and_children',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_11_20_000002_remove_password_number_from_parents_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_11_20_222741_create_notifications_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_11_20_222807_create_sms_logs_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_11_20_230324_create_push_subscriptions_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_11_22_000001_add_barangay_index_to_parents_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_11_23_231258_make_notifiable_nullable_in_sms_logs_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_11_23_231714_increase_gateway_response_size_in_sms_logs',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_11_25_174349_add_fcm_token_to_parents_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_11_26_000001_create_barangays_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_11_26_000002_add_barangay_id_to_health_workers_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_11_26_000003_add_performance_indexes_for_barangay_system',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_11_30_000001_create_password_reset_tokens_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_11_30_100000_sync_production_database',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_11_30_120000_fix_production_schema_differences',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_12_01_000001_create_backup_settings_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_12_01_000001_add_remember_token_to_users',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_12_09_000001_add_session_token_to_user_tables',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_12_10_012027_add_profile_image_to_patients_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_12_12_214420_create_activity_log_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_12_12_214421_add_event_column_to_activity_log_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_12_12_214422_add_batch_uuid_column_to_activity_log_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_12_13_000001_add_profile_fields_to_health_workers',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_12_13_100000_add_account_management_fields_to_health_workers',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_12_13_015412_make_email_and_contact_nullable_in_health_workers',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_12_13_031322_add_indexes_to_activity_log_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_12_13_063933_add_eligible_population_0_23_months_to_vaccination_report_snapshots',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_12_13_120000_add_separate_name_fields_to_patients_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_01_25_000001_add_qr_uuid_to_patients_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_01_26_020100_add_index_to_qr_uuid_on_patients_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_06_02_000001_add_config_columns_to_vaccines_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_06_02_000002_create_vaccine_dose_configs_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_06_02_000003_seed_vaccine_config_from_static',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_06_28_000001_add_patient_vaccine_record_dose_7_to_10_columns',55);
