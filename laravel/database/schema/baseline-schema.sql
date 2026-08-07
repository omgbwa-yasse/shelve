SET FOREIGN_KEY_CHECKS=0;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `communicability_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activities_code_unique` (`code`),
  KEY `activities_parent_id_foreign` (`parent_id`),
  KEY `activities_communicability_id_foreign` (`communicability_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_skills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` enum('system','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `folder` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `installed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_skills_slug_unique` (`slug`),
  KEY `ai_skills_installed_by_foreign` (`installed_by`),
  KEY `ai_skills_location_enabled_index` (`location`,`enabled`),
  CONSTRAINT `ai_skills_installed_by_foreign` FOREIGN KEY (`installed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_templates_created_by_foreign` (`created_by`),
  CONSTRAINT `ai_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alternative_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_type` enum('altLabel','hiddenLabel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'altLabel',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `relation_type` enum('synonym','quasi_synonym','abbreviation','acronym','scientific_name','common_name','brand_name','variant_spelling','old_form','modern_form','antonym','broader_synonym','narrower_synonym') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'synonym',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alternative_labels_concept_id_label_type_index` (`concept_id`,`label_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `associative_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept1_id` bigint unsigned NOT NULL,
  `concept2_id` bigint unsigned NOT NULL,
  `relation_subtype` enum('cause_effect','whole_part','action_agent','action_product','action_object','action_location','science_object','object_property','object_role','raw_material_product','process_neutralizer','object_origin','concept_measurement','profession_person','temporal','spatial','functional','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `relation_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relation_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_associative_relation` (`concept1_id`,`concept2_id`),
  KEY `associative_relations_concept2_id_foreign` (`concept2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `crypt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_path` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_generated_at` timestamp NULL DEFAULT NULL COMMENT 'Date de génération de la vignette',
  `thumbnail_error` text COLLATE utf8mb4_unicode_ci COMMENT 'Erreur lors de la génération de la vignette',
  `thumbnail_size_bytes` int DEFAULT NULL COMMENT 'Taille de la vignette en bytes (max 10KB)',
  `thumbnail_density_ppi` int NOT NULL DEFAULT '60' COMMENT 'Densité de la vignette en PPI (pixels par pouce)',
  `thumbnail_compression_quality` int NOT NULL DEFAULT '60' COMMENT 'Qualité de compression JPEG (0-100)',
  `size` int NOT NULL,
  `crypt_sha512` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_hash_md5` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('mail','record','communication','transferting','bulletinboardpost','bulletinboard','bulletinboardevent','digital_folder','digital_document','artifact','book','periodic') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Fichier principal',
  `display_order` int NOT NULL DEFAULT '0',
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_extension` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_encoding` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_text` longtext COLLATE utf8mb4_unicode_ci,
  `ocr_language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocr_confidence` decimal(5,2) DEFAULT NULL COMMENT 'Score qualité OCR 0-100',
  `page_count` int DEFAULT NULL COMMENT 'Nombre de pages PDF',
  `word_count` int DEFAULT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_creator_id_foreign` (`creator_id`),
  KEY `idx_type_primary` (`type`,`is_primary`),
  KEY `idx_file_hash` (`file_hash_md5`),
  KEY `idx_extension` (`file_extension`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `author_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parallel_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lifespan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locations` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `author_addresses_name_unique` (`name`),
  KEY `author_addresses_parent_id_foreign` (`parent_id`),
  KEY `author_addresses_type_id_foreign` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `author_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `phone1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other` text COLLATE utf8mb4_unicode_ci,
  `po_box` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_contacts_author_id_foreign` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `author_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `author_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parallel_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lifespan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locations` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authors_name_unique` (`name`),
  KEY `authors_parent_id_foreign` (`parent_id`),
  KEY `authors_type_id_foreign` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `backup_id` bigint unsigned NOT NULL,
  `path_original` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_storage` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint NOT NULL,
  `hash` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_files_backup_id_foreign` (`backup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_plannings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `backup_id` bigint unsigned NOT NULL,
  `frequence` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week_day` int DEFAULT NULL,
  `month_day` int DEFAULT NULL,
  `hour` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_plannings_backup_id_foreign` (`backup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('metadata','full') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('in_progress','success','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `size` bigint NOT NULL,
  `backup_file` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batch_mail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int unsigned DEFAULT NULL,
  `mail_id` bigint unsigned DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `remove_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_mail_batch_id_foreign` (`batch_id`),
  KEY `batch_mail_mail_id_foreign` (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batch_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int unsigned NOT NULL,
  `organisation_send_id` bigint unsigned NOT NULL,
  `organisation_received_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `batch_transactions_batch_id_foreign` (`batch_id`),
  KEY `batch_transactions_organisation_send_id_foreign` (`organisation_send_id`),
  KEY `batch_transactions_organisation_received_id_foreign` (`organisation_received_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organisation_holder_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batches_code_unique` (`code`),
  KEY `batches_organisation_holder_id_foreign` (`organisation_holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `visibility` enum('public','private','inherit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private' COMMENT 'Visibilité du bâtiment: public, private, ou inherit',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `creator_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buildings_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulletin_board_organisation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bulletin_board_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `assigned_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bulletin_board_organisation_bulletin_board_id_foreign` (`bulletin_board_id`),
  KEY `bulletin_board_organisation_organisation_id_foreign` (`organisation_id`),
  KEY `bulletin_board_organisation_assigned_by_foreign` (`assigned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulletin_board_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bulletin_board_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` enum('super_admin','admin','moderator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `permissions` enum('write','delete','edit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'write',
  `assigned_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bulletin_board_user_bulletin_board_id_user_id_unique` (`bulletin_board_id`,`user_id`),
  KEY `bulletin_board_user_user_id_foreign` (`user_id`),
  KEY `bulletin_board_user_assigned_by_foreign` (`assigned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulletin_boards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bulletin_boards_name_unique` (`name`),
  KEY `bulletin_boards_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Classification name',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description',
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classifications_name_index` (`name`),
  KEY `classifications_parent_id_index` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `collection_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `order_index` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_members_collection_id_concept_id_unique` (`collection_id`,`concept_id`),
  KEY `collection_members_concept_id_foreign` (`concept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_scheme_id` bigint unsigned NOT NULL,
  `uri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `notation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_type` enum('Collection','OrderedCollection') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Collection',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collections_uri_hash_unique` (`uri_hash`),
  KEY `collections_concept_scheme_id_foreign` (`concept_scheme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communicabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `communicabilities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communication_record` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `communication_id` bigint unsigned NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `is_original` tinyint(1) NOT NULL DEFAULT '0',
  `return_date` date NOT NULL,
  `return_effective` date DEFAULT NULL,
  `operator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communication_record_communication_id_foreign` (`communication_id`),
  KEY `communication_record_record_id_foreign` (`record_id`),
  KEY `communication_record_operator_id_foreign` (`operator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `operator_id` bigint unsigned NOT NULL,
  `operator_organisation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `user_organisation_id` bigint unsigned NOT NULL,
  `return_date` date NOT NULL,
  `return_effective` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','in_consultation','returned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `communications_code_unique` (`code`),
  KEY `communications_operator_id_foreign` (`operator_id`),
  KEY `communications_user_id_foreign` (`user_id`),
  KEY `idx_comm_operator_org` (`operator_organisation_id`),
  KEY `idx_comm_user_org` (`user_organisation_id`),
  KEY `idx_comm_dual_org` (`operator_organisation_id`,`user_organisation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concept_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `action_type` enum('created','updated','deprecated','merged','split') COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `user_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `concept_history_concept_id_action_type_index` (`concept_id`,`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concept_schemes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `creator` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contributor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publisher` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rights` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coverage` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `status` enum('active','deprecated','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `concept_schemes_uri_hash_unique` (`uri_hash`),
  UNIQUE KEY `concept_schemes_identifier_unique` (`identifier`),
  KEY `concept_schemes_status_language_index` (`status`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concepts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_scheme_id` bigint unsigned NOT NULL,
  `uri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `definition` text COLLATE utf8mb4_unicode_ci,
  `scope_note` text COLLATE utf8mb4_unicode_ci,
  `history_note` text COLLATE utf8mb4_unicode_ci,
  `editorial_note` text COLLATE utf8mb4_unicode_ci,
  `example` text COLLATE utf8mb4_unicode_ci,
  `change_note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('approved','candidate','deprecated','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'candidate',
  `iso_status` int DEFAULT NULL,
  `is_top_concept` tinyint(1) NOT NULL DEFAULT '0',
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `additional_properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `concepts_uri_hash_unique` (`uri_hash`),
  KEY `concepts_concept_scheme_id_status_index` (`concept_scheme_id`,`status`),
  KEY `concepts_language_status_index` (`language`,`status`),
  KEY `concepts_is_top_concept_index` (`is_top_concept`),
  KEY `concepts_notation_index` (`notation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('email','telephone','gps','fax','code_postal','adresse') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` float NOT NULL,
  `length` float NOT NULL,
  `depth` float NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `container_properties_name_unique` (`name`),
  KEY `container_properties_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `container_statuses_name_unique` (`name`),
  KEY `container_statuses_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `containers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shelve_id` bigint unsigned NOT NULL,
  `status_id` bigint unsigned NOT NULL,
  `property_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `creator_organisation_id` bigint unsigned NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `containers_code_unique` (`code`),
  KEY `containers_creator_organisation_id_foreign` (`creator_organisation_id`),
  KEY `containers_creator_id_foreign` (`creator_id`),
  KEY `containers_shelve_id_foreign` (`shelve_id`),
  KEY `containers_status_id_foreign` (`status_id`),
  KEY `containers_property_id_foreign` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declassement_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `declassement_list_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `declassement_comments_declassement_list_id_foreign` (`declassement_list_id`),
  KEY `declassement_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `declassement_comments_declassement_list_id_foreign` FOREIGN KEY (`declassement_list_id`) REFERENCES `declassement_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declassement_containers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `declassement_list_id` bigint unsigned NOT NULL,
  `container_id` bigint unsigned NOT NULL,
  `added_by` bigint unsigned DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `declassement_containers_declassement_list_id_container_id_unique` (`declassement_list_id`,`container_id`),
  KEY `declassement_containers_container_id_foreign` (`container_id`),
  KEY `declassement_containers_added_by_foreign` (`added_by`),
  CONSTRAINT `declassement_containers_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `declassement_containers_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_containers_declassement_list_id_foreign` FOREIGN KEY (`declassement_list_id`) REFERENCES `declassement_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declassement_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `organisation_id` bigint unsigned NOT NULL,
  `declassement_status_id` bigint unsigned NOT NULL,
  `query_criteria` json DEFAULT NULL,
  `digital_support` tinyint(1) NOT NULL DEFAULT '0',
  `analog_support` tinyint(1) NOT NULL DEFAULT '0',
  `include_subrecords` tinyint(1) NOT NULL DEFAULT '0',
  `creator_id` bigint unsigned NOT NULL,
  `is_approval_requested` tinyint(1) DEFAULT '0',
  `approval_requested_date` datetime DEFAULT NULL,
  `approval_requested_by` bigint unsigned DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `approved_date` datetime DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT '0',
  `validated_date` datetime DEFAULT NULL,
  `validated_by` bigint unsigned DEFAULT NULL,
  `is_treated` tinyint(1) DEFAULT '0',
  `treated_date` datetime DEFAULT NULL,
  `treated_by` bigint unsigned DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `declassement_lists_code_unique` (`code`),
  KEY `declassement_lists_organisation_id_foreign` (`organisation_id`),
  KEY `declassement_lists_declassement_status_id_foreign` (`declassement_status_id`),
  KEY `declassement_lists_creator_id_foreign` (`creator_id`),
  KEY `declassement_lists_approval_requested_by_foreign` (`approval_requested_by`),
  KEY `declassement_lists_approved_by_foreign` (`approved_by`),
  KEY `declassement_lists_validated_by_foreign` (`validated_by`),
  KEY `declassement_lists_treated_by_foreign` (`treated_by`),
  CONSTRAINT `declassement_lists_approval_requested_by_foreign` FOREIGN KEY (`approval_requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_declassement_status_id_foreign` FOREIGN KEY (`declassement_status_id`) REFERENCES `declassement_statuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_treated_by_foreign` FOREIGN KEY (`treated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_lists_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declassement_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `declassement_list_id` bigint unsigned NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `added_by` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `declassement_list_record_unique` (`declassement_list_id`,`record_id`),
  KEY `declassement_records_added_by_foreign` (`added_by`),
  KEY `declassement_records_record_id_foreign` (`record_id`),
  CONSTRAINT `declassement_records_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_records_declassement_list_id_foreign` FOREIGN KEY (`declassement_list_id`) REFERENCES `declassement_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `declassement_records_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declassement_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dollies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('mail','transaction','record','slip','building','shelf','container','communication','room','digital_folder','digital_document','artifact','book','book_series') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `owner_organisation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dollies_name_unique` (`name`),
  KEY `dollies_owner_organisation_id_foreign` (`owner_organisation_id`),
  KEY `dollies_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_artifacts` (
  `artifact_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `dolly_artifacts_artifact_id_dolly_id_unique` (`artifact_id`,`dolly_id`),
  KEY `dolly_artifacts_dolly_id_index` (`dolly_id`),
  KEY `dolly_artifacts_artifact_id_index` (`artifact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_book_series` (
  `series_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `dolly_book_series_series_id_dolly_id_unique` (`series_id`,`dolly_id`),
  KEY `dolly_book_series_dolly_id_index` (`dolly_id`),
  KEY `dolly_book_series_series_id_index` (`series_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_books` (
  `book_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `dolly_books_book_id_dolly_id_unique` (`book_id`,`dolly_id`),
  KEY `dolly_books_dolly_id_index` (`dolly_id`),
  KEY `dolly_books_book_id_index` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_buildings` (
  `building_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_buildings_building_id_foreign` (`building_id`),
  KEY `dolly_buildings_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_communications` (
  `communication_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_communications_communication_id_foreign` (`communication_id`),
  KEY `dolly_communications_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_containers` (
  `container_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_containers_container_id_foreign` (`container_id`),
  KEY `dolly_containers_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_digital_documents` (
  `document_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `dolly_digital_documents_document_id_dolly_id_unique` (`document_id`,`dolly_id`),
  KEY `dolly_digital_documents_dolly_id_index` (`dolly_id`),
  KEY `dolly_digital_documents_document_id_index` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_digital_folders` (
  `folder_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `dolly_digital_folders_folder_id_dolly_id_unique` (`folder_id`,`dolly_id`),
  KEY `dolly_digital_folders_dolly_id_index` (`dolly_id`),
  KEY `dolly_digital_folders_folder_id_index` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_mail_transactions` (
  `mail_transaction_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_mail_transactions_mail_transaction_id_foreign` (`mail_transaction_id`),
  KEY `dolly_mail_transactions_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_mails` (
  `mail_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_mails_mail_id_foreign` (`mail_id`),
  KEY `dolly_mails_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_records` (
  `record_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_records_record_id_foreign` (`record_id`),
  KEY `dolly_records_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_rooms` (
  `room_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_rooms_room_id_foreign` (`room_id`),
  KEY `dolly_rooms_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_shelves` (
  `shelf_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_shelves_shelf_id_foreign` (`shelf_id`),
  KEY `dolly_shelves_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_slip_records` (
  `record_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `slip_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_slip_records_slip_id_foreign` (`slip_id`),
  KEY `dolly_slip_records_dolly_id_foreign` (`dolly_id`),
  KEY `dolly_slip_records_record_id_foreign` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dolly_slips` (
  `slip_id` bigint unsigned NOT NULL,
  `dolly_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `dolly_slips_slip_id_foreign` (`slip_id`),
  KEY `dolly_slips_dolly_id_foreign` (`dolly_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_attachments_event_id_foreign` (`event_id`),
  KEY `event_attachments_attachment_id_foreign` (`attachment_id`),
  KEY `event_attachments_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bulletin_board_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_name_unique` (`name`),
  KEY `events_bulletin_board_id_foreign` (`bulletin_board_id`),
  KEY `events_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_alignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `external_uri` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_label` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_notation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_vocabulary` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vocabulary_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_type` enum('exact','close','broad','narrow','related') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exact',
  `confidence_score` decimal(3,2) DEFAULT NULL,
  `additional_metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_alignments_concept_id_foreign` (`concept_id`),
  KEY `external_alignments_external_vocabulary_match_type_index` (`external_vocabulary`,`match_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `position` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Poste ou fonction de la personne',
  `external_organization_id` bigint unsigned DEFAULT NULL,
  `is_primary_contact` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Contact principal pour l''organisation',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_contacts_first_name_index` (`first_name`),
  KEY `external_contacts_last_name_index` (`last_name`),
  KEY `external_contacts_email_index` (`email`),
  KEY `external_contacts_external_organization_id_index` (`external_organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numéro d''immatriculation',
  `tax_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `legal_form` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Forme juridique: SARL, SA, etc.',
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'France',
  PRIMARY KEY (`id`),
  KEY `external_organizations_name_index` (`name`),
  KEY `external_organizations_email_index` (`email`),
  KEY `external_organizations_registration_number_index` (`registration_number`),
  KEY `external_organizations_city_index` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `crypt` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `files_record_id_foreign` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `floors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `building_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`building_id`),
  KEY `floors_building_id_foreign` (`building_id`),
  KEY `floors_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hierarchical_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `broader_concept_id` bigint unsigned NOT NULL,
  `narrower_concept_id` bigint unsigned NOT NULL,
  `relation_type` enum('generic','partitive','instance','disciplinary','causal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generic',
  `relation_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hierarchical_relation` (`broader_concept_id`,`narrower_concept_id`),
  KEY `hierarchical_relations_narrower_concept_id_foreign` (`narrower_concept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keywords` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keywords_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ladp_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` smallint unsigned DEFAULT NULL,
  `server_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ladp_clients_name_unique` (`name`),
  UNIQUE KEY `ladp_clients_ip_address_port_unique` (`ip_address`,`port`),
  KEY `ladp_clients_server_id_foreign` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ladp_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned DEFAULT NULL,
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ladp_contents_name_server_id_unique` (`name`,`server_id`),
  KEY `ladp_contents_server_id_foreign` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ladp_distribution` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `status` enum('pending','in_progress','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ladp_distribution_content_id_client_id_unique` (`content_id`,`client_id`),
  KEY `ladp_distribution_client_id_foreign` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ladp_servers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` smallint unsigned DEFAULT NULL,
  `status` enum('online','offline','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ladp_servers_name_unique` (`name`),
  UNIQUE KEY `ladp_servers_ip_address_port_unique` (`ip_address`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `law_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `law_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `law_articles_law_id_foreign` (`law_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `law_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laws` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `publish_date` date NOT NULL,
  `law_type_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laws_law_type_id_foreign` (`law_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `llm_daily_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `provider` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `requests_count` int unsigned NOT NULL DEFAULT '0',
  `success_count` int unsigned NOT NULL DEFAULT '0',
  `error_count` int unsigned NOT NULL DEFAULT '0',
  `total_prompt_tokens` bigint unsigned NOT NULL DEFAULT '0',
  `total_completion_tokens` bigint unsigned NOT NULL DEFAULT '0',
  `total_tokens` bigint unsigned NOT NULL DEFAULT '0',
  `total_cost_microusd` bigint unsigned NOT NULL DEFAULT '0',
  `avg_latency_ms` int unsigned NOT NULL DEFAULT '0',
  `max_latency_ms` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `llm_daily_stats_unique` (`date`,`provider`,`model`,`source`,`user_id`),
  KEY `llm_daily_stats_user_id_foreign` (`user_id`),
  KEY `llm_daily_stats_date_provider_model_source_index` (`date`,`provider`,`model`,`source`),
  KEY `llm_daily_stats_provider_model_index` (`provider`,`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `llm_interactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `provider` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int unsigned NOT NULL DEFAULT '0',
  `completion_tokens` int unsigned NOT NULL DEFAULT '0',
  `total_tokens` int unsigned NOT NULL DEFAULT '0',
  `latency_ms` int unsigned NOT NULL DEFAULT '0',
  `temperature` decimal(4,2) DEFAULT NULL,
  `top_p` decimal(5,4) DEFAULT NULL,
  `cost_microusd` bigint unsigned NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `llm_interactions_provider_request_id_unique` (`provider`,`request_id`),
  KEY `llm_interactions_provider_index` (`provider`),
  KEY `llm_interactions_model_index` (`model`),
  KEY `llm_interactions_source_index` (`source`),
  KEY `llm_interactions_status_index` (`status`),
  KEY `llm_interactions_error_code_index` (`error_code`),
  KEY `llm_interactions_completed_at_index` (`completed_at`),
  KEY `llm_interactions_user_id_started_at_index` (`user_id`,`started_at`),
  KEY `llm_interactions_started_at_provider_model_index` (`started_at`,`provider`,`model`),
  KEY `llm_interactions_started_at_source_index` (`started_at`,`source`),
  KEY `llm_interactions_status_started_at_index` (`status`,`started_at`),
  KEY `llm_interactions_provider_model_index` (`provider`,`model`),
  KEY `llm_interactions_uuid_index` (`uuid`),
  KEY `llm_interactions_started_at_index` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1956 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `to_return` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_actions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_archives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `container_id` bigint unsigned NOT NULL,
  `mail_id` bigint unsigned NOT NULL,
  `archived_by` bigint unsigned NOT NULL,
  `document_type` enum('original','duplicate','copy') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'original',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_archives_container_id_foreign` (`container_id`),
  KEY `mail_archives_mail_id_foreign` (`mail_id`),
  KEY `mail_archives_archived_by_foreign` (`archived_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_attachment` (
  `mail_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `added_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`mail_id`,`attachment_id`),
  KEY `mail_attachment_attachment_id_foreign` (`attachment_id`),
  KEY `mail_attachment_added_by_foreign` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_author` (
  `author_id` bigint unsigned NOT NULL,
  `mail_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`author_id`,`mail_id`),
  KEY `mail_author_mail_id_foreign` (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_containers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `creator_organisation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `property_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_containers_code_unique` (`code`),
  KEY `mail_containers_created_by_foreign` (`created_by`),
  KEY `mail_containers_creator_organisation_id_foreign` (`creator_organisation_id`),
  KEY `mail_containers_property_id_foreign` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mail_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_changed` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` json DEFAULT NULL,
  `new_value` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `location_data` json DEFAULT NULL,
  `processing_time` int DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_histories_mail_id_created_at_index` (`mail_id`,`created_at`),
  KEY `mail_histories_user_id_action_index` (`user_id`,`action`),
  KEY `mail_histories_action_index` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_priorities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_priorities_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_related` (
  `mail_id` bigint unsigned NOT NULL,
  `mail_related_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`mail_id`,`mail_related_id`),
  KEY `mail_related_mail_related_id_foreign` (`mail_related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_typologies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `activity_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_typologies_code_unique` (`code`),
  UNIQUE KEY `mail_typologies_name_unique` (`name`),
  KEY `mail_typologies_activity_id_foreign` (`activity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_workflows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mail_id` bigint unsigned NOT NULL,
  `current_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_assignee_id` bigint unsigned DEFAULT NULL,
  `workflow_data` json DEFAULT NULL,
  `approval_required` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_to` bigint unsigned DEFAULT NULL,
  `deadline` timestamp NULL DEFAULT NULL,
  `auto_escalate_hours` int DEFAULT NULL,
  `priority_escalation_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_workflows_mail_id_foreign` (`mail_id`),
  KEY `mail_workflows_approved_by_foreign` (`approved_by`),
  KEY `mail_workflows_rejected_by_foreign` (`rejected_by`),
  KEY `mail_workflows_escalated_to_foreign` (`escalated_to`),
  KEY `mail_workflows_current_assignee_id_current_status_index` (`current_assignee_id`,`current_status`),
  KEY `mail_workflows_deadline_index` (`deadline`),
  KEY `mail_workflows_approval_required_approved_at_index` (`approval_required`,`approved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `document_type` enum('original','duplicate','copy') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'original',
  `status` enum('draft','pending_review','in_progress','pending_approval','approved','transmitted','completed','rejected','cancelled','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `priority_id` bigint unsigned DEFAULT NULL,
  `typology_id` bigint unsigned NOT NULL,
  `action_id` bigint unsigned DEFAULT NULL,
  `sender_user_id` bigint unsigned DEFAULT NULL,
  `sender_organisation_id` bigint unsigned DEFAULT NULL,
  `recipient_user_id` bigint unsigned DEFAULT NULL,
  `recipient_organisation_id` bigint unsigned DEFAULT NULL,
  `mail_type` enum('internal','incoming','outgoing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `deadline` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `estimated_processing_time` int DEFAULT NULL COMMENT 'Temps estimé en minutes',
  `assigned_organisation_id` bigint unsigned DEFAULT NULL,
  `external_sender_id` bigint unsigned DEFAULT NULL,
  `external_sender_organization_id` bigint unsigned DEFAULT NULL,
  `external_recipient_id` bigint unsigned DEFAULT NULL,
  `external_recipient_organization_id` bigint unsigned DEFAULT NULL,
  `sender_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type de l''expéditeur: user, organisation, external',
  `recipient_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type du destinataire: user, organisation, external',
  `sent_at` datetime DEFAULT NULL COMMENT 'Date d''envoi effectif',
  `received_at` datetime DEFAULT NULL COMMENT 'Date de réception confirmée',
  `delivery_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Méthode d''envoi/réception: email, courrier, en main propre, etc.',
  `tracking_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numéro de suivi pour les courriers postaux',
  `receipt_confirmed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Confirmation de réception',
  PRIMARY KEY (`id`),
  KEY `mails_priority_id_foreign` (`priority_id`),
  KEY `mails_typology_id_foreign` (`typology_id`),
  KEY `mails_action_id_foreign` (`action_id`),
  KEY `mails_sender_user_id_foreign` (`sender_user_id`),
  KEY `mails_recipient_user_id_foreign` (`recipient_user_id`),
  KEY `mails_assigned_to_index` (`assigned_to`),
  KEY `mails_deadline_index` (`deadline`),
  KEY `mails_status_assigned_to_index` (`status`,`assigned_to`),
  KEY `mails_assigned_organisation_id_index` (`assigned_organisation_id`),
  KEY `mails_external_sender_id_foreign` (`external_sender_id`),
  KEY `mails_external_recipient_id_foreign` (`external_recipient_id`),
  KEY `idx_mail_sender_org` (`sender_organisation_id`),
  KEY `idx_mail_recipient_org` (`recipient_organisation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapping_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_concept_id` bigint unsigned NOT NULL,
  `target_concept_id` bigint unsigned DEFAULT NULL,
  `target_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_label` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapping_type` enum('exactMatch','closeMatch','broadMatch','narrowMatch','relatedMatch') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_scheme` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapping_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mapping_relations_target_concept_id_foreign` (`target_concept_id`),
  KEY `mapping_relations_source_concept_id_mapping_type_index` (`source_concept_id`,`mapping_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metadata_definitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique identifier code',
  `description` text COLLATE utf8mb4_unicode_ci,
  `data_type` enum('text','textarea','number','date','datetime','boolean','select','multi_select','reference_list','email','url') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `validation_rules` json DEFAULT NULL COMMENT 'JSON validation rules',
  `options` json DEFAULT NULL COMMENT 'Options for select/multi-select fields',
  `reference_list_id` bigint unsigned DEFAULT NULL,
  `searchable` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metadata_definitions_code_unique` (`code`),
  KEY `metadata_definitions_reference_list_id_foreign` (`reference_list_id`),
  KEY `metadata_definitions_created_by_foreign` (`created_by`),
  KEY `metadata_definitions_updated_by_foreign` (`updated_by`),
  KEY `metadata_definitions_active_index` (`active`),
  KEY `metadata_definitions_searchable_index` (`searchable`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `non_descriptors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint unsigned NOT NULL,
  `non_descriptor_label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `non_descriptors_term_id_language_index` (`term_id`,`language`),
  KEY `non_descriptors_non_descriptor_label_index` (`non_descriptor_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `organisation_id` bigint unsigned DEFAULT NULL,
  `module` enum('BulletinBoards','Mails','Records','Communications','Transfers','Deposits','Tools','Dollies','Workflows','Contacts','AI','Public','Settings') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `action` enum('CREATE','READ','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_entity_id` bigint unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `notifications_organisation_id_is_read_index` (`organisation_id`,`is_read`),
  KEY `notifications_module_created_at_index` (`module`,`created_at`),
  KEY `notifications_action_created_at_index` (`action`,`created_at`),
  KEY `notifications_related_entity_type_related_entity_id_index` (`related_entity_type`,`related_entity_id`),
  KEY `notifications_user_id_organisation_id_is_read_index` (`user_id`,`organisation_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opac_configurations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint unsigned NOT NULL,
  `config_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_value` json NOT NULL,
  `config_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mixed',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opac_configurations_organisation_id_config_key_unique` (`organisation_id`,`config_key`),
  KEY `opac_configurations_organisation_id_config_key_index` (`organisation_id`,`config_key`),
  KEY `opac_configurations_organisation_id_is_active_index` (`organisation_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisation_activity` (
  `organisation_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`organisation_id`,`activity_id`),
  KEY `organisation_activity_activity_id_foreign` (`activity_id`),
  KEY `organisation_activity_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisation_contact` (
  `organisation_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  UNIQUE KEY `org_contact_unique` (`organisation_id`,`contact_id`),
  KEY `organisation_contact_organisation_id_index` (`organisation_id`),
  KEY `organisation_contact_contact_id_index` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisation_room` (
  `room_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_id`,`organisation_id`),
  KEY `organisation_room_organisation_id_foreign` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisations_parent_id_foreign` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `guard_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_attachments_post_id_foreign` (`post_id`),
  KEY `post_attachments_attachment_id_foreign` (`attachment_id`),
  KEY `post_attachments_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('draft','published','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `bulletin_board_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_name_unique` (`name`),
  KEY `posts_bulletin_board_id_foreign` (`bulletin_board_id`),
  KEY `posts_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prompt_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `organisation_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prompt_categories_organisation_id_foreign` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prompt_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prompt_id` bigint unsigned DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organisation_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `entity` enum('record','mail','communication','slip_record') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_ids` json DEFAULT NULL,
  `status` enum('started','succeeded','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'started',
  `tokens_input` int unsigned DEFAULT NULL,
  `tokens_output` int unsigned DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `latency_ms` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prompt_transactions_user_id_foreign` (`user_id`),
  KEY `pt_org_entity_started_idx` (`organisation_id`,`entity`,`started_at`),
  KEY `pt_prompt_status_started_idx` (`prompt_id`,`status`,`started_at`),
  KEY `prompt_transactions_started_at_index` (`started_at`),
  KEY `prompt_transactions_finished_at_index` (`finished_at`),
  KEY `prompt_transactions_model_index` (`model`),
  KEY `prompt_transactions_model_provider_index` (`model_provider`),
  KEY `prompt_transactions_entity_index` (`entity`),
  KEY `prompt_transactions_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prompts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `organisation_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `prompt_category_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prompts_organisation_id_foreign` (`organisation_id`),
  KEY `prompts_user_id_foreign` (`user_id`),
  KEY `prompts_is_system_index` (`is_system`),
  KEY `prompts_prompt_category_id_foreign` (`prompt_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_chat_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_chat_messages_chat_id_index` (`chat_id`),
  KEY `public_chat_messages_user_id_index` (`user_id`),
  KEY `public_chat_messages_is_read_index` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_chat_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_chat_participants_chat_id_user_id_unique` (`chat_id`,`user_id`),
  KEY `public_chat_participants_chat_id_index` (`chat_id`),
  KEY `public_chat_participants_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_chats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_chats_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_document_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `request_type` enum('digital','physical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_document_requests_user_id_index` (`user_id`),
  KEY `public_document_requests_record_id_index` (`record_id`),
  KEY `public_document_requests_status_index` (`status`),
  KEY `public_document_requests_processed_at_index` (`processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_event_registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('registered','confirmed','cancelled','attended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_event_registrations_event_id_user_id_unique` (`event_id`,`user_id`),
  KEY `public_event_registrations_event_id_index` (`event_id`),
  KEY `public_event_registrations_user_id_index` (`user_id`),
  KEY `public_event_registrations_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `online_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_events_start_date_index` (`start_date`),
  KEY `public_events_end_date_index` (`end_date`),
  KEY `public_events_is_online_index` (`is_online`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_feedbacks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('bug','feature','improvement','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('new','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `contact_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint unsigned DEFAULT NULL,
  `related_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_feedbacks_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_news` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` bigint unsigned NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_news_slug_unique` (`slug`),
  KEY `public_news_slug_index` (`slug`),
  KEY `public_news_user_id_index` (`author_id`),
  KEY `public_news_is_published_index` (`is_published`),
  KEY `public_news_published_at_index` (`published_at`),
  KEY `public_news_status_index` (`status`),
  KEY `public_news_featured_index` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint unsigned DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `featured_image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `parent_id` int DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_pages_slug_unique` (`slug`),
  KEY `public_pages_slug_index` (`slug`),
  KEY `public_pages_is_published_index` (`is_published`),
  KEY `public_pages_parent_id_index` (`parent_id`),
  KEY `public_pages_author_id_foreign` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_record_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `public_record_id` bigint unsigned NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_record_attachments_public_record_id_index` (`public_record_id`),
  KEY `public_record_attachments_uploaded_by_index` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `published_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `published_by` bigint unsigned NOT NULL,
  `publication_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_records_record_id_index` (`record_id`),
  KEY `public_records_published_by_index` (`published_by`),
  KEY `public_records_published_at_index` (`published_at`),
  KEY `public_records_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_response_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `public_response_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `download_count` int NOT NULL DEFAULT '0',
  `expires_at` datetime DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_response_attachments_public_response_id_index` (`public_response_id`),
  KEY `public_response_attachments_attachment_id_index` (`attachment_id`),
  KEY `public_response_attachments_expires_at_index` (`expires_at`),
  KEY `public_response_attachments_uploaded_by_index` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_request_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','sent','updated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_responses_document_request_id_index` (`document_request_id`),
  KEY `public_responses_responded_by_index` (`user_id`),
  KEY `public_responses_status_index` (`status`),
  KEY `public_responses_sent_at_index` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_search_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `search_term` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filters` json DEFAULT NULL,
  `results_count` int NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_search_logs_user_id_index` (`user_id`),
  KEY `public_search_logs_search_term_index` (`search_term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('page','email','notification') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'page',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `parameters` json NOT NULL,
  `values` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_templates_is_active_index` (`is_active`),
  KEY `public_templates_author_id_foreign` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone1` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone2` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `preferences` json DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_users_email_unique` (`email`),
  KEY `public_users_email_index` (`email`),
  KEY `public_users_is_approved_index` (`is_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_author` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_author_record_id_author_id_unique` (`record_id`,`author_id`),
  KEY `record_author_author_id_foreign` (`author_id`),
  CONSTRAINT `record_author_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_author_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_author_book` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint unsigned NOT NULL,
  `book_id` bigint unsigned NOT NULL,
  `responsibility_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UNIMARC code (ex: 070=author, 730=translator)',
  `function` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Function (Author, Translator, Preface writer, etc.)',
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'author' COMMENT 'Role (legacy: author, editor, translator, illustrator, contributor)',
  `display_order` int NOT NULL DEFAULT '1' COMMENT 'Display order',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes on contribution',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_author_book_author_id_index` (`author_id`),
  KEY `record_author_book_book_id_index` (`book_id`),
  KEY `record_author_book_responsibility_type_index` (`responsibility_type`),
  KEY `record_author_book_role_index` (`role`),
  KEY `record_author_book_book_id_display_order_index` (`book_id`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_authors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_type` enum('person','organization','conference') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'person' COMMENT 'Author type',
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Last name or organization name',
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'First name',
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full name (indexed)',
  `pseudonym` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pseudonym or pen name',
  `rejected_form` text COLLATE utf8mb4_unicode_ci COMMENT 'Other name forms',
  `dates` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dates (ex: 1909-1943)',
  `birth_year` int DEFAULT NULL COMMENT 'Birth year',
  `death_year` int DEFAULT NULL COMMENT 'Death year',
  `birth_place` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Birth place',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Country',
  `nationality` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nationality (ISO 3166-1 alpha-2)',
  `language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Language',
  `biographical_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Biographical note',
  `biography` text COLLATE utf8mb4_unicode_ci COMMENT 'Short biography (legacy)',
  `specializations` json DEFAULT NULL COMMENT 'Specializations/Fields (JSON array)',
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Personal website',
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Author photo',
  `ppn_authority` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'PPN authority SUDOC',
  `isni` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISNI (International Standard Name Identifier)',
  `orcid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ORCID iD',
  `viaf` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VIAF (Virtual International Authority File)',
  `total_books` int NOT NULL DEFAULT '0' COMMENT 'Nombre total de livres',
  `total_works` int NOT NULL DEFAULT '0' COMMENT 'Nombre total d''œuvres (tous rôles)',
  `metadata` json DEFAULT NULL COMMENT 'Métadonnées additionnelles',
  `status` enum('active','deceased','unknown') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Statut',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_authors_isni_unique` (`isni`),
  UNIQUE KEY `record_authors_orcid_unique` (`orcid`),
  KEY `record_authors_last_name_index` (`last_name`),
  KEY `record_authors_full_name_index` (`full_name`),
  KEY `record_authors_author_type_index` (`author_type`),
  KEY `record_authors_ppn_authority_index` (`ppn_authority`),
  KEY `record_authors_nationality_index` (`nationality`),
  KEY `record_authors_status_index` (`status`),
  FULLTEXT KEY `authors_fulltext_idx` (`full_name`,`pseudonym`,`biography`,`biographical_note`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_book_classification` (
  `book_id` bigint unsigned NOT NULL,
  `classification_id` bigint unsigned NOT NULL,
  `display_order` int NOT NULL DEFAULT '1' COMMENT 'Display order',
  PRIMARY KEY (`book_id`,`classification_id`),
  KEY `record_book_classification_book_id_index` (`book_id`),
  KEY `record_book_classification_classification_id_index` (`classification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_book_subject` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `relevance` int NOT NULL DEFAULT '100' COMMENT 'Pertinence (0-100)',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sujet principal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_subject_unique` (`book_id`,`subject_id`),
  KEY `record_book_subject_book_id_index` (`book_id`),
  KEY `record_book_subject_subject_id_index` (`subject_id`),
  KEY `record_book_subject_is_primary_index` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_confidentialities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_confidentialities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_document_keyword` (
  `document_id` bigint unsigned NOT NULL,
  `keyword_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`document_id`,`keyword_id`),
  KEY `record_digital_document_keyword_keyword_id_foreign` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_document_metadata_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_type_id` bigint unsigned NOT NULL,
  `metadata_definition_id` bigint unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `validation_rules` json DEFAULT NULL COMMENT 'Additional validation rules specific to this profile',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_meta_prof_unique` (`document_type_id`,`metadata_definition_id`),
  KEY `record_digital_document_metadata_profiles_mandatory_index` (`mandatory`),
  KEY `record_digital_document_metadata_profiles_sort_order_index` (`sort_order`),
  KEY `doc_meta_prof_def_fk` (`metadata_definition_id`),
  KEY `doc_meta_prof_creator_fk` (`created_by`),
  KEY `doc_meta_prof_updater_fk` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_document_thesaurus_concept` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `weight` decimal(3,2) NOT NULL DEFAULT '0.50' COMMENT 'Relevance weight 0.00-1.00',
  `context` text COLLATE utf8mb4_unicode_ci COMMENT 'Context where concept applies',
  `extraction_note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Notes on concept extraction',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rddc_document_concept_unique` (`document_id`,`concept_id`),
  KEY `record_digital_document_thesaurus_concept_concept_id_foreign` (`concept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_document_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique du type (ex: INVOICE, CONTRACT, REPORT)',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du type de document',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description détaillée du type',
  `category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catégorie (administratif, financier, RH, etc.)',
  `tags` json DEFAULT NULL COMMENT 'Tags pour recherche et organisation',
  `allowed_mime_types` json DEFAULT NULL COMMENT 'Types MIME autorisés (application/pdf, image/*, etc.)',
  `allowed_extensions` json DEFAULT NULL COMMENT 'Extensions autorisées (pdf, docx, xlsx, etc.)',
  `max_file_size` bigint DEFAULT NULL COMMENT 'Taille max en octets',
  `min_file_size` bigint DEFAULT NULL COMMENT 'Taille min en octets',
  `metadata_template_id` bigint unsigned DEFAULT NULL,
  `required_metadata_fields` json DEFAULT NULL COMMENT 'Champs de métadonnées obligatoires',
  `optional_metadata_fields` json DEFAULT NULL COMMENT 'Champs de métadonnées optionnels',
  `naming_pattern` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pattern de nommage (ex: {TYPE}-{DATE}-{NNN})',
  `validation_rules` json DEFAULT NULL COMMENT 'Règles de validation personnalisées',
  `requires_versioning` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Versioning obligatoire',
  `max_versions` int DEFAULT NULL COMMENT 'Nombre max de versions conservées',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nécessite approbation',
  `requires_signature` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nécessite signature électronique',
  `default_access_level` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public' COMMENT 'Niveau d''accès par défaut',
  `requires_encryption` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Chiffrement obligatoire',
  `watermark_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Filigrane activé',
  `retention_years` int DEFAULT NULL COMMENT 'Durée de conservation en années',
  `retention_policy` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Politique de conservation',
  `auto_archive` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Archivage automatique',
  `archive_after_days` int DEFAULT NULL COMMENT 'Archiver après X jours',
  `ocr_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'OCR automatique activé',
  `thumbnail_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Génération de miniature',
  `preview_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Prévisualisation activée',
  `documents_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de documents de ce type',
  `total_size` bigint NOT NULL DEFAULT '0' COMMENT 'Taille totale en octets',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière utilisation',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icône pour l''interface',
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Couleur pour l''interface',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_digital_document_types_code_unique` (`code`),
  KEY `record_digital_document_types_created_by_foreign` (`created_by`),
  KEY `record_digital_document_types_updated_by_foreign` (`updated_by`),
  KEY `record_digital_document_types_code_index` (`code`),
  KEY `record_digital_document_types_category_index` (`category`),
  KEY `record_digital_document_types_is_active_index` (`is_active`),
  KEY `record_digital_document_types_display_order_index` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique généré selon le pattern du type',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du document',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description détaillée',
  `type_id` bigint unsigned NOT NULL,
  `folder_id` bigint unsigned DEFAULT NULL,
  `attachment_id` bigint unsigned DEFAULT NULL,
  `version_number` int NOT NULL DEFAULT '1' COMMENT 'Numéro de version',
  `is_current_version` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Version courante',
  `parent_version_id` bigint unsigned DEFAULT NULL,
  `version_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes de version',
  `checked_out_by` bigint unsigned DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL COMMENT 'Date de réservation',
  `signature_status` enum('unsigned','pending','signed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unsigned' COMMENT 'Statut de signature',
  `signed_by` bigint unsigned DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL COMMENT 'Date de signature',
  `signature_data` text COLLATE utf8mb4_unicode_ci COMMENT 'Données de signature (hash, certificat)',
  `metadata` json DEFAULT NULL COMMENT 'Métadonnées personnalisées selon le type',
  `access_level` enum('public','internal','confidential','secret') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal' COMMENT 'Niveau d''accès',
  `status` enum('draft','active','archived','obsolete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Statut du document',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nécessite approbation',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Date d''approbation',
  `approval_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes d''approbation',
  `retention_until` date DEFAULT NULL COMMENT 'Date de fin de rétention',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Document archivé',
  `archived_at` timestamp NULL DEFAULT NULL COMMENT 'Date d''archivage',
  `creator_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `download_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de téléchargements',
  `last_viewed_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière consultation',
  `last_viewed_by` bigint unsigned DEFAULT NULL,
  `document_date` date DEFAULT NULL COMMENT 'Date du document',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `transferred_at` timestamp NULL DEFAULT NULL COMMENT 'When this document was transferred to physical',
  `transferred_to_record_id` bigint unsigned DEFAULT NULL COMMENT 'ID of the physical record it was transferred to',
  `transfer_metadata` json DEFAULT NULL COMMENT 'Metadata about the transfer operation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_digital_documents_code_unique` (`code`),
  KEY `record_digital_documents_parent_version_id_foreign` (`parent_version_id`),
  KEY `record_digital_documents_checked_out_by_foreign` (`checked_out_by`),
  KEY `record_digital_documents_signed_by_foreign` (`signed_by`),
  KEY `record_digital_documents_approved_by_foreign` (`approved_by`),
  KEY `record_digital_documents_assigned_to_foreign` (`assigned_to`),
  KEY `record_digital_documents_last_viewed_by_foreign` (`last_viewed_by`),
  KEY `record_digital_documents_type_id_index` (`type_id`),
  KEY `record_digital_documents_folder_id_index` (`folder_id`),
  KEY `record_digital_documents_attachment_id_index` (`attachment_id`),
  KEY `record_digital_documents_status_index` (`status`),
  KEY `record_digital_documents_signature_status_index` (`signature_status`),
  KEY `record_digital_documents_creator_id_index` (`creator_id`),
  KEY `record_digital_documents_organisation_id_index` (`organisation_id`),
  KEY `record_digital_documents_type_id_status_index` (`type_id`,`status`),
  KEY `record_digital_documents_folder_id_is_current_version_index` (`folder_id`,`is_current_version`),
  KEY `record_digital_documents_organisation_id_status_index` (`organisation_id`,`status`),
  KEY `record_digital_documents_is_current_version_version_number_index` (`is_current_version`,`version_number`),
  KEY `record_digital_documents_transferred_to_record_id_foreign` (`transferred_to_record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_folder_keyword` (
  `folder_id` bigint unsigned NOT NULL,
  `keyword_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`folder_id`,`keyword_id`),
  KEY `record_digital_folder_keyword_keyword_id_foreign` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_folder_metadata_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folder_type_id` bigint unsigned NOT NULL,
  `metadata_definition_id` bigint unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `validation_rules` json DEFAULT NULL COMMENT 'Additional validation rules specific to this profile',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_meta_prof_unique` (`folder_type_id`,`metadata_definition_id`),
  KEY `record_digital_folder_metadata_profiles_mandatory_index` (`mandatory`),
  KEY `record_digital_folder_metadata_profiles_sort_order_index` (`sort_order`),
  KEY `folder_meta_prof_def_fk` (`metadata_definition_id`),
  KEY `folder_meta_prof_creator_fk` (`created_by`),
  KEY `folder_meta_prof_updater_fk` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_folder_thesaurus_concept` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `weight` decimal(3,2) NOT NULL DEFAULT '0.50' COMMENT 'Relevance weight 0.00-1.00',
  `context` text COLLATE utf8mb4_unicode_ci COMMENT 'Context where concept applies',
  `extraction_note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Notes on concept extraction',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rdfc_folder_concept_unique` (`folder_id`,`concept_id`),
  KEY `record_digital_folder_thesaurus_concept_concept_id_foreign` (`concept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_folder_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique du type (ex: CONTRACTS, HR, INVOICES)',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du type de dossier',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description détaillée du type',
  `code_pattern` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pattern de génération de code (ex: CTR-{YYYY}-{NNN})',
  `max_depth` int NOT NULL DEFAULT '5' COMMENT 'Profondeur maximale de hiérarchie autorisée',
  `allows_documents` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Autorise les documents dans ce type de dossier',
  `allowed_document_types` json DEFAULT NULL COMMENT 'Types de documents autorisés (IDs)',
  `metadata_template_id` bigint unsigned DEFAULT NULL,
  `required_metadata_fields` json DEFAULT NULL COMMENT 'Champs de métadonnées obligatoires',
  `naming_convention` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Convention de nommage des dossiers',
  `validation_rules` json DEFAULT NULL COMMENT 'Règles de validation personnalisées',
  `default_access_level` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public' COMMENT 'Niveau d''accès par défaut',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nécessite approbation',
  `version_control` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Contrôle de version activé',
  `retention_years` int DEFAULT NULL COMMENT 'Durée de conservation en années',
  `retention_policy` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Politique de conservation',
  `folders_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de dossiers de ce type',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière utilisation',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icône pour l''interface',
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Couleur pour l''interface',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_digital_folder_types_code_unique` (`code`),
  KEY `record_digital_folder_types_created_by_foreign` (`created_by`),
  KEY `record_digital_folder_types_updated_by_foreign` (`updated_by`),
  KEY `record_digital_folder_types_code_index` (`code`),
  KEY `record_digital_folder_types_is_active_index` (`is_active`),
  KEY `record_digital_folder_types_display_order_index` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_digital_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique généré selon le pattern du type',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du dossier',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description détaillée',
  `type_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL COMMENT 'Métadonnées personnalisées selon le type',
  `access_level` enum('public','internal','confidential','secret') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal' COMMENT 'Niveau d''accès au dossier',
  `status` enum('active','archived','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Statut du dossier',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nécessite approbation',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Date d''approbation',
  `approval_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes d''approbation',
  `creator_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `documents_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de documents',
  `subfolders_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de sous-dossiers',
  `total_size` bigint NOT NULL DEFAULT '0' COMMENT 'Taille totale en octets',
  `start_date` date DEFAULT NULL COMMENT 'Date de début',
  `end_date` date DEFAULT NULL COMMENT 'Date de fin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `transferred_at` timestamp NULL DEFAULT NULL COMMENT 'When this folder was transferred to physical',
  `transferred_to_record_id` bigint unsigned DEFAULT NULL COMMENT 'ID of the physical record it was transferred to',
  `transfer_metadata` json DEFAULT NULL COMMENT 'Metadata about the transfer operation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_digital_folders_code_unique` (`code`),
  KEY `record_digital_folders_approved_by_foreign` (`approved_by`),
  KEY `record_digital_folders_assigned_to_foreign` (`assigned_to`),
  KEY `record_digital_folders_type_id_index` (`type_id`),
  KEY `record_digital_folders_parent_id_index` (`parent_id`),
  KEY `record_digital_folders_status_index` (`status`),
  KEY `record_digital_folders_creator_id_index` (`creator_id`),
  KEY `record_digital_folders_organisation_id_index` (`organisation_id`),
  KEY `record_digital_folders_type_id_status_index` (`type_id`,`status`),
  KEY `record_digital_folders_organisation_id_status_index` (`organisation_id`,`status`),
  KEY `record_digital_folders_transferred_to_record_id_foreign` (`transferred_to_record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_keyword` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `keyword_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_keyword_record_id_keyword_id_unique` (`record_id`,`keyword_id`),
  KEY `record_keyword_keyword_id_foreign` (`keyword_id`),
  CONSTRAINT `record_keyword_keyword_id_foreign` FOREIGN KEY (`keyword_id`) REFERENCES `keywords` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_keyword_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 639-1, 639-2 or 639-3 code',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Language name in French',
  `name_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Language name in English',
  `native_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Language name in its native script',
  `script` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Writing system (Latin, Arabic, Cyrillic, etc.)',
  `direction` enum('ltr','rtl') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ltr' COMMENT 'Text direction',
  `iso_639_1` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO 639-1 two-letter code',
  `iso_639_2` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO 639-2 three-letter code',
  `iso_639_3` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO 639-3 three-letter code',
  `total_books` int NOT NULL DEFAULT '0' COMMENT 'Number of books in this language',
  `status` enum('active','deprecated','historical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_languages_code_unique` (`code`),
  KEY `record_languages_code_index` (`code`),
  KEY `record_languages_iso_639_1_index` (`iso_639_1`),
  KEY `record_languages_iso_639_2_index` (`iso_639_2`),
  KEY `record_languages_script_index` (`script`),
  KEY `record_languages_direction_index` (`direction`),
  KEY `record_languages_status_index` (`status`),
  FULLTEXT KEY `record_languages_name_name_en_native_name_fulltext` (`name`,`name_en`,`native_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `child_id` bigint unsigned DEFAULT NULL,
  `has_child` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_levels_child_id_foreign` (`child_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_mediums` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `support_id` bigint unsigned NOT NULL,
  `container_id` bigint unsigned DEFAULT NULL,
  `attachment_id` bigint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_principal` tinyint(1) NOT NULL DEFAULT '1',
  `copy_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_out_by` bigint unsigned DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `signature_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unsigned',
  `signed_by` bigint unsigned DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `signature_data` text COLLATE utf8mb4_unicode_ci,
  `legacy_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_mediums_support_id_foreign` (`support_id`),
  KEY `record_mediums_container_id_foreign` (`container_id`),
  KEY `record_mediums_attachment_id_foreign` (`attachment_id`),
  KEY `record_mediums_checked_out_by_foreign` (`checked_out_by`),
  KEY `record_mediums_signed_by_foreign` (`signed_by`),
  KEY `record_mediums_record_id_support_id_index` (`record_id`,`support_id`),
  CONSTRAINT `record_mediums_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_mediums_checked_out_by_foreign` FOREIGN KEY (`checked_out_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_mediums_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_mediums_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_mediums_signed_by_foreign` FOREIGN KEY (`signed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_mediums_support_id_foreign` FOREIGN KEY (`support_id`) REFERENCES `record_supports` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_periodic_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` bigint unsigned NOT NULL,
  `periodic_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Titre de l''article',
  `abstract` text COLLATE utf8mb4_unicode_ci COMMENT 'Résumé',
  `authors` json NOT NULL COMMENT 'Auteurs [{"name": "...", "affiliation": "..."}]',
  `page_start` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Page de début',
  `page_end` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Page de fin',
  `section` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Section/Rubrique',
  `doi` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'DOI de l''article',
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL texte intégral',
  `keywords` json DEFAULT NULL COMMENT 'Mots-clés',
  `language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr' COMMENT 'Langue de l''article',
  `article_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'recherche, revue, éditorial, etc.',
  `metadata` json DEFAULT NULL,
  `is_peer_reviewed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Article à comité de lecture',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_periodic_articles_doi_unique` (`doi`),
  KEY `record_periodic_articles_issue_id_foreign` (`issue_id`),
  KEY `record_periodic_articles_doi_index` (`doi`),
  KEY `record_periodic_articles_periodic_id_issue_id_index` (`periodic_id`,`issue_id`),
  FULLTEXT KEY `record_periodic_articles_title_abstract_fulltext` (`title`,`abstract`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_periodic_issues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periodic_id` bigint unsigned NOT NULL,
  `issue_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numéro',
  `volume` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Volume',
  `year` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Année',
  `publication_date` date DEFAULT NULL COMMENT 'Date de publication',
  `season` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Saison (printemps, été, automne, hiver)',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Titre du numéro (thématique)',
  `summary` text COLLATE utf8mb4_unicode_ci COMMENT 'Sommaire/Résumé',
  `page_count` int DEFAULT NULL COMMENT 'Nombre de pages',
  `doi` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'DOI du numéro',
  `cover_image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chemin vers image de couverture',
  `status` enum('expected','received','catalogued','archived','missing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expected',
  `received_date` date DEFAULT NULL COMMENT 'Date de réception',
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Localisation physique',
  `call_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cote',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_issue` (`periodic_id`,`volume`(50),`issue_number`(50),`year`(10)),
  KEY `record_periodic_issues_issue_number_index` (`issue_number`),
  KEY `record_periodic_issues_publication_date_index` (`publication_date`),
  KEY `record_periodic_issues_status_index` (`status`),
  KEY `record_periodic_issues_periodic_id_index` (`periodic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_periodic_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periodic_id` bigint unsigned NOT NULL,
  `subscription_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numéro d''abonnement',
  `start_date` date NOT NULL COMMENT 'Date de début',
  `end_date` date NOT NULL COMMENT 'Date de fin',
  `auto_renewal` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Renouvellement automatique',
  `cost` decimal(10,2) NOT NULL COMMENT 'Coût',
  `currency` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR' COMMENT 'Devise',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Méthode de paiement',
  `invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numéro de facture',
  `supplier` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fournisseur/Agence',
  `supplier_contact` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contact fournisseur',
  `subscription_type` enum('print','online','print_online') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'print',
  `access_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes sur l''accès',
  `status` enum('active','expired','cancelled','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes',
  `responsible_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_periodic_subscriptions_responsible_user_id_foreign` (`responsible_user_id`),
  KEY `record_periodic_subscriptions_periodic_id_index` (`periodic_id`),
  KEY `record_periodic_subscriptions_status_index` (`status`),
  KEY `record_periodic_subscriptions_end_date_index` (`end_date`),
  KEY `record_periodic_subscriptions_periodic_id_status_index` (`periodic_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_periodics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique PER-YYYY-NNNN',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Titre de la publication',
  `subtitle` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sous-titre',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description',
  `issn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISSN (International Standard Serial Number)',
  `eissn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'eISSN pour version électronique',
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'revue, magazine, journal, newsletter',
  `subject_area` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Domaine thématique',
  `keywords` json DEFAULT NULL COMMENT 'Mots-clés',
  `publisher` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Éditeur',
  `publisher_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lieu d''édition',
  `language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr' COMMENT 'Langue principale',
  `frequency` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mensuel, bimensuel, trimestriel, annuel',
  `first_year` int DEFAULT NULL COMMENT 'Année de première publication',
  `last_year` int DEFAULT NULL COMMENT 'Année de dernière publication (si cessé)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Toujours publié',
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Site web',
  `contact_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email de contact',
  `metadata` json DEFAULT NULL COMMENT 'Métadonnées personnalisées',
  `access_level` enum('public','internal','confidential') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `status` enum('active','ceased','suspended','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `creator_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_periodics_code_unique` (`code`),
  UNIQUE KEY `record_periodics_issn_unique` (`issn`),
  KEY `record_periodics_creator_id_foreign` (`creator_id`),
  KEY `record_periodics_code_index` (`code`),
  KEY `record_periodics_issn_index` (`issn`),
  KEY `record_periodics_type_index` (`type`),
  KEY `record_periodics_status_index` (`status`),
  KEY `record_periodics_organisation_id_status_index` (`organisation_id`,`status`),
  FULLTEXT KEY `record_periodics_title_subtitle_description_publisher_fulltext` (`title`,`subtitle`,`description`,`publisher`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_attachment` (
  `record_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_id`,`attachment_id`),
  KEY `record_attachment_attachment_id_foreign` (`attachment_id`),
  CONSTRAINT `record_physical_attachment_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_author` (
  `author_id` bigint unsigned NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`author_id`,`record_id`),
  KEY `record_author_record_id_foreign` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_container` (
  `record_physical_id` bigint unsigned NOT NULL,
  `container_id` int unsigned NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`record_physical_id`,`container_id`),
  KEY `record_container_container_id_foreign` (`container_id`),
  KEY `record_container_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_keyword` (
  `record_id` bigint unsigned NOT NULL,
  `keyword_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`record_id`,`keyword_id`),
  KEY `record_keyword_keyword_id_foreign` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_physical_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `record_links_record_id_foreign` (`record_physical_id`),
  KEY `record_links_parent_id_foreign` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physical_thesaurus_concept` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_physical_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `weight` decimal(3,2) NOT NULL DEFAULT '1.00' COMMENT 'Poids de la relation (0.0 à 1.0)',
  `context` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contexte de la relation (manuel, automatique, etc.)',
  `extraction_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Note sur l''extraction du terme',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_record_concept` (`record_physical_id`,`concept_id`),
  KEY `record_thesaurus_concept_record_id_weight_index` (`record_physical_id`,`weight`),
  KEY `record_thesaurus_concept_concept_id_weight_index` (`concept_id`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_physicals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_format` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_start` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_end` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_exact` date DEFAULT NULL,
  `level_id` bigint unsigned NOT NULL,
  `width` float DEFAULT NULL,
  `width_description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biographical_history` text COLLATE utf8mb4_unicode_ci,
  `archival_history` text COLLATE utf8mb4_unicode_ci,
  `acquisition_source` text COLLATE utf8mb4_unicode_ci,
  `content` text COLLATE utf8mb4_unicode_ci,
  `appraisal` text COLLATE utf8mb4_unicode_ci,
  `accrual` text COLLATE utf8mb4_unicode_ci,
  `arrangement` text COLLATE utf8mb4_unicode_ci,
  `access_conditions` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reproduction_conditions` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language_material` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `characteristic` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finding_aids` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_original` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_copy` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_unit` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_note` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `archivist_note` text COLLATE utf8mb4_unicode_ci,
  `rule_convention` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` bigint unsigned NOT NULL,
  `support_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `linked_digital_metadata` json DEFAULT NULL COMMENT 'Metadata about linked digital content',
  PRIMARY KEY (`id`),
  KEY `records_status_id_foreign` (`status_id`),
  KEY `records_support_id_foreign` (`support_id`),
  KEY `records_activity_id_foreign` (`activity_id`),
  KEY `records_parent_id_foreign` (`parent_id`),
  KEY `records_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_reactivations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `previous_status_id` bigint unsigned DEFAULT NULL,
  `previous_transfer_date` date DEFAULT NULL,
  `new_transfer_date` date DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) DEFAULT '0',
  `requested_by` bigint unsigned NOT NULL,
  `requested_date` datetime NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_reactivations_organisation_id_foreign` (`organisation_id`),
  KEY `record_reactivations_previous_status_id_foreign` (`previous_status_id`),
  KEY `record_reactivations_requested_by_foreign` (`requested_by`),
  KEY `record_reactivations_approved_by_foreign` (`approved_by`),
  KEY `record_reactivations_record_id_foreign` (`record_id`),
  CONSTRAINT `record_reactivations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_reactivations_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_reactivations_previous_status_id_foreign` FOREIGN KEY (`previous_status_id`) REFERENCES `record_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_reactivations_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_reactivations_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_id` bigint unsigned NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_relations_source_id_target_id_type_unique` (`source_id`,`target_id`,`type`),
  KEY `record_relations_target_id_type_index` (`target_id`,`type`),
  CONSTRAINT `record_relations_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_relations_target_id_foreign` FOREIGN KEY (`target_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_statuses_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du sujet',
  `name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nom en anglais',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description du sujet',
  `dewey_class` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Classe Dewey',
  `lcc_class` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Classe LCC (Library of Congress)',
  `rameau` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Identifiant RAMEAU',
  `lcsh` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Library of Congress Subject Heading',
  `parent_id` bigint unsigned DEFAULT NULL,
  `related_subjects` json DEFAULT NULL COMMENT 'Sujets connexes (IDs)',
  `synonyms` json DEFAULT NULL COMMENT 'Synonymes et variantes',
  `total_books` int NOT NULL DEFAULT '0' COMMENT 'Nombre de livres',
  `status` enum('active','deprecated','merged') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_subjects_name_unique` (`name`),
  KEY `record_subjects_name_index` (`name`),
  KEY `record_subjects_dewey_class_index` (`dewey_class`),
  KEY `record_subjects_lcc_class_index` (`lcc_class`),
  KEY `record_subjects_parent_id_index` (`parent_id`),
  KEY `record_subjects_status_index` (`status`),
  FULLTEXT KEY `record_subjects_name_name_en_description_fulltext` (`name`,`name_en`,`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_supports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_supports_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_thesaurus_concept` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `weight` double DEFAULT NULL,
  `context` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extraction_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_thesaurus_concept_record_id_concept_id_unique` (`record_id`,`concept_id`),
  KEY `record_thesaurus_concept_concept_id_foreign` (`concept_id`),
  CONSTRAINT `record_thesaurus_concept_concept_id_foreign` FOREIGN KEY (`concept_id`) REFERENCES `thesaurus_concepts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_thesaurus_concept_record_id_foreign` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_type_metadata_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_type_id` bigint unsigned NOT NULL,
  `metadata_definition_id` bigint unsigned NOT NULL,
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `validation_rules` json DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_type_meta_prof_unique` (`record_type_id`,`metadata_definition_id`),
  KEY `record_type_metadata_profiles_metadata_definition_id_foreign` (`metadata_definition_id`),
  KEY `record_type_metadata_profiles_created_by_foreign` (`created_by`),
  KEY `record_type_metadata_profiles_updated_by_foreign` (`updated_by`),
  KEY `record_type_metadata_profiles_mandatory_index` (`mandatory`),
  KEY `record_type_metadata_profiles_sort_order_index` (`sort_order`),
  CONSTRAINT `record_type_metadata_profiles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_type_metadata_profiles_metadata_definition_id_foreign` FOREIGN KEY (`metadata_definition_id`) REFERENCES `metadata_definitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_type_metadata_profiles_record_type_id_foreign` FOREIGN KEY (`record_type_id`) REFERENCES `record_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `record_type_metadata_profiles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `reference_list_id` bigint unsigned DEFAULT NULL,
  `is_container` tinyint(1) NOT NULL DEFAULT '0',
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_prefix` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_pattern` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allowed_mime_types` json DEFAULT NULL,
  `allowed_extensions` json DEFAULT NULL,
  `max_file_size` bigint unsigned DEFAULT NULL,
  `requires_versioning` tinyint(1) NOT NULL DEFAULT '0',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0',
  `requires_signature` tinyint(1) NOT NULL DEFAULT '0',
  `default_access_level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `legacy_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_types_code_unique` (`code`),
  KEY `record_types_parent_id_foreign` (`parent_id`),
  KEY `record_types_reference_list_id_foreign` (`reference_list_id`),
  KEY `record_types_created_by_foreign` (`created_by`),
  KEY `record_types_updated_by_foreign` (`updated_by`),
  KEY `record_types_is_container_index` (`is_container`),
  KEY `record_types_is_active_index` (`is_active`),
  KEY `record_types_display_order_index` (`display_order`),
  CONSTRAINT `record_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_types_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `record_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_types_reference_list_id_foreign` FOREIGN KEY (`reference_list_id`) REFERENCES `reference_lists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `record_types_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `biographical_history` text COLLATE utf8mb4_unicode_ci,
  `archival_history` text COLLATE utf8mb4_unicode_ci,
  `acquisition_source` text COLLATE utf8mb4_unicode_ci,
  `content` text COLLATE utf8mb4_unicode_ci,
  `appraisal` text COLLATE utf8mb4_unicode_ci,
  `accrual` text COLLATE utf8mb4_unicode_ci,
  `arrangement` text COLLATE utf8mb4_unicode_ci,
  `access_conditions` text COLLATE utf8mb4_unicode_ci,
  `reproduction_conditions` text COLLATE utf8mb4_unicode_ci,
  `language_material` text COLLATE utf8mb4_unicode_ci,
  `characteristic` text COLLATE utf8mb4_unicode_ci,
  `finding_aids` text COLLATE utf8mb4_unicode_ci,
  `location_original` text COLLATE utf8mb4_unicode_ci,
  `location_copy` text COLLATE utf8mb4_unicode_ci,
  `related_unit` text COLLATE utf8mb4_unicode_ci,
  `publication_note` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `archivist_note` text COLLATE utf8mb4_unicode_ci,
  `rule_convention` text COLLATE utf8mb4_unicode_ci,
  `extent` text COLLATE utf8mb4_unicode_ci,
  `category_precision` text COLLATE utf8mb4_unicode_ci,
  `opening_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `processing_date` date DEFAULT NULL,
  `transfer_approved_date` date DEFAULT NULL,
  `transfer_effective_date` date DEFAULT NULL,
  `deposit_approved_date` date DEFAULT NULL,
  `deposit_effective_date` date DEFAULT NULL,
  `destruction_approved_date` date DEFAULT NULL,
  `destruction_effective_date` date DEFAULT NULL,
  `last_reminder_date` date DEFAULT NULL,
  `old_record_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archival_status_gvaa` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unavailable` tinyint(1) NOT NULL DEFAULT '0',
  `annual_opening` tinyint(1) NOT NULL DEFAULT '0',
  `is_essential` tinyint(1) NOT NULL DEFAULT '0',
  `loaned_to` bigint unsigned DEFAULT NULL,
  `loaned_at` datetime DEFAULT NULL,
  `loan_planned_return_at` datetime DEFAULT NULL,
  `loan_actual_return_at` datetime DEFAULT NULL,
  `modified_after_loan` tinyint(1) NOT NULL DEFAULT '0',
  `confidentiality_id` bigint unsigned DEFAULT NULL,
  `access_limit_id` bigint unsigned DEFAULT NULL,
  `table_of_contents` text COLLATE utf8mb4_unicode_ci,
  `quantity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimension` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publisher` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `received_date` datetime DEFAULT NULL,
  `signature_date` datetime DEFAULT NULL,
  `final_version_creation` tinyint(1) NOT NULL DEFAULT '0',
  `location_before_add` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geographic_scope` json DEFAULT NULL,
  `type_id` bigint unsigned DEFAULT NULL,
  `level_id` bigint unsigned NOT NULL,
  `status_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `access_level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `date_exact` date DEFAULT NULL,
  `date_format` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_number` int NOT NULL DEFAULT '1',
  `is_current_version` tinyint(1) NOT NULL DEFAULT '1',
  `legacy_source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `records_code_unique` (`code`),
  KEY `records_loaned_to_foreign` (`loaned_to`),
  KEY `records_confidentiality_id_foreign` (`confidentiality_id`),
  KEY `records_access_limit_id_foreign` (`access_limit_id`),
  KEY `records_level_id_foreign` (`level_id`),
  KEY `records_status_id_foreign` (`status_id`),
  KEY `records_activity_id_foreign` (`activity_id`),
  KEY `records_parent_id_foreign` (`parent_id`),
  KEY `records_creator_id_foreign` (`creator_id`),
  KEY `records_assigned_to_foreign` (`assigned_to`),
  KEY `records_approved_by_foreign` (`approved_by`),
  KEY `records_legacy_source_legacy_id_index` (`legacy_source`,`legacy_id`),
  KEY `records_organisation_id_status_id_index` (`organisation_id`,`status_id`),
  KEY `records_type_id_parent_id_index` (`type_id`,`parent_id`),
  KEY `records_is_current_version_index` (`is_current_version`),
  CONSTRAINT `records_access_limit_id_foreign` FOREIGN KEY (`access_limit_id`) REFERENCES `reference_lists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_confidentiality_id_foreign` FOREIGN KEY (`confidentiality_id`) REFERENCES `record_confidentialities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  CONSTRAINT `records_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `record_levels` (`id`),
  CONSTRAINT `records_loaned_to_foreign` FOREIGN KEY (`loaned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`),
  CONSTRAINT `records_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `records_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `record_statuses` (`id`),
  CONSTRAINT `records_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `record_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reference_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique identifier code',
  `description` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_lists_name_unique` (`name`),
  UNIQUE KEY `reference_lists_code_unique` (`code`),
  KEY `reference_lists_created_by_foreign` (`created_by`),
  KEY `reference_lists_updated_by_foreign` (`updated_by`),
  KEY `reference_lists_active_index` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reference_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `list_id` bigint unsigned NOT NULL,
  `value` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique identifier code within list',
  `description` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_list_value_code` (`list_id`,`code`),
  KEY `reference_values_created_by_foreign` (`created_by`),
  KEY `reference_values_updated_by_foreign` (`updated_by`),
  KEY `reference_values_active_index` (`active`),
  KEY `reference_values_sort_order_index` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_record` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `is_original` tinyint(1) NOT NULL DEFAULT '0',
  `reservation_date` date NOT NULL,
  `operator_id` bigint unsigned NOT NULL,
  `communication_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_record_communication_id_foreign` (`communication_id`),
  KEY `reservation_record_reservation_id_foreign` (`reservation_id`),
  KEY `reservation_record_record_id_foreign` (`record_id`),
  KEY `reservation_record_operator_id_foreign` (`operator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `operator_id` bigint unsigned NOT NULL,
  `operator_organisation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `user_organisation_id` bigint unsigned NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `communication_id` bigint unsigned DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_effective` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_code_unique` (`code`),
  KEY `reservations_operator_id_foreign` (`operator_id`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_communication_id_foreign` (`communication_id`),
  KEY `idx_reservation_operator_org` (`operator_organisation_id`),
  KEY `idx_reservation_user_org` (`user_organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retention_activity` (
  `retention_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`retention_id`,`activity_id`),
  KEY `retention_activity_activity_id_foreign` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retention_law_articles` (
  `retention_id` bigint unsigned NOT NULL,
  `law_article_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`retention_id`,`law_article_id`),
  KEY `retention_law_articles_law_article_id_foreign` (`law_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retentions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `sort_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `retentions_sort_id_foreign` (`sort_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `visibility` enum('public','private','inherit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inherit' COMMENT 'Visibilité de la salle: public, private, ou inherit du bâtiment parent',
  `type` enum('archives','producer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'archives' COMMENT 'Type de salle: archives ou producer',
  `floor_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_floor_id_foreign` (`floor_id`),
  KEY `rooms_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheme_properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_scheme_id` bigint unsigned NOT NULL,
  `property_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_type` enum('string','text','integer','float','boolean','date','uri') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_multiple` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `validation_rules` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scheme_properties_concept_scheme_id_property_name_unique` (`concept_scheme_id`,`property_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `setting_categories_parent_id_foreign` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('integer','string','boolean','json','float','array') COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_value` json NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `constraints` json DEFAULT NULL COMMENT 'JSON containing min, max, options, etc.',
  `user_id` bigint unsigned DEFAULT NULL,
  `organisation_id` bigint unsigned DEFAULT NULL,
  `value` json DEFAULT NULL COMMENT 'Current value (null = use default_value)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_name_unique` (`name`),
  KEY `settings_category_id_foreign` (`category_id`),
  KEY `settings_organisation_id_foreign` (`organisation_id`),
  KEY `settings_user_id_organisation_id_index` (`user_id`,`organisation_id`),
  KEY `settings_name_user_id_index` (`name`,`user_id`),
  KEY `settings_name_organisation_id_index` (`name`,`organisation_id`),
  KEY `settings_is_system_index` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shelves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observation` longtext COLLATE utf8mb4_unicode_ci,
  `face` float NOT NULL,
  `ear` float NOT NULL,
  `shelf` float NOT NULL,
  `shelf_length` float NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`room_id`),
  KEY `shelves_room_id_foreign` (`room_id`),
  KEY `shelves_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_attachment` (
  `slip_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slip_id`,`attachment_id`),
  KEY `slip_attachment_attachment_id_foreign` (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_record_attachments` (
  `slip_record_id` bigint unsigned NOT NULL,
  `attachment_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slip_record_id`,`attachment_id`),
  KEY `slip_record_attachment_attachment_id_foreign` (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_record_container` (
  `slip_record_id` bigint unsigned NOT NULL,
  `container_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slip_record_id`,`container_id`),
  KEY `slip_record_container_container_id_foreign` (`container_id`),
  KEY `slip_record_container_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_record_keyword` (
  `slip_record_id` bigint unsigned NOT NULL,
  `keyword_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slip_record_id`,`keyword_id`),
  KEY `slip_record_keyword_keyword_id_foreign` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slip_id` bigint unsigned NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_format` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_start` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_end` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_exact` date DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `level_id` bigint unsigned NOT NULL,
  `width` float DEFAULT NULL,
  `width_description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slip_records_slip_id_foreign` (`slip_id`),
  KEY `slip_records_level_id_foreign` (`level_id`),
  KEY `slip_records_support_id_foreign` (`support_id`),
  KEY `slip_records_activity_id_foreign` (`activity_id`),
  KEY `slip_records_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slip_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `officer_organisation_id` bigint unsigned NOT NULL,
  `officer_id` bigint unsigned NOT NULL,
  `user_organisation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `slip_status_id` bigint unsigned NOT NULL,
  `is_received` tinyint(1) DEFAULT '0',
  `received_date` datetime DEFAULT NULL,
  `received_by` bigint unsigned DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `approved_date` datetime DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `is_integrated` tinyint(1) DEFAULT '0',
  `integrated_date` datetime DEFAULT NULL,
  `integrated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slips_code_unique` (`code`),
  KEY `slips_officer_id_foreign` (`officer_id`),
  KEY `slips_user_id_foreign` (`user_id`),
  KEY `slips_slip_status_id_foreign` (`slip_status_id`),
  KEY `slips_received_by_foreign` (`received_by`),
  KEY `slips_approved_by_foreign` (`approved_by`),
  KEY `slips_integrated_by_foreign` (`integrated_by`),
  KEY `idx_slip_officer_org` (`officer_organisation_id`),
  KEY `idx_slip_user_org` (`user_organisation_id`),
  KEY `idx_slip_dual_org` (`officer_organisation_id`,`user_organisation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sorts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` enum('E','T','C') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_versions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `previous_version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` timestamp NOT NULL,
  `changelog` json DEFAULT NULL,
  `installation_method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `is_rollback` tinyint(1) NOT NULL DEFAULT '0',
  `installed_by` bigint unsigned DEFAULT NULL,
  `download_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checksum` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_versions_installed_by_foreign` (`installed_by`),
  KEY `system_versions_version_installed_at_index` (`version`,`installed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `attachable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachable_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `attached_by` bigint unsigned NOT NULL,
  `attached_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attachment_task` (`task_id`),
  KEY `idx_attachment_user` (`attached_by`),
  KEY `idx_attachment_attachable` (`attachable_type`,`attachable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_comment_task` (`task_id`),
  KEY `idx_comment_user` (`user_id`),
  KEY `idx_comment_date` (`created_at`),
  KEY `task_comments_updated_by_foreign` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `field_changed` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_history_task` (`task_id`),
  KEY `idx_history_user` (`changed_by`),
  KEY `idx_history_date` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_reminders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `remind_at` timestamp NOT NULL,
  `reminder_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reminder_task` (`task_id`),
  KEY `idx_reminder_date` (`remind_at`),
  KEY `idx_reminder_status` (`is_sent`),
  KEY `task_reminders_created_by_foreign` (`created_by`),
  KEY `idx_reminder_sent_date` (`is_sent`,`remind_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_watchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `notify_on_update` tinyint(1) NOT NULL DEFAULT '1',
  `notify_on_comment` tinyint(1) NOT NULL DEFAULT '1',
  `notify_on_completion` tinyint(1) NOT NULL DEFAULT '1',
  `added_by` bigint unsigned NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_task_watcher` (`task_id`,`user_id`),
  KEY `idx_watcher_task` (`task_id`),
  KEY `idx_watcher_user` (`user_id`),
  KEY `task_watchers_added_by_foreign` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint unsigned NOT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `workflow_instance_id` bigint unsigned DEFAULT NULL,
  `task_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_data` json DEFAULT NULL,
  `sequence_order` int DEFAULT NULL,
  `parent_task_id` bigint unsigned DEFAULT NULL,
  `taskable_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taskable_id` bigint unsigned DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_by` bigint unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_task_workflow` (`workflow_instance_id`),
  KEY `idx_task_assigned` (`assigned_to`),
  KEY `idx_task_created` (`created_by`),
  KEY `idx_task_updated` (`updated_by`),
  KEY `idx_task_completed` (`completed_by`),
  KEY `idx_task_parent` (`parent_task_id`),
  KEY `idx_task_taskable` (`taskable_type`,`taskable_id`),
  KEY `idx_task_status_perf` (`status`),
  KEY `idx_task_priority_perf` (`priority`),
  KEY `idx_task_due_date_perf` (`due_date`),
  KEY `idx_task_status_assigned` (`status`,`assigned_to`),
  KEY `idx_task_status_due` (`status`,`due_date`),
  KEY `idx_task_org` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_preview_cache` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `cache_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Clé de cache MD5',
  `device_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'desktop' COMMENT 'desktop, tablet, mobile',
  `rendered_html` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'HTML généré',
  `css_compiled` text COLLATE utf8mb4_unicode_ci COMMENT 'CSS compilé',
  `variables_used` json DEFAULT NULL COMMENT 'Variables utilisées pour ce rendu',
  `file_size` int NOT NULL COMMENT 'Taille du HTML généré en octets',
  `expires_at` timestamp NOT NULL COMMENT 'Date d''expiration du cache',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_preview_cache_cache_key_unique` (`cache_key`),
  KEY `template_device_cache` (`template_id`,`device_type`),
  KEY `cache_expiration` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_versions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `version` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Numéro de version',
  `layout` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Structure HTML de cette version',
  `custom_css` longtext COLLATE utf8mb4_unicode_ci COMMENT 'CSS de cette version',
  `custom_js` longtext COLLATE utf8mb4_unicode_ci COMMENT 'JavaScript de cette version',
  `variables` json DEFAULT NULL COMMENT 'Variables de cette version',
  `components` json DEFAULT NULL COMMENT 'Composants de cette version',
  `meta` json DEFAULT NULL COMMENT 'Métadonnées de cette version',
  `created_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Utilisateur ayant créé cette version',
  `change_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description des changements',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Version actuellement active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_version_unique` (`template_id`,`version`),
  KEY `template_active_version` (`template_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du template',
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identifiant unique du template',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description du template',
  `type` enum('opac','public','admin','email') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'opac' COMMENT 'Type de template',
  `status` enum('draft','active','inactive','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Statut du template',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Contenu HTML du template',
  `settings` json DEFAULT NULL COMMENT 'Paramètres de configuration',
  `theme` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default' COMMENT 'Thème associé',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Template par défaut',
  `created_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Créateur du template',
  `updated_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dernière personne à l''avoir modifié',
  `layout` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Structure HTML du template',
  `custom_css` longtext COLLATE utf8mb4_unicode_ci COMMENT 'CSS personnalisé du template',
  `custom_js` longtext COLLATE utf8mb4_unicode_ci COMMENT 'JavaScript personnalisé du template',
  `variables` json DEFAULT NULL COMMENT 'Variables de configuration du template',
  `components` json DEFAULT NULL COMMENT 'Composants actifs dans le template',
  `version` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0' COMMENT 'Version du template',
  `meta` json DEFAULT NULL COMMENT 'Métadonnées additionnelles du template',
  `last_modified` timestamp NULL DEFAULT NULL COMMENT 'Dernière modification du contenu',
  `modified_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Utilisateur ayant modifié en dernier',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `templates_slug_unique` (`slug`),
  KEY `template_type_status` (`type`,`status`),
  KEY `template_default` (`is_default`),
  KEY `template_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `preferred_label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope_note` text COLLATE utf8mb4_unicode_ci,
  `language` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('candidate','approved','deprecated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'candidate',
  `notation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `modified_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terms_notation_unique` (`notation`),
  KEY `terms_language_status_index` (`language`,`status`),
  KEY `terms_preferred_label_language_index` (`preferred_label`,`language`),
  KEY `terms_created_by_foreign` (`created_by`),
  KEY `terms_modified_by_foreign` (`modified_by`),
  KEY `terms_preferred_label_index` (`preferred_label`),
  KEY `terms_language_index` (`language`),
  KEY `terms_category_index` (`category`),
  KEY `terms_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_collection_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `collection_id` bigint unsigned NOT NULL,
  `label` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Forme littérale du label de la collection',
  `label_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type de label (prefLabel, altLabel, hiddenLabel)',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr' COMMENT 'Langue du label',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thes_coll_labels_index` (`collection_id`,`language`,`label_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_collection_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `collection_id` bigint unsigned NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `position` int DEFAULT NULL COMMENT 'Position pour les collections ordonnées',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thes_coll_concept_unique` (`collection_id`,`concept_id`),
  KEY `thesaurus_collection_members_concept_id_foreign` (`concept_id`),
  KEY `thes_coll_pos_index` (`collection_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scheme_id` bigint unsigned NOT NULL,
  `uri` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URI unique de la collection SKOS',
  `ordered` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indique si la collection est ordonnée',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thesaurus_collections_uri_unique` (`uri`),
  KEY `thesaurus_collections_scheme_id_foreign` (`scheme_id`),
  KEY `thesaurus_collections_uri_index` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_concept_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `type` enum('definition','scopeNote','example','historyNote','editorialNote') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'definition',
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contenu de la note',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr-fr' COMMENT 'Langue de la note',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesaurus_concept_notes_concept_id_type_index` (`concept_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_concept_properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `property_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de la propriété',
  `property_value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valeur de la propriété',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Langue de la propriété',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesaurus_concept_properties_concept_id_property_name_index` (`concept_id`,`property_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_concept_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `related_concept_id` bigint unsigned NOT NULL,
  `relation_type` enum('broader','narrower','related','broadMatch','narrowMatch','exactMatch','relatedMatch') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type de relation SKOS',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thesaurus_concept_relations_unique` (`concept_id`,`related_concept_id`,`relation_type`),
  KEY `thesaurus_concept_relations_related_concept_id_foreign` (`related_concept_id`),
  KEY `thesaurus_concept_relations_concept_id_relation_type_index` (`concept_id`,`relation_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_concepts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scheme_id` bigint unsigned NOT NULL,
  `uri` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URI unique du concept SKOS',
  `notation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Notation du concept',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT 'Statut du concept',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thesaurus_concepts_uri_unique` (`uri`),
  KEY `thesaurus_concepts_scheme_id_foreign` (`scheme_id`),
  KEY `thesaurus_concepts_notation_index` (`notation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_imports` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('skos-rdf','csv','json') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type d''import',
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du fichier importé',
  `status` enum('processing','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `total_items` int NOT NULL DEFAULT '0' COMMENT 'Nombre total d''éléments à traiter',
  `processed_items` int NOT NULL DEFAULT '0' COMMENT 'Nombre d''éléments traités',
  `created_items` int NOT NULL DEFAULT '0' COMMENT 'Nombre d''éléments créés',
  `updated_items` int NOT NULL DEFAULT '0' COMMENT 'Nombre d''éléments mis à jour',
  `error_items` int NOT NULL DEFAULT '0' COMMENT 'Nombre d''éléments en erreur',
  `relationships_created` int NOT NULL DEFAULT '0' COMMENT 'Nombre de relations créées',
  `message` text COLLATE utf8mb4_unicode_ci COMMENT 'Message de statut ou d''erreur',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesaurus_imports_status_created_at_index` (`status`,`created_at`),
  KEY `thesaurus_imports_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concept_id` bigint unsigned NOT NULL,
  `uri` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URI du label',
  `type` enum('prefLabel','altLabel','hiddenLabel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prefLabel',
  `literal_form` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Forme littérale du label',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr-fr' COMMENT 'Langue du label',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thesaurus_labels_concept_id_type_index` (`concept_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_namespaces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Préfixe du namespace',
  `namespace_uri` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URI du namespace',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Description du namespace',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thesaurus_namespaces_prefix_unique` (`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_nested_collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_collection_id` bigint unsigned NOT NULL,
  `child_collection_id` bigint unsigned NOT NULL,
  `position` int DEFAULT NULL COMMENT 'Position pour les collections ordonnées',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thes_nested_coll_unique` (`parent_collection_id`,`child_collection_id`),
  KEY `thesaurus_nested_collections_child_collection_id_foreign` (`child_collection_id`),
  KEY `thes_parent_pos_index` (`parent_collection_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de l''organisation',
  `homepage` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Site web',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesaurus_schemes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URI unique du schéma SKOS',
  `identifier` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Identifiant externe',
  `title` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Titre principal du schéma',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description du schéma',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr-fr' COMMENT 'Langue par défaut',
  `namespace_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thesaurus_schemes_uri_unique` (`uri`),
  KEY `thesaurus_schemes_uri_index` (`uri`),
  KEY `thesaurus_schemes_namespace_id_foreign` (`namespace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_concept_id` bigint unsigned NOT NULL,
  `target_concept_id` bigint unsigned NOT NULL,
  `translation_type` enum('exact','partial','broad','narrow') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exact',
  `translation_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`source_concept_id`,`target_concept_id`),
  KEY `translations_target_concept_id_foreign` (`target_concept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_organisation_role` (
  `user_id` bigint unsigned NOT NULL,
  `organisation_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `creator_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`organisation_id`),
  KEY `user_organisation_role_role_id_foreign` (`role_id`),
  KEY `user_organisation_role_organisation_id_foreign` (`organisation_id`),
  KEY `user_organisation_role_creator_id_foreign` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permissions_user_id_permission_id_unique` (`user_id`,`permission_id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_organisation_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_definitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `bpmn_xml` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_created` (`created_by`),
  KEY `idx_workflow_updated` (`updated_by`),
  KEY `idx_workflow_def_status` (`status`),
  KEY `idx_workflow_def_status_created` (`status`,`created_at`),
  KEY `idx_workflow_def_org` (`organisation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_instances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` bigint unsigned NOT NULL,
  `definition_id` bigint unsigned NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_state` json NOT NULL,
  `started_by` bigint unsigned NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_by` bigint unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_definition` (`definition_id`),
  KEY `idx_workflow_started` (`started_by`),
  KEY `idx_workflow_updated` (`updated_by`),
  KEY `idx_workflow_completed` (`completed_by`),
  KEY `idx_workflow_instance_status` (`status`),
  KEY `idx_workflow_instance_status_started` (`status`,`started_at`),
  KEY `idx_workflow_inst_org` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_transitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `definition_id` bigint unsigned NOT NULL,
  `from_task_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_task_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition` text COLLATE utf8mb4_unicode_ci,
  `sequence_order` int NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transition_definition` (`definition_id`),
  KEY `idx_transition_from` (`from_task_key`),
  KEY `idx_transition_to` (`to_task_key`),
  KEY `workflow_transitions_created_by_foreign` (`created_by`),
  KEY `workflow_transitions_updated_by_foreign` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `activity_type` enum('created_folder','created_document','shared_folder','shared_document','updated_folder','updated_document','deleted_folder','deleted_document','joined','left','member_added','member_removed','settings_changed') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type d''activité',
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type d''entité concernée (polymorphic)',
  `subject_id` bigint unsigned DEFAULT NULL COMMENT 'ID de l''entité concernée',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description de l''activité',
  `metadata` json DEFAULT NULL COMMENT 'Données supplémentaires (ancien/nouveau, etc.)',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `workplace_activities_workplace_id_foreign` (`workplace_id`),
  KEY `workplace_activities_user_id_foreign` (`user_id`),
  KEY `workplace_activities_activity_type_index` (`activity_type`),
  KEY `workplace_activities_created_at_index` (`created_at`),
  KEY `workplace_activities_subject_type_subject_id_index` (`subject_type`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_bookmarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `bookmarkable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type: RecordDigitalFolder, RecordDigitalDocument',
  `bookmarkable_id` bigint unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci COMMENT 'Note personnelle',
  `tags` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tags personnels séparés par des virgules',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`workplace_id`,`user_id`,`bookmarkable_type`,`bookmarkable_id`),
  KEY `workplace_bookmarks_user_id_foreign` (`user_id`),
  KEY `workplace_bookmarks_bookmarkable_type_bookmarkable_id_index` (`bookmarkable_type`,`bookmarkable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique (HR, FINANCE, IT, etc.)',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de la catégorie',
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icône FontAwesome',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Couleur hexa',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_categories_code_unique` (`code`),
  KEY `workplace_categories_is_active_index` (`is_active`),
  KEY `workplace_categories_display_order_index` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_conversation_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wcp_conv_user_unique` (`conversation_id`,`user_id`),
  KEY `workplace_conversation_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `workplace_conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `workplace_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workplace_conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned DEFAULT NULL,
  `type` enum('private','group','channel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workplace_conversations_created_by_foreign` (`created_by`),
  KEY `workplace_conversations_workplace_id_type_index` (`workplace_id`,`type`),
  CONSTRAINT `workplace_conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workplace_conversations_workplace_id_foreign` FOREIGN KEY (`workplace_id`) REFERENCES `workplaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `document_id` bigint unsigned NOT NULL,
  `shared_by` bigint unsigned NOT NULL,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date de partage',
  `share_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Note de partage',
  `access_level` enum('view','edit','full') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view' COMMENT 'Niveau d''accès pour les membres',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Document mis en avant',
  `views_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de vues',
  `last_viewed_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière consultation',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_documents_workplace_id_document_id_unique` (`workplace_id`,`document_id`),
  KEY `workplace_documents_document_id_foreign` (`document_id`),
  KEY `workplace_documents_shared_by_index` (`shared_by`),
  KEY `workplace_documents_is_featured_index` (`is_featured`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `folder_id` bigint unsigned NOT NULL,
  `shared_by` bigint unsigned NOT NULL,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date de partage',
  `share_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Note de partage',
  `access_level` enum('view','edit','full') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view' COMMENT 'Niveau d''accès pour les membres',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Épinglé en haut',
  `display_order` int NOT NULL DEFAULT '0' COMMENT 'Ordre d''affichage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_folders_workplace_id_folder_id_unique` (`workplace_id`,`folder_id`),
  KEY `workplace_folders_folder_id_foreign` (`folder_id`),
  KEY `workplace_folders_shared_by_index` (`shared_by`),
  KEY `workplace_folders_is_pinned_index` (`is_pinned`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `invited_by` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email si utilisateur externe',
  `proposed_role` enum('admin','editor','contributor','viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contributor',
  `message` text COLLATE utf8mb4_unicode_ci COMMENT 'Message d''invitation personnalisé',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token unique pour accepter l''invitation',
  `status` enum('pending','accepted','declined','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `expires_at` timestamp NOT NULL COMMENT 'Date d''expiration',
  `responded_at` timestamp NULL DEFAULT NULL COMMENT 'Date de réponse',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_invitations_token_unique` (`token`),
  KEY `workplace_invitations_workplace_id_foreign` (`workplace_id`),
  KEY `workplace_invitations_invited_by_foreign` (`invited_by`),
  KEY `workplace_invitations_user_id_foreign` (`user_id`),
  KEY `workplace_invitations_status_index` (`status`),
  KEY `workplace_invitations_token_index` (`token`),
  KEY `workplace_invitations_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workplace_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` enum('owner','admin','editor','contributor','viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contributor' COMMENT 'Rôle du membre dans l''espace',
  `can_create_folders` tinyint(1) NOT NULL DEFAULT '1',
  `can_create_documents` tinyint(1) NOT NULL DEFAULT '1',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `can_share` tinyint(1) NOT NULL DEFAULT '1',
  `can_invite` tinyint(1) NOT NULL DEFAULT '0',
  `notify_on_new_content` tinyint(1) NOT NULL DEFAULT '1',
  `notify_on_mentions` tinyint(1) NOT NULL DEFAULT '1',
  `notify_on_updates` tinyint(1) NOT NULL DEFAULT '0',
  `invited_by` bigint unsigned DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière activité dans l''espace',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_members_workplace_id_user_id_unique` (`workplace_id`,`user_id`),
  KEY `workplace_members_user_id_foreign` (`user_id`),
  KEY `workplace_members_invited_by_foreign` (`invited_by`),
  KEY `workplace_members_role_index` (`role`),
  KEY `workplace_members_joined_at_index` (`joined_at`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workplace_messages_user_id_foreign` (`user_id`),
  KEY `workplace_messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  CONSTRAINT `workplace_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `workplace_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workplace_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplace_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique du template',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom du template',
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catégorie de template',
  `default_settings` json DEFAULT NULL COMMENT 'Paramètres par défaut',
  `default_structure` json DEFAULT NULL COMMENT 'Structure de dossiers par défaut',
  `default_permissions` json DEFAULT NULL COMMENT 'Permissions par défaut',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Template système non modifiable',
  `usage_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre d''utilisations',
  `display_order` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplace_templates_code_unique` (`code`),
  KEY `workplace_templates_created_by_foreign` (`created_by`),
  KEY `workplace_templates_code_index` (`code`),
  KEY `workplace_templates_category_index` (`category`),
  KEY `workplace_templates_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workplaces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code unique : WP-YYYY-NNNN',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nom de l''espace de travail',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description et objectifs',
  `category_id` bigint unsigned DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icône FontAwesome',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3498db' COMMENT 'Couleur hexa pour UI',
  `settings` json DEFAULT NULL COMMENT 'Configuration JSON (notifications, permissions, etc.)',
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible par tous',
  `allow_external_sharing` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Partage externe autorisé',
  `max_members` int NOT NULL DEFAULT '50' COMMENT 'Nombre max de membres',
  `max_storage_mb` int NOT NULL DEFAULT '5120' COMMENT 'Espace de stockage max en MB (5GB par défaut)',
  `members_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de membres',
  `folders_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de dossiers',
  `documents_count` int NOT NULL DEFAULT '0' COMMENT 'Nombre de documents',
  `storage_used_bytes` bigint NOT NULL DEFAULT '0' COMMENT 'Stockage utilisé en octets',
  `status` enum('active','archived','suspended','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Statut de l''espace',
  `start_date` date DEFAULT NULL COMMENT 'Date de début',
  `end_date` date DEFAULT NULL COMMENT 'Date de fin prévue',
  `archived_at` timestamp NULL DEFAULT NULL COMMENT 'Date d''archivage',
  `organisation_id` bigint unsigned NOT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workplaces_code_unique` (`code`),
  KEY `workplaces_created_by_foreign` (`created_by`),
  KEY `workplaces_updated_by_foreign` (`updated_by`),
  KEY `workplaces_category_id_index` (`category_id`),
  KEY `workplaces_status_index` (`status`),
  KEY `workplaces_organisation_id_index` (`organisation_id`),
  KEY `workplaces_owner_id_index` (`owner_id`),
  KEY `workplaces_category_id_status_index` (`category_id`,`status`),
  KEY `workplaces_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xl_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concept_id` bigint unsigned NOT NULL,
  `label_type` enum('prefLabel','altLabel','hiddenLabel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `literal_form` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `xl_labels_uri_hash_unique` (`uri_hash`),
  KEY `xl_labels_concept_id_label_type_index` (`concept_id`,`label_type`),
  KEY `xl_labels_language_index` (`language`)
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

SET FOREIGN_KEY_CHECKS=1;
