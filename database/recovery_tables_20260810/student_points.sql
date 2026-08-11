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
-- Table structure for table `student_points`
--

DROP TABLE IF EXISTS `student_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_points` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `activity_type` varchar(40) NOT NULL,
  `points` int(11) NOT NULL,
  `award_key` varchar(190) NOT NULL,
  `description` varchar(255) NOT NULL,
  `awarded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`award_key`),
  KEY `grade_id` (`grade_id`,`user_id`),
  KEY `user_id_2` (`user_id`,`awarded_at`),
  KEY `subject_id` (`subject_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `student_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_points_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`),
  CONSTRAINT `student_points_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `student_points_ibfk_4` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_points_ibfk_5` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_points`
--

LOCK TABLES `student_points` WRITE;
/*!40000 ALTER TABLE `student_points` DISABLE KEYS */;
INSERT INTO `student_points` VALUES (1,3,1,7,27,NULL,'lesson_complete',20,'lesson:27:complete','Lesson completed','2026-08-06 17:31:59'),(2,1,4,36,440,NULL,'lesson_complete',20,'lesson:440:complete','Lesson completed','2026-08-06 18:42:31'),(3,1,4,36,440,437,'quiz_attempt',10,'quiz:437:first','First quiz attempt','2026-08-06 18:42:45'),(4,1,4,37,444,NULL,'lesson_complete',20,'lesson:444:complete','Lesson completed','2026-08-06 18:46:09'),(6,4,4,37,441,NULL,'lesson_complete',20,'lesson:441:complete','Lesson completed','2026-08-06 19:24:00'),(7,4,4,37,441,438,'quiz_attempt',10,'quiz:438:first','First quiz attempt','2026-08-06 19:24:14'),(8,10,1,12,76,NULL,'lesson_complete',20,'lesson:76:complete','Lesson completed','2026-08-07 14:17:07'),(9,10,1,12,76,73,'quiz_attempt',10,'quiz:73:first','First quiz attempt','2026-08-07 14:17:46'),(10,3,1,3,16,4,'quiz_attempt',10,'quiz:4:first','First quiz attempt','2026-08-07 14:20:39'),(11,3,1,9,31,NULL,'lesson_complete',20,'lesson:31:complete','Lesson completed','2026-08-07 14:23:02'),(12,1,4,32,364,NULL,'lesson_complete',20,'lesson:364:complete','Lesson completed','2026-08-07 21:30:31'),(13,16,3,24,352,NULL,'lesson_complete',20,'lesson:352:complete','Lesson completed','2026-08-08 02:56:59'),(14,16,3,24,353,NULL,'lesson_complete',20,'lesson:353:complete','Lesson completed','2026-08-08 02:57:32'),(15,16,3,24,370,NULL,'lesson_complete',20,'lesson:370:complete','Lesson completed','2026-08-08 02:57:48'),(16,18,1,10,97,NULL,'lesson_complete',20,'lesson:97:complete','Lesson completed','2026-08-08 11:05:49'),(17,18,1,10,98,NULL,'lesson_complete',20,'lesson:98:complete','Lesson completed','2026-08-08 11:06:03'),(18,21,1,12,79,NULL,'lesson_complete',20,'lesson:79:complete','Lesson completed','2026-08-08 12:03:28'),(19,21,1,6,117,114,'quiz_attempt',10,'quiz:114:first','First quiz attempt','2026-08-08 12:37:12'),(20,21,1,12,77,NULL,'lesson_complete',20,'lesson:77:complete','Lesson completed','2026-08-08 13:04:53'),(21,21,1,10,98,NULL,'lesson_complete',20,'lesson:98:complete','Lesson completed','2026-08-08 13:05:11');
/*!40000 ALTER TABLE `student_points` ENABLE KEYS */;
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
