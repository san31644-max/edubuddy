-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: educhat
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `chat_sessions`
--

DROP TABLE IF EXISTS `chat_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_sessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `subject_id` (`subject_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_sessions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_sessions_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_sessions`
--

LOCK TABLES `chat_sessions` WRITE;
/*!40000 ALTER TABLE `chat_sessions` DISABLE KEYS */;
INSERT INTO `chat_sessions` VALUES (1,3,2,NULL,'Learning chat','2026-08-06 17:35:00','2026-08-06 23:05:00'),(2,3,2,NULL,'Learning chat','2026-08-06 17:35:40','2026-08-06 23:05:40'),(3,1,31,NULL,'Learning chat','2026-08-06 18:33:39','2026-08-07 00:03:39'),(4,1,32,NULL,'Learning chat','2026-08-07 13:53:06','2026-08-07 19:23:06'),(5,1,32,NULL,'Learning chat','2026-08-07 13:53:40','2026-08-07 19:23:40'),(6,10,12,NULL,'Learning chat','2026-08-07 13:55:29','2026-08-07 19:25:29'),(7,10,12,NULL,'Learning chat','2026-08-07 13:56:10','2026-08-07 19:26:10'),(8,10,12,NULL,'Learning chat','2026-08-07 13:56:33','2026-08-07 19:26:33'),(9,10,12,NULL,'Learning chat','2026-08-07 14:14:48','2026-08-07 19:44:48'),(10,10,12,NULL,'Learning chat','2026-08-07 14:16:04','2026-08-07 19:46:04'),(11,10,12,NULL,'Learning chat','2026-08-07 14:16:41','2026-08-07 19:46:41'),(12,3,9,NULL,'Learning chat','2026-08-07 14:24:15','2026-08-07 19:54:15'),(13,1,36,NULL,'Learning chat','2026-08-07 14:34:40','2026-08-07 20:04:40'),(14,1,37,NULL,'Learning chat','2026-08-07 14:35:19','2026-08-07 20:05:19'),(15,1,NULL,NULL,'Learning chat','2026-08-07 21:01:29','2026-08-08 02:31:29'),(16,1,NULL,NULL,'Learning chat','2026-08-07 21:01:57','2026-08-08 02:31:57'),(17,1,NULL,NULL,'Learning chat','2026-08-07 21:02:13','2026-08-08 02:32:13'),(18,1,NULL,NULL,'Learning chat','2026-08-07 21:02:35','2026-08-08 02:32:35'),(19,1,NULL,NULL,'Learning chat','2026-08-07 21:03:11','2026-08-08 02:33:11'),(20,16,NULL,NULL,'Learning chat','2026-08-07 23:12:22','2026-08-08 04:42:22'),(21,16,NULL,NULL,'Learning chat','2026-08-08 02:55:47','2026-08-08 08:25:47'),(22,16,NULL,NULL,'Learning chat','2026-08-08 02:56:22','2026-08-08 08:26:22'),(23,16,NULL,NULL,'Learning chat','2026-08-08 03:00:37','2026-08-08 08:30:37'),(24,19,NULL,NULL,'Learning chat','2026-08-08 11:49:32','2026-08-08 17:19:32'),(25,19,NULL,NULL,'Learning chat','2026-08-08 11:50:22','2026-08-08 17:20:22'),(26,21,NULL,NULL,'Learning chat','2026-08-08 13:01:32','2026-08-08 18:31:32'),(27,17,NULL,NULL,'Learning chat','2026-08-09 02:59:14','2026-08-09 08:29:14'),(28,22,NULL,NULL,'Learning chat','2026-08-09 03:14:44','2026-08-09 08:44:44'),(29,23,NULL,NULL,'Learning chat','2026-08-10 09:13:19','2026-08-10 14:43:19'),(30,23,25,NULL,'Learning chat','2026-08-10 09:17:19','2026-08-10 14:47:19'),(31,23,30,NULL,'Learning chat','2026-08-10 10:49:32','2026-08-10 16:19:32');
/*!40000 ALTER TABLE `chat_sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-10 22:04:09
