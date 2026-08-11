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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(190) NOT NULL,
  `school_name` varchar(190) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `medium` enum('Sinhala','Tamil','English') NOT NULL,
  `preferred_language` enum('en','si','ta') NOT NULL DEFAULT 'en',
  `profile_image` varchar(255) DEFAULT NULL,
  `subscription_expires_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `grade_id` (`grade_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'test9','test9','test@gmail.com','rivisanda','$2y$10$tapmGnmU3iGexKQGTGrslu5mRtjSIizcNaLfSCtJnp77ujRv/jObC',4,'English','en',NULL,NULL,'active','2026-08-06 17:17:02','2026-08-06 17:17:02'),(3,'test1','test1','test12@gmail.com','rivisanda','$2y$10$XyIfUM5AdzHHUzi1rxSCMua10WOtJ5eqipvKaf9iWXEbNOdiw0HZi',1,'English','en',NULL,NULL,'active','2026-08-06 17:19:40','2026-08-06 17:19:40'),(4,'sandaru dinusha','sandaru','sandaru@gmail.com','rivisanda','$2y$10$yoi.WWteZ6dZoK9mY7R.Ie/hFWniz1oT4DtON5W/XElm0h3ipC60u',4,'English','en',NULL,NULL,'active','2026-08-06 19:23:25','2026-08-06 19:23:25'),(10,'JAYANATH','jayanath','jayanath@gmail.com','rivisanda','$2y$10$qtBkCLi3gdBunM8PkNgh9.nlsp.CZK/NbC3lA.U8wuTu7FT5e2M.i',1,'Sinhala','si',NULL,NULL,'active','2026-08-07 13:54:30','2026-08-07 13:54:30'),(12,'TEST10','test10','test10@gmail.com','test10@gmail.com','$2y$10$Up1ou8Af4EGAbfaI0EsYyecLHEARItpJ3Hk2On2zy7FF1M71dh8um',3,'English','en',NULL,NULL,'active','2026-08-07 21:51:04','2026-08-07 21:51:04'),(13,'test11','test11','test11@gmail.com','test10@gmail.com','$2y$10$5M4IpmJjgOVEwK2Bomf.D.fRqFIXmC3fpiHNCe//jiWDiKVoi5XMO',3,'English','en',NULL,NULL,'active','2026-08-07 21:55:56','2026-08-07 21:55:56'),(15,'test12','test12','test124@gmail.com','test12','$2y$10$P7CPLU2fQYO.Cha/5PsgxuTykuZs2GTGhF/IiQT0RxIuwqC4.YK9m',3,'English','en',NULL,NULL,'active','2026-08-07 23:09:50','2026-08-07 23:09:50'),(16,'testnew','testnew','testnew@gmail.com','testnew','$2y$10$tZ03uexvvMgJYvUWxxssee43b8MD7QksZ6JxPAAoeDgSZz9SIAm/W',3,'English','en',NULL,NULL,'active','2026-08-07 23:10:47','2026-08-07 23:10:47'),(17,'testsinhala','testsinhala','testsinhala@gmail.com','testsinhala','$2y$10$DMSkWZ9EMg0kiKezJh4uiuLn7KHKOGhsmU91lnb6ida2NQW0H6oJe',1,'Sinhala','si',NULL,NULL,'active','2026-08-08 10:28:52','2026-08-08 10:28:52'),(18,'testnews','testnews','testnews@gmail.com','testnews','$2y$10$cCMS0Fx.0iEljHtrCiNiqOlOP6h29NTcpl9Wo6TRldBqgkapul3/W',1,'Sinhala','si',NULL,NULL,'active','2026-08-08 10:57:25','2026-08-08 10:57:25'),(19,'rohana','rohana','rohanawijerathna@gmail.com','paliyagala school','$2y$10$dmXTZ1VdJV1L6kSXwdDG0.23JjwUIrpcvVU9bc6q3dNEeZ97fYSoK',2,'English','en',NULL,NULL,'active','2026-08-08 11:44:49','2026-08-08 11:44:49'),(21,'TEST SINHALA','testsinhala12','testsinh@gmail.com','testsinh@gmail.com','$2y$10$2VYy5ODTKVphX4O159MNXuXZu69kr9W5bHI5MM9LSEEpnP.8v9kR6',1,'Sinhala','si',NULL,NULL,'active','2026-08-08 11:52:21','2026-08-08 11:52:21'),(22,'WAGEESH','WAGEESH','WAGEESH@GMAIL.COM','WAGEESH@GMAIL.COM','$2y$10$PYcJpzlRLzdRdMFJ4whkTeeXuduhaRJJ/0Bs8GYmPez5vo..SXyOS',1,'English','en',NULL,NULL,'active','2026-08-08 13:57:36','2026-08-08 13:57:36'),(23,'sandaru dinusha','grdae8','grdae8@gmail.com','grdae8','$2y$10$6/XSLVJ8hYltMYk6uzOf2.uzH/goHVZ/nmzsOnfmdecmDKQrWzHe6',3,'Sinhala','si',NULL,NULL,'active','2026-08-10 08:28:12','2026-08-10 08:28:12'),(24,'testtamil','testtamil','testtamil@gmail.com','testtamil@gmail.com','$2y$10$DbMPd.egJe1EfJzT0UVcQuiQaZrbdFaJZjnKpn8NWIBLPrrU25.1e',1,'Tamil','ta',NULL,NULL,'active','2026-08-10 11:04:35','2026-08-10 11:04:35'),(26,'grade8@gmail.com','grade8','grade8@gmail.com','grade8@gmail.com','$2y$10$V/IjwjLI68Te.qujrvccd..kY9INWRKBgismqiDFwuCcczQBhBgDe',3,'Sinhala','si',NULL,NULL,'active','2026-08-10 11:44:14','2026-08-10 11:44:14'),(27,'grade88@gmail.com','grade88','grade88@gmail.com','grade88@gmail.com','$2y$10$YD9UBVB8KanQzHElxyBma.lt8N8gL4wJj154UcrobzV.lgBULH4Sq',3,'Sinhala','si',NULL,NULL,'active','2026-08-10 11:45:10','2026-08-10 11:45:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-10 22:04:12
