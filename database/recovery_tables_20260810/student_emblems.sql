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
-- Table structure for table `student_emblems`
--

DROP TABLE IF EXISTS `student_emblems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_emblems` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `tier` enum('bronze','silver','gold','master') NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`lesson_id`,`tier`),
  KEY `grade_id` (`grade_id`,`user_id`),
  KEY `subject_id` (`subject_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `student_emblems_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_emblems_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`),
  CONSTRAINT `student_emblems_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `student_emblems_ibfk_4` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_emblems`
--

LOCK TABLES `student_emblems` WRITE;
/*!40000 ALTER TABLE `student_emblems` DISABLE KEYS */;
INSERT INTO `student_emblems` VALUES (1,3,1,7,27,'bronze','2026-08-06 17:31:59'),(2,1,4,36,440,'bronze','2026-08-06 18:42:31'),(3,1,4,37,444,'bronze','2026-08-06 18:46:09'),(5,4,4,37,441,'bronze','2026-08-06 19:24:00'),(6,10,1,12,76,'bronze','2026-08-07 14:17:07'),(7,3,1,9,31,'bronze','2026-08-07 14:23:02'),(8,1,4,32,364,'bronze','2026-08-07 21:30:31'),(9,16,3,24,352,'bronze','2026-08-08 02:56:59'),(10,16,3,24,353,'bronze','2026-08-08 02:57:32'),(11,16,3,24,370,'bronze','2026-08-08 02:57:48'),(12,18,1,10,97,'bronze','2026-08-08 11:05:49'),(13,18,1,10,98,'bronze','2026-08-08 11:06:03'),(14,21,1,12,79,'bronze','2026-08-08 12:03:28'),(15,21,1,12,77,'bronze','2026-08-08 13:04:53'),(16,21,1,10,98,'bronze','2026-08-08 13:05:11');
/*!40000 ALTER TABLE `student_emblems` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-10 22:04:11
