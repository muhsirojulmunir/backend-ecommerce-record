-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: ecommerce_record
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`),
  CONSTRAINT `activity_log_chk_1` CHECK (json_valid(`properties`))
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,'pengaturan','menambahkan pengaturan: Tagline Toko','App\\Models\\WebsiteSetting','created',6,NULL,NULL,'{\"attributes\":{\"value\":\"Langkah Nyaman Setiap Hari\"}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(2,'pengaturan','menambahkan pengaturan: Deskripsi Toko','App\\Models\\WebsiteSetting','created',7,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(3,'pengaturan','menambahkan pengaturan: Favicon','App\\Models\\WebsiteSetting','created',8,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(4,'pengaturan','menambahkan pengaturan: Email Customer Service','App\\Models\\WebsiteSetting','created',9,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(5,'pengaturan','menambahkan pengaturan: Nomor Telepon','App\\Models\\WebsiteSetting','created',10,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(6,'pengaturan','menambahkan pengaturan: Nomor WhatsApp','App\\Models\\WebsiteSetting','created',11,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(7,'pengaturan','menambahkan pengaturan: Instagram','App\\Models\\WebsiteSetting','created',12,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(8,'pengaturan','menambahkan pengaturan: Facebook','App\\Models\\WebsiteSetting','created',13,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(9,'pengaturan','menambahkan pengaturan: TikTok','App\\Models\\WebsiteSetting','created',14,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(10,'pengaturan','menambahkan pengaturan: Kode Pos Toko','App\\Models\\WebsiteSetting','created',15,NULL,NULL,'{\"attributes\":{\"value\":\"60117\"}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(11,'pengaturan','menambahkan pengaturan: Minimal Gratis Ongkir','App\\Models\\WebsiteSetting','created',16,NULL,NULL,'{\"attributes\":{\"value\":\"0\"}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(12,'pengaturan','menambahkan pengaturan: Nama Bank','App\\Models\\WebsiteSetting','created',17,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(13,'pengaturan','menambahkan pengaturan: Nomor Rekening','App\\Models\\WebsiteSetting','created',18,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(14,'pengaturan','menambahkan pengaturan: Atas Nama','App\\Models\\WebsiteSetting','created',19,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:17','2026-08-04 03:19:17'),(15,'pengaturan','menambahkan pengaturan: Aktifkan COD','App\\Models\\WebsiteSetting','created',20,NULL,NULL,'{\"attributes\":{\"value\":\"1\"}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(16,'pengaturan','menambahkan pengaturan: Catatan Pembayaran','App\\Models\\WebsiteSetting','created',21,NULL,NULL,'{\"attributes\":{\"value\":null}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(17,'pengaturan','menambahkan pengaturan: Mode Perbaikan','App\\Models\\WebsiteSetting','created',22,NULL,NULL,'{\"attributes\":{\"value\":\"0\"}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(18,'pengaturan','menambahkan pengaturan: Pesan Mode Perbaikan','App\\Models\\WebsiteSetting','created',23,NULL,NULL,'{\"attributes\":{\"value\":\"Toko sedang dalam perbaikan. Silakan kembali beberapa saat lagi.\"}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(19,'pengaturan','menambahkan pengaturan: Batas Bayar (Jam)','App\\Models\\WebsiteSetting','created',24,NULL,NULL,'{\"attributes\":{\"value\":\"24\"}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(20,'pengaturan','menambahkan pengaturan: Ambang Stok Menipis','App\\Models\\WebsiteSetting','created',25,NULL,NULL,'{\"attributes\":{\"value\":\"5\"}}',NULL,'2026-08-04 03:19:18','2026-08-04 03:19:18'),(21,'pengaturan','memperbarui pengaturan: Nama Toko','App\\Models\\WebsiteSetting','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"RECORD Uji Coba\"},\"old\":{\"value\":\"Shopee-Like E-Commerce\"}}',NULL,'2026-08-04 03:21:06','2026-08-04 03:21:06'),(22,'pengaturan','memperbarui pengaturan: Kurir Aktif','App\\Models\\WebsiteSetting','updated',5,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"[\\\"jne\\\",\\\"sicepat\\\"]\"},\"old\":{\"value\":\"[\\\"jne\\\",\\\"pos\\\",\\\"tiki\\\"]\"}}',NULL,'2026-08-04 03:21:06','2026-08-04 03:21:06'),(23,'pengaturan','memperbarui pengaturan: Minimal Gratis Ongkir','App\\Models\\WebsiteSetting','updated',16,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"250000\"},\"old\":{\"value\":\"0\"}}',NULL,'2026-08-04 03:21:06','2026-08-04 03:21:06'),(24,'produk','memperbarui produk: RECORD Suga Sepatu Dewasa Premium HITAM PUTIH','App\\Models\\Product','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"stock\":157},\"old\":{\"stock\":150}}',NULL,'2026-08-04 03:21:06','2026-08-04 03:21:06'),(25,'produk','memperbarui produk: RECORD Suga Sepatu Dewasa Premium HITAM PUTIH','App\\Models\\Product','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"stock\":150},\"old\":{\"stock\":157}}',NULL,'2026-08-04 03:21:06','2026-08-04 03:21:06'),(26,'pengaturan','memperbarui pengaturan: Nama Toko','App\\Models\\WebsiteSetting','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"RECORD Uji Coba\"},\"old\":{\"value\":\"Shopee-Like E-Commerce\"}}',NULL,'2026-08-04 03:21:55','2026-08-04 03:21:55'),(27,'pengaturan','memperbarui pengaturan: Kurir Aktif','App\\Models\\WebsiteSetting','updated',5,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"[\\\"jne\\\",\\\"sicepat\\\"]\"},\"old\":{\"value\":\"[\\\"jne\\\",\\\"jnt\\\",\\\"sicepat\\\",\\\"anteraja\\\",\\\"pos\\\"]\"}}',NULL,'2026-08-04 03:21:55','2026-08-04 03:21:55'),(28,'pengaturan','memperbarui pengaturan: Minimal Gratis Ongkir','App\\Models\\WebsiteSetting','updated',16,'App\\Models\\User',1,'{\"attributes\":{\"value\":\"250000\"},\"old\":{\"value\":\"0\"}}',NULL,'2026-08-04 03:21:55','2026-08-04 03:21:55'),(29,'produk','memperbarui produk: RECORD Suga Sepatu Dewasa Premium HITAM PUTIH','App\\Models\\Product','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"stock\":157},\"old\":{\"stock\":150}}',NULL,'2026-08-04 03:21:55','2026-08-04 03:21:55'),(30,'produk','memperbarui produk: RECORD Suga Sepatu Dewasa Premium HITAM PUTIH','App\\Models\\Product','updated',1,'App\\Models\\User',1,'{\"attributes\":{\"stock\":150},\"old\":{\"stock\":157}}',NULL,'2026-08-04 03:21:55','2026-08-04 03:21:55'),(31,'kategori','menambahkan kategori: Sepatu Gunung','App\\Models\\Category','created',7,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Gunung\",\"slug\":\"sepatu-gunung\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(32,'produk','menambahkan produk: Sepatu Uji Lengkap','App\\Models\\Product','created',21,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Uji Lengkap\",\"price\":\"450000.00\",\"original_price\":\"600000.00\",\"stock\":20,\"status\":\"active\",\"is_featured\":true,\"category_id\":7}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(33,'kategori','menambahkan kategori: Sandal Uji','App\\Models\\Category','created',8,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sandal Uji\",\"slug\":\"sandal-uji\",\"is_active\":true,\"sort_order\":8}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(34,'produk','menambahkan produk: Sandal Uji Minimal','App\\Models\\Product','created',22,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sandal Uji Minimal\",\"price\":\"45000.00\",\"original_price\":null,\"stock\":120,\"status\":\"active\",\"is_featured\":false,\"category_id\":8}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(35,'produk','menambahkan produk: Sepatu Uji Kategori Lama','App\\Models\\Product','created',23,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Uji Kategori Lama\",\"price\":\"99000.00\",\"original_price\":null,\"stock\":10,\"status\":\"inactive\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(36,'produk','menambahkan produk: Produk Stok Beda','App\\Models\\Product','created',24,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Produk Stok Beda\",\"price\":\"120000.00\",\"original_price\":null,\"stock\":50,\"status\":\"active\",\"is_featured\":false,\"category_id\":7}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(37,'produk','menambahkan produk: Produk Format Rupiah','App\\Models\\Product','created',25,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Produk Format Rupiah\",\"price\":\"1250000.00\",\"original_price\":null,\"stock\":7,\"status\":\"active\",\"is_featured\":false,\"category_id\":8}}',NULL,'2026-08-06 00:25:50','2026-08-06 00:25:50'),(38,'produk','memperbarui produk: Sepatu Uji Lengkap','App\\Models\\Product','updated',21,'App\\Models\\User',1,'{\"attributes\":{\"price\":\"1.00\",\"stock\":1},\"old\":{\"price\":\"450000.00\",\"stock\":20}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(39,'produk','memperbarui produk: Sepatu Uji Lengkap','App\\Models\\Product','updated',21,'App\\Models\\User',1,'{\"attributes\":{\"price\":\"450000.00\",\"stock\":20},\"old\":{\"price\":\"1.00\",\"stock\":1}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(40,'kategori','menambahkan kategori: Kategori CSV','App\\Models\\Category','created',9,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Kategori CSV\",\"slug\":\"kategori-csv\",\"is_active\":true,\"sort_order\":9}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(41,'produk','menambahkan produk: Produk Dari CSV','App\\Models\\Product','created',26,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Produk Dari CSV\",\"price\":\"77000.00\",\"original_price\":null,\"stock\":15,\"status\":\"active\",\"is_featured\":false,\"category_id\":9}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(42,'produk','menghapus produk: Sepatu Uji Lengkap','App\\Models\\Product','deleted',21,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Uji Lengkap\",\"price\":\"450000.00\",\"original_price\":\"600000.00\",\"stock\":20,\"status\":\"active\",\"is_featured\":true,\"category_id\":7}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(43,'produk','menghapus produk: Sandal Uji Minimal','App\\Models\\Product','deleted',22,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sandal Uji Minimal\",\"price\":\"45000.00\",\"original_price\":null,\"stock\":120,\"status\":\"active\",\"is_featured\":false,\"category_id\":8}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(44,'produk','menghapus produk: Sepatu Uji Kategori Lama','App\\Models\\Product','deleted',23,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Uji Kategori Lama\",\"price\":\"99000.00\",\"original_price\":null,\"stock\":10,\"status\":\"inactive\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(45,'produk','menghapus produk: Produk Format Rupiah','App\\Models\\Product','deleted',25,'App\\Models\\User',1,'{\"old\":{\"name\":\"Produk Format Rupiah\",\"price\":\"1250000.00\",\"original_price\":null,\"stock\":7,\"status\":\"active\",\"is_featured\":false,\"category_id\":8}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(46,'produk','menghapus produk: Produk Dari CSV','App\\Models\\Product','deleted',26,'App\\Models\\User',1,'{\"old\":{\"name\":\"Produk Dari CSV\",\"price\":\"77000.00\",\"original_price\":null,\"stock\":15,\"status\":\"active\",\"is_featured\":false,\"category_id\":9}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(47,'kategori','menghapus kategori: Kategori CSV','App\\Models\\Category','deleted',9,'App\\Models\\User',1,'{\"old\":{\"name\":\"Kategori CSV\",\"slug\":\"kategori-csv\",\"is_active\":true,\"sort_order\":9}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(48,'kategori','menghapus kategori: Sandal Uji','App\\Models\\Category','deleted',8,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sandal Uji\",\"slug\":\"sandal-uji\",\"is_active\":true,\"sort_order\":8}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(49,'kategori','menghapus kategori: Sepatu Gunung','App\\Models\\Category','deleted',7,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Gunung\",\"slug\":\"sepatu-gunung\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 00:25:51','2026-08-06 00:25:51'),(50,'kategori','menambahkan kategori: Kategori Baru Uji','App\\Models\\Category','created',10,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Kategori Baru Uji\",\"slug\":\"kategori-baru-uji\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 01:00:28','2026-08-06 01:00:28'),(51,'produk','menambahkan produk: Sepatu SKU Test','App\\Models\\Product','created',27,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu SKU Test\",\"price\":\"250000.00\",\"original_price\":\"300000.00\",\"stock\":18,\"status\":\"active\",\"is_featured\":true,\"category_id\":10}}',NULL,'2026-08-06 01:00:28','2026-08-06 01:00:28'),(52,'produk','menambahkan produk: Sandal SKU Kosong','App\\Models\\Product','created',28,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sandal SKU Kosong\",\"price\":\"60000.00\",\"original_price\":null,\"stock\":20,\"status\":\"active\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 01:00:28','2026-08-06 01:00:28'),(53,'kategori','menambahkan kategori: Kategori Frontend Uji','App\\Models\\Category','created',11,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Kategori Frontend Uji\",\"slug\":\"kategori-frontend-uji\",\"is_active\":true,\"sort_order\":8}}',NULL,'2026-08-06 01:00:28','2026-08-06 01:00:28'),(54,'kategori','memperbarui kategori: Kategori Frontend Uji','App\\Models\\Category','updated',11,'App\\Models\\User',1,'{\"attributes\":{\"is_active\":false},\"old\":{\"is_active\":true}}',NULL,'2026-08-06 01:00:28','2026-08-06 01:00:28'),(55,'produk','menghapus produk: Sepatu SKU Test','App\\Models\\Product','deleted',27,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu SKU Test\",\"price\":\"250000.00\",\"original_price\":\"300000.00\",\"stock\":18,\"status\":\"active\",\"is_featured\":true,\"category_id\":10}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(56,'produk','menghapus produk: Sandal SKU Kosong','App\\Models\\Product','deleted',28,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sandal SKU Kosong\",\"price\":\"60000.00\",\"original_price\":null,\"stock\":20,\"status\":\"active\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(57,'kategori','menghapus kategori: Kategori Baru Uji','App\\Models\\Category','deleted',10,'App\\Models\\User',1,'{\"old\":{\"name\":\"Kategori Baru Uji\",\"slug\":\"kategori-baru-uji\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(58,'kategori','menghapus kategori: Kategori Frontend Uji','App\\Models\\Category','deleted',11,'App\\Models\\User',1,'{\"old\":{\"name\":\"Kategori Frontend Uji\",\"slug\":\"kategori-frontend-uji\",\"is_active\":false,\"sort_order\":8}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(59,'kategori','menambahkan kategori: Kategori Sementara Uji','App\\Models\\Category','created',12,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Kategori Sementara Uji\",\"slug\":\"kategori-sementara-uji\",\"is_active\":true,\"sort_order\":99}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(60,'kategori','menghapus kategori: Kategori Sementara Uji','App\\Models\\Category','deleted',12,'App\\Models\\User',1,'{\"old\":{\"name\":\"Kategori Sementara Uji\",\"slug\":\"kategori-sementara-uji\",\"is_active\":true,\"sort_order\":99}}',NULL,'2026-08-06 01:01:21','2026-08-06 01:01:21'),(61,'kategori','menambahkan kategori: Kategori Sementara Uji','App\\Models\\Category','created',13,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Kategori Sementara Uji\",\"slug\":\"kategori-sementara-uji\",\"is_active\":true,\"sort_order\":99}}',NULL,'2026-08-06 01:01:57','2026-08-06 01:01:57'),(62,'kategori','menghapus kategori: Kategori Sementara Uji','App\\Models\\Category','deleted',13,'App\\Models\\User',1,'{\"old\":{\"name\":\"Kategori Sementara Uji\",\"slug\":\"kategori-sementara-uji\",\"is_active\":true,\"sort_order\":99}}',NULL,'2026-08-06 01:01:57','2026-08-06 01:01:57'),(63,'kategori','menambahkan kategori: Sepatu Olahraga','App\\Models\\Category','created',14,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Olahraga\",\"slug\":\"sepatu-olahraga\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 01:43:37','2026-08-06 01:43:37'),(64,'produk','menambahkan produk: Sepatu Lari Pria Hitam','App\\Models\\Product','created',29,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Lari Pria Hitam\",\"price\":\"320000.00\",\"original_price\":\"400000.00\",\"stock\":24,\"status\":\"active\",\"is_featured\":true,\"category_id\":14}}',NULL,'2026-08-06 01:43:37','2026-08-06 01:43:37'),(65,'kategori','menambahkan kategori: Sepatu Formal','App\\Models\\Category','created',15,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Formal\",\"slug\":\"sepatu-formal\",\"is_active\":true,\"sort_order\":8}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(66,'produk','menambahkan produk: Sepatu Kantor Kulit','App\\Models\\Product','created',30,'App\\Models\\User',1,'{\"attributes\":{\"name\":\"Sepatu Kantor Kulit\",\"price\":\"450000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":15}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(67,'produk','menghapus produk: Sepatu Lari Pria Hitam','App\\Models\\Product','deleted',29,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Lari Pria Hitam\",\"price\":\"320000.00\",\"original_price\":\"400000.00\",\"stock\":24,\"status\":\"active\",\"is_featured\":true,\"category_id\":14}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(68,'produk','menghapus produk: Sepatu Kantor Kulit','App\\Models\\Product','deleted',30,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Kantor Kulit\",\"price\":\"450000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":15}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(69,'kategori','menghapus kategori: Sepatu Formal','App\\Models\\Category','deleted',15,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Formal\",\"slug\":\"sepatu-formal\",\"is_active\":true,\"sort_order\":8}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(70,'kategori','menghapus kategori: Sepatu Olahraga','App\\Models\\Category','deleted',14,'App\\Models\\User',1,'{\"old\":{\"name\":\"Sepatu Olahraga\",\"slug\":\"sepatu-olahraga\",\"is_active\":true,\"sort_order\":7}}',NULL,'2026-08-06 01:43:38','2026-08-06 01:43:38'),(71,'produk','menghapus produk: RECORD Titan Sepatu Pria Sport','App\\Models\\Product','deleted',17,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Titan Sepatu Pria Sport\",\"price\":\"175000.00\",\"original_price\":\"270000.00\",\"stock\":100,\"status\":\"active\",\"is_featured\":true,\"category_id\":6}}',NULL,'2026-08-06 02:00:32','2026-08-06 02:00:32'),(72,'produk','menghapus produk: RECORD Bloom Sepatu Wanita Casual','App\\Models\\Product','deleted',16,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Bloom Sepatu Wanita Casual\",\"price\":\"115000.00\",\"original_price\":null,\"stock\":130,\"status\":\"active\",\"is_featured\":false,\"category_id\":5}}',NULL,'2026-08-06 02:00:35','2026-08-06 02:00:35'),(73,'produk','menghapus produk: RECORD Aria Sepatu Wanita Sport','App\\Models\\Product','deleted',15,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Aria Sepatu Wanita Sport\",\"price\":\"155000.00\",\"original_price\":\"230000.00\",\"stock\":90,\"status\":\"active\",\"is_featured\":false,\"category_id\":5}}',NULL,'2026-08-06 02:00:40','2026-08-06 02:00:40'),(74,'produk','menghapus produk: RECORD Luna Sepatu Wanita Pink','App\\Models\\Product','deleted',14,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Luna Sepatu Wanita Pink\",\"price\":\"135000.00\",\"original_price\":\"210000.00\",\"stock\":110,\"status\":\"active\",\"is_featured\":true,\"category_id\":5}}',NULL,'2026-08-06 02:00:43','2026-08-06 02:00:43'),(75,'produk','menghapus produk: RECORD Drift Sepatu Sneakers','App\\Models\\Product','deleted',13,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Drift Sepatu Sneakers\",\"price\":\"195000.00\",\"original_price\":\"300000.00\",\"stock\":70,\"status\":\"active\",\"is_featured\":false,\"category_id\":4}}',NULL,'2026-08-06 02:00:45','2026-08-06 02:00:45'),(76,'produk','menghapus produk: RECORD Street Sepatu Casual','App\\Models\\Product','deleted',12,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Street Sepatu Casual\",\"price\":\"155000.00\",\"original_price\":null,\"stock\":100,\"status\":\"active\",\"is_featured\":false,\"category_id\":4}}',NULL,'2026-08-06 02:00:48','2026-08-06 02:00:48'),(77,'produk','menghapus produk: RECORD Urban Sepatu Lifestyle','App\\Models\\Product','deleted',11,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Urban Sepatu Lifestyle\",\"price\":\"175000.00\",\"original_price\":\"260000.00\",\"stock\":90,\"status\":\"active\",\"is_featured\":true,\"category_id\":4}}',NULL,'2026-08-06 02:00:51','2026-08-06 02:00:51'),(78,'produk','menghapus produk: RECORD Tiny Steps Sepatu Balita','App\\Models\\Product','deleted',7,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Tiny Steps Sepatu Balita\",\"price\":\"65000.00\",\"original_price\":null,\"stock\":150,\"status\":\"active\",\"is_featured\":false,\"category_id\":2}}',NULL,'2026-08-06 02:00:55','2026-08-06 02:00:55'),(79,'produk','menghapus produk: RECORD Kiddo Sepatu Anak Casual','App\\Models\\Product','deleted',6,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Kiddo Sepatu Anak Casual\",\"price\":\"75000.00\",\"original_price\":\"120000.00\",\"stock\":180,\"status\":\"active\",\"is_featured\":false,\"category_id\":2}}',NULL,'2026-08-06 02:00:59','2026-08-06 02:00:59'),(80,'produk','menghapus produk: RECORD Junior Sepatu Anak Sport','App\\Models\\Product','deleted',5,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Junior Sepatu Anak Sport\",\"price\":\"89000.00\",\"original_price\":\"150000.00\",\"stock\":200,\"status\":\"active\",\"is_featured\":true,\"category_id\":2}}',NULL,'2026-08-06 02:01:02','2026-08-06 02:01:02'),(81,'produk','menghapus produk: RECORD Fury Sepatu Olahraga','App\\Models\\Product','deleted',4,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Fury Sepatu Olahraga\",\"price\":\"145000.00\",\"original_price\":null,\"stock\":120,\"status\":\"active\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 02:01:07','2026-08-06 02:01:07'),(82,'produk','menghapus produk: RECORD Venom Running Shoes','App\\Models\\Product','deleted',3,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Venom Running Shoes\",\"price\":\"189000.00\",\"original_price\":\"280000.00\",\"stock\":80,\"status\":\"active\",\"is_featured\":false,\"category_id\":1}}',NULL,'2026-08-06 02:01:10','2026-08-06 02:01:10'),(83,'produk','menghapus produk: RECORD Blaze Sepatu Sport NAVY PUTIH','App\\Models\\Product','deleted',2,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Blaze Sepatu Sport NAVY PUTIH\",\"price\":\"165000.00\",\"original_price\":\"250000.00\",\"stock\":100,\"status\":\"active\",\"is_featured\":true,\"category_id\":1}}',NULL,'2026-08-06 02:01:13','2026-08-06 02:01:13'),(84,'produk','menghapus produk: RECORD Suga Sepatu Dewasa Premium HITAM PUTIH','App\\Models\\Product','deleted',1,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD Suga Sepatu Dewasa Premium HITAM PUTIH\",\"price\":\"120000.00\",\"original_price\":\"220000.00\",\"stock\":150,\"status\":\"active\",\"is_featured\":true,\"category_id\":1}}',NULL,'2026-08-06 02:01:16','2026-08-06 02:01:16'),(85,'produk','menghapus produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','deleted',20,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN\",\"price\":\"140000.00\",\"original_price\":\"150000.00\",\"stock\":92,\"status\":\"active\",\"is_featured\":true,\"category_id\":3}}',NULL,'2026-08-06 03:03:34','2026-08-06 03:03:34'),(86,'produk','menghapus produk: RECORD THEON BTS SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','deleted',19,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD THEON BTS SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH PEREKAT 30-37 4-13TH\",\"price\":\"145000.00\",\"original_price\":null,\"stock\":138,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:04:08','2026-08-06 03:04:08'),(87,'produk','menghapus produk: RECORD JAYDEN BTS SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','deleted',10,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD JAYDEN BTS SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH PEREKAT 30-37 4-13TH\",\"price\":\"85000.00\",\"original_price\":\"85000.00\",\"stock\":118,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:04:54','2026-08-06 03:04:54'),(88,'produk','menghapus produk: RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','deleted',9,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH\",\"price\":\"125000.00\",\"original_price\":\"200000.00\",\"stock\":248,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:04:58','2026-08-06 03:04:58'),(89,'produk','menghapus produk: RECORD HAWK SEPATU ANAK TK-SMP 4-13 TAHUN PEREKAT FULL BLACK HITAM PUTIH SEKOLAH 30-37 COWOK/CEWEK','App\\Models\\Product','deleted',8,'App\\Models\\User',6,'{\"old\":{\"name\":\"RECORD HAWK SEPATU ANAK TK-SMP 4-13 TAHUN PEREKAT FULL BLACK HITAM PUTIH SEKOLAH 30-37 COWOK\\/CEWEK\",\"price\":\"95000.00\",\"original_price\":\"160000.00\",\"stock\":133,\"status\":\"active\",\"is_featured\":true,\"category_id\":3}}',NULL,'2026-08-06 03:05:00','2026-08-06 03:05:00'),(90,'produk','menambahkan produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','created',31,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":true,\"category_id\":3}}',NULL,'2026-08-06 03:05:41','2026-08-06 03:05:41'),(91,'produk','menambahkan produk: RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','created',32,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":true,\"category_id\":3}}',NULL,'2026-08-06 03:05:41','2026-08-06 03:05:41'),(92,'produk','menambahkan produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','created',33,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:05:42','2026-08-06 03:05:42'),(93,'produk','menambahkan produk: RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','created',34,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:05:42','2026-08-06 03:05:42'),(94,'produk','menambahkan produk: RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','created',35,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":3}}',NULL,'2026-08-06 03:05:42','2026-08-06 03:05:42'),(95,'produk','menambahkan produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','created',36,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":4}}',NULL,'2026-08-06 03:05:42','2026-08-06 03:05:42'),(96,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:06:57','2026-08-06 03:06:57'),(97,'produk','memperbarui produk: RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',35,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:07:42','2026-08-06 03:07:42'),(98,'produk','memperbarui produk: RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',34,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:08:22','2026-08-06 03:08:22'),(99,'produk','memperbarui produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',33,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:09:19','2026-08-06 03:09:19'),(100,'produk','memperbarui produk: RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','updated',32,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:09:43','2026-08-06 03:09:43'),(101,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-06 03:10:11','2026-08-06 03:10:11'),(102,'kategori','memperbarui kategori: Sepatu Sport','App\\Models\\Category','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":2},\"old\":{\"sort_order\":1}}',NULL,'2026-08-06 03:13:32','2026-08-06 03:13:32'),(103,'kategori','memperbarui kategori: Sepatu Anak','App\\Models\\Category','updated',2,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":1},\"old\":{\"sort_order\":2}}',NULL,'2026-08-06 03:13:32','2026-08-06 03:13:32'),(104,'kategori','memperbarui kategori: Sepatu Anak','App\\Models\\Category','updated',2,'App\\Models\\User',6,'{\"attributes\":{\"is_active\":false},\"old\":{\"is_active\":true}}',NULL,'2026-08-06 06:33:37','2026-08-06 06:33:37'),(105,'kategori','memperbarui kategori: Sepatu Anak','App\\Models\\Category','updated',2,'App\\Models\\User',6,'{\"attributes\":{\"is_active\":true},\"old\":{\"is_active\":false}}',NULL,'2026-08-06 06:33:40','2026-08-06 06:33:40'),(106,'kategori','memperbarui kategori: Sepatu Sekolah','App\\Models\\Category','updated',3,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":2},\"old\":{\"sort_order\":3}}',NULL,'2026-08-06 19:14:38','2026-08-06 19:14:38'),(107,'kategori','memperbarui kategori: Sepatu Sport','App\\Models\\Category','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":3},\"old\":{\"sort_order\":2}}',NULL,'2026-08-06 19:14:38','2026-08-06 19:14:38'),(108,'kategori','memperbarui kategori: Sepatu Lifestyle','App\\Models\\Category','updated',4,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":3},\"old\":{\"sort_order\":4}}',NULL,'2026-08-06 19:14:42','2026-08-06 19:14:42'),(109,'kategori','memperbarui kategori: Sepatu Sport','App\\Models\\Category','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":4},\"old\":{\"sort_order\":3}}',NULL,'2026-08-06 19:14:42','2026-08-06 19:14:42'),(110,'kategori','memperbarui kategori: Sepatu Cowok','App\\Models\\Category','updated',6,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":5},\"old\":{\"sort_order\":6}}',NULL,'2026-08-06 19:14:46','2026-08-06 19:14:46'),(111,'kategori','memperbarui kategori: Sepatu Cewek','App\\Models\\Category','updated',5,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":6},\"old\":{\"sort_order\":5}}',NULL,'2026-08-06 19:14:46','2026-08-06 19:14:46'),(112,'kategori','memperbarui kategori: Sepatu Cowok','App\\Models\\Category','updated',6,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":4},\"old\":{\"sort_order\":5}}',NULL,'2026-08-06 19:14:51','2026-08-06 19:14:51'),(113,'kategori','memperbarui kategori: Sepatu Sport','App\\Models\\Category','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":5},\"old\":{\"sort_order\":4}}',NULL,'2026-08-06 19:14:51','2026-08-06 19:14:51'),(114,'kategori','memperbarui kategori: Sepatu Cewek','App\\Models\\Category','updated',5,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":5},\"old\":{\"sort_order\":6}}',NULL,'2026-08-06 19:14:54','2026-08-06 19:14:54'),(115,'kategori','memperbarui kategori: Sepatu Sport','App\\Models\\Category','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":6},\"old\":{\"sort_order\":5}}',NULL,'2026-08-06 19:14:55','2026-08-06 19:14:55'),(116,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',6,'{\"attributes\":{\"stock\":180},\"old\":{\"stock\":0}}',NULL,'2026-08-06 20:17:45','2026-08-06 20:17:45'),(117,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":-1}}',NULL,'2026-08-07 02:32:21','2026-08-07 02:32:21'),(118,'produk','memperbarui produk: RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','updated',32,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":0}}',NULL,'2026-08-07 02:32:22','2026-08-07 02:32:22'),(119,'produk','memperbarui produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',33,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":0}}',NULL,'2026-08-07 02:32:22','2026-08-07 02:32:22'),(120,'produk','memperbarui produk: RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',34,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":0}}',NULL,'2026-08-07 02:32:22','2026-08-07 02:32:22'),(121,'produk','memperbarui produk: RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',35,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":0}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(122,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',6,'{\"attributes\":{\"stock\":10},\"old\":{\"stock\":180}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(123,'produk','menambahkan produk: RECORD FOGO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','created',37,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD FOGO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":4}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(124,'produk','menambahkan produk: RECORD NEUTRA SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','created',38,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD NEUTRA SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":4}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(125,'produk','menambahkan produk: RECORD SUPER KIDS19 FASHIONABLE SEPATU LAMPU LED ANAK LAKI PAUD 1-7THN  VELKRO MOTO BIKE  SIZE 26-31','App\\Models\\Product','created',39,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD SUPER KIDS19 FASHIONABLE SEPATU LAMPU LED ANAK LAKI PAUD 1-7THN  VELKRO MOTO BIKE  SIZE 26-31\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":2}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(126,'produk','menambahkan produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','created',40,'App\\Models\\User',6,'{\"attributes\":{\"name\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"price\":\"250000.00\",\"original_price\":null,\"stock\":10,\"status\":\"active\",\"is_featured\":false,\"category_id\":5}}',NULL,'2026-08-07 02:32:23','2026-08-07 02:32:23'),(127,'pengguna','menambahkan akun: Uji R_Pay','App\\Models\\User','created',16,NULL,NULL,'{\"attributes\":{\"name\":\"Uji R_Pay\",\"email\":\"uji-rpay@contoh.test\",\"phone\":null,\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-08 00:18:54','2026-08-08 00:18:54'),(128,'pengguna','menghapus akun: Uji R_Pay','App\\Models\\User','deleted',16,NULL,NULL,'{\"old\":{\"name\":\"Uji R_Pay\",\"email\":\"uji-rpay@contoh.test\",\"phone\":null,\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-08 00:18:57','2026-08-08 00:18:57'),(131,'rpaywithdrawal','memperbarui RpayWithdrawal: #1','App\\Models\\RpayWithdrawal','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"rejected\",\"admin_notes\":\"Nama rekening tidak cocok dengan data akun.\",\"processed_by\":6,\"processed_at\":\"2026-08-08T07:44:17.000000Z\",\"updated_at\":\"2026-08-08T07:44:17.000000Z\"},\"old\":{\"status\":\"pending\",\"admin_notes\":null,\"processed_by\":null,\"processed_at\":null,\"updated_at\":\"2026-08-08T07:43:04.000000Z\"}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(132,'pesanan','menghapus pesanan: UJI-ALUR-1','App\\Models\\Order','deleted',20,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"370000.00\"}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(133,'pesanan','menghapus pesanan: UJI-ALUR-2','App\\Models\\Order','deleted',21,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"110000.00\"}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(134,'pesanan','menghapus pesanan: UJI-ALUR-3','App\\Models\\Order','deleted',22,'App\\Models\\User',6,'{\"old\":{\"status\":\"pending\",\"payment_status\":\"unpaid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"55000.00\"}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(135,'pesanan','menghapus pesanan: UJI-ALUR-BAYAR','App\\Models\\Order','deleted',23,'App\\Models\\User',6,'{\"old\":{\"status\":\"processing\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"100000.00\"}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(136,'pengguna','menghapus akun: Pembeli Uji','App\\Models\\User','deleted',17,'App\\Models\\User',6,'{\"old\":{\"name\":\"Pembeli Uji\",\"email\":\"uji-alur@contoh.test\",\"phone\":\"081234567890\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-08 00:44:17','2026-08-08 00:44:17'),(138,'pesanan','menghapus pesanan: UJI-TUKAR-1','App\\Models\\Order','deleted',30,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"215000.00\"}}',NULL,'2026-08-08 01:52:53','2026-08-08 01:52:53'),(139,'pesanan','menghapus pesanan: UJI-TUKAR-2','App\\Models\\Order','deleted',31,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"110000.00\"}}',NULL,'2026-08-08 01:52:53','2026-08-08 01:52:53'),(140,'pengguna','menghapus akun: Uji Tukar','App\\Models\\User','deleted',23,'App\\Models\\User',6,'{\"old\":{\"name\":\"Uji Tukar\",\"email\":\"uji-tukar@contoh.test\",\"phone\":\"0812000555\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-08 01:52:53','2026-08-08 01:52:53'),(143,'pesanan','menambahkan pesanan: UJI-TAHAP-2','App\\Models\\Order','created',33,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"160000.00\"}}',NULL,'2026-08-08 03:44:59','2026-08-08 03:44:59'),(144,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',6,'{\"attributes\":{\"stock\":36},\"old\":{\"stock\":10}}',NULL,'2026-08-08 04:15:16','2026-08-08 04:15:16'),(148,'pesanan','menghapus pesanan: UJI-KIRIM-1','App\\Models\\Order','deleted',47,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"215000.00\"}}',NULL,'2026-08-08 04:31:12','2026-08-08 04:31:12'),(149,'pengguna','menghapus akun: Uji Kirim','App\\Models\\User','deleted',32,'App\\Models\\User',6,'{\"old\":{\"name\":\"Uji Kirim\",\"email\":\"uji-kirim@contoh.test\",\"phone\":\"0812222000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-08 04:31:12','2026-08-08 04:31:12'),(155,'pengembalian','mengajukan pengembalian pesanan: ORD-20260803-0003','App\\Models\\OrderReturn','created',20,'App\\Models\\User',9,'{\"alasan\":\"kurang_lengkap\",\"penyelesaian\":\"refund\"}',NULL,'2026-08-09 00:14:11','2026-08-09 00:14:11'),(156,'pengembalian','mengirim balik barang pengembalian: ORD-20260803-0003','App\\Models\\OrderReturn','updated',20,'App\\Models\\User',9,'{\"kurir\":\"JNE 2321321312\",\"resi\":\"JP21312312\"}',NULL,'2026-08-09 00:17:39','2026-08-09 00:17:39'),(157,'pengguna','menambahkan akun: Uji Rupiah','App\\Models\\User','created',36,NULL,NULL,'{\"attributes\":{\"name\":\"Uji Rupiah\",\"email\":\"uji-rp@contoh.test\",\"phone\":\"0812666000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-09 00:31:57','2026-08-09 00:31:57'),(158,'pesanan','menambahkan pesanan: UJI-RP-1','App\\Models\\Order','created',56,NULL,NULL,'{\"attributes\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"165000.00\"}}',NULL,'2026-08-09 00:31:58','2026-08-09 00:31:58'),(159,'pesanan','menghapus pesanan: UJI-RP-1','App\\Models\\Order','deleted',56,'App\\Models\\User',6,'{\"old\":{\"status\":\"shipped\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"165000.00\"}}',NULL,'2026-08-09 00:32:45','2026-08-09 00:32:45'),(160,'pengguna','menghapus akun: Uji Rupiah','App\\Models\\User','deleted',36,'App\\Models\\User',6,'{\"old\":{\"name\":\"Uji Rupiah\",\"email\":\"uji-rp@contoh.test\",\"phone\":\"0812666000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-09 00:32:45','2026-08-09 00:32:45'),(162,'rpay','mengajukan pencairan R_Pay: WD260809RKATD','App\\Models\\RpayWithdrawal','created',3,'App\\Models\\User',9,'{\"nominal\":\"127500\",\"bank\":\"BCA\"}',NULL,'2026-08-09 00:36:59','2026-08-09 00:36:59'),(163,'rpaywithdrawal','memperbarui RpayWithdrawal: #3','App\\Models\\RpayWithdrawal','updated',3,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"completed\",\"processed_by\":6,\"processed_at\":\"2026-08-09T07:37:35.000000Z\",\"updated_at\":\"2026-08-09T07:37:36.000000Z\"},\"old\":{\"status\":\"pending\",\"processed_by\":null,\"processed_at\":null,\"updated_at\":\"2026-08-09T07:36:59.000000Z\"}}',NULL,'2026-08-09 00:37:36','2026-08-09 00:37:36'),(164,'pengembalian','mengajukan pengembalian pesanan: ORD-20260803-0002','App\\Models\\OrderReturn','created',22,'App\\Models\\User',9,'{\"alasan\":\"berubah_pikiran\",\"penyelesaian\":\"refund\"}',NULL,'2026-08-09 01:43:38','2026-08-09 01:43:38'),(165,'pesanan','memperbarui pesanan: ORD-20260805-0003','App\\Models\\Order','updated',7,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"processing\",\"payment_status\":\"paid\"},\"old\":{\"status\":\"pending\",\"payment_status\":\"pending_verification\"}}',NULL,'2026-08-09 19:17:34','2026-08-09 19:17:34'),(166,'pesanan','memperbarui pesanan: ORD-20260805-0004','App\\Models\\Order','updated',8,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"processing\",\"payment_status\":\"paid\"},\"old\":{\"status\":\"pending\",\"payment_status\":\"pending_verification\"}}',NULL,'2026-08-09 19:17:35','2026-08-09 19:17:35'),(167,'pesanan','memperbarui pesanan: ORD-20260805-0005','App\\Models\\Order','updated',9,'App\\Models\\User',6,'{\"attributes\":{\"payment_status\":\"failed\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-09 19:17:35','2026-08-09 19:17:35'),(168,'pesanan','memperbarui pesanan: ORD-20260805-0006','App\\Models\\Order','updated',10,'App\\Models\\User',6,'{\"attributes\":{\"payment_status\":\"failed\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-09 19:17:35','2026-08-09 19:17:35'),(169,'pesanan','memperbarui pesanan: ORD-20260805-0007','App\\Models\\Order','updated',11,'App\\Models\\User',6,'{\"attributes\":{\"payment_status\":\"paid\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-09 19:17:36','2026-08-09 19:17:36'),(170,'pesanan','memperbarui pesanan: ORD-20260810-0001','App\\Models\\Order','updated',57,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"shipped\",\"tracking_number\":\"WYB-1786328421759\",\"courier\":\"jne\"},\"old\":{\"status\":\"processing\",\"tracking_number\":null,\"courier\":\"GoSend Instant\"}}',NULL,'2026-08-09 19:20:23','2026-08-09 19:20:23'),(171,'pengembalian','mengajukan pengembalian pesanan: ORD-20260810-0001','App\\Models\\OrderReturn','created',23,'App\\Models\\User',9,'{\"alasan\":\"berubah_pikiran\",\"penyelesaian\":\"refund\"}',NULL,'2026-08-10 00:50:43','2026-08-10 00:50:43'),(172,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',6,'{\"attributes\":{\"stock\":34},\"old\":{\"stock\":32}}',NULL,'2026-08-10 01:28:23','2026-08-10 01:28:23'),(173,'pengguna','menambahkan akun: Budi Santoso','App\\Models\\User','created',41,NULL,NULL,'{\"attributes\":{\"name\":\"Budi Santoso\",\"email\":\"ref-budi@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:20','2026-08-10 02:05:20'),(174,'pengguna','memperbarui akun: Budi Santoso','App\\Models\\User','updated',41,NULL,NULL,'{\"attributes\":{\"is_blocked\":false},\"old\":{\"is_blocked\":null}}',NULL,'2026-08-10 02:05:20','2026-08-10 02:05:20'),(175,'pengguna','menambahkan akun:   Siti  Nur\'aini-Zahra Wulandari ','App\\Models\\User','created',42,NULL,NULL,'{\"attributes\":{\"name\":\"  Siti  Nur\'aini-Zahra Wulandari \",\"email\":\"ref-aneh@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:20','2026-08-10 02:05:20'),(176,'pengguna','memperbarui akun:   Siti  Nur\'aini-Zahra Wulandari ','App\\Models\\User','updated',42,NULL,NULL,'{\"attributes\":{\"is_blocked\":false},\"old\":{\"is_blocked\":null}}',NULL,'2026-08-10 02:05:20','2026-08-10 02:05:20'),(177,'pengguna','menambahkan akun: Budi Santoso','App\\Models\\User','created',43,NULL,NULL,'{\"attributes\":{\"name\":\"Budi Santoso\",\"email\":\"ref-kembar@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(178,'pengguna','memperbarui akun: Budi Santoso','App\\Models\\User','updated',43,NULL,NULL,'{\"attributes\":{\"is_blocked\":false},\"old\":{\"is_blocked\":null}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(179,'pengguna','menambahkan akun: Pemakai Kode','App\\Models\\User','created',44,NULL,NULL,'{\"attributes\":{\"name\":\"Pemakai Kode\",\"email\":\"ref-pakai@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(180,'pesanan','menambahkan pesanan: REF-PTKKH7','App\\Models\\Order','created',58,NULL,NULL,'{\"attributes\":{\"status\":\"pending\",\"payment_status\":\"unpaid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"320000.00\"}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(181,'pesanan','memperbarui pesanan: REF-PTKKH7','App\\Models\\Order','updated',58,NULL,NULL,'{\"attributes\":{\"payment_status\":\"paid\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(182,'pesanan','memperbarui pesanan: REF-PTKKH7','App\\Models\\Order','updated',58,NULL,NULL,'{\"attributes\":{\"status\":\"cancelled\"},\"old\":{\"status\":\"pending\"}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(183,'pesanan','memperbarui pesanan: REF-PTKKH7','App\\Models\\Order','updated',58,NULL,NULL,'{\"attributes\":{\"status\":\"completed\"},\"old\":{\"status\":\"cancelled\"}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(184,'pengguna','memperbarui akun: Budi Santoso','App\\Models\\User','updated',41,NULL,NULL,'{\"attributes\":{\"is_blocked\":true},\"old\":{\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(185,'pengguna','memperbarui akun: Budi Santoso','App\\Models\\User','updated',41,NULL,NULL,'{\"attributes\":{\"is_blocked\":false},\"old\":{\"is_blocked\":true}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(186,'pengguna','menghapus akun: Budi Santoso','App\\Models\\User','deleted',41,NULL,NULL,'{\"old\":{\"name\":\"Budi Santoso\",\"email\":\"ref-budi@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(187,'pengguna','menghapus akun:   Siti  Nur\'aini-Zahra Wulandari ','App\\Models\\User','deleted',42,NULL,NULL,'{\"old\":{\"name\":\"  Siti  Nur\'aini-Zahra Wulandari \",\"email\":\"ref-aneh@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(188,'pengguna','menghapus akun: Budi Santoso','App\\Models\\User','deleted',43,NULL,NULL,'{\"old\":{\"name\":\"Budi Santoso\",\"email\":\"ref-kembar@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:21','2026-08-10 02:05:21'),(189,'pengguna','menghapus akun: Pemakai Kode','App\\Models\\User','deleted',44,NULL,NULL,'{\"old\":{\"name\":\"Pemakai Kode\",\"email\":\"ref-pakai@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:05:22','2026-08-10 02:05:22'),(190,'pengguna','menambahkan akun: Andi Pratama','App\\Models\\User','created',45,NULL,NULL,'{\"attributes\":{\"name\":\"Andi Pratama\",\"email\":\"kom-pemilik@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:08:45','2026-08-10 02:08:45'),(191,'pesanan','menambahkan pesanan: KOM-AWAL','App\\Models\\Order','created',59,NULL,NULL,'{\"attributes\":{\"status\":\"completed\",\"payment_status\":\"paid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"110000.00\"}}',NULL,'2026-08-10 02:08:45','2026-08-10 02:08:45'),(192,'pengguna','menambahkan akun: Rina Sari','App\\Models\\User','created',46,NULL,NULL,'{\"attributes\":{\"name\":\"Rina Sari\",\"email\":\"kom-pembeli@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(193,'pesanan','menambahkan pesanan: KOM-PAKAI-1','App\\Models\\Order','created',60,NULL,NULL,'{\"attributes\":{\"status\":\"pending\",\"payment_status\":\"unpaid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"311000.00\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(194,'pesanan','memperbarui pesanan: KOM-PAKAI-1','App\\Models\\Order','updated',60,NULL,NULL,'{\"attributes\":{\"payment_status\":\"paid\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(195,'pesanan','memperbarui pesanan: KOM-PAKAI-1','App\\Models\\Order','updated',60,NULL,NULL,'{\"attributes\":{\"status\":\"processing\"},\"old\":{\"status\":\"pending\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(196,'pesanan','memperbarui pesanan: KOM-PAKAI-1','App\\Models\\Order','updated',60,NULL,NULL,'{\"attributes\":{\"tracking_number\":\"JP123\"},\"old\":{\"tracking_number\":null}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(197,'pesanan','memperbarui pesanan: KOM-PAKAI-1','App\\Models\\Order','updated',60,NULL,NULL,'{\"attributes\":{\"status\":\"cancelled\"},\"old\":{\"status\":\"processing\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(198,'pesanan','menambahkan pesanan: KOM-PAKAI-2','App\\Models\\Order','created',61,NULL,NULL,'{\"attributes\":{\"status\":\"pending\",\"payment_status\":\"unpaid\",\"tracking_number\":null,\"courier\":\"JNE\",\"grand_total\":\"291000.00\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(199,'pesanan','memperbarui pesanan: KOM-PAKAI-2','App\\Models\\Order','updated',61,NULL,NULL,'{\"attributes\":{\"payment_status\":\"paid\"},\"old\":{\"payment_status\":\"unpaid\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(200,'pesanan','memperbarui pesanan: KOM-PAKAI-2','App\\Models\\Order','updated',61,NULL,NULL,'{\"attributes\":{\"status\":\"cancelled\"},\"old\":{\"status\":\"pending\"}}',NULL,'2026-08-10 02:08:46','2026-08-10 02:08:46'),(201,'pengguna','menghapus akun: Andi Pratama','App\\Models\\User','deleted',45,NULL,NULL,'{\"old\":{\"name\":\"Andi Pratama\",\"email\":\"kom-pemilik@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:08:47','2026-08-10 02:08:47'),(202,'pengguna','menghapus akun: Rina Sari','App\\Models\\User','deleted',46,NULL,NULL,'{\"old\":{\"name\":\"Rina Sari\",\"email\":\"kom-pembeli@contoh.test\",\"phone\":\"0812000000\",\"role\":\"customer\",\"is_blocked\":false}}',NULL,'2026-08-10 02:08:47','2026-08-10 02:08:47'),(203,'pesanan','memperbarui pesanan: ORD-20260810-0002','App\\Models\\Order','updated',72,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"shipped\",\"tracking_number\":\"WYB-1786354394454\",\"courier\":\"jne\"},\"old\":{\"status\":\"processing\",\"tracking_number\":null,\"courier\":\"AnterAja Reguler\"}}',NULL,'2026-08-10 02:33:15','2026-08-10 02:33:15'),(204,'pesanan','memperbarui pesanan: ORD-20260810-0005','App\\Models\\Order','updated',93,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"shipped\",\"tracking_number\":\"WYB-1786356651897\",\"courier\":\"jne\"},\"old\":{\"status\":\"processing\",\"tracking_number\":null,\"courier\":\"AnterAja Reguler\"}}',NULL,'2026-08-10 03:10:52','2026-08-10 03:10:52'),(205,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-10 19:09:38','2026-08-10 19:09:38'),(206,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":false},\"old\":{\"is_featured\":true}}',NULL,'2026-08-10 19:09:39','2026-08-10 19:09:39'),(207,'produk','memperbarui produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',33,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-10 19:14:11','2026-08-10 19:14:11'),(208,'produk','memperbarui produk: RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',34,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-10 19:14:13','2026-08-10 19:14:13'),(209,'produk','memperbarui produk: RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',35,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-10 19:14:16','2026-08-10 19:14:16'),(210,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"is_featured\":false},\"old\":{\"is_featured\":true}}',NULL,'2026-08-10 19:14:26','2026-08-10 19:14:26'),(211,'kategori','menghapus kategori: Sepatu Sport','App\\Models\\Category','deleted',1,'App\\Models\\User',7,'{\"old\":{\"name\":\"Sepatu Sport\",\"slug\":\"sepatu-sport\",\"is_active\":true,\"sort_order\":6}}',NULL,'2026-08-11 01:10:03','2026-08-11 01:10:03'),(214,'pengembalian','mengajukan pengembalian pesanan: ORD-20260810-0001','App\\Models\\OrderReturn','created',27,'App\\Models\\User',9,'{\"alasan\":\"ukuran_tidak_pas\",\"penyelesaian\":\"refund\"}',NULL,'2026-08-11 02:54:06','2026-08-11 02:54:06'),(219,'ulasan','menilai 1 produk dari pesanan: ORD-20260810-0005','App\\Models\\Order','created',93,'App\\Models\\User',59,'{\"jumlah\":1}',NULL,'2026-08-11 19:31:53','2026-08-11 19:31:53'),(220,'ulasan','menyembunyikan ulasan','App\\Models\\ProductReview',NULL,14,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"alasan\":\"Menyebut nama karyawan secara langsung\"}',NULL,'2026-08-11 19:34:16','2026-08-11 19:34:16'),(221,'ulasan','menampilkan kembali ulasan','App\\Models\\ProductReview',NULL,14,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"alasan\":null}',NULL,'2026-08-11 19:35:41','2026-08-11 19:35:41'),(222,'ulasan','menyembunyikan ulasan','App\\Models\\ProductReview',NULL,14,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"alasan\":\"Uji pesan sukses\"}',NULL,'2026-08-11 19:36:10','2026-08-11 19:36:10'),(223,'ulasan','menampilkan kembali ulasan','App\\Models\\ProductReview',NULL,14,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"alasan\":null}',NULL,'2026-08-11 19:36:10','2026-08-11 19:36:10'),(224,'ulasan','menilai 1 produk dari pesanan: ORD-20260810-0002','App\\Models\\Order','created',72,'App\\Models\\User',9,'{\"jumlah\":1}',NULL,'2026-08-11 19:38:34','2026-08-11 19:38:34'),(225,'produk','memperbarui produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','updated',40,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-11 20:33:30','2026-08-11 20:33:30'),(226,'produk','memperbarui produk: RECORD SUPER KIDS19 FASHIONABLE SEPATU LAMPU LED ANAK LAKI PAUD 1-7THN  VELKRO MOTO BIKE  SIZE 26-31','App\\Models\\Product','updated',39,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-11 20:35:30','2026-08-11 20:35:30'),(227,'produk','memperbarui produk: RECORD NEUTRA SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',38,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-11 20:36:38','2026-08-11 20:36:38'),(228,'produk','memperbarui produk: RECORD FOGO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',37,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-11 20:37:12','2026-08-11 20:37:12'),(229,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',6,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-11 20:37:48','2026-08-11 20:37:48'),(230,'ulasan','menilai 1 produk dari pesanan: ORD-20260810-0002','App\\Models\\Order','created',72,'App\\Models\\User',9,'{\"jumlah\":1}',NULL,'2026-08-11 20:43:44','2026-08-11 20:43:44'),(231,'banner','menambahkan banner: Video','App\\Models\\Banner','created',7,'App\\Models\\User',6,'{\"attributes\":{\"title\":\"Video\",\"position\":\"hero\",\"is_active\":true,\"sort_order\":3,\"link\":\"\\/products\",\"image\":\"banners\\/7h0iStoAAN217dBkQD4YRGFCnlBMHaPn6VyD4vl3.mp4\"}}',NULL,'2026-08-11 20:48:17','2026-08-11 20:48:17'),(234,'banner','memperbarui banner: Video','App\\Models\\Banner','updated',7,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/sopdDTSCaPREglwPWCyFwgM3S41L1LfFZY1DHrez.mp4\"},\"old\":{\"image\":\"banners\\/7h0iStoAAN217dBkQD4YRGFCnlBMHaPn6VyD4vl3.mp4\"}}',NULL,'2026-08-11 21:49:29','2026-08-11 21:49:29'),(238,'banner','memperbarui banner: Video','App\\Models\\Banner','updated',7,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/dki4HHXTz5Dc2tIrYlmiY0UNpEdn94TpOqBJVg21.mp4\"},\"old\":{\"image\":\"banners\\/sopdDTSCaPREglwPWCyFwgM3S41L1LfFZY1DHrez.mp4\"}}',NULL,'2026-08-11 22:50:09','2026-08-11 22:50:09'),(239,'banner','memperbarui banner: Video','App\\Models\\Banner','updated',7,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/JYOYgXHZceBjPRNsuIKXRiUIQ2CGzZFRQm48zyQY.mp4\"},\"old\":{\"image\":\"banners\\/sopdDTSCaPREglwPWCyFwgM3S41L1LfFZY1DHrez.mp4\"}}',NULL,'2026-08-11 22:50:09','2026-08-11 22:50:09'),(240,'banner','menghapus banner: Video','App\\Models\\Banner','deleted',7,'App\\Models\\User',6,'{\"old\":{\"title\":\"Video\",\"position\":\"hero\",\"is_active\":true,\"sort_order\":3,\"link\":\"\\/products\",\"image\":\"banners\\/dki4HHXTz5Dc2tIrYlmiY0UNpEdn94TpOqBJVg21.mp4\"}}',NULL,'2026-08-11 23:23:17','2026-08-11 23:23:17'),(241,'banner','menghapus banner: Video','App\\Models\\Banner','deleted',7,'App\\Models\\User',6,'{\"old\":{\"title\":\"Video\",\"position\":\"hero\",\"is_active\":true,\"sort_order\":3,\"link\":\"\\/products\",\"image\":\"banners\\/dki4HHXTz5Dc2tIrYlmiY0UNpEdn94TpOqBJVg21.mp4\"}}',NULL,'2026-08-11 23:23:17','2026-08-11 23:23:17'),(242,'banner','memperbarui banner: NEW COLLECTION','App\\Models\\Banner','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/3bRaBWrUfSkVcFcCkePXfwhUnKdLVG9zlkyWNYhV.jpg\"},\"old\":{\"image\":\"banners\\/UwYsoTrlnMeh7Z0UFjRjvBuKcKnJ1Xq1lYljsV4Y.png\"}}',NULL,'2026-08-12 01:14:17','2026-08-12 01:14:17'),(243,'banner','memperbarui banner: asdsa','App\\Models\\Banner','updated',6,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/yesielehBtycBs0RhqwyD3hIMfZZ3tm9EE7c9D3V.jpg\"},\"old\":{\"image\":\"banners\\/mdmeQBH5gMUzDSp99Fw4yvQsnXrNxjUNx4VAh9lk.png\"}}',NULL,'2026-08-12 01:59:12','2026-08-12 01:59:12'),(244,'banner','memperbarui banner: NEW COLLECTION','App\\Models\\Banner','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/ty7gZ2l7t04BPmAwpVZOjWDEGZSwtIWyfcFg9OWp.jpg\"},\"old\":{\"image\":\"banners\\/3bRaBWrUfSkVcFcCkePXfwhUnKdLVG9zlkyWNYhV.jpg\"}}',NULL,'2026-08-12 01:59:21','2026-08-12 01:59:21'),(245,'banner','memperbarui banner: NEW COLLECTION','App\\Models\\Banner','updated',1,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/oKtZO5j18BeHHQpdUBbDkfX63TAoreCmMeb60NB2.jpg\"},\"old\":{\"image\":\"banners\\/ty7gZ2l7t04BPmAwpVZOjWDEGZSwtIWyfcFg9OWp.jpg\"}}',NULL,'2026-08-12 02:26:59','2026-08-12 02:26:59'),(246,'banner','memperbarui banner: asdsa','App\\Models\\Banner','updated',6,'App\\Models\\User',6,'{\"attributes\":{\"image\":\"banners\\/ljbHxGCHk9KURQSdgsR7yxZ7FgB6RIN3L2x1V2B1.jpg\"},\"old\":{\"image\":\"banners\\/yesielehBtycBs0RhqwyD3hIMfZZ3tm9EE7c9D3V.jpg\"}}',NULL,'2026-08-12 02:27:16','2026-08-12 02:27:16'),(247,'banner','menambahkan banner: Video','App\\Models\\Banner','created',11,'App\\Models\\User',6,'{\"attributes\":{\"title\":\"Video\",\"position\":\"hero\",\"is_active\":true,\"sort_order\":0,\"link\":\"\\/products\",\"image\":\"banners\\/Rk8fTQVlNppVWHwPj0vywbYOHi6gerc5UIsitJvh.mov\"}}',NULL,'2026-08-12 02:29:03','2026-08-12 02:29:03'),(248,'banner','memperbarui banner: Video','App\\Models\\Banner','updated',11,'App\\Models\\User',6,'{\"attributes\":{\"sort_order\":3},\"old\":{\"sort_order\":0}}',NULL,'2026-08-12 02:29:28','2026-08-12 02:29:28'),(249,'pesanan','memperbarui pesanan: ORD-20260813-0001','App\\Models\\Order','updated',110,'App\\Models\\User',6,'{\"attributes\":{\"status\":\"shipped\",\"tracking_number\":\"WYB-1786592042594\",\"courier\":\"jne\"},\"old\":{\"status\":\"processing\",\"tracking_number\":null,\"courier\":\"GoSend Instant\"}}',NULL,'2026-08-12 20:34:07','2026-08-12 20:34:07'),(251,'banner','memperbarui banner: NEW COLLECTION','App\\Models\\Banner','updated',1,'App\\Models\\User',7,'{\"attributes\":{\"image\":\"banners\\/7ncGXB2ctLDr9zypbORnFY2kuhSS03eaeUL2ZZPt.jpg\"},\"old\":{\"image\":\"banners\\/oKtZO5j18BeHHQpdUBbDkfX63TAoreCmMeb60NB2.jpg\"}}',NULL,'2026-08-12 23:39:37','2026-08-12 23:39:37'),(252,'banner','memperbarui banner: asdsa','App\\Models\\Banner','updated',6,'App\\Models\\User',7,'{\"attributes\":{\"image\":\"banners\\/HY612qh9K3wVnBsVeHZysqjjMfXhcHezRcj7MegJ.jpg\"},\"old\":{\"image\":\"banners\\/ljbHxGCHk9KURQSdgsR7yxZ7FgB6RIN3L2x1V2B1.jpg\"}}',NULL,'2026-08-12 23:39:45','2026-08-12 23:39:45'),(253,'produk','memperbarui produk: RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',31,'App\\Models\\User',7,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-12 23:50:26','2026-08-12 23:50:26'),(254,'produk','memperbarui produk: RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','App\\Models\\Product','updated',32,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":10}}',NULL,'2026-08-12 23:50:26','2026-08-12 23:50:26'),(255,'produk','memperbarui produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',33,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0,\"is_featured\":false},\"old\":{\"stock\":10,\"is_featured\":true}}',NULL,'2026-08-12 23:50:26','2026-08-12 23:50:26'),(256,'produk','memperbarui produk: RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',34,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0,\"is_featured\":false},\"old\":{\"stock\":10,\"is_featured\":true}}',NULL,'2026-08-12 23:50:26','2026-08-12 23:50:26'),(257,'produk','memperbarui produk: RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',35,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0,\"is_featured\":false},\"old\":{\"stock\":10,\"is_featured\":true}}',NULL,'2026-08-12 23:50:27','2026-08-12 23:50:27'),(258,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":31}}',NULL,'2026-08-12 23:50:27','2026-08-12 23:50:27'),(259,'produk','memperbarui produk: RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','App\\Models\\Product','updated',33,'App\\Models\\User',7,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-12 23:50:33','2026-08-12 23:50:33'),(260,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',7,'{\"attributes\":{\"is_featured\":true},\"old\":{\"is_featured\":false}}',NULL,'2026-08-12 23:50:37','2026-08-12 23:50:37'),(261,'produk','memperbarui produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','updated',40,'App\\Models\\User',7,'{\"attributes\":{\"stock\":25},\"old\":{\"stock\":0}}',NULL,'2026-08-13 00:23:18','2026-08-13 00:23:18'),(262,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1350,'App\\Models\\User',7,'{\"produk\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"varian\":\"33 Hitam Putih\",\"sku\":\"RECORD-KYLIE-HP-33_01225033\",\"sebelum\":0,\"sesudah\":25}',NULL,'2026-08-13 00:23:18','2026-08-13 00:23:18'),(263,'produk','memperbarui produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','updated',40,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":25}}',NULL,'2026-08-13 00:23:19','2026-08-13 00:23:19'),(264,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1350,'App\\Models\\User',7,'{\"produk\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"varian\":\"33 Hitam Putih\",\"sku\":\"RECORD-KYLIE-HP-33_01225033\",\"sebelum\":25,\"sesudah\":0}',NULL,'2026-08-13 00:23:19','2026-08-13 00:23:19'),(265,'produk','memperbarui produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','updated',40,'App\\Models\\User',7,'{\"attributes\":{\"stock\":40},\"old\":{\"stock\":0}}',NULL,'2026-08-13 00:24:15','2026-08-13 00:24:15'),(266,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1350,'App\\Models\\User',7,'{\"produk\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"varian\":\"33 Hitam Putih\",\"sku\":\"RECORD-KYLIE-HP-33_01225033\",\"sebelum\":0,\"sesudah\":40}',NULL,'2026-08-13 00:24:15','2026-08-13 00:24:15'),(267,'produk','memperbarui produk: RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','App\\Models\\Product','updated',40,'App\\Models\\User',7,'{\"attributes\":{\"stock\":0},\"old\":{\"stock\":40}}',NULL,'2026-08-13 00:24:17','2026-08-13 00:24:17'),(268,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1350,'App\\Models\\User',7,'{\"produk\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"varian\":\"33 Hitam Putih\",\"sku\":\"RECORD-KYLIE-HP-33_01225033\",\"sebelum\":40,\"sesudah\":0}',NULL,'2026-08-13 00:24:17','2026-08-13 00:24:17'),(269,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1350,'App\\Models\\User',7,'{\"produk\":\"RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM\\/PUTIH 12 TH KEATAS TALI 33-40\",\"varian\":\"33 Hitam Putih\",\"sku\":\"RECORD-KYLIE-HP-33_01225033\",\"sebelum\":0,\"sesudah\":0}',NULL,'2026-08-13 02:05:29','2026-08-13 02:05:29'),(270,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',7,'{\"attributes\":{\"stock\":18},\"old\":{\"stock\":0}}',NULL,'2026-08-13 02:07:37','2026-08-13 02:07:37'),(271,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',7,'{\"attributes\":{\"stock\":17},\"old\":{\"stock\":18}}',NULL,'2026-08-13 02:41:59','2026-08-13 02:41:59'),(272,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1374,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"varian\":\"39 Hitam Putih\",\"sku\":\"RECORD-BLANCO-HP-39_01246239\",\"sebelum\":1,\"sesudah\":0}',NULL,'2026-08-13 02:41:59','2026-08-13 02:41:59'),(273,'produk','memperbarui produk: RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','App\\Models\\Product','updated',36,'App\\Models\\User',7,'{\"attributes\":{\"stock\":18},\"old\":{\"stock\":17}}',NULL,'2026-08-13 02:42:03','2026-08-13 02:42:03'),(274,'produk','mengubah stok varian','App\\Models\\ProductVariant',NULL,1374,'App\\Models\\User',7,'{\"produk\":\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\",\"varian\":\"39 Hitam Putih\",\"sku\":\"RECORD-BLANCO-HP-39_01246239\",\"sebelum\":0,\"sesudah\":1}',NULL,'2026-08-13 02:42:03','2026-08-13 02:42:03');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,9,'Alamat Rumah','MuNir','085706013038','Jln ploso','Mojokerto','Jawa Timur','12313',-7.25750000,112.75210000,1,'2026-08-02 23:28:13','2026-08-02 23:28:13'),(2,9,'Rumah','Munir','085706013038','Gang Seger Waras, Ngembeh','Mojokerto','Jawa Timur','213123',-7.56112870,112.48193200,0,'2026-08-02 23:36:08','2026-08-02 23:36:08'),(7,59,'Alamat Rumah','Sirojul','08953356183','jln mojokerto','KABUPATEN MOJOKERTO','JAWA TIMUR','61374',-7.65051530,112.53731530,1,'2026-08-10 02:51:39','2026-08-10 02:51:39'),(8,67,'Rumah','Cindy','08570612312','Jln kyai tambak deres no 30, bulak , Surabaya','KOTA SURABAYA','JAWA TIMUR','60122',-7.23214287,112.78561074,1,'2026-08-12 20:30:34','2026-08-12 20:30:34');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` enum('hero','promo','sidebar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hero',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'NEW COLLECTION','FASHION FOR EVERY YOU - UNLEASH YOUR STYLE','banners/7ncGXB2ctLDr9zypbORnFY2kuhSS03eaeUL2ZZPt.jpg','/products','hero',1,1,NULL,NULL,'2026-07-10 01:43:36','2026-08-12 23:39:37'),(2,'10 + 10','Shopee Mall 100% ORI','banners/promo-1.png','/promo','promo',1,1,NULL,NULL,'2026-07-10 01:43:36','2026-07-10 01:43:36'),(3,'RECORD SHOES','#1 Spesialis Sepatu Sekolah','banners/promo-2.png','/category/sepatu-sekolah','promo',1,2,NULL,NULL,'2026-07-10 01:43:36','2026-07-10 01:43:36'),(4,'BELI 1 GRATIS 1','Voucher s/d 250RB','banners/promo-3.png','/promo','promo',1,3,NULL,NULL,'2026-07-10 01:43:36','2026-07-10 01:43:36'),(5,'EKSTRA DISKON 50%','DENGAN SpayLater','banners/promo-4.png','/promo','promo',1,4,NULL,NULL,'2026-07-10 01:43:36','2026-07-10 01:43:36'),(6,'asdsa','asdas','banners/HY612qh9K3wVnBsVeHZysqjjMfXhcHezRcj7MegJ.jpg','/products','hero',1,2,NULL,NULL,'2026-07-21 01:31:14','2026-08-12 23:39:45'),(11,'Video',NULL,'banners/Rk8fTQVlNppVWHwPj0vywbYOHi6gerc5UIsitJvh.mov','/products','hero',1,3,NULL,NULL,'2026-08-12 02:29:03','2026-08-12 02:29:28');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `biteship_saldo`
--

DROP TABLE IF EXISTS `biteship_saldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `biteship_saldo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `saldo_tercatat` bigint unsigned NOT NULL,
  `dicatat_pada` timestamp NOT NULL,
  `dicatat_oleh` bigint unsigned DEFAULT NULL,
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biteship_saldo_dicatat_oleh_foreign` (`dicatat_oleh`),
  KEY `biteship_saldo_dicatat_pada_index` (`dicatat_pada`),
  CONSTRAINT `biteship_saldo_dicatat_oleh_foreign` FOREIGN KEY (`dicatat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biteship_saldo`
--

LOCK TABLES `biteship_saldo` WRITE;
/*!40000 ALTER TABLE `biteship_saldo` DISABLE KEYS */;
/*!40000 ALTER TABLE `biteship_saldo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-lacak:jne:WYB-1786592042594','a:12:{s:2:\"ok\";b:0;s:5:\"pesan\";s:159:\"Saldo Biteship habis, jadi pelacakan tidak bisa dijalankan. Isi ulang saldo di dashboard Biteship — pelacakan ditagih per panggilan, bukan langganan bulanan.\";s:6:\"status\";N;s:5:\"kurir\";N;s:4:\"resi\";s:17:\"WYB-1786592042594\";s:7:\"riwayat\";a:0:{}s:5:\"tahap\";i:-1;s:5:\"gagal\";N;s:6:\"tautan\";N;s:10:\"kurir_nama\";N;s:8:\"kurir_hp\";N;s:10:\"kurir_plat\";N;}',1786672787),('laravel-cache-pembelian-terbaru','a:4:{i:0;a:6:{s:4:\"nama\";s:6:\"Ci****\";s:6:\"produk\";s:97:\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\";s:6:\"gambar\";s:53:\"products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png\";s:4:\"slug\";s:103:\"record-blanco-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-n3VAv\";s:7:\"nominal\";d:250000;s:5:\"waktu\";s:25:\"2026-08-13T03:30:34+00:00\";}i:1;a:6:{s:4:\"nama\";s:6:\"Si****\";s:6:\"produk\";s:97:\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\";s:6:\"gambar\";s:53:\"products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png\";s:4:\"slug\";s:103:\"record-blanco-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-n3VAv\";s:7:\"nominal\";d:250000;s:5:\"waktu\";s:25:\"2026-08-10T10:02:10+00:00\";}i:2;a:6:{s:4:\"nama\";s:6:\"Mu****\";s:6:\"produk\";s:97:\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\";s:6:\"gambar\";s:53:\"products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png\";s:4:\"slug\";s:103:\"record-blanco-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-n3VAv\";s:7:\"nominal\";d:250000;s:5:\"waktu\";s:25:\"2026-08-10T09:32:02+00:00\";}i:3;a:6:{s:4:\"nama\";s:6:\"Mu****\";s:6:\"produk\";s:97:\"RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44\";s:6:\"gambar\";s:53:\"products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png\";s:4:\"slug\";s:103:\"record-blanco-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-n3VAv\";s:7:\"nominal\";d:1000000;s:5:\"waktu\";s:25:\"2026-08-10T02:07:53+00:00\";}}',1787275645),('laravel-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:15:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"manage products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:14:\"manage banners\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:16:\"manage discounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"manage orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"manage customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:8;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:12:\"manage roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:18:\"manage permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:18:\"view activity logs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:11;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:14:\"manage returns\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:11:\"manage rpay\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:13;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:19:\"process withdrawals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:8;}}i:14;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"manage reviews\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:10:\"management\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}',1786758872);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `dipilih` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_id_product_variant_id_unique` (`cart_id`,`product_id`,`product_variant_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (56,72,36,1376,1,1,'2026-08-13 21:06:05','2026-08-13 21:06:20'),(57,72,36,1375,2,1,'2026-08-13 21:06:38','2026-08-13 21:24:54'),(59,72,36,1374,1,1,'2026-08-13 21:25:33','2026-08-13 21:25:33');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`),
  KEY `carts_session_id_index` (`session_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,NULL,'jemXo74BkZdaGXtLLI0fxsCQ6Ca3EG1ImM8Oowqg','2026-07-10 02:00:00','2026-07-10 02:00:00'),(3,NULL,'oDTO1dh3kNSNqKyrSkeVscrcjhhuD9M9oBltJbmk','2026-07-10 02:01:53','2026-07-10 02:01:53'),(4,NULL,'luVyEru6Kth2k5sYZcmD1Q6PzoEtaiEbV7xiK69p','2026-07-10 02:01:54','2026-07-10 02:01:54'),(5,NULL,'J2YgORUTy627GLxOt1aRy3mgLY3EoKMuaya1mLnT','2026-07-10 02:01:54','2026-07-10 02:01:54'),(6,NULL,'0OTjVWqd0djgA2ynfU6rnhFxkP7J76zyJPkfhlbu','2026-07-10 02:01:55','2026-07-10 02:01:55'),(7,NULL,'tNyJUYHHmVHiIxX8KpZmWUudrq6kZnH9T42y8syn','2026-07-10 02:01:55','2026-07-10 02:01:55'),(8,NULL,'OV3YUsrrp9iREkBNe7XVcdJPZrocoOd6nZaMIEA6','2026-07-10 02:01:55','2026-07-10 02:01:55'),(9,NULL,'dcChDV14UJi5sG4mdt0yhJAIdIVe36kLfxUik6mf','2026-07-10 02:02:02','2026-07-10 02:02:02'),(10,NULL,'EXjfUWBxZ2I3al8gTuhtyNRpcpbxN9qmozUBmad2','2026-07-10 02:02:03','2026-07-10 02:02:03'),(11,NULL,'elngcZMtxyb5xnLUneSclmmeMPSOmm7KcAmq2bWW','2026-07-10 02:02:03','2026-07-10 02:02:03'),(12,NULL,'ITMbPcWtlGjGFVuKlsryDLr7zn5uahPzl7e0MEL8','2026-07-10 02:02:03','2026-07-10 02:02:03'),(13,NULL,'YbTZfjhRAPuO7kTJnSN2jPQqytYnESR1KPSm4qvK','2026-07-10 02:02:04','2026-07-10 02:02:04'),(14,NULL,'gSTIxztKMi2AUYlo71Sz6pXXiuOf17mWzpVfz122','2026-07-10 02:02:04','2026-07-10 02:02:04'),(15,NULL,'JyPe2Ny3gMDa2Sj8q2cnQPPA2IiR1oxYHkwiJH2P','2026-07-10 02:02:04','2026-07-10 02:02:04'),(16,NULL,'CWzZnzUqLwKuJFxWnp86UbWjRNCaR5pMARc9lLwW','2026-07-10 02:02:05','2026-07-10 02:02:05'),(17,NULL,'2QvxScA2YyTHKlTqqz3EngEBoMl7qKZffuexoF7k','2026-07-10 02:02:05','2026-07-10 02:02:05'),(18,NULL,'vnoD0J8MzRTQdUGKSXQKqIw1MvnJcn2HDZJqkgDt','2026-07-10 02:02:06','2026-07-10 02:02:06'),(19,NULL,'9EkZWplKNhzdKpWK6VaxLZprn9hCkB75pMaZ6D1F','2026-07-10 02:02:06','2026-07-10 02:02:06'),(20,NULL,'Z2OhywwXnH7Q7CtwKfZMPhKEvv4efPckumSrDfz9','2026-07-10 02:02:07','2026-07-10 02:02:07'),(21,NULL,'5N6gnWt5BedKocP8R10pneUmW0yRsWl81ThltFPN','2026-07-10 02:02:09','2026-07-10 02:02:09'),(22,NULL,'E0wuIJIZK6SLwKt3dHTn75YQNPqXLKeSJcojwiZV','2026-07-10 02:02:09','2026-07-10 02:02:09'),(23,NULL,'mUKlykY5f7bEnjoy1zDQRDCvRXZv9LXNtRPOcvYP','2026-07-10 02:02:09','2026-07-10 02:02:09'),(24,NULL,'pJCADDRK4C9tOXUa34HhEFgYb8DdGLANqgJRhqqe','2026-07-10 02:02:10','2026-07-10 02:02:10'),(25,NULL,'8b7RsuZuoKBHYJfuO4mPg79PBDpUpGO1DzgLVQAZ','2026-07-10 02:02:10','2026-07-10 02:02:10'),(26,NULL,'5D33ctANNTzWxL8YIt4XHg0zY8k0lmG5B1vgV92K','2026-07-10 02:02:10','2026-07-10 02:02:10'),(27,NULL,'2nCUcOKfTrKZ3bSLImIA5IK2qQBoWJwn2slupM3J','2026-07-10 02:02:11','2026-07-10 02:02:11'),(28,NULL,'ygYlSU2MlsCzOYyvSbJ90MHFq4LKkvhTnn53kuQP','2026-07-10 02:02:11','2026-07-10 02:02:11'),(29,NULL,'RvTgweWnWvncs6cM6tqsnHn69joeiQu4YaXtGnyP','2026-07-10 02:02:13','2026-07-10 02:02:13'),(30,NULL,'fbpe7sVTYOK2IWSwUqSnJW53HjYcjs8X1nA8ipVk','2026-07-10 02:02:14','2026-07-10 02:02:14'),(31,NULL,'p7yClbahmLBRpWBYYp1WmdaVBVoaZayQrRPdAHXr','2026-07-10 02:02:14','2026-07-10 02:02:14'),(32,NULL,'uFWIqcDQhWXJUikqk7ZUPGGD2xNSMYZjI0j4kAsU','2026-07-10 02:02:14','2026-07-10 02:02:14'),(33,NULL,'ww1TA8pQaxuL08lsejREoV8yXNipilsiRNN045Td','2026-07-10 02:02:14','2026-07-10 02:02:14'),(34,NULL,'ILFH8OeG1tHNe6n4qxRHNZ6H4qmQjPYvf1ePRaon','2026-07-10 02:04:35','2026-07-10 02:04:35'),(35,NULL,'xCZDyJ7qgrHX13XhCvcWSDM0JU9WvwOa7dLOEjte','2026-07-10 02:05:04','2026-07-10 02:05:04'),(36,NULL,'eUCfriTMXwnKxN5ZdQ8vWH7Vp8KYIXqVklFg7ULX','2026-07-10 02:07:19','2026-07-10 02:07:19'),(37,NULL,'ooc0mdVmWDeNwMFAOmQaq4hEAiSHvYxqmJy37YJS','2026-07-10 02:08:09','2026-07-10 02:08:09'),(38,NULL,'C2V6GXDjVw9N0Xz2wXTsGXfngtRptdRmLijvIHL7','2026-07-10 02:08:54','2026-07-10 02:08:54'),(39,NULL,'yCI6zB3YfGghRDmhB5WB3s4Hmeg7xHTJPu77BtMm','2026-07-10 02:09:17','2026-07-10 02:09:17'),(40,NULL,'hgJXaXHPQ5THHG1OrAgtZ3sown3Vs7CowtSWbO96','2026-07-10 02:09:38','2026-07-10 02:09:38'),(41,NULL,'VFPvFojMejCNRqvWmYO5icVl6a1Wd13CmOtw3Tzf','2026-07-10 02:09:56','2026-07-10 02:09:56'),(42,NULL,'056u3ALG7iMJzGt0r8nou9kexg6IUCqTJm0faloc','2026-07-10 02:10:20','2026-07-10 02:10:20'),(43,NULL,'bcvE1vPDgHqhUKcFKJSpQvjy35vEw7DcgpiTG1IF','2026-07-10 02:10:56','2026-07-10 02:10:56'),(44,NULL,'CDrKNfXatZiXtfHrqXOL3SGDGZPceLEnswUsAOd0','2026-07-10 02:11:17','2026-07-10 02:11:17'),(45,NULL,'gY44Eakwl0jdGqGo0pUtSc502zIHV7dpeVbO3bDD','2026-07-10 02:11:41','2026-07-10 02:11:41'),(46,NULL,'y8W9kkk3GBlsRZfnofA2Zt1AlRmdOC7tCjRkYO8N','2026-07-10 02:12:05','2026-07-10 02:12:05'),(47,NULL,'L9Sxz96fUEM3RCaT06e0B4fHvmJhsrESGlNpiG8j','2026-07-10 02:12:26','2026-07-10 02:12:26'),(48,NULL,'4mWeNsimI2ThYkXOHwWosNrZkUxN5ZVRGtrJHYov','2026-07-10 02:12:52','2026-07-10 02:12:52'),(49,NULL,'8DufhZbIe6N5KWfgh4AxvU9zSI01AuyvjB6Oo5jK','2026-07-10 02:13:21','2026-07-10 02:13:21'),(50,NULL,'GehB2RGAhVvvacugKRMNOapxRfe9mF5bOnVDmOvt','2026-07-10 02:17:52','2026-07-10 02:17:52'),(51,NULL,'2kvwQp7kjf6Dp0CoDFp6dXLylVil2yhAxDx55eLi','2026-07-10 02:20:21','2026-07-10 02:20:21'),(52,NULL,'bls9VpNnqI1TefzeIzaqMmtitdzXnaWubJDcvc3j','2026-07-10 02:20:23','2026-07-10 02:20:23'),(53,NULL,'npBHuiB8PfYpvMh0HICu4m6CZT3EwiYtrWE7Fkep','2026-07-10 02:23:24','2026-07-10 02:23:24'),(54,1,NULL,'2026-07-10 02:34:02','2026-07-10 02:34:02'),(55,NULL,'dSrUG0YYRd3qwPGmAM7cvbfIvoxHssuu3CDfb9d4','2026-07-10 02:37:34','2026-07-10 02:37:34'),(56,NULL,'ci7e4jhwWVhevz1t1AGcvpO3TaAUhE6gXLxKxvei','2026-07-10 02:37:41','2026-07-10 02:37:41'),(57,NULL,'WqDRYxjxefEJExLGUHkbAqzeOL8bFhPhLf09fbWL','2026-07-10 02:38:02','2026-07-10 02:38:02'),(58,NULL,'shELtXZO2PXDu9RAX02aw2YYzkEZpskwEX6ELLTk','2026-07-10 02:38:54','2026-07-10 02:38:54'),(59,NULL,'EmczcwOSHaZkHfLGVBHzp8lpPBycMAzMxYRZ4PrB','2026-07-10 02:40:41','2026-07-10 02:40:41'),(60,NULL,'VTpnRFVbSpwtgzA3SwGH7AJ2mhgaM8OmGcbrEPme','2026-07-10 02:42:14','2026-07-10 02:42:14'),(61,NULL,'HA2RCGzgIYzCSI7cHBOlH211laJImZ9btF8niheK','2026-07-10 02:42:15','2026-07-10 02:42:15'),(62,NULL,'8uDpEWMQAx8oibB8sbWChGPJjfClC4M9HvRplV9F','2026-07-10 03:07:03','2026-07-10 03:07:03'),(63,NULL,'8LFRHgznxEyombKx6rxxfS5FpyZ6OZcMS6wdIngp','2026-07-10 03:09:41','2026-07-10 03:09:41'),(64,NULL,'hXeufQwihXUa0F1KZPtMZcH7XeEuFlogEYCKcvwy','2026-07-10 03:09:41','2026-07-10 03:09:41'),(65,NULL,'qkEdFxYGYyY6GnaVI5GEW76jHg8J66QNREI9RN2O','2026-07-12 20:50:32','2026-07-12 20:50:32'),(66,NULL,'Qvuyxdziyoaew6dPZ8fo52KUllhVOQblxL1lVc7B','2026-07-14 01:35:33','2026-07-14 01:35:33'),(67,NULL,'jvuslLUxBNEXOAsoqmtQOtkFJyp8BOTgaX05wO5W','2026-07-14 22:50:04','2026-07-14 22:50:04'),(68,NULL,'TEcoKSVyshvBD2db9KCdFLGfKBlIShSKT68ICNf3','2026-07-15 02:18:46','2026-07-15 02:18:46'),(69,NULL,'xoXdt506wA37xiDJ76KjuAfxjskV3eddWdJAjcg1','2026-07-16 23:17:50','2026-07-16 23:17:50'),(70,NULL,'vJCox8P4L7eHwg9vrtaBlGqG3iWiiBINaKiDNfMG','2026-07-17 03:16:26','2026-07-17 03:16:26'),(71,NULL,'8XTdQmSredovnyX8Z6WtHGouIUcldxNz98mZKOVw','2026-07-20 21:20:56','2026-07-20 21:20:56'),(72,9,NULL,'2026-07-20 21:53:08','2026-07-20 21:53:08'),(74,NULL,'9BMBysyviN2gl3ApUeZhsMM0YgPnTEk436OdPS9H','2026-07-21 05:07:17','2026-07-21 05:07:17'),(75,NULL,'7hF8l3OiIV1NGRTkWizNVoptcam7bHYOARoJ2yIX','2026-07-24 07:16:36','2026-07-24 07:16:36'),(76,NULL,'2W71x7larRcwpyMV7SUQTlJQKuwPQMlXbj4qBdZE','2026-07-26 03:19:07','2026-07-26 03:19:07'),(78,NULL,'ysXhehRv3IVvEq31Tsvyhy6CapNeQmRdZ0ZPFYAD','2026-07-29 23:37:39','2026-07-29 23:37:39'),(79,NULL,'7O5a6AkylMvucaHkllazPdvfpcPzL4hKC0PlhI4R','2026-07-29 23:45:15','2026-07-29 23:45:15'),(80,NULL,'EF059KZ35jHkPeT5pyefzvQlvsWM9XNzz04tAWF2','2026-07-29 23:45:22','2026-07-29 23:45:22'),(81,NULL,'Pyax52PdAbE9g28tYHJcdXmkQBZRKeY5FRYQCOfE','2026-07-30 01:24:56','2026-07-30 01:24:56'),(82,NULL,'P0IsTvMH4KUbgZMKAVPI5ahIdumWHNE92IuHcwAa','2026-07-30 01:24:57','2026-07-30 01:24:57'),(83,NULL,'15jy4nUfXk441KGHByw9GEcoVUQUPCXQiYfn6unL','2026-07-30 01:24:57','2026-07-30 01:24:57'),(84,NULL,'h0iSQzJ8KHUMp6XRIxYdBsahoFGEetXlnHn5qNxm','2026-07-30 03:39:15','2026-07-30 03:39:15'),(85,3,NULL,'2026-07-30 03:39:36','2026-07-30 03:39:36'),(86,NULL,'OYpiHBY5ex3fvHhtJ6lxMZnt2N4Y8KdDiBphZPUJ','2026-07-31 03:05:20','2026-07-31 03:05:20'),(87,NULL,'yUOEJg4hxmOPfejwSdvIgCI6H1NCk01hiJ2OWr1W','2026-07-31 03:05:20','2026-07-31 03:05:20'),(88,NULL,'uj2VcaCSICKTjsIEa5PzYL1AYu6xafkADcfJIq0t','2026-08-02 20:19:13','2026-08-02 20:19:13'),(90,NULL,'iA3oVPlQH3kNXHAPDMhxs3OHZ7sbok1oiytP1PuF','2026-08-02 22:52:23','2026-08-02 22:52:23'),(91,NULL,'agVEQjOXWBMZujbMm53fBtGT6ZZ4iu1lin1wOyRx','2026-08-04 02:48:30','2026-08-04 02:48:30'),(92,NULL,'6UfJi22StfJjv2K22wY5UU2QhjttFGg15GTU6v1Q','2026-08-04 05:29:04','2026-08-04 05:29:04'),(96,NULL,'2ufkaIywsPhs4d4CZex36JpHZqALXeVlTrPnCFXI','2026-08-05 23:14:35','2026-08-05 23:14:35'),(97,NULL,'YBkJ7brKPsDH4c8aLXKwRI8PW89wxHPpGdB1YkLH','2026-08-06 01:02:50','2026-08-06 01:02:50'),(98,NULL,'EGMHxIpZTKlkzo1nJNcS0cLRDNhSBRnIeBKQf9Is','2026-08-06 01:32:57','2026-08-06 01:32:57'),(99,NULL,'DuUIlnnGj4pPzydBGLfBQUimOXX66IIn5Of6dt0p','2026-08-06 02:36:43','2026-08-06 02:36:43'),(100,NULL,'ESxKaMbavHNcB0GNAUxZc9ntL09bolWplODLZBm5','2026-08-06 02:37:55','2026-08-06 02:37:55'),(101,NULL,'Jngq2deTBheyL2ABeegd8jFNEBVJa6CjC0atO6fp','2026-08-06 02:44:25','2026-08-06 02:44:25'),(102,NULL,'iTkl5bnJupGb2akbjamLs2jU6HQzn71kxIWNzpaz','2026-08-06 02:50:59','2026-08-06 02:50:59'),(103,NULL,'nlGqpfyvRESuIutXGDyrWEeZc5UrXB45g4KhO7zl','2026-08-06 02:55:36','2026-08-06 02:55:36'),(104,NULL,'s453b2jcsZbQiL2Rd1HArC5sYmkCzKGclrr05bIG','2026-08-06 02:58:04','2026-08-06 02:58:04'),(105,NULL,'Li8Q9oJnCDWkwQVFbhcZEXEjUMonR2VntdDIYqI2','2026-08-06 06:31:01','2026-08-06 06:31:01'),(106,NULL,'HgUXTfribLTN2XJBGjqH9jta7UoOT0UmL5FzIfV0','2026-08-06 06:31:54','2026-08-06 06:31:54'),(107,NULL,'MrF7IiSqxhXpQLSw6Q8oysHlgwZ0mzdwfARAEad7','2026-08-06 06:43:43','2026-08-06 06:43:43'),(109,NULL,'Ce3rAkDZelRlt9fbIx0dCqxxC9VuAq7dhc3aqNU1','2026-08-06 18:30:49','2026-08-06 18:30:49'),(110,NULL,'UAD5rVRK0cUnsIuGs7hI4DTWhoPgPEh6q4VZ3xI6','2026-08-06 18:31:42','2026-08-06 18:31:42'),(111,NULL,'rdS3Pb1wvmleQexseb2eyL8ew6uUx0BotkAY7w2P','2026-08-06 18:32:45','2026-08-06 18:32:45'),(112,NULL,'jgcxGJDAyT3FWro723S2MLBItzpwGxtGZTbflLBR','2026-08-06 18:33:37','2026-08-06 18:33:37'),(113,NULL,'rroymKxrm0Z1HPLGO25jbqatAygiYxNtanZ33AJ5','2026-08-06 18:34:59','2026-08-06 18:34:59'),(114,NULL,'T3kaMeRGKYIOib2IkCPMHjGIyFBOfAdg9TeRFfPa','2026-08-06 18:35:22','2026-08-06 18:35:22'),(115,NULL,'LHJxVq97CgzMIn2x8IxmpzFqa6becdjE3QS1htI6','2026-08-06 18:36:30','2026-08-06 18:36:30'),(116,NULL,'vBMODNomKMBrR7GD7CjGpR0umTtP8eklt11zEajS','2026-08-06 18:36:44','2026-08-06 18:36:44'),(117,NULL,'Zt0Z9gwRaymDvMF7lfB8a4EmLzE8tTfKLvMeeTyB','2026-08-06 18:42:31','2026-08-06 18:42:31'),(118,NULL,'yErem42ZpIbQJiURoRI1IYXyRjao8pszsOucwu2s','2026-08-06 18:43:14','2026-08-06 18:43:14'),(119,NULL,'9yZPI8AQvrtcd4qskjB3MfRNW0GBpOotOOmaHGDk','2026-08-06 18:50:53','2026-08-06 18:50:53'),(120,NULL,'o1e30jzajp1BvNDdyv4OQkLNqANTvPlLMRFwHSNT','2026-08-06 18:58:16','2026-08-06 18:58:16'),(121,NULL,'6TKRgpUH9Rm8b9pgKVKSEwLKXZYLskC3al9mfSRV','2026-08-06 18:58:53','2026-08-06 18:58:53'),(122,NULL,'g628iUTZS4PX25JNkbHvztrAVU8UPUTiVWzamkvM','2026-08-06 19:23:55','2026-08-06 19:23:55'),(123,NULL,'ybzziobbvOPxADmWyRagLpdxWTOtsEG1eqvp4vGO','2026-08-06 19:24:35','2026-08-06 19:24:35'),(124,NULL,'oemsUOJURNEcl8ow2rEbc8fzUyX1iEFqXqz59Vwy','2026-08-06 19:25:08','2026-08-06 19:25:08'),(125,NULL,'ptuVAqPmRayd9YNFilNAJZxA0WBg0ssmPV4IQzgz','2026-08-06 19:43:18','2026-08-06 19:43:18'),(127,NULL,'Iw5yTzCNO1dohP27RDkXxSnHNK9I649OThqaZCgQ','2026-08-06 23:14:53','2026-08-06 23:14:53'),(128,NULL,'WOpaa38Y3HbdKeK4GF1Ext2Ug8UmNfXSZwhpW2qr','2026-08-06 23:15:48','2026-08-06 23:15:48'),(129,NULL,'g0AUxC4nt2W3F16zsXIfW2a0WQiO25zjs3jE4apF','2026-08-06 23:25:37','2026-08-06 23:25:37'),(130,NULL,'NPTZfLT6leDWKGOSmMz8WaOV4E8S4xtSM1CnVUnc','2026-08-06 23:26:09','2026-08-06 23:26:09'),(131,NULL,'pNFe9wM0OgFLdEoA6lPmbdZg6mDC40zZq6EeUdKs','2026-08-06 23:38:12','2026-08-06 23:38:12'),(132,NULL,'HF9Tt8wLGgm5lFdIQ78r4D5EIPiLzr32bjKzm5G2','2026-08-06 23:38:53','2026-08-06 23:38:53'),(140,NULL,'ASDkb200QtfiWCFD6fqslMu6O8vghQsJ7waVdpux','2026-08-07 01:23:11','2026-08-07 01:23:11'),(142,NULL,'SOqhMkwtMqJw6F9zrYLnQzcz92GVJxmfSGjaXV9i','2026-08-07 01:24:55','2026-08-07 01:24:55'),(150,15,NULL,'2026-08-07 01:40:45','2026-08-07 01:40:45'),(151,NULL,'Qtvivgb4Jb4q7HOfJhx8vJBloBfjr4yHnaNon82h','2026-08-07 02:01:58','2026-08-07 02:01:58'),(152,NULL,'ZiVmKVGyXSISbAJEZmJFddUDvAbLp4w31ES5K2y3','2026-08-07 02:07:41','2026-08-07 02:07:41'),(153,NULL,'84aNA2sjSRT6YDGU9P5sLgKZzJtdOzZmBQMseRu2','2026-08-07 02:08:35','2026-08-07 02:08:35'),(154,NULL,'ioIXq24McXBrfVi5zjy62Gd5om0VrFl3Y8r7ENt2','2026-08-07 02:45:19','2026-08-07 02:45:19'),(155,NULL,'zTk1nDoePIcIBTfxOTdrQ8OEqVua7hsAaThagsKt','2026-08-07 02:46:09','2026-08-07 02:46:09'),(156,NULL,'3EIAXY6Sd690AkaPapCMLIxPqKzNUn1WdHmA1ofD','2026-08-07 03:02:33','2026-08-07 03:02:33'),(188,NULL,'wK1DCjHZrK7dmPqBuHAx8vd6prpb1t4SmAiGjiEN','2026-08-10 02:46:52','2026-08-10 02:46:52'),(189,59,NULL,'2026-08-10 02:49:38','2026-08-10 02:49:38'),(192,NULL,'SbyuABR6IakYz85gM15oPRcWjGhZx2Vc6Hh04l71','2026-08-10 19:11:22','2026-08-10 19:11:22'),(193,NULL,'H6A1ywv0vNzwQSXgl8JgZSXsKtGN9xFCnUPylmqz','2026-08-10 19:11:29','2026-08-10 19:11:29'),(194,NULL,'qkT3h6DCCpIjef1h7jdR1kjEFitRspM11xQwkQBj','2026-08-10 19:11:29','2026-08-10 19:11:29'),(195,NULL,'54LH5FRWblfktlgjcn52IG89PtyVpBmCFtLlSHXj','2026-08-10 19:11:30','2026-08-10 19:11:30'),(196,NULL,'NAeZcotyobIkld92ikTg5NAL9vJxjFbXWwL56BMC','2026-08-10 19:11:30','2026-08-10 19:11:30'),(197,NULL,'R95E1xwtjZHI0NuMYIos9EprFBAvdkCzwVBQMIps','2026-08-10 19:11:30','2026-08-10 19:11:30'),(198,NULL,'3AAQhot7VDckGZqYSmVzPuuX8hhKZT6vxRWD5dfL','2026-08-10 19:11:30','2026-08-10 19:11:30'),(199,NULL,'rS8q67XQNAZnlb04JJyHNFYDZDi8xe0tsabazO5Q','2026-08-10 19:11:30','2026-08-10 19:11:30'),(200,NULL,'AbSDXGsqhRPpP3BiYsnqF3BW5qIkRrD79S4QqAoG','2026-08-10 19:37:05','2026-08-10 19:37:05'),(201,NULL,'4aInwI3nrU4V9ifMQpprfD3C70MT6nl99gnCRSMw','2026-08-10 19:37:28','2026-08-10 19:37:28'),(202,NULL,'9N0zkjiDvD5UyZcMOAZWLMXLP38wkHqE2oxiQRTe','2026-08-10 19:54:56','2026-08-10 19:54:56'),(204,NULL,'3N7cWZvfFV6XIx3fqeoi4BkbicPPIp5ZNoXMV4Qf','2026-08-10 21:15:47','2026-08-10 21:15:47'),(205,NULL,'X0DIqYhZMXF54KyoJhKYnsCa4ZqY8RU0bnNc4NK9','2026-08-10 21:16:40','2026-08-10 21:16:40'),(206,NULL,'lK89GC9sMPXK9e3EesmPfnCTZ21ghIQ7NFITqWV9','2026-08-10 21:16:40','2026-08-10 21:16:40'),(207,NULL,'Yrx1kcZgz2kp3ArFtp6Y6k7PiH0LUNkjlgJLUpvv','2026-08-10 22:52:46','2026-08-10 22:52:46'),(208,NULL,'hj2bEtHa47I7Z1nq38G6NbhhRGOKWfcYxMj42f8E','2026-08-10 22:53:28','2026-08-10 22:53:28'),(209,NULL,'zJZoPtKUm6kgXLUqjM4rVG2wTPV3rcRswNnxPbTz','2026-08-10 22:54:30','2026-08-10 22:54:30'),(210,NULL,'vozldzEMZAUi8cm08sRvBY6GMz9McvsnZOjzonFZ','2026-08-10 23:01:02','2026-08-10 23:01:02'),(211,NULL,'lrwpUxo7itrdMCwxHViN8Ifzv0uooykQs8nhSVmy','2026-08-10 23:05:45','2026-08-10 23:05:45'),(212,NULL,'B3bQNJNo8jD6om93z5T23uCFlrNFrvHboF9FrCVh','2026-08-10 23:27:48','2026-08-10 23:27:48'),(213,NULL,'QxrZPmCa6rL6iifPhDEeyqJXRuWJkP4rKn5stHd4','2026-08-10 23:28:26','2026-08-10 23:28:26'),(214,NULL,'5VLLam2XMm95a992us3voSEvm736N4P5OjeCBmpm','2026-08-11 00:00:56','2026-08-11 00:00:56'),(215,NULL,'Aw4UqZEn4C9D6mBRa37LhfC4x5OmzfTECJT0ZUuu','2026-08-11 00:01:42','2026-08-11 00:01:42'),(216,65,NULL,'2026-08-11 00:08:10','2026-08-11 00:08:10'),(220,NULL,'V6dXmfOF7yCU4S2Ya7kDcQhWPqWKAiWnD3535j8K','2026-08-11 02:48:56','2026-08-11 02:48:56'),(221,NULL,'5wL4bUZPb5H4H43Rm60g3oNEAQ7n78q5Y5Pcd30y','2026-08-11 02:53:24','2026-08-11 02:53:24'),(222,NULL,'JVb4PAB26xNvuh230xwv2GYZ9sRoWB7g93s1jNsi','2026-08-11 03:06:01','2026-08-11 03:06:01'),(224,NULL,'HapN2OSoFBi0U1TSotooJzegqX6Gh8PAbfGfJONs','2026-08-11 18:31:32','2026-08-11 18:31:32'),(225,NULL,'NBywnsXOgutJ8Hyc7UD87EVtIQkinSD4cPpRwboW','2026-08-11 18:46:33','2026-08-11 18:46:33'),(227,NULL,'AK4qMT1gEFP2NHV8wXmRuCzoHVuAsU6An8FawXBI','2026-08-11 19:30:51','2026-08-11 19:30:51'),(228,NULL,'Ulp8OzYYnoESFZNXqwTNv6I4HptiTnZqicNiqBav','2026-08-11 19:35:08','2026-08-11 19:35:08'),(229,NULL,'7XhGHWSzN26ilfZyDQ5QOH5WXKTZTlXpoSSXkFCm','2026-08-11 19:54:19','2026-08-11 19:54:19'),(230,NULL,'Clk564zS74eXg9jCggxQQkepZUQtyCAI2XDHLSyR','2026-08-11 19:54:30','2026-08-11 19:54:30'),(231,NULL,'9avydSzB00SjhVKW1enKNXhJ0h2LcIkYnVCgmTUS','2026-08-11 20:24:31','2026-08-11 20:24:31'),(233,NULL,'zk2lVf04CV8RB2AamHqQOILQQhvs5HT2tAh6dGu5','2026-08-11 21:08:02','2026-08-11 21:08:02'),(234,NULL,'sgpamPiCUV49Z1Iz2S5hI7W5o0yVm37vbya5udoN','2026-08-11 21:10:36','2026-08-11 21:10:36'),(235,NULL,'W6Ty6k7Nov7xSTdl8Jl2QEHU4onFxuHdY19lDSIj','2026-08-11 21:59:04','2026-08-11 21:59:04'),(236,NULL,'s60029Pw7b9JWg7Vcv4ZVodF1IaJ2RXuJ8OUl9Ry','2026-08-11 22:01:34','2026-08-11 22:01:34'),(237,NULL,'nnFg4HpuCmY2QejBFCvsHrTy2amsGVa0wahkCcXX','2026-08-11 22:01:57','2026-08-11 22:01:57'),(238,NULL,'c2SyXkRyjPvDX9g6sHw6oiElWibX8ZBXOj1QYRgC','2026-08-11 22:59:41','2026-08-11 22:59:41'),(241,NULL,'A4qWjBDfkEW05p66nfCkvPienQQd8uxMr0r8FzgN','2026-08-12 02:42:29','2026-08-12 02:42:29'),(242,NULL,'POpBJKy9FGB7lAXsy4Q5a70QXJBDg2KnPCYX2KVm','2026-08-12 02:44:34','2026-08-12 02:44:34'),(243,NULL,'JHfNKs7jdQnSi1b4hrAiNuQdIrkUmVVMJbaGnjCo','2026-08-12 02:44:51','2026-08-12 02:44:51'),(244,NULL,'ocnrhssJwR9R99VIs7Mxnqh2dg6XLEXjt3ShIa8j','2026-08-12 02:48:36','2026-08-12 02:48:36'),(246,67,NULL,'2026-08-12 20:26:25','2026-08-12 20:26:25'),(247,NULL,'Xyo6jMJeLJkSu6l4HsL0Us43Gt277nQRacDKMWZS','2026-08-12 22:50:18','2026-08-12 22:50:18'),(248,NULL,'DzIbvJ1HQCH281UbVLgbgEFKEAShvaPiYUiXYzXZ','2026-08-12 23:00:42','2026-08-12 23:00:42'),(249,NULL,'ZcEOndajJFDuTIHBvRhT4KbZUj1HWXOf5b7cJNl4','2026-08-13 02:04:49','2026-08-13 02:04:49'),(253,NULL,'TTdgms1x6749ZvUXEvqB9vtBD9ihKZePFhzTSays','2026-08-14 01:41:27','2026-08-14 01:41:27'),(254,NULL,'l3fFAxju7rrhe06J3HMlTkRV4Nf8Fe8vLasdKq8o','2026-08-14 01:42:34','2026-08-14 01:42:34'),(255,NULL,'KC4VKEnQ6835tVbOcfkjh5z27SfezaIHJE6u4CV0','2026-08-19 01:58:57','2026-08-19 01:58:57'),(256,NULL,'wVkxKJBEiLVT8fSO53XR509gdL1miGZJYOpb7JIL','2026-08-19 20:27:45','2026-08-19 20:27:45'),(257,NULL,'tNqNXIE2FTEWJ7YLonA7lyydgULqOLsxc3KjWv6n','2026-08-19 21:18:49','2026-08-19 21:18:49'),(258,NULL,'RFR8wRyzsXUPi0iMAe8q4z1M3fKNVTQcTYUT8buk','2026-08-19 21:18:54','2026-08-19 21:18:54'),(263,NULL,'eZgy3IObDY8nx0lqIoglLwbwLPwyGaSk85ihbNOt','2026-08-20 18:25:24','2026-08-20 18:25:24');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_position_x` tinyint unsigned NOT NULL DEFAULT '50',
  `image_position_y` tinyint unsigned NOT NULL DEFAULT '50',
  `image_zoom` decimal(4,2) NOT NULL DEFAULT '1.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,'Sepatu Anak','sepatu-anak','categories/lK1CwlWZZrcxFnY5ouLGuuWnK8fPh2EdcR0fPB1O.jpg',50,62,1.00,'Sepatu nyaman dan tahan lama untuk anak-anak.',1,1,'2026-07-10 01:43:31','2026-08-11 00:31:01'),(3,'Sepatu Sekolah','sepatu-sekolah','categories/LpiEBzHSGIrrcEply4iGVOsIi4MfQ0NpYGOB4eGq.jpg',68,49,1.00,'Sepatu sekolah berkualitas tinggi untuk setiap langkah.',2,1,'2026-07-10 01:43:31','2026-08-11 01:07:43'),(4,'Sepatu Lifestyle','sepatu-lifestyle','categories/Nak7ZzpUxLfCzJLbHFHQ4pi8RWFJ5SdfskXvG9DH.jpg',52,95,1.00,'Sepatu lifestyle trendy untuk gaya sehari-hari.',3,1,'2026-07-10 01:43:31','2026-08-11 00:37:15'),(5,'Sepatu Cewek','sepatu-cewek','categories/J29cspybVE2QrxdKXQkZbOpRx0OK5j9qKhO8W1kD.jpg',50,50,1.00,'Koleksi sepatu khusus wanita dengan desain modern.',5,1,'2026-07-10 01:43:31','2026-08-11 00:36:59'),(6,'Sepatu Cowok','sepatu-cowok','categories/JqpslX03toNpI4q4kMbaLwp21MhtaCgXwNguKFG7.jpg',47,26,1.00,'Koleksi sepatu pria dengan kualitas premium.',4,1,'2026-07-10 01:43:31','2026-08-11 00:44:23');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discounts_product_id_foreign` (`product_id`),
  KEY `discounts_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `discounts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discounts_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discounts`
--

LOCK TABLES `discounts` WRITE;
/*!40000 ALTER TABLE `discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_10_100000_add_role_to_users_table',1),(5,'2026_07_10_100001_create_categories_table',1),(6,'2026_07_10_100002_create_products_table',1),(7,'2026_07_10_100003_create_product_images_table',1),(8,'2026_07_10_100004_create_product_variants_table',1),(9,'2026_07_10_100005_create_carts_table',1),(10,'2026_07_10_100006_create_addresses_table',1),(11,'2026_07_10_100007_create_orders_table',1),(12,'2026_07_10_100008_create_banners_table',1),(13,'2026_07_14_000001_create_discounts_table',2),(14,'2026_07_14_000002_create_product_shippings_table',2),(15,'2026_07_14_000003_create_order_returns_table',2),(16,'2026_07_14_000004_create_payments_table',2),(17,'2026_07_14_000005_create_website_settings_table',2),(18,'2026_07_14_070454_create_permission_tables',2),(19,'2026_07_14_070501_create_activity_log_table',2),(20,'2026_07_14_070502_add_event_column_to_activity_log_table',2),(21,'2026_07_14_070503_add_batch_uuid_column_to_activity_log_table',2),(22,'2026_07_14_071758_create_personal_access_tokens_table',3),(23,'2026_07_15_100001_add_product_variant_id_to_discounts_table',4),(24,'2026_08_04_000001_add_is_blocked_to_users_table',5),(25,'2026_08_06_000001_add_image_position_to_categories_table',6),(26,'2026_08_07_000001_add_cancellation_reason_to_orders_table',7),(27,'2026_08_07_000002_add_color_to_product_images_table',8),(28,'2026_08_07_000003_add_invoice_to_orders_table',9),(29,'2026_08_08_000001_create_rpay_tables',10),(30,'2026_08_08_000002_extend_order_returns_table',10),(31,'2026_08_08_000003_add_completed_at_to_orders_table',11),(32,'2026_08_08_000004_rename_exchange_size_on_order_returns',12),(33,'2026_08_08_000005_add_return_shipping_stages',13),(34,'2026_08_08_000006_make_return_reason_nullable',14),(35,'2026_08_09_000001_create_referral_columns',15),(36,'2026_08_09_000002_widen_rpay_source',16),(37,'2026_08_11_000001_add_return_evidence_columns',17),(38,'2026_08_12_000001_create_product_reviews_table',18),(39,'2026_08_13_000001_add_fee_columns_to_orders_table',19),(40,'2026_08_14_000001_add_return_number_to_order_returns',20),(41,'2026_08_14_000002_add_courier_code_to_orders_table',21),(42,'2026_08_14_000003_create_order_exports_table',22),(43,'2026_08_14_000010_add_dipilih_to_cart_items_table',23),(44,'2026_08_20_000001_add_unique_reference_to_rpay_transactions',24),(45,'2026_08_20_000002_create_biteship_saldo_table',25),(46,'2026_08_20_000003_add_insurance_fee_to_orders_table',26);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',6),(2,'App\\Models\\User',7),(3,'App\\Models\\User',8);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_exports`
--

DROP TABLE IF EXISTS `order_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dari` date DEFAULT NULL,
  `sampai` date DEFAULT NULL,
  `jumlah_pesanan` int unsigned NOT NULL DEFAULT '0',
  `jumlah_baris` int unsigned NOT NULL DEFAULT '0',
  `ukuran` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_exports_user_id_foreign` (`user_id`),
  KEY `order_exports_created_at_index` (`created_at`),
  CONSTRAINT `order_exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_exports`
--

LOCK TABLES `order_exports` WRITE;
/*!40000 ALTER TABLE `order_exports` DISABLE KEYS */;
INSERT INTO `order_exports` VALUES (28,6,'Pesanan_awal_akhir_20260814_034428_jipv.xlsx','ekspor-pesanan/Pesanan_awal_akhir_20260814_034428_jipv.xlsx',NULL,NULL,17,17,5884,'2026-08-13 20:44:28','2026-08-13 20:44:28');
/*!40000 ALTER TABLE `order_exports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (51,57,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 39 - Hitam Putih',4,250000.00,'2026-08-09 19:07:53','2026-08-09 19:07:53'),(55,72,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 40 - Hitam Putih',1,250000.00,'2026-08-10 02:32:02','2026-08-10 02:32:02'),(62,79,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 40 - Hitam Putih',1,250000.00,'2026-08-10 02:51:40','2026-08-10 02:51:40'),(75,92,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 40 - Hitam Putih',1,250000.00,'2026-08-10 03:00:33','2026-08-10 03:00:33'),(76,93,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 40 - Hitam Putih',1,250000.00,'2026-08-10 03:02:10','2026-08-10 03:02:10'),(93,110,36,NULL,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','Size 41 - Hitam Putih',1,250000.00,'2026-08-12 20:30:34','2026-08-12 20:30:34');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_returns`
--

DROP TABLE IF EXISTS `order_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('return','cancellation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cancellation',
  `reason_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `receipt_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unboxing_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_duration` smallint unsigned DEFAULT NULL,
  `resolution` enum('refund','exchange') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exchange_request` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_amount` decimal(14,2) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `return_courier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipped_back_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `inspection_notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `decided_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_returns_return_number_unique` (`return_number`),
  KEY `order_returns_order_id_foreign` (`order_id`),
  KEY `order_returns_user_id_foreign` (`user_id`),
  KEY `order_returns_decided_by_foreign` (`decided_by`),
  CONSTRAINT `order_returns_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_returns_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_returns`
--

LOCK TABLES `order_returns` WRITE;
/*!40000 ALTER TABLE `order_returns` DISABLE KEYS */;
INSERT INTO `order_returns` VALUES (20,'RET-20260809-0001',3,9,'return','kurang_lengkap',NULL,NULL,NULL,NULL,NULL,'refund',NULL,127500.00,'completed','2026-08-09 00:15:06','JNE 2321321312','JP21312312','2026-08-09 00:17:39','2026-08-09 00:19:23','barang sesuai dengan kondisi awal',NULL,6,'2026-08-09 00:35:56','2026-08-09 00:14:11','2026-08-09 00:35:56'),(22,'RET-20260809-0002',2,9,'return','berubah_pikiran',NULL,NULL,NULL,NULL,NULL,'refund',NULL,145000.00,'completed','2026-08-09 01:43:55','JNE Express (Retur Otomatis)','RTR-BITESHIP-00000022','2026-08-09 01:43:55','2026-08-09 01:45:19','bagus',NULL,6,'2026-08-09 01:45:32','2026-08-09 01:43:38','2026-08-09 01:45:32'),(23,'RET-20260810-0001',57,9,'return','berubah_pikiran',NULL,NULL,NULL,NULL,NULL,'refund',NULL,NULL,'rejected',NULL,NULL,NULL,NULL,NULL,NULL,'Barang yang diberikan tidak sama dengan kondisi awal.',6,'2026-08-10 20:56:58','2026-08-10 00:50:42','2026-08-10 20:56:58');
/*!40000 ALTER TABLE `order_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_issued_at` timestamp NULL DEFAULT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL,
  `midtrans_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_actual_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_markup_profit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_insurance_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_insurance_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_revenue` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `courier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_note` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `referral_code_used` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer_id` bigint unsigned DEFAULT NULL,
  `referral_discount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `referral_commission` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_referrer_id_foreign` (`referrer_id`),
  KEY `orders_referral_code_used_index` (`referral_code_used`),
  CONSTRAINT `orders_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_chk_1` CHECK (json_valid(`shipping_address`))
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,9,'ORD-20260803-0001',NULL,NULL,255000.00,16000.00,271000.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','JNE Reguler (REG)','jne:reg','REC-JNER-000001','BCA','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-02 23:28:14','2026-08-03 01:12:25'),(2,9,'ORD-20260803-0002',NULL,NULL,145000.00,7500.00,152500.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Rumah\",\"recipient_name\":\"Munir\",\"phone\":\"085706013038\",\"address_line\":\"Gang Seger Waras, Ngembeh\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"213123\"}','J&T Express','jnt:reg','REC-JTEX-000002','QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-02 23:36:08','2026-08-03 01:12:26'),(3,9,'ORD-20260803-0003',NULL,NULL,127500.00,7000.00,134500.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','J&T Express','jnt:reg','REC-JTEX-000003','QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-03 00:57:35','2026-08-03 01:10:45'),(4,9,'ORD-20260803-0004',NULL,NULL,85000.00,8000.00,93000.00,0.00,0.00,0.00,0.00,0.00,NULL,'completed','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','jne','jne:reg','WYB-1785746311383','QRIS','paid',NULL,NULL,NULL,NULL,'2026-08-06 20:36:10',NULL,NULL,0.00,0.00,'2026-08-03 01:21:21','2026-08-06 20:36:10'),(5,9,'ORD-20260805-0001',NULL,NULL,127500.00,7000.00,134500.00,0.00,0.00,0.00,0.00,0.00,NULL,'pending','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','J&T Express','jnt:reg',NULL,'QRIS','unpaid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-04 23:59:05','2026-08-04 23:59:05'),(6,9,'ORD-20260805-0002',NULL,NULL,127500.00,8000.00,135500.00,0.00,0.00,0.00,0.00,0.00,NULL,'pending','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','JNE Reguler (REG)','jne:reg',NULL,'BCA','unpaid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 00:36:59','2026-08-05 00:36:59'),(7,9,'ORD-20260805-0003','INV/2026/08/0002','2026-08-09 19:17:34',85000.00,6500.00,91500.00,0.00,0.00,0.00,0.00,0.00,NULL,'processing','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','AnterAja Reguler','anteraja:reg',NULL,'QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 00:46:55','2026-08-09 19:17:34'),(8,9,'ORD-20260805-0004','INV/2026/08/0003','2026-08-09 19:17:35',145000.00,8500.00,153500.00,0.00,0.00,0.00,0.00,0.00,NULL,'processing','{\"label\":\"Rumah\",\"recipient_name\":\"Munir\",\"phone\":\"085706013038\",\"address_line\":\"Gang Seger Waras, Ngembeh\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"213123\"}','JNE Reguler (REG)','jne:reg',NULL,'QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 00:58:05','2026-08-09 19:17:35'),(9,9,'ORD-20260805-0005',NULL,NULL,127500.00,8500.00,136000.00,0.00,0.00,0.00,0.00,0.00,NULL,'pending','{\"label\":\"Rumah\",\"recipient_name\":\"Munir\",\"phone\":\"085706013038\",\"address_line\":\"Gang Seger Waras, Ngembeh\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"213123\"}','JNE Reguler (REG)','jne:reg',NULL,'QRIS','failed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 01:01:09','2026-08-09 19:17:35'),(10,9,'ORD-20260805-0006',NULL,NULL,127500.00,8000.00,135500.00,0.00,0.00,0.00,0.00,0.00,NULL,'pending','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','JNE Reguler (REG)','jne:reg',NULL,'QRIS','failed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 02:04:31','2026-08-09 19:17:35'),(11,9,'ORD-20260805-0007','INV/2026/08/0004','2026-08-09 19:17:36',127500.00,8000.00,135500.00,0.00,0.00,0.00,0.00,0.00,NULL,'cancelled','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','JNE Reguler (REG)','jne:reg',NULL,'QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-05 02:10:59','2026-08-09 19:17:36'),(57,9,'ORD-20260810-0001','INV/2026/08/0001','2026-08-09 19:08:25',1000000.00,12000.00,1012000.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','jne','jne:reg','WYB-1786328421759','QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-09 19:07:53','2026-08-09 19:20:23'),(72,9,'ORD-20260810-0002','INV/2026/08/0005','2026-08-10 02:32:30',250000.00,6500.00,256500.00,0.00,0.00,0.00,0.00,0.00,NULL,'completed','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"MuNir\",\"phone\":\"085706013038\",\"address_line\":\"Jln ploso\",\"city\":\"Mojokerto\",\"province\":\"Jawa Timur\",\"postal_code\":\"12313\"}','jne','jne:reg','WYB-1786354394454','BCA','paid',NULL,NULL,NULL,NULL,'2026-08-10 02:33:23',NULL,NULL,0.00,0.00,'2026-08-10 02:32:02','2026-08-10 02:33:23'),(79,59,'ORD-20260810-0003',NULL,NULL,250000.00,7000.00,249500.00,0.00,0.00,0.00,0.00,0.00,NULL,'cancelled','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"Sirojul\",\"phone\":\"08953356183\",\"address_line\":\"jln mojokerto\",\"city\":\"KABUPATEN MOJOKERTO\",\"province\":\"JAWA TIMUR\",\"postal_code\":\"61374\"}','AnterAja Reguler','anteraja:reg',NULL,'BCA','unpaid',NULL,'Saya berubah pikiran',NULL,'2026-08-10 02:59:18',NULL,'RECORD-MUNIR',9,7500.00,7500.00,'2026-08-10 02:51:40','2026-08-10 02:59:18'),(92,59,'ORD-20260810-0004',NULL,NULL,250000.00,7000.00,249500.00,0.00,0.00,0.00,0.00,0.00,NULL,'cancelled','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"Sirojul\",\"phone\":\"08953356183\",\"address_line\":\"jln mojokerto\",\"city\":\"KABUPATEN MOJOKERTO\",\"province\":\"JAWA TIMUR\",\"postal_code\":\"61374\"}','AnterAja Reguler','anteraja:reg',NULL,'QRIS','unpaid',NULL,'Ada kendala saat pembayaran',NULL,'2026-08-10 03:01:39',NULL,'RECORD-MUNIR',9,7500.00,7500.00,'2026-08-10 03:00:33','2026-08-10 03:01:39'),(93,59,'ORD-20260810-0005','INV/2026/08/0006','2026-08-10 03:10:06',250000.00,7000.00,249500.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Alamat Rumah\",\"recipient_name\":\"Sirojul\",\"phone\":\"08953356183\",\"address_line\":\"jln mojokerto\",\"city\":\"KABUPATEN MOJOKERTO\",\"province\":\"JAWA TIMUR\",\"postal_code\":\"61374\"}','jne','jne:reg','WYB-1786356651897','BCA','paid',NULL,NULL,NULL,NULL,NULL,'RECORD-MUNIR',9,7500.00,7500.00,'2026-08-10 03:02:10','2026-08-11 21:43:36'),(110,67,'ORD-20260813-0001','INV/2026/08/0007','2026-08-12 20:32:45',250000.00,16500.00,266500.00,0.00,0.00,0.00,0.00,0.00,NULL,'shipped','{\"label\":\"Rumah\",\"recipient_name\":\"Cindy\",\"phone\":\"08570612312\",\"address_line\":\"Jln kyai tambak deres no 30, bulak , Surabaya\",\"city\":\"KOTA SURABAYA\",\"province\":\"JAWA TIMUR\",\"postal_code\":\"60122\"}','jne','jne:reg','WYB-1786592042594','QRIS','paid',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,'2026-08-12 20:30:34','2026-08-12 20:34:06');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('admin@record.com','$2y$12$zOyH4IMfly5L89iWQGryKegjyVC0YdTkX35qRxGaJyYeRWcDl/6AG','2026-07-30 03:08:31');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_gateway` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_chk_1` CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view dashboard','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(2,'manage products','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(3,'manage banners','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(4,'manage discounts','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(5,'manage orders','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(6,'manage customers','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(7,'view reports','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(8,'manage roles','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(9,'manage permissions','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(10,'manage settings','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(11,'view activity logs','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(14,'manage returns','web','2026-08-08 00:20:01','2026-08-08 00:20:01'),(15,'manage rpay','web','2026-08-08 00:20:01','2026-08-08 00:20:01'),(16,'process withdrawals','web','2026-08-08 00:20:01','2026-08-08 00:20:01'),(17,'manage reviews','web','2026-08-11 19:26:53','2026-08-11 19:26:53');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',6,'seller_center_token','156d42db296f4dcbbedeb3d24cc28c287fe6000fdb32ac76c92346c9101fe1fc','[\"admin-access\"]','2026-07-14 00:20:04',NULL,'2026-07-14 00:19:40','2026-07-14 00:20:04'),(2,'App\\Models\\User',8,'customer_token','f3d0edb147245531563ee91b40db781c259ea144097c2db2c7aa7c4e718c1980','[\"customer-access\"]',NULL,NULL,'2026-07-14 00:20:40','2026-07-14 00:20:40');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  KEY `product_images_product_id_color_index` (`product_id`,`color`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (18,35,'products/hZ8lftJzYivKLKnhPMLfl3mE54M4kuSExleNxCoK.png',NULL,0,'2026-08-06 03:07:42','2026-08-06 03:07:42'),(19,34,'products/h83e2QMTBh9qy1IIe19Ukux8BrKQoPYquzhb0yZt.png',NULL,0,'2026-08-06 03:08:22','2026-08-06 03:08:22'),(20,33,'products/A2uvz1GbnbRUVS628c26CSPJEbqts2tldMF4vaU1.png',NULL,0,'2026-08-06 03:09:19','2026-08-06 03:09:19'),(21,32,'products/edxd1WU1MYLv6cRlkSjc5ORYukNA963qWc74BK01.png',NULL,0,'2026-08-06 03:09:43','2026-08-06 03:09:43'),(39,36,'products/9vWSnFF0UwMqCINlajB1Ohznea9CdrPph8QfULZi.png','Hitam Putih',100,'2026-08-06 23:18:59','2026-08-06 23:18:59'),(40,36,'products/ocJwOBivvyKFvJGx1PO1AtMmdUrPYzO5u62ZHRUv.png','Putih Biru',101,'2026-08-06 23:18:59','2026-08-06 23:18:59'),(41,36,'products/Tf1zeFfV5sIxMq0m3ABXTRM0UmBeC0RUg5BZgm17.png','Putih Khaki',102,'2026-08-06 23:18:59','2026-08-06 23:18:59'),(77,40,'products/H8Ojyy9teqq5oYJDrpXHpisYKawPa9oZ89mWKDJo.png',NULL,0,'2026-08-11 20:33:30','2026-08-11 20:33:30'),(78,40,'products/4TMbD6z1uzvAOQM0hGEc4EArwduiWoOVyt1hcLxo.png','Hitam Putih',100,'2026-08-11 20:33:30','2026-08-11 20:33:30'),(79,39,'products/BY602rUhn6tlzBbWdZGSbS4QiVm5uXbZgr1e4csZ.png','Hitam Merah',100,'2026-08-11 20:35:30','2026-08-11 20:35:30'),(80,38,'products/zhL9DTikEE2N1a4xT8AdPFoDT14Dm6BasOCRK4vr.png',NULL,0,'2026-08-11 20:36:38','2026-08-11 20:36:38'),(81,38,'products/2uTwvTHd9vMIp4LPjwi8aQPm9JulqAvgUaIUGTP8.png','Abu Putih',100,'2026-08-11 20:36:38','2026-08-11 20:36:38'),(82,38,'products/lJzJAVLC7TjNCMatpkGBIuTcMwVOcXaMLJlRTMyg.png','Hitam Putih',101,'2026-08-11 20:36:38','2026-08-11 20:36:38'),(83,38,'products/CT29KkQyPzjmCbFOeSzd3SX8Xs7PGyR1okgvcgoE.png','Putih Hitam',102,'2026-08-11 20:36:38','2026-08-11 20:36:38'),(84,37,'products/VVBdoy1Rvsc4fBiR3rOMNKZfrTh8IYsGhU8cAsPB.png',NULL,0,'2026-08-11 20:37:12','2026-08-11 20:37:12'),(85,37,'products/HAimFc9OAadOfmmXwkGgNyRxurWx91y4OLbbNxzB.png','Hitam Putih',100,'2026-08-11 20:37:12','2026-08-11 20:37:12'),(86,37,'products/R0dElH46Xio3qtVh38XdDDmcYoDxC095ngejAePw.png','Putih Abu',101,'2026-08-11 20:37:12','2026-08-11 20:37:12'),(87,37,'products/1Yb0TiQ3lAOBoM7HLLf2FfDhsQ38DV1avMOOsTQ8.png','Putih Biru',102,'2026-08-11 20:37:13','2026-08-11 20:37:13'),(88,31,'products/Nk8SIBvc3EKHvjGOuRm67dXqtOpIWR6LIZ2Xwxif.png',NULL,0,'2026-08-11 20:37:48','2026-08-11 20:37:48'),(89,31,'products/7Qsa5zbKeStMmHlAebqFjPiBATBgGaQGDf9B340p.png','Hitam Hitam',100,'2026-08-11 20:37:48','2026-08-11 20:37:48'),(90,31,'products/tvf87FFzmb22MN6Yy8qhZ0o0cW03k60wIvojM0Kf.png','Hitam Putih',101,'2026-08-11 20:37:48','2026-08-11 20:37:48'),(91,31,'products/Fpn6UnbJt5GKVgxxzhwtgGmmNDSjCERrAd1Mdl5Y.png','PUTIH ABU',102,'2026-08-11 20:37:48','2026-08-11 20:37:48'),(92,39,'products/bqb5PDhJmiKPL980F1HEQZUrXsxGFWuAu5grhI8N.png',NULL,0,'2026-08-11 20:41:38','2026-08-11 20:41:38'),(93,36,'products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png',NULL,0,'2026-08-13 02:07:38','2026-08-13 02:07:38'),(94,36,'products/aQan3JICy2oamRZK99FTTIyrlPfLEE61lNF1jIS4.png',NULL,1,'2026-08-13 02:07:38','2026-08-13 02:07:38'),(95,36,'products/rUmr64WvU7U5DPe2Gy8pJUObBQHM0HuUYsiZjDLj.png',NULL,2,'2026-08-13 02:07:38','2026-08-13 02:07:38'),(96,36,'products/v5UanyDLrLxoPaulRAL6CUcEv2DfkARlN0fBAz80.png',NULL,3,'2026-08-13 02:07:38','2026-08-13 02:07:38');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `photos` json DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `hidden_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hidden_by` bigint unsigned DEFAULT NULL,
  `hidden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_reviews_order_item_id_unique` (`order_item_id`),
  KEY `product_reviews_order_id_foreign` (`order_id`),
  KEY `product_reviews_user_id_foreign` (`user_id`),
  KEY `product_reviews_hidden_by_foreign` (`hidden_by`),
  KEY `product_reviews_product_id_is_hidden_created_at_index` (`product_id`,`is_hidden`,`created_at`),
  CONSTRAINT `product_reviews_hidden_by_foreign` FOREIGN KEY (`hidden_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
INSERT INTO `product_reviews` VALUES (20,36,72,55,9,5,NULL,NULL,0,NULL,NULL,NULL,'2026-08-11 20:43:44','2026-08-11 20:43:44');
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_shippings`
--

DROP TABLE IF EXISTS `product_shippings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_shippings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `weight_gram` int NOT NULL DEFAULT '0',
  `package_length` decimal(8,2) NOT NULL DEFAULT '0.00',
  `package_width` decimal(8,2) NOT NULL DEFAULT '0.00',
  `package_height` decimal(8,2) NOT NULL DEFAULT '0.00',
  `courier_providers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_shippings_product_id_foreign` (`product_id`),
  CONSTRAINT `product_shippings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_shippings_chk_1` CHECK (json_valid(`courier_providers`))
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_shippings`
--

LOCK TABLES `product_shippings` WRITE;
/*!40000 ALTER TABLE `product_shippings` DISABLE KEYS */;
INSERT INTO `product_shippings` VALUES (18,31,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-06 03:05:41','2026-08-12 23:50:26'),(19,32,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-06 03:05:41','2026-08-07 02:32:22'),(20,33,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-06 03:05:42','2026-08-07 02:32:22'),(21,34,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-06 03:05:42','2026-08-07 02:32:22'),(22,35,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-06 03:05:42','2026-08-07 02:32:23'),(23,36,400,5.00,5.00,5.00,'[\"jne\"]','2026-08-06 03:05:42','2026-08-13 02:07:38'),(24,37,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-07 02:32:23','2026-08-12 23:50:27'),(25,38,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-07 02:32:23','2026-08-12 23:50:27'),(26,39,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-07 02:32:23','2026-08-12 23:50:27'),(27,40,400,5.00,5.00,5.00,'[\"jne\",\"jnt\",\"sicepat\"]','2026-08-07 02:32:23','2026-08-12 23:50:27');
/*!40000 ALTER TABLE `product_shippings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `price_adjustment` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_product_id_size_color_unique` (`product_id`,`size`,`color`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1392 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1178,31,'30','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-30_01201930','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1179,31,'31','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-31_01201931','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1180,31,'32','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-32_01201932','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1181,31,'33','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-33_01201933','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1182,31,'34','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-34_01201934','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1183,31,'35','Hitam Putih',NULL,0,1.00,'RECORD-CONFERO-HP-35_01201935','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1184,31,'36','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-36_01201936','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1185,31,'37','Hitam Putih',NULL,0,0.00,'RECORD-CONFERO-HP-37_01201937','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1186,31,'30','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-30_01202130','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1187,31,'31','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-31_01202131','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1188,31,'32','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-32_01202132','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1189,31,'33','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-33_01202133','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1190,31,'34','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-34_01201834','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1191,31,'35','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-35_01201835','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1192,31,'36','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-36_01201836','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1193,31,'37','Hitam Hitam',NULL,0,0.00,'RECORD-CONFERO-HH-37_01201837','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1194,31,'30','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-30_01202030','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1195,31,'31','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-31_01202031','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1196,31,'32','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-32_01202032','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1197,31,'33','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-33_01202033','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1198,31,'34','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-34_01201734','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1199,31,'35','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-35_01201735','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1200,31,'36','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-36_01201736','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1201,31,'37','PUTIH ABU',NULL,0,0.00,'RECORD-CONFERO-PA-37_01201737','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1202,32,'30','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-30_01201030','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1203,32,'31','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-31_01201031','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1204,32,'32','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-32_01201032','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1205,32,'33','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-33_01201033','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1206,32,'34','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-34_01200834','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1207,32,'35','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-35_01200835','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1208,32,'36','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-36_01200836','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1209,32,'37','Hitam Putih',NULL,0,0.00,'RECORD-ZENIX-HP-37_01200837','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1210,32,'30','Hitam Hitam',NULL,0,0.00,'01201130RECORD-ZENIX-HH-30_','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1211,32,'31','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-31_01201131','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1212,32,'32','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-32_01201132','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1213,32,'33','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-33_01201133','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1214,32,'34','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-34_01200934','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1215,32,'35','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-35_01200935','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1216,32,'36','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-36_01200936','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1217,32,'37','Hitam Hitam',NULL,0,0.00,'RECORD-ZENIX-HH-37_01200937','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1218,33,'30','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-30_01230130','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1219,33,'31','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-31_01230131','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1220,33,'32','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-32_01230132','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1221,33,'33','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-33_01230133','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1222,33,'34','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-34_01230334','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1223,33,'35','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-35_01230335','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1224,33,'36','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-36_01230336','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1225,33,'37','Hitam Hitam',NULL,0,0.00,'RECORD-PHANTOM-HH-37_01230337','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1226,34,'30','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-30_01239930','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1227,34,'31','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-31_01239931','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1228,34,'32','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-32_01239932','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1229,34,'33','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-33_01239933','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1230,34,'34','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-34_01240234','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1231,34,'35','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-35_01240235','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1232,34,'36','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-36_01240236','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1233,34,'37','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-37_01240237','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1234,34,'38','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-38_01255738','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1235,34,'39','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-39_01255739','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1236,34,'40','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-40_01255740','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1237,34,'41','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-41_01255741','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1238,34,'42','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-42_01255742','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1239,34,'43','Putih Hitam',NULL,0,0.00,'RECORD-TUCSON-PH-43_01255743','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1240,34,'30','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-30_01240130','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1241,34,'31','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-31_01240131','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1242,34,'32','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-32_01240132','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1243,34,'33','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-33_01240133','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1244,34,'34','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-34_01240434','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1245,34,'35','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-35_01240435','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1246,34,'36','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-36_01240436','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1247,34,'37','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-37_01240437','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1248,34,'38','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-38_01255838','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1249,34,'39','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-39_01255839','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1250,34,'40','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-40_01255840','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1251,34,'41','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-41_01255841','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1252,34,'42','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-42_01255842','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1253,34,'43','Hitam Hitam',NULL,0,0.00,'RECORD-TUCSON-HH-43_01255843','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1254,34,'30','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-30_01240030','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1255,34,'31','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-31_01240031','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1256,34,'32','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-32_01240032','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1257,34,'33','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-33_01240033','2026-08-12 23:50:26','2026-08-12 23:50:26'),(1258,34,'34','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-34_01240334','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1259,34,'35','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-35_01240335','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1260,34,'36','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-36_01240336','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1261,34,'37','PUTIH ABU',NULL,0,0.00,'RECORD-TUCSON-PA-37_01240337','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1262,35,'30','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-30_01253730','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1263,35,'31','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-31_01253731','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1264,35,'32','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-32_01253732','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1265,35,'33','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-33_01253733','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1266,35,'34','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-34_01253534','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1267,35,'35','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-35_01253535','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1268,35,'36','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-36_01253536','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1269,35,'37','Putih Hitam',NULL,0,0.00,'RECORD-PALISADE-PH-37_01253537','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1270,35,'30','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-30_01253830','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1271,35,'31','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-31_01253831','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1272,35,'32','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-32_01253832','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1273,35,'33','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-33_01253833','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1274,35,'34','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-34_01253634','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1275,35,'35','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-35_01253635','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1276,35,'36','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-36_01253636','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1277,35,'37','Hitam Hitam',NULL,0,0.00,'RECORD-PALISADE-HH-37_01253637','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1296,37,'39','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-39_01245939','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1297,37,'40','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-40_01245940','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1298,37,'41','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-41_01245941','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1299,37,'42','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-42_01245942','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1300,37,'43','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-43_01245943','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1301,37,'44','Hitam Putih',NULL,0,0.00,'RECORD-FOGO-HP-44_01245944','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1302,37,'39','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-39_01245839','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1303,37,'40','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-40_01245840','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1304,37,'41','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-41_01245841','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1305,37,'42','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-42_01245842','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1306,37,'43','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-43_01245843','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1307,37,'44','Putih Biru',NULL,0,0.00,'RECORD-FOGO-PB-44_01245844','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1308,37,'39','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-39_01245739','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1309,37,'40','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-40_01245740','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1310,37,'41','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-41_01245741','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1311,37,'42','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-42_01245742','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1312,37,'43','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-43_01245743','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1313,37,'44','Putih Abu',NULL,0,0.00,'RECORD-FOGO-PA-44_01245744','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1314,38,'39','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-39_01249039','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1315,38,'40','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-40_01249040','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1316,38,'41','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-41_01249041','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1317,38,'42','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-42_01249042','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1318,38,'43','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-43_01249043','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1319,38,'44','Abu Putih',NULL,0,0.00,'RECORD-NEUTRA-AB-44_01249044','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1320,38,'39','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-39_01249139','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1321,38,'40','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-40_01249140','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1322,38,'41','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-41_01249141','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1323,38,'42','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-42_01249142','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1324,38,'43','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-43_01249143','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1325,38,'44','Hitam Putih',NULL,0,0.00,'RECORD-NEUTRA-HP-44_01249144','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1326,38,'39','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-39_01249239','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1327,38,'40','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-40_01249240','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1328,38,'41','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-41_01249241','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1329,38,'42','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-42_01249242','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1330,38,'43','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-43_01249243','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1331,38,'44','Putih Hitam',NULL,0,0.00,'RECORD-NEUTRA-PH-44_01249244','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1332,39,'26','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-26_01225226','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1333,39,'27','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-27_01225227','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1334,39,'28','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-28_01225228','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1335,39,'29','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-29_01225229','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1336,39,'30','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-30_01225230','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1337,39,'31','Hitam Merah',NULL,0,0.00,'RECORD-SPK19-HM-31_01225231','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1338,39,'26','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-26_01225326','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1339,39,'27','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-27_01225327','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1340,39,'28','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-28_01225328','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1341,39,'29','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-29_01225329','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1342,39,'30','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-30_01225330','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1343,39,'31','Biru Abu',NULL,0,0.00,'RECORD-SPK19-BA-31_01225331','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1344,39,'26','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-26_01225426','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1345,39,'27','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-27_01225427','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1346,39,'28','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-28_01225428','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1347,39,'29','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-29_01225429','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1348,39,'30','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-30_01225430','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1349,39,'31','Sitrun Biru',NULL,0,0.00,'RECORD-SPK19-SB-31_01225431','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1350,40,'33','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-33_01225033','2026-08-12 23:50:27','2026-08-13 00:24:17'),(1351,40,'34','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-34_01225034','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1352,40,'35','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-35_01225035','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1353,40,'36','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-36_01225036','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1354,40,'37','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-37_01224737','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1355,40,'38','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-38_01224738','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1356,40,'39','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-39_01224739','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1357,40,'40','Hitam Putih',NULL,0,0.00,'RECORD-KYLIE-HP-40_01224740','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1358,40,'33','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-33_01225133','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1359,40,'34','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-34_01225134','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1360,40,'35','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-35_01225135','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1361,40,'36','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-36_01225136','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1362,40,'37','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-37_01224837','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1363,40,'38','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-38_01224838','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1364,40,'39','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-39_01224839','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1365,40,'40','Hitam Hitam',NULL,0,0.00,'RECORD-KYLIE-HH-40_01224840','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1366,40,'33','Putih',NULL,0,0.00,'RECORD-KYLIE-P-33_01224933','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1367,40,'34','Putih',NULL,0,0.00,'RECORD-KYLIE-P-34_01224934','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1368,40,'35','Putih',NULL,0,0.00,'RECORD-KYLIE-P-35_01224935','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1369,40,'36','Putih',NULL,0,0.00,'RECORD-KYLIE-P-36_01224936','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1370,40,'37','Putih',NULL,0,0.00,'RECORD-KYLIE-P-37_01224637','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1371,40,'38','Putih',NULL,0,0.00,'RECORD-KYLIE-P-38_01224638','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1372,40,'39','Putih',NULL,0,0.00,'RECORD-KYLIE-P-39_01224639','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1373,40,'40','Putih',NULL,0,0.00,'RECORD-KYLIE-P-40_01224640','2026-08-12 23:50:27','2026-08-12 23:50:27'),(1374,36,'39','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-39_01246239','2026-08-13 02:07:38','2026-08-13 02:42:03'),(1375,36,'40','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-40_01246240','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1376,36,'41','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-41_01246241','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1377,36,'42','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-42_01246242','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1378,36,'43','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-43_01246243','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1379,36,'44','Hitam Putih','#cccccc',1,0.00,'RECORD-BLANCO-HP-44_01246244','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1380,36,'39','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-39_01246139','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1381,36,'40','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-40_01246140','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1382,36,'41','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-41_01246141','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1383,36,'42','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-42_01246142','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1384,36,'43','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-43_01246143','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1385,36,'44','Putih Biru','#cccccc',1,0.00,'RECORD-BLANCO-PB-44_01246144','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1386,36,'39','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-39_01246039','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1387,36,'40','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-40_01246040','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1388,36,'41','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-41_01246041','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1389,36,'42','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-42_01246042','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1390,36,'43','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-43_01246043','2026-08-13 02:07:38','2026-08-13 02:07:38'),(1391,36,'44','Putih Khaki','#cccccc',1,0.00,'RECORD-BLANCO-PK-44_01246044','2026-08-13 02:07:38','2026-08-13 02:07:38');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','sold_out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_chk_1` CHECK (json_valid(`details`))
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (31,3,'RECORD CONFERO SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','record-confero-sepatu-sekolah-anak-tk-sd-smp-laki-perempuan-black-hitam-putih-perekat-30-37-4-13thn-lEVov','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/Nk8SIBvc3EKHvjGOuRm67dXqtOpIWR6LIZ2Xwxif.png',1,'active','2026-08-06 03:05:41','2026-08-12 23:50:26'),(32,3,'RECORD ZENIX BTS SEPATU SEKOLAH ANAK TK SD SMP COWO CEWE FULL BLACK HITAM PUTIH PEREKAT 30-37 4-13TH','record-zenix-bts-sepatu-sekolah-anak-tk-sd-smp-cowo-cewe-full-black-hitam-putih-perekat-30-37-4-13th-j1kcK','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/edxd1WU1MYLv6cRlkSjc5ORYukNA963qWc74BK01.png',1,'active','2026-08-06 03:05:41','2026-08-12 23:50:26'),(33,3,'RECORD PHANTOM SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','record-phantom-sepatu-sekolah-anak-tk-sd-smp-laki-perempuan-black-hitam-putih-perekat-30-37-4-13thn-Kd9Z8','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/A2uvz1GbnbRUVS628c26CSPJEbqts2tldMF4vaU1.png',1,'active','2026-08-06 03:05:42','2026-08-12 23:50:33'),(34,3,'RECORD TUCSON SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','record-tucson-sepatu-sekolah-anak-tk-sd-smp-laki-perempuan-black-hitam-putih-perekat-30-37-4-13thn-NVO7v','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/h83e2QMTBh9qy1IIe19Ukux8BrKQoPYquzhb0yZt.png',0,'active','2026-08-06 03:05:42','2026-08-12 23:50:26'),(35,3,'RECORD PALISADE SEPATU SEKOLAH ANAK TK SD SMP LAKI PEREMPUAN BLACK HITAM PUTIH  PEREKAT 30-37 4-13THN','record-palisade-sepatu-sekolah-anak-tk-sd-smp-laki-perempuan-black-hitam-putih-perekat-30-37-4-13thn-jsBuX','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/hZ8lftJzYivKLKnhPMLfl3mE54M4kuSExleNxCoK.png',0,'active','2026-08-06 03:05:42','2026-08-12 23:50:27'),(36,4,'RECORD BLANCO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','record-blanco-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-n3VAv','Sepatu lifestyle bahan premium jahitan kuat','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,18,'products/D5J4iIJWobsILb9tW2j8oJ4pkM0F7CAk0KFaLLjn.png',1,'active','2026-08-06 03:05:42','2026-08-13 02:42:03'),(37,4,'RECORD FOGO SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','record-fogo-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-AjmwE','Sepatu lifestyle bahan premium jahitan kuat','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/VVBdoy1Rvsc4fBiR3rOMNKZfrTh8IYsGhU8cAsPB.png',0,'active','2026-08-07 02:32:23','2026-08-12 23:50:27'),(38,4,'RECORD NEUTRA SEPATU SOL FLAT TALI IMPORT SEKOLAH FASHION SPORT KASUAL KERJA LAKI PEREMPUAN 39-44','record-neutra-sepatu-sol-flat-tali-import-sekolah-fashion-sport-kasual-kerja-laki-perempuan-39-44-gd1cz','Sepatu lifestyle bahan premium jahitan kuat','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/zhL9DTikEE2N1a4xT8AdPFoDT14Dm6BasOCRK4vr.png',0,'active','2026-08-07 02:32:23','2026-08-12 23:50:27'),(39,2,'RECORD SUPER KIDS19 FASHIONABLE SEPATU LAMPU LED ANAK LAKI PAUD 1-7THN  VELKRO MOTO BIKE  SIZE 26-31','record-super-kids19-fashionable-sepatu-lampu-led-anak-laki-paud-1-7thn-velkro-moto-bike-size-26-31-aYgiB','Sepatu anak lucu jahitan kuat dan terdapat lampu','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/bqb5PDhJmiKPL980F1HEQZUrXsxGFWuAu5grhI8N.png',0,'active','2026-08-07 02:32:23','2026-08-12 23:50:27'),(40,5,'RECORD KYLIE BTS SEPATU SEKOLAH ANAK SMP SMA PEREMPUAN ALL BLACK HITAM/PUTIH 12 TH KEATAS TALI 33-40','record-kylie-bts-sepatu-sekolah-anak-smp-sma-perempuan-all-black-hitamputih-12-th-keatas-tali-33-40-0lJd2','Sepatu sekolah bahan kulit sintetis, jahitan kuat, nyaman dipakai seharian.','{\"material\":\"Kulit Sintetis\"}',250000.00,NULL,0,'products/H8Ojyy9teqq5oYJDrpXHpisYKawPa9oZ89mWKDJo.png',0,'active','2026-08-07 02:32:23','2026-08-13 00:24:17');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(14,1),(15,1),(16,1),(17,1),(2,2),(3,2),(4,2),(5,2),(6,2),(14,2),(16,2),(17,2),(1,8),(6,8),(7,8),(11,8),(15,8),(16,8);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2026-07-14 00:12:27','2026-07-14 00:12:27'),(2,'admin','web','2026-07-14 00:14:32','2026-07-14 00:14:32'),(3,'customer','web','2026-07-14 00:20:40','2026-07-14 00:20:40'),(8,'management','web','2026-08-08 00:20:01','2026-08-08 00:20:01');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rpay_transactions`
--

DROP TABLE IF EXISTS `rpay_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpay_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `direction` enum('credit','debit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL,
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rpay_transaksi_sumber_unik` (`reference_type`,`reference_id`,`source`),
  KEY `rpay_transactions_created_by_foreign` (`created_by`),
  KEY `rpay_transactions_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `rpay_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `rpay_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rpay_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rpay_transactions`
--

LOCK TABLES `rpay_transactions` WRITE;
/*!40000 ALTER TABLE `rpay_transactions` DISABLE KEYS */;
INSERT INTO `rpay_transactions` VALUES (13,9,'credit',127500.00,127500.00,'refund','App\\Models\\OrderReturn',20,'Pengembalian dana pesanan ORD-20260803-0003',6,'2026-08-09 00:35:56','2026-08-09 00:35:56'),(14,9,'debit',127500.00,0.00,'withdrawal','App\\Models\\RpayWithdrawal',3,'Pencairan ke BCA (WD260809RKATD)',9,'2026-08-09 00:36:59','2026-08-09 00:36:59'),(15,9,'credit',145000.00,145000.00,'refund','App\\Models\\OrderReturn',22,'Pengembalian dana pesanan ORD-20260803-0002',6,'2026-08-09 01:45:32','2026-08-09 01:45:32'),(25,9,'credit',7500.00,152500.00,'referral','App\\Models\\Order',93,'Komisi referal dari pesanan ORD-20260810-0005',NULL,'2026-08-10 03:10:06','2026-08-10 03:10:06');
/*!40000 ALTER TABLE `rpay_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rpay_withdrawals`
--

DROP TABLE IF EXISTS `rpay_withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpay_withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `estimated_ready_at` date DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `processed_by` bigint unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rpay_withdrawals_reference_unique` (`reference`),
  KEY `rpay_withdrawals_user_id_foreign` (`user_id`),
  KEY `rpay_withdrawals_processed_by_foreign` (`processed_by`),
  KEY `rpay_withdrawals_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `rpay_withdrawals_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rpay_withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rpay_withdrawals`
--

LOCK TABLES `rpay_withdrawals` WRITE;
/*!40000 ALTER TABLE `rpay_withdrawals` DISABLE KEYS */;
INSERT INTO `rpay_withdrawals` VALUES (3,9,'WD260809RKATD',127500.00,'BCA','123123121','MUNIR','completed','2026-08-11',NULL,6,'2026-08-09 00:37:35','2026-08-09 00:36:59','2026-08-09 00:37:36');
/*!40000 ALTER TABLE `rpay_withdrawals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0r5Do0DONbAVkS4FkRGbsX0JxDJdecmfBfbpqZvC',9,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJuNjNnbjdseGszcjJqc0lWZktwdndqRlk4d1hBeXNzWEw5VlZ5OWRNIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvZnJvbnRlbmQtZWNvbW1lcmNlLXJlY29yZC50ZXN0Iiwicm91dGUiOiJob21lIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo5fQ==',1786688227),('1DZksqDcOrfBLJ730d7NrA286wneXQ1qAXpRrJPy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOVZKc2poclQxeWJBdkF0bFpXZlNTeGxRRlJPelJ3WmJSaG5palM4RSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786703049),('AncQniOl8EkGBgStqTDfAUdovnzQnnphu1W4Yrqs',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ3pUc1VuYWNnUEJNMlRtRm5GeHU4T3pLcXA1dDgxOHBDeVd6ZmhaWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1787140626),('CkQm5NdHiCSLpr3uN85AX1z1lgp2o2iufklNzQlV',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.28.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieUJOdlF4ODRPVXBvOU9abk5oeHhGdUx4SXRBbWtRc1h5aUVlcENicCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC8/aGVyZD1wcmV2aWV3IjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1787140178),('cLtndoGnRKnurucLLmewq0cT6uQafdxuoNh9UJS8',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic1Z1QmY3c3RxTlFPVGU5bnpMczF6S3p3QmlnRnJ0RWpQVFh3dko2diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787196450),('eG1ylmcVcjQBdGGoFLFMsiQ4UoMOIa2EVIEcP5yH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiODlOMUdNY0lkcDlZZGxxM2xyWnI5VjN5bk51WWRoRkw2WkxWNkdVayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787129954),('eZgy3IObDY8nx0lqIoglLwbwLPwyGaSk85ihbNOt',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJRSDBsaWRDYkZSR0daYXYxclZXVG1Od3hQakk4YWd3RmJra2s1U0luIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Zyb250ZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1787275528),('KC4VKEnQ6835tVbOcfkjh5z27SfezaIHJE6u4CV0',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJqNTI2cERZcXRiZG9tU2UwdW5abWxLNTNxS0xZaGU5bllpV1pWUmFSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Zyb250ZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1787129943),('l3fFAxju7rrhe06J3HMlTkRV4Nf8Fe8vLasdKq8o',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.28.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','eyJfdG9rZW4iOiJ2UXBUVUI2NDBSU2pINVB2cmpQdUpMejNUWFNuR0xJN2pKVWVWeFNEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Zyb250ZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdFwvP2hlcmQ9cHJldmlldyIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1786696954),('NbOacjIKaRpU8c7RAFw5GRTPVABeOl5Mye5Cdk14',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUx0N1ltcHpoRHN6TVo2eHJDTXd2ajNwMzVyNnBBMUZvbW9HckJ0WiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787129954),('OgMeH1sFF6dxUm3TFRtFoCDxMro70ouqEm5lLRAT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWxTNXZIN3c4YW5BTERCZUpvOWRqZjEyVHc2NjAxbXpwelVmWDQ0NSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787195326),('OzgSZVTE4c6sOqXljS2uHXU00oz9TaE1AZlQGl18',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoiTEVtOGljeE5CYlZPTVlMN3JMRzZCZFFHNEd4OEtjWGdEVktKalBUOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1787140044),('Q9yrgkI0Ssww9UvqhcIYU63fxzWefTkIxcuMHBoe',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoidVdGUlJCdUlYWTREcE9zTFBoMGtPSFNhaGNDMkZnMmZNZTFLZnVwSCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0OToiaHR0cDovL2JhY2tlbmQtZWNvbW1lcmNlLXJlY29yZC50ZXN0L2FkbWluL29yZGVycyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ4OiJodHRwOi8vYmFja2VuZC1lY29tbWVyY2UtcmVjb3JkLnRlc3QvYWRtaW4vbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1786686866),('RFR8wRyzsXUPi0iMAe8q4z1M3fKNVTQcTYUT8buk',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/1.32885.1 Chrome/148.0.7778.280 Electron/42.9.2 Safari/537.36 MSIX','eyJfdG9rZW4iOiI1eEN6dHFpTmdQdHV0dWozeFFzZkJkWDRLaDNob0NHMnVFS0I4Sm96IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MzkxXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1787199601),('T9Yp4K1aQne18xXUkdwinx6zYO1aW2i2wD5Wlv7X',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUms5amtmWnJMbEFKb2pEUUFGWDM5RnNsNlpHY2pzcFRWY29tWkNRZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9iYWNrZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787275533),('tNqNXIE2FTEWJ7YLonA7lyydgULqOLsxc3KjWv6n',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJuRnZ1dVh5U3QyUUVOZzVTVFNnaGZ1d2dVbEJPV3JPbVE5QXIxcU5VIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MzkxIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1787199530),('TTdgms1x6749ZvUXEvqB9vtBD9ihKZePFhzTSays',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Herd/1.28.0 Chrome/120.0.6099.291 Electron/28.2.5 Safari/537.36','eyJfdG9rZW4iOiJ5R2Z2cjhmekxEa3EwYnh6OW9qYWR6WmZLcVNGMzZzYVMzUWRsOFJLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Zyb250ZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdFwvP2hlcmQ9cHJldmlldyIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1786696888),('Vb2QCT0xrFepgoJgmad1G5liuXemqzsDyd32JJJV',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJiUFpRbUNtYU5BQW5RQXg4VURHWGo2dm5JYWFpTEtqQnVNSmNSUFVtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MzcwXC9fdWppLW9uZ2tpclwvMjAiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1786712400),('wVkxKJBEiLVT8fSO53XR509gdL1miGZJYOpb7JIL',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJOajkwSzFMdFNRa2E0Snp3OTVTc3ZtOFBvQkY3MFAxa29WdWw0djJYIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Zyb250ZW5kLWVjb21tZXJjZS1yZWNvcmQudGVzdCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1787199849);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('customer','admin','super_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `rpay_balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `referral_code` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_issued_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_referral_code_unique` (`referral_code`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin Record','super_admin','superadmin@record.com','081234567890',NULL,0,0.00,NULL,NULL,'2026-07-10 01:43:29','$2y$12$INOKidyOjQ4O5xeJlYHCr.Uq7D3ML38N6zDotj3APgY4GcZA1EzxG',NULL,'2026-07-10 01:43:30','2026-07-10 01:43:30'),(2,'Admin Record','admin','admin@record.com','081234567891',NULL,0,0.00,NULL,NULL,'2026-07-10 01:43:30','$2y$12$uQYVE.JjzLLN6AbItKLlHe5lPPoLCb1/xPm/tqCi4R3l7cx9XYNdO',NULL,'2026-07-10 01:43:30','2026-07-10 01:43:30'),(3,'Budi Customer','customer','customer@record.com','081234567892',NULL,0,0.00,NULL,NULL,'2026-07-10 01:43:30','$2y$12$hUoLo5Uk3hoRNhpAW/ScEeQFS2ou/BS1DHxeUakK9GdJCBfFaj3vy',NULL,'2026-07-10 01:43:30','2026-08-07 00:57:02'),(4,'Siti Pembeli','customer','siti@record.com','081234567893',NULL,0,0.00,NULL,NULL,'2026-07-10 01:43:31','$2y$12$nHksrOVTUC.E9PKMf8zMOOP5Aa1SZ5Lh0iTC4ED0Tg1ES85CanrwW',NULL,'2026-07-10 01:43:31','2026-07-10 01:43:31'),(6,'Super Admin','super_admin','superadmin@sellercenter.com',NULL,NULL,0,0.00,NULL,NULL,NULL,'$2y$12$210Tj3HrDN0i22PDWeFir.t8EwT.U.VvyAL6.Swzjr6jaJ7CedSe6','ydXy8scqR9wk4jUSYUyDp5s54jSeGTQEBU6gCwRshqC8877472Xx2v4PrjD4','2026-07-14 00:14:33','2026-07-14 00:54:23'),(7,'Admin Toko','admin','admin@sellercenter.com',NULL,NULL,0,0.00,NULL,NULL,NULL,'$2y$12$7rpg3Mcq96ngx/DuRDzYO.B5ILngWpNqB3FkCMyEmuZTzYaEy0WpS',NULL,'2026-07-14 00:14:34','2026-07-14 00:54:23'),(8,'Test Customer','customer','customer@test.com',NULL,NULL,0,0.00,NULL,NULL,NULL,'$2y$12$Xoy8y3L9sqk2JjSlMR2BX.cOyiBkD8lcEgFP982y9mF0p7FBm7IwW',NULL,'2026-07-14 00:20:40','2026-07-14 00:20:40'),(9,'MuNir','customer','munirxanm40@gmail.com','085706013038',NULL,0,152500.00,'RECORD-MUNIR','2026-08-10 02:32:30',NULL,'$2y$12$Vk9aDfP3fcKzOLiTUiC54u3uvq3EBln812ACr3lYe/.WW2g1yy//a','qC1e9WfcW2Kw5maVmomj5r17wn33uXR7o9V1IN7sFvDTeTnipwtHMBmwPjwS','2026-07-20 21:53:08','2026-08-10 03:10:06'),(15,'Sirojul','customer','munirxam@gmail.com','08723817231',NULL,0,0.00,NULL,NULL,NULL,'$2y$12$LX12qc3Ta1x7ke5fYejTXuoPPE9Ji7zZK9LSc/myAA6A/bRVfyp9C',NULL,'2026-08-07 01:40:45','2026-08-07 01:51:41'),(59,'Sirojul','customer','munirxanm@gmail.com','08953356183',NULL,0,0.00,'RECORD-SIROJUL','2026-08-10 03:10:06',NULL,'$2y$12$o0sRzrxyvZYau6Dz0Jsj6OIeYGytYbiWZ5/XQy8l2PYIHMG02kjtm',NULL,'2026-08-10 02:49:37','2026-08-10 03:10:06'),(65,'Rojul','customer','munirxanm12@gmail.com','08578213798213',NULL,0,0.00,NULL,NULL,NULL,'$2y$12$bB4YfrXnYiIzSkEY/Ye3webZqEVMUvaW3K0YTw3Fd3CWUh71fna76',NULL,'2026-08-11 00:08:10','2026-08-11 00:08:10'),(67,'CIndy','customer','munirxanm1@gmail.com','085790987765',NULL,0,0.00,'RECORD-CINDY','2026-08-12 20:32:45',NULL,'$2y$12$/jPwCqrpK/ipdf2lySxM3.puPUpOqzcsk3LjRGNMToW5Z7qtqZ4ym',NULL,'2026-08-12 20:26:25','2026-08-12 20:32:45');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_settings`
--

DROP TABLE IF EXISTS `website_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `website_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `website_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_settings`
--

LOCK TABLES `website_settings` WRITE;
/*!40000 ALTER TABLE `website_settings` DISABLE KEYS */;
INSERT INTO `website_settings` VALUES (1,'site_name','Shopee-Like E-Commerce','string','general','Nama Toko','Nama toko yang tampil di judul halaman dan email.','2026-07-14 00:14:34','2026-08-04 03:21:55'),(2,'site_logo',NULL,'image','general','Logo Toko','Format PNG transparan, disarankan 512 × 512 piksel.','2026-07-14 00:14:34','2026-08-04 03:19:17'),(3,'store_address','Jl. E-Commerce No. 123, Jakarta','text','general','Alamat Toko','Alamat lengkap toko untuk halaman kontak & label pengiriman.','2026-07-14 00:14:34','2026-08-04 03:19:17'),(4,'store_city_id','152','integer','shipping','ID Kota Asal','ID kota asal pengiriman sesuai data kurir.','2026-07-14 00:14:34','2026-08-04 03:19:17'),(5,'couriers','[\"jne\",\"jnt\",\"sicepat\",\"anteraja\",\"pos\"]','json','shipping','Kurir Aktif','Kurir yang bisa dipilih pembeli saat checkout.','2026-07-14 00:14:34','2026-08-04 03:21:55'),(6,'site_tagline','Langkah Nyaman Setiap Hari','string','general','Tagline Toko','Slogan singkat di bawah nama toko.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(7,'site_description',NULL,'text','general','Deskripsi Toko','Penjelasan singkat tentang toko, dipakai untuk SEO.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(8,'site_favicon',NULL,'image','general','Favicon','Ikon kecil di tab browser, 64 × 64 piksel.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(9,'contact_email',NULL,'string','contact','Email Customer Service','Email yang bisa dihubungi pembeli.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(10,'contact_phone',NULL,'string','contact','Nomor Telepon','Nomor telepon toko.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(11,'contact_whatsapp',NULL,'string','contact','Nomor WhatsApp','Diawali 62, contoh: 6281234567890.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(12,'social_instagram',NULL,'string','contact','Instagram','Username tanpa tanda @.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(13,'social_facebook',NULL,'string','contact','Facebook','Nama halaman atau URL Facebook.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(14,'social_tiktok',NULL,'string','contact','TikTok','Username tanpa tanda @.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(15,'store_postal_code','60117','string','shipping','Kode Pos Toko','Kode pos alamat asal pengiriman.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(16,'free_shipping_min','0','integer','shipping','Minimal Gratis Ongkir','Belanja minimal (Rp) agar ongkir gratis. Isi 0 untuk menonaktifkan.','2026-08-04 03:19:17','2026-08-04 03:21:55'),(17,'bank_name',NULL,'string','payment','Nama Bank','Bank tujuan transfer manual.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(18,'bank_account',NULL,'string','payment','Nomor Rekening','Nomor rekening tujuan transfer.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(19,'bank_holder',NULL,'string','payment','Atas Nama','Nama pemilik rekening.','2026-08-04 03:19:17','2026-08-04 03:19:17'),(20,'enable_cod','1','boolean','payment','Aktifkan COD','Izinkan pembeli membayar di tempat (Cash On Delivery).','2026-08-04 03:19:17','2026-08-04 03:19:17'),(21,'payment_note',NULL,'text','payment','Catatan Pembayaran','Instruksi tambahan yang tampil di halaman pembayaran.','2026-08-04 03:19:18','2026-08-04 03:19:18'),(22,'maintenance_mode','0','boolean','operational','Mode Perbaikan','Jika aktif, halaman toko ditutup sementara untuk pembeli.','2026-08-04 03:19:18','2026-08-04 03:19:18'),(23,'maintenance_message','Toko sedang dalam perbaikan. Silakan kembali beberapa saat lagi.','text','operational','Pesan Mode Perbaikan','Pesan yang tampil ke pembeli saat mode perbaikan aktif.','2026-08-04 03:19:18','2026-08-04 03:19:18'),(24,'order_auto_cancel','24','integer','operational','Batas Bayar (Jam)','Pesanan dibatalkan otomatis jika belum dibayar melewati jam ini. 0 = nonaktif.','2026-08-04 03:19:18','2026-08-04 03:19:18'),(25,'low_stock_threshold','5','integer','operational','Ambang Stok Menipis','Produk ditandai stok menipis jika jumlahnya di bawah angka ini.','2026-08-04 03:19:18','2026-08-04 03:19:18');
/*!40000 ALTER TABLE `website_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-21  8:35:01
