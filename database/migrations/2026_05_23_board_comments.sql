/*M!999999\- enable the sandbox mode */ 

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `patient_board_columns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `clinic_id` bigint(20) unsigned DEFAULT NULL,
  `doctor_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pbc_clinic` (`clinic_id`),
  KEY `idx_pbc_doctor` (`doctor_id`),
  KEY `idx_pbc_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `patient_board_assignments` (
  `patient_id` bigint(20) unsigned NOT NULL,
  `column_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `moved_at` timestamp NULL DEFAULT current_timestamp(),
  `moved_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  KEY `idx_pba_col_sort` (`column_id`,`sort_order`),
  CONSTRAINT `fk_pba_column` FOREIGN KEY (`column_id`) REFERENCES `patient_board_columns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pba_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `commentable_type` enum('patient','appointment','board_card') NOT NULL,
  `commentable_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_c_target` (`commentable_type`,`commentable_id`,`created_at`),
  KEY `idx_c_parent` (`parent_id`),
  KEY `idx_c_user` (`user_id`),
  CONSTRAINT `fk_c_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `comment_mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL,
  `mentioned_user_id` bigint(20) unsigned NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cm_unread` (`mentioned_user_id`,`read_at`),
  KEY `fk_cm_comment` (`comment_id`),
  CONSTRAINT `fk_cm_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `comment_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `kind` enum('image','audio','file') NOT NULL DEFAULT 'file',
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `duration_ms` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ca_comment` (`comment_id`),
  KEY `idx_ca_user` (`user_id`),
  CONSTRAINT `fk_ca_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- ---------------------------------------------------------------------
-- Seed default patient-board columns (clinic 1). INSERT IGNORE keyed on
-- explicit id so re-running is a no-op and a doctor's later renames are
-- never overwritten. No patient assignment rows are seeded (local-only).
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `patient_board_columns` (`id`,`clinic_id`,`doctor_id`,`name`,`color`,`sort_order`,`is_system`) VALUES
 (1,1,NULL,'New Consultation','#0ea5e9',10,1),
 (2,1,NULL,'Awaiting Investigations','#f59e0b',20,0),
 (3,1,NULL,'Diagnosis & Plan','#a855f7',30,0),
 (4,1,NULL,'Under Treatment','#22c55e',40,0),
 (5,1,NULL,'Surgery — Pre-op','#ef4444',50,0),
 (6,1,NULL,'Surgery — Post-op','#dc2626',60,0),
 (7,1,NULL,'Follow-up','#64748b',80,0),
 (8,1,NULL,'Discharged','#475569',90,0);
