-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: odin_api_db
-- ------------------------------------------------------
-- Server version	9.1.0

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
-- Current Database: `odin_api_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `odin_api_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `odin_api_db`;

--
-- Table structure for table `anexos`
--

DROP TABLE IF EXISTS `anexos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anexos` (
  `id_anexo` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  PRIMARY KEY (`id_anexo`),
  KEY `anexos_id_radicado_fkey` (`id_radicado`),
  CONSTRAINT `anexos_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anexos`
--

LOCK TABLES `anexos` WRITE;
/*!40000 ALTER TABLE `anexos` DISABLE KEYS */;
/*!40000 ALTER TABLE `anexos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_radicados`
--

DROP TABLE IF EXISTS `auditoria_radicados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_radicados` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint NOT NULL,
  `id_usuario` bigint NOT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campo_modificado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_anterior` text COLLATE utf8mb4_unicode_ci,
  `valor_nuevo` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `tabla_afectada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_afectado` bigint DEFAULT NULL,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `auditoria_radicados_id_radicado_fkey` (`id_radicado`),
  KEY `FK_auditoria_radicados_usuarios` (`id_usuario`),
  CONSTRAINT `auditoria_radicados_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`),
  CONSTRAINT `FK_auditoria_radicados_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_radicados`
--

LOCK TABLES `auditoria_radicados` WRITE;
/*!40000 ALTER TABLE `auditoria_radicados` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoria_radicados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ccd_series`
--

DROP TABLE IF EXISTS `ccd_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ccd_series` (
  `id_serie` bigint NOT NULL AUTO_INCREMENT,
  `codigo_serie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_serie` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_unidad` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_seccion` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_subseccion` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `informacion_publica` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_serie`),
  UNIQUE KEY `uq_ccd_serie` (`codigo_serie`),
  KEY `ccd_series_codigo_unidad_fkey` (`codigo_unidad`),
  CONSTRAINT `ccd_series_codigo_unidad_fkey` FOREIGN KEY (`codigo_unidad`) REFERENCES `ccd_unidades` (`codigo_unidad`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ccd_series`
--

LOCK TABLES `ccd_series` WRITE;
/*!40000 ALTER TABLE `ccd_series` DISABLE KEYS */;
INSERT INTO `ccd_series` VALUES (1,'1.1010-42','ACTAS','1.10',NULL,NULL,'Actas de comités, consejos y reuniones institucionales','PUBLICO'),(2,'1.1012-27','HISTORIAS LABORALES','1.10',NULL,NULL,'Expedientes de hojas de vida y trayectoria del personal','RESERVADO'),(3,'1.1013-27','INFORMES DE GESTIÓN','1.10',NULL,NULL,'Informes semestrales, anuales y a entes de control','PUBLICO'),(4,'1.1015-27','RESOLUCIONES ADMINISTRATIVAS','1.10',NULL,NULL,'Actos administrativos expedidos por la dirección','PUBLICO'),(5,'1.2020-27','PETICIONES, QUEJAS Y RECLAMOS (PQRSF)','1.20',NULL,NULL,'Correspondencia de solicitudes ciudadanas y externas','PUBLICO');
/*!40000 ALTER TABLE `ccd_series` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ccd_subseries`
--

DROP TABLE IF EXISTS `ccd_subseries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ccd_subseries` (
  `id_subserie` bigint NOT NULL AUTO_INCREMENT,
  `codigo_subserie` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_subserie` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_serie` bigint NOT NULL,
  `tipo_pqrsf` text COLLATE utf8mb4_unicode_ci,
  `retencion_anios` int DEFAULT '5',
  `disposicion_final` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'CONSERVACION',
  `nivel_acceso` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'PUBLICO',
  `valor_documental` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ADMINISTRATIVO',
  `observaciones_retencion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_subserie`),
  UNIQUE KEY `uq_ccd_subserie` (`codigo_subserie`),
  KEY `ccd_subseries_id_serie_fkey` (`id_serie`),
  CONSTRAINT `ccd_subseries_id_serie_fkey` FOREIGN KEY (`id_serie`) REFERENCES `ccd_series` (`id_serie`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ccd_subseries`
--

LOCK TABLES `ccd_subseries` WRITE;
/*!40000 ALTER TABLE `ccd_subseries` DISABLE KEYS */;
INSERT INTO `ccd_subseries` VALUES (1,'1.1010-42-01','Actas de Consejo Directivo',1,NULL,10,'CONSERVACION','PUBLICO','ADMINISTRATIVO',NULL),(2,'1.1010-42-02','Actas de Comité Técnico y de Gerencia',1,NULL,5,'CONSERVACION','PUBLICO','ADMINISTRATIVO',NULL),(3,'1.1012-27-01','Historias Laborales de Funcionarios de Planta',2,NULL,80,'CONSERVACION','RESERVADO','LEGAL',NULL),(4,'1.1012-27-02','Historias Laborales de Contratistas de Prestación de Servicios',2,NULL,20,'CONSERVACION','RESERVADO','LEGAL',NULL),(5,'1.1012-27-03','Hojas de Vida y Documentos de Selección de Personal',2,NULL,5,'SELECCION','RESERVADO','ADMINISTRATIVO',NULL),(6,'1.1013-27-01','Informes a Entes de Control (Contraloría / Procuraduría)',3,NULL,10,'CONSERVACION','PUBLICO','TESTIMONIAL',NULL),(7,'1.1013-27-02','Informes de Gestión y Rendición de Cuentas Interna',3,NULL,5,'CONSERVACION','PUBLICO','ADMINISTRATIVO',NULL),(8,'1.1015-27-01','Resoluciones de Nombramiento, Traslado y Retiro',4,NULL,80,'CONSERVACION','PUBLICO','LEGAL',NULL),(9,'1.1015-27-02','Resoluciones Administrativas y Reglamentarias',4,NULL,20,'CONSERVACION','PUBLICO','ADMINISTRATIVO',NULL),(10,'1.2020-27-01','Derechos de Petición de Interés General y Particular',5,NULL,5,'CONSERVACION','PUBLICO','LEGAL',NULL),(11,'1.2020-27-02','Quejas, Reclamos y Sugerencias Ciudadanas',5,NULL,5,'ELIMINACION','PUBLICO','ADMINISTRATIVO',NULL);
/*!40000 ALTER TABLE `ccd_subseries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ccd_unidades`
--

DROP TABLE IF EXISTS `ccd_unidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ccd_unidades` (
  `id_unidad` bigint NOT NULL AUTO_INCREMENT,
  `codigo_unidad` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_unidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_padre` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_unidad`),
  UNIQUE KEY `uq_ccd_unidad` (`codigo_unidad`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ccd_unidades`
--

LOCK TABLES `ccd_unidades` WRITE;
/*!40000 ALTER TABLE `ccd_unidades` DISABLE KEYS */;
INSERT INTO `ccd_unidades` VALUES (1,'1.10','Dirección General',NULL,1,'Despacho principal y alta dirección'),(2,'1.20','Secretaría General y Gestión Documental',NULL,1,'Administración de correspondencia y archivo'),(3,'1.30','Área Jurídica',NULL,1,'Asesoría jurídica y representación judicial');
/*!40000 ALTER TABLE `ccd_unidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dependencias`
--

DROP TABLE IF EXISTS `dependencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dependencias` (
  `id_dependencia` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dependencia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'operativa',
  PRIMARY KEY (`id_dependencia`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dependencias`
--

LOCK TABLES `dependencias` WRITE;
/*!40000 ALTER TABLE `dependencias` DISABLE KEYS */;
INSERT INTO `dependencias` VALUES (1,'Dirección General','Despacho y Administración General','Activo','Dirección General','DIR-01','directiva'),(2,'Gestión Documental','Ventanilla Única y Correspondencia','Activo','Gestión Documental','GD-02','operativa'),(3,'Secretaría General','Secretaría y Despacho','Activo','Secretaría General','SG-03','administrativa'),(4,'Área Jurídica','Asuntos Legales y Tutelas','Activo','Área Jurídica','JUR-04','operativa');
/*!40000 ALTER TABLE `dependencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos` (
  `id_documento` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint DEFAULT NULL,
  `tamano` int DEFAULT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_subida` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (now()),
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checksum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_actual` int DEFAULT '1',
  `fecha_limite_anexo` datetime DEFAULT NULL,
  PRIMARY KEY (`id_documento`),
  KEY `documentos_id_radicado_fkey` (`id_radicado`),
  CONSTRAINT `documentos_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
INSERT INTO `documentos` VALUES (1,1,50,'numero_radicado','c:local/escritorio','pdf','2026-06-18 23:50:19.343326','remitente externos o interno',NULL,NULL,1,NULL),(2,31,23351,'31_ff69eb00-e679-4855-ab63-e3a7b6befb8e_jhon.jpg','C:\\Users\\julia\\OneDrive\\Escritorio\\odinPinillos\\Gestor-Documental-Odin\\Odin\\uploads\\31_ff69eb00-e679-4855-ab63-e3a7b6befb8e_jhon.jpg','image/jpeg','2026-09-23T14:46:38.714111900','jhon.jpg',NULL,NULL,1,NULL),(3,41,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-09 10:16:30','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(4,42,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-09 10:16:30','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(5,43,64,'DOC_TEMP_20261009101831_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009101831_20261009_76eddb93.pdf','pdf','2026-10-09 10:18:31','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(6,44,64,'DOC_TEMP_20261009101929_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009101929_20261009_76eddb93.pdf','pdf','2026-10-09 10:19:29','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(7,45,64,'DOC_TEMP_20261009101948_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009101948_20261009_76eddb93.pdf','pdf','2026-10-09 10:19:48','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(8,46,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-09 10:20:15','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(9,47,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-09 10:20:15','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(10,48,64,'DOC_TEMP_20261009102017_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009102017_20261009_76eddb93.pdf','pdf','2026-10-09 10:20:17','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(11,49,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-09 10:21:07','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(12,50,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-09 10:21:07','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(13,51,64,'DOC_TEMP_20261009102109_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009102109_20261009_76eddb93.pdf','pdf','2026-10-09 10:21:09','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(14,52,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-09 10:42:05','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(15,53,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-09 10:42:05','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(16,54,4096,'DOC_RES_001.pdf','vistas/archivos/radicados/2026/10/DOC_RES_001.pdf','pdf','2026-10-09 10:42:05','resolucion_001.pdf','application/pdf','21e53634f21ee506739d165745d12b23b3af127dda84740dda5be29aee3e7a87',1,NULL),(17,55,64,'DOC_TEMP_20261009104208_20261009_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009104208_20261009_76eddb93.pdf','pdf','2026-10-09 10:42:08','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(18,56,72549,'DOC_TEMP_20261009174623_20261009_4f3276b0.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261009174623_20261009_4f3276b0.pdf','pdf','2026-10-09 17:46:23','Reporte Proyecto Formativo - 2954837 - SISTEMATIZACIÓN DE PROCESOS TÉ.pdf','application/pdf','4f3276b0b423d4664772426414bef5b053cd2225dc27532ce80175c7c2808c7b',1,NULL),(19,57,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-16 10:50:45','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(20,58,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-16 10:50:45','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(21,59,4096,'DOC_RES_001.pdf','vistas/archivos/radicados/2026/10/DOC_RES_001.pdf','pdf','2026-10-16 10:50:45','resolucion_001.pdf','application/pdf','21e53634f21ee506739d165745d12b23b3af127dda84740dda5be29aee3e7a87',1,NULL),(22,60,64,'DOC_TEMP_20261016105046_20261016_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261016105046_20261016_76eddb93.pdf','pdf','2026-10-16 10:50:46','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(23,61,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-16 10:55:22','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(24,62,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-16 10:55:22','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(25,63,4096,'DOC_RES_001.pdf','vistas/archivos/radicados/2026/10/DOC_RES_001.pdf','pdf','2026-10-16 10:55:22','resolucion_001.pdf','application/pdf','21e53634f21ee506739d165745d12b23b3af127dda84740dda5be29aee3e7a87',1,NULL),(26,64,64,'DOC_TEMP_20261016105523_20261016_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261016105523_20261016_76eddb93.pdf','pdf','2026-10-16 10:55:23','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(27,65,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-16 10:55:41','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(28,66,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-16 10:55:41','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(29,67,4096,'DOC_RES_001.pdf','vistas/archivos/radicados/2026/10/DOC_RES_001.pdf','pdf','2026-10-16 10:55:42','resolucion_001.pdf','application/pdf','21e53634f21ee506739d165745d12b23b3af127dda84740dda5be29aee3e7a87',1,NULL),(30,68,64,'DOC_TEMP_20261016105543_20261016_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261016105543_20261016_76eddb93.pdf','pdf','2026-10-16 10:55:43','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(31,69,2048,'DOC_TEST_001.pdf','vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf','pdf','2026-10-16 11:03:24','formulario_certificacion.pdf','application/pdf','0bde7f2a8f45c0db39e2caae987362599fd04857aeb976a49b28bc4bf8fda1a1',1,NULL),(32,70,1024,'DOC_ANON_001.png','vistas/archivos/radicados/2026/10/DOC_ANON_001.png','png','2026-10-16 11:03:24','evidencia_anonima.png','image/png','10f5e59e860750c58cdb1510e8b4247f9f7857923603447af92fcd4dfba5b282',1,NULL),(33,71,4096,'DOC_RES_001.pdf','vistas/archivos/radicados/2026/10/DOC_RES_001.pdf','pdf','2026-10-16 11:03:24','resolucion_001.pdf','application/pdf','21e53634f21ee506739d165745d12b23b3af127dda84740dda5be29aee3e7a87',1,NULL),(34,72,64,'DOC_TEMP_20261016110326_20261016_76eddb93.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261016110326_20261016_76eddb93.pdf','pdf','2026-10-16 11:03:26','peticion_firmada.pdf','application/pdf','76eddb93187569f458502304c92637cbc8623e1691c7c0d0c0966759903260f6',1,NULL),(35,73,32543,'DOC_TEMP_20261016181735_20261016_cf568ba4.pdf','vistas/archivos/radicados/2026/10/DOC_TEMP_20261016181735_20261016_cf568ba4.pdf','pdf','2026-10-16 18:17:35','Informe Programa de Formación Complementaria.pdf','application/pdf','cf568ba4c042ed41d70af659c8fb52bd11b4e7c661d10df4995276efd33a5b14',1,NULL);
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estados`
--

DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados` (
  `id_estado` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flujo_pqrsf` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estados`
--

LOCK TABLES `estados` WRITE;
/*!40000 ALTER TABLE `estados` DISABLE KEYS */;
INSERT INTO `estados` VALUES (1,'Pendiente','#ffc107',1),(2,'En proceso','#17a2b8',1),(3,'En revisión','#6f42c1',1),(4,'Respondido','#007bff',1),(5,'Rechazado','#dc3545',1),(6,'Finalizado','#28a745',1),(7,'Archivado','#6c757d',0);
/*!40000 ALTER TABLE `estados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expedientes`
--

DROP TABLE IF EXISTS `expedientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expedientes` (
  `id_expediente` bigint NOT NULL AUTO_INCREMENT,
  `codigo_expediente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  PRIMARY KEY (`id_expediente`),
  UNIQUE KEY `uq_codigo_expediente` (`codigo_expediente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expedientes`
--

LOCK TABLES `expedientes` WRITE;
/*!40000 ALTER TABLE `expedientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `expedientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firmas`
--

DROP TABLE IF EXISTS `firmas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `firmas` (
  `id_firma` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint NOT NULL,
  `id_usuario` bigint NOT NULL,
  `fecha_firma` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `hash_documento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_firma` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_firma`),
  KEY `firmas_id_radicado_fkey` (`id_radicado`),
  KEY `FK_firmas_usuarios` (`id_usuario`),
  CONSTRAINT `firmas_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`),
  CONSTRAINT `FK_firmas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firmas`
--

LOCK TABLES `firmas` WRITE;
/*!40000 ALTER TABLE `firmas` DISABLE KEYS */;
/*!40000 ALTER TABLE `firmas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_radicado`
--

DROP TABLE IF EXISTS `historial_radicado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_radicado` (
  `id_historial` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint NOT NULL,
  `id_usuario` bigint NOT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historial`),
  KEY `historial_radicado_id_radicado_fkey` (`id_radicado`),
  KEY `FK_historial_radicado_usuarios` (`id_usuario`),
  CONSTRAINT `FK_historial_radicado_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `historial_radicado_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_radicado`
--

LOCK TABLES `historial_radicado` WRITE;
/*!40000 ALTER TABLE `historial_radicado` DISABLE KEYS */;
INSERT INTO `historial_radicado` VALUES (1,41,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:16:30'),(2,42,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:16:30'),(3,43,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:18:31'),(4,44,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:19:29'),(5,45,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:19:48'),(6,46,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:20:15'),(7,47,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:20:15'),(8,48,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:20:17'),(9,49,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:21:07'),(10,50,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:21:07'),(11,51,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:21:09'),(12,52,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:42:05'),(13,53,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:42:05'),(14,54,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:42:05'),(15,55,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 10:42:08'),(16,56,5,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-09 17:46:23'),(17,57,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:50:45'),(18,58,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:50:45'),(19,59,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:50:45'),(20,60,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:50:46'),(21,61,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:22'),(22,62,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:22'),(23,63,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:22'),(24,64,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:23'),(25,65,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:41'),(26,66,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:41'),(27,67,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:42'),(28,68,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 10:55:43'),(29,69,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 11:03:24'),(30,70,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 11:03:24'),(31,71,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 11:03:24'),(32,72,1,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 11:03:26'),(33,73,5,'radicacion','Radicación inicial generada exitosamente (1 documento(s) adjunto(s)).','2026-10-16 18:17:35');
/*!40000 ALTER TABLE `historial_radicado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_accesos`
--

DROP TABLE IF EXISTS `log_accesos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_accesos` (
  `id_log` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `exito` tinyint(1) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `fk_log_usuario` (`id_usuario`),
  CONSTRAINT `fk_log_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_accesos`
--

LOCK TABLES `log_accesos` WRITE;
/*!40000 ALTER TABLE `log_accesos` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_accesos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint NOT NULL,
  `id_radicado` bigint DEFAULT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notificacion`),
  KEY `notificaciones_id_radicado_fkey` (`id_radicado`),
  CONSTRAINT `notificaciones_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observaciones`
--

DROP TABLE IF EXISTS `observaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observaciones` (
  `id_observacion` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint DEFAULT NULL,
  `id_usuario` bigint DEFAULT NULL,
  `comentario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_observacion`),
  KEY `observaciones_id_radicado_fkey` (`id_radicado`),
  KEY `FK_observaciones_usuarios` (`id_usuario`),
  CONSTRAINT `FK_observaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `observaciones_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observaciones`
--

LOCK TABLES `observaciones` WRITE;
/*!40000 ALTER TABLE `observaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `observaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_codes`
--

DROP TABLE IF EXISTS `password_reset_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_codes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `codigo` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `id_usuario` bigint NOT NULL,
  `usado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_codes`
--

LOCK TABLES `password_reset_codes` WRITE;
/*!40000 ALTER TABLE `password_reset_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permiso` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `permisos_nombre_key` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'ver_radicados','Ver listado y detalle de radicados','radicados','ver'),(2,'crear_radicado','Crear nuevos radicados','radicados','crear'),(3,'editar_radicado','Editar radicados','radicados','editar'),(4,'trasladar_radicado','Trasladar radicados entre dependencias','radicados','trasladar'),(5,'finalizar_radicado','Finalizar radicados','radicados','finalizar'),(6,'ver_usuarios','Ver usuarios del sistema','usuarios','ver'),(7,'admin_usuarios','Gestionar usuarios y roles','usuarios','admin'),(8,'ver_reportes','Acceder a reportes y estadísticas','reportes','ver'),(9,'admin_ccd','Gestionar Cuadro de Clasificación Documental','ccd','admin'),(10,'ver_tramites','Consultar trámites','tramites','ver'),(11,'gestionar_tramites','Gestionar trámites y cambiar su estado','tramites','gestionar'),(12,'asignar_tramites','Asignar y reasignar trámites','tramites','asignar'),(13,'ver_bitacora','Consultar bitácora y trazabilidad','bitacora','ver'),(14,'gestionar_documentos','Gestionar documentos y anexos','documentos','gestionar'),(15,'ver_dependencias','Consultar dependencias','dependencias','ver'),(16,'admin_roles','Administrar roles y permisos','seguridad','admin');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plantilla`
--

DROP TABLE IF EXISTS `plantilla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plantilla` (
  `id_plantilla` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint DEFAULT NULL,
  `id_usuario` bigint DEFAULT NULL,
  `accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (now()),
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos` json DEFAULT NULL,
  `accion_detalle` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_evento` bigint NOT NULL,
  PRIMARY KEY (`id_plantilla`),
  KEY `plantilla_id_radicado_fkey` (`id_radicado`),
  KEY `FK_plantilla_usuarios` (`id_usuario`),
  CONSTRAINT `FK_plantilla_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `plantilla_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plantilla`
--

LOCK TABLES `plantilla` WRITE;
/*!40000 ALTER TABLE `plantilla` DISABLE KEYS */;
/*!40000 ALTER TABLE `plantilla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radicado_consecutivos`
--

DROP TABLE IF EXISTS `radicado_consecutivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radicado_consecutivos` (
  `anio` int NOT NULL,
  `prefijo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consecutivo` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`anio`,`prefijo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radicado_consecutivos`
--

LOCK TABLES `radicado_consecutivos` WRITE;
/*!40000 ALTER TABLE `radicado_consecutivos` DISABLE KEYS */;
INSERT INTO `radicado_consecutivos` VALUES (2026,'E',36),(2026,'INT',13),(2026,'S',8);
/*!40000 ALTER TABLE `radicado_consecutivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radicados`
--

DROP TABLE IF EXISTS `radicados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radicados` (
  `id_radicado` bigint NOT NULL AUTO_INCREMENT,
  `numero_radicado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tramite` bigint DEFAULT NULL,
  `id_estado` bigint DEFAULT NULL,
  `id_dependencia` bigint DEFAULT NULL,
  `id_usuario` bigint DEFAULT NULL,
  `id_remitente` bigint DEFAULT NULL,
  `remitente` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_radicado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (now()),
  `codigo_serie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_subserie` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_radicado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medio_recepcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prioridad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'MEDIA',
  `fecha_vencimiento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confidencialidad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'PUBLICO',
  `id_expediente` bigint DEFAULT NULL,
  `id_serie` bigint DEFAULT NULL,
  `id_subserie` bigint DEFAULT NULL,
  `fecha_limite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `id_usuario_cierre` bigint DEFAULT NULL,
  PRIMARY KEY (`id_radicado`),
  UNIQUE KEY `radicados_numero_radicado_key` (`numero_radicado`),
  KEY `fk65y7d2e5aors82nyaho7xtcs9` (`id_serie`),
  KEY `fk8fwoby147y96d8a9o7c60k1wk` (`id_subserie`),
  KEY `fk_radicado_expediente` (`id_expediente`),
  KEY `fk_radicados_tramites` (`id_tramite`),
  KEY `radicados_codigo_serie_fkey` (`codigo_serie`),
  KEY `radicados_codigo_subserie_fkey` (`codigo_subserie`),
  KEY `radicados_id_dependencia_fkey` (`id_dependencia`),
  KEY `radicados_id_estado_fkey` (`id_estado`),
  KEY `radicados_id_remitente_fkey` (`id_remitente`),
  KEY `FK_radicados_usuarios` (`id_usuario`),
  CONSTRAINT `fk65y7d2e5aors82nyaho7xtcs9` FOREIGN KEY (`id_serie`) REFERENCES `ccd_series` (`id_serie`),
  CONSTRAINT `fk8fwoby147y96d8a9o7c60k1wk` FOREIGN KEY (`id_subserie`) REFERENCES `ccd_subseries` (`id_subserie`),
  CONSTRAINT `fk_radicado_expediente` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`),
  CONSTRAINT `fk_radicados_tramites` FOREIGN KEY (`id_tramite`) REFERENCES `tramites` (`id_tramite`),
  CONSTRAINT `FK_radicados_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `radicados_codigo_serie_fkey` FOREIGN KEY (`codigo_serie`) REFERENCES `ccd_series` (`codigo_serie`),
  CONSTRAINT `radicados_codigo_subserie_fkey` FOREIGN KEY (`codigo_subserie`) REFERENCES `ccd_subseries` (`codigo_subserie`),
  CONSTRAINT `radicados_id_dependencia_fkey` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencias` (`id_dependencia`),
  CONSTRAINT `radicados_id_estado_fkey` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id_estado`),
  CONSTRAINT `radicados_id_remitente_fkey` FOREIGN KEY (`id_remitente`) REFERENCES `remitentes_externos` (`id_remitente`),
  CONSTRAINT `chk_radicados_confidencialidad` CHECK (((`confidencialidad` is null) or (`confidencialidad` in (_utf8mb4'PUBLICO',_utf8mb4'RESERVADO',_utf8mb4'CONFIDENCIAL')))),
  CONSTRAINT `chk_radicados_prioridad` CHECK (((`prioridad` is null) or (`prioridad` in (_utf8mb4'BAJA',_utf8mb4'MEDIA',_utf8mb4'ALTA',_utf8mb4'URGENTE')))),
  CONSTRAINT `chk_radicados_tipo` CHECK (((`tipo_radicado` is null) or (`tipo_radicado` in (_utf8mb4'ENTRADA',_utf8mb4'SALIDA',_utf8mb4'INTERNO'))))
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radicados`
--

LOCK TABLES `radicados` WRITE;
/*!40000 ALTER TABLE `radicados` DISABLE KEYS */;
INSERT INTO `radicados` VALUES (1,'1',1,6,2,1,NULL,'anonimo','asunto para resolver por otro asunto','2026-05-01 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(2,'2',6,7,2,1,NULL,'1','asunto repuesta','2026-05-01 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(5,'ENT-20260609195445',2,2,2,1,NULL,'Anonimo','quiero pan','2026-06-09 19:53:00','1.1012-27','1.1012-27-01',NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(6,'PQR-20260609201757',NULL,NULL,NULL,NULL,NULL,'Anonimo','Sin asunto','2026-06-09 20:17:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(7,'ENT-20260609202325',2,2,1,2,NULL,'pepe','quiero pan','2026-06-09 20:22:00','1.1010-42','1.1012-27-03',NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(8,'ENT-20260611082549',1,1,1,2,NULL,'jjgg','pon','2026-06-11 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(9,'ENT-20260611090146',1,1,NULL,2,NULL,'d','Sin asunto','2026-06-11 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(10,'ENT-20260611090546',1,1,NULL,2,NULL,'jjgg','pon','2026-06-11 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(11,'ENT-20260611091108',1,1,NULL,2,NULL,'jjgg','Sin asunto','2026-06-10 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(12,'ENT-20260611091223',1,1,NULL,2,NULL,'prueba','pon','2026-06-10 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(13,'ENT-20260611091235',1,1,NULL,2,NULL,'prueba','pon','2026-06-10 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(14,'ENT-20260611091244',1,1,NULL,2,NULL,'prueba','pon','2026-06-10 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(15,'ENT-20260611091717',1,1,1,2,NULL,'jjgg','aaaaa','2026-06-11 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(16,'ENT-20260611093728',1,1,1,2,NULL,'jjgg','pon','2026-06-11 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(17,'SAL-20260615172907',1,1,1,2,NULL,'jose','quiero pan','2026-06-15 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(18,'SAL-20260616195643',1,1,1,2,NULL,'PEPE','quiero panNNNNNNNNNN','2026-06-16 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(19,'INT-20260616195806',1,1,2,2,NULL,'pablo','hola q mas','2026-06-16 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(20,'PQR-20260616195904',1,1,3,2,NULL,'juan','hola yuca papaya','2026-06-16 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(21,'RAD-20260617191319',3,1,1,1,NULL,'Juan Camilo','quiero pan','2026-06-17 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,4,1,NULL,NULL,NULL),(22,'RAD-20260617194734',6,1,1,1,NULL,'Santiago Garcia','prueba dos jaksjakjs','2026-06-17 00:00:00','1.2020-27','1.1013-27-01',NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(23,'RAD-BAS-001',1,1,2,2,NULL,'Juan Pérez','Solicitud de información general','2026-05-27 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(24,'RAD-EXT-002',1,6,2,2,NULL,'María López García','Petición formal','2026-05-27 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(25,'RAD-20260618073315',3,2,1,1,NULL,'nicol','quiero pan','2026-06-18 00:00:00','1.2020-27','1.1012-27-03',NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(26,'RAD-20260618073402',3,2,2,1,NULL,'nicol','quiero pan','2026-06-18 00:00:00','1.2020-27','1.1012-27-03',NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(30,'RAD-20260922153003',1,1,1,1,NULL,'diego ','si','2026-09-23','1.1015-27','1.1012-27-01',NULL,NULL,'MEDIA','2026-09-23','PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(31,'RAD-20260923144637',4,1,1,2,NULL,'Diego oo','si','2026-09-24','1.2020-27','1.1012-27-01',NULL,NULL,'MEDIA','2026-09-23','PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(32,'TEST-BAS-001',1,1,2,2,NULL,'Pedro Gómez','Solicitud de certificación de estudios','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(34,'TEST-BAS-002',1,1,2,2,NULL,'Pedro Gómez','Prueba de traslado','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(35,'TEST-BAS-0044',1,1,2,2,NULL,'juaco Gómez','Prueba de traslado','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(36,'BAS-20260529-005',1,1,2,2,NULL,'Laura Gómez','Solicitud de información general','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(37,'EXT-20260529-006',1,1,2,2,NULL,'María López','Petición formal externa','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(38,'ANON-20260529-007',1,1,2,2,NULL,'ANÓNIMO','Queja sin identificación','2026-05-29 00:00:00',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(40,'BAS-20260529-008',1,1,2,2,NULL,'Juan Pérez','Prueba corregida','2026-05-29 22:58:30',NULL,NULL,NULL,NULL,'MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(41,'E-2026-000002',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-09 10:16:30',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(42,'E-2026-000003',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-09 10:16:30',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(43,'E-2026-000004',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:18:31',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(44,'E-2026-000005',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:19:29',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(45,'E-2026-000006',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:19:48',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(46,'E-2026-000008',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-09 10:20:15',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(47,'E-2026-000009',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-09 10:20:15',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(48,'E-2026-000010',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:20:17',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(49,'E-2026-000012',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-09 10:21:07',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(50,'E-2026-000013',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-09 10:21:07',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(51,'E-2026-000014',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:21:09',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(52,'E-2026-000016',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-09 10:42:05',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(53,'E-2026-000017',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-09 10:42:05',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(54,'INT-2026-000005',4,1,1,1,NULL,'Despacho Dirección','Resolución de Nombramiento - Test CCD','2026-10-09 10:42:05','1.1015-27','1.1015-27-01','INTERNO','Oficial','MEDIA',NULL,'PUBLICO',NULL,4,8,NULL,NULL,NULL),(55,'E-2026-000018',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-09 10:42:08',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(56,'E-2026-000019',6,1,4,5,NULL,'ANÓNIMO (Ley de Transparencia)','este es el asunto','2026-10-09 17:46:23','1.1012-27','1.1012-27-01','ENTRADA','correo_fisico','BAJA','2026-10-16','PUBLICO',NULL,2,3,'2026-10-16',NULL,NULL),(57,'E-2026-000021',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-16 10:50:45',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(58,'E-2026-000022',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-16 10:50:45',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(59,'INT-2026-000007',4,1,1,1,NULL,'Despacho Dirección','Resolución de Nombramiento - Test CCD','2026-10-16 10:50:45','1.1015-27','1.1015-27-01','INTERNO','Oficial','MEDIA',NULL,'PUBLICO',NULL,4,8,NULL,NULL,NULL),(60,'E-2026-000023',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-16 10:50:46',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(61,'E-2026-000025',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-16 10:55:22',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(62,'E-2026-000026',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-16 10:55:22',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(63,'INT-2026-000009',4,1,1,1,NULL,'Despacho Dirección','Resolución de Nombramiento - Test CCD','2026-10-16 10:55:22','1.1015-27','1.1015-27-01','INTERNO','Oficial','MEDIA',NULL,'PUBLICO',NULL,4,8,NULL,NULL,NULL),(64,'E-2026-000027',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-16 10:55:23',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(65,'E-2026-000029',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-16 10:55:41',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(66,'E-2026-000030',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-16 10:55:41',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(67,'INT-2026-000011',4,1,1,1,NULL,'Despacho Dirección','Resolución de Nombramiento - Test CCD','2026-10-16 10:55:42','1.1015-27','1.1015-27-01','INTERNO','Oficial','MEDIA',NULL,'PUBLICO',NULL,4,8,NULL,NULL,NULL),(68,'E-2026-000031',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-16 10:55:43',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(69,'E-2026-000033',1,1,1,1,1,'Carlos Mendoza','Solicitud de Certificación Laboral - Test Unitario','2026-10-16 11:03:24',NULL,NULL,'ENTRADA','Ventanilla','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(70,'E-2026-000034',2,1,2,1,NULL,'ANÓNIMO (Ley de Transparencia)','Denuncia Anónima por Transparencia - Test','2026-10-16 11:03:24',NULL,NULL,'ENTRADA','Plataforma Web','ALTA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(71,'INT-2026-000013',4,1,1,1,NULL,'Despacho Dirección','Resolución de Nombramiento - Test CCD','2026-10-16 11:03:24','1.1015-27','1.1015-27-01','INTERNO','Oficial','MEDIA',NULL,'PUBLICO',NULL,4,8,NULL,NULL,NULL),(72,'E-2026-000035',1,1,1,1,2,'Ana Maria Torres','Petición de Información Pública - Test API','2026-10-16 11:03:26',NULL,NULL,'ENTRADA','plataforma_web','MEDIA',NULL,'PUBLICO',NULL,NULL,NULL,NULL,NULL,NULL),(73,'E-2026-000036',2,1,4,5,3,'Nicol Alomía','mi vainas','2026-10-16 18:17:35','1.1013-27','1.1013-27-01','ENTRADA','correo_electronico','ALTA','2026-10-15','PUBLICO',NULL,3,6,'2026-10-15',NULL,NULL);
/*!40000 ALTER TABLE `radicados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reasignaciones`
--

DROP TABLE IF EXISTS `reasignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reasignaciones` (
  `id_reasignacion` bigint NOT NULL AUTO_INCREMENT,
  `id_radicado` bigint DEFAULT NULL,
  `id_usuario_anterior` bigint DEFAULT NULL,
  `id_usuario_nuevo` bigint DEFAULT NULL,
  `id_dependencia_nueva` bigint DEFAULT NULL,
  `fecha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (now()),
  PRIMARY KEY (`id_reasignacion`),
  KEY `reasignaciones_id_radicado_fkey` (`id_radicado`),
  KEY `FK_reasignaciones_usuarios` (`id_usuario_anterior`),
  KEY `FK_reasignaciones_usuarios_2` (`id_usuario_nuevo`),
  KEY `reasignaciones_id_dependencia_nueva_fkey` (`id_dependencia_nueva`),
  CONSTRAINT `FK_reasignaciones_usuarios` FOREIGN KEY (`id_usuario_anterior`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `FK_reasignaciones_usuarios_2` FOREIGN KEY (`id_usuario_nuevo`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `reasignaciones_id_dependencia_nueva_fkey` FOREIGN KEY (`id_dependencia_nueva`) REFERENCES `dependencias` (`id_dependencia`),
  CONSTRAINT `reasignaciones_id_radicado_fkey` FOREIGN KEY (`id_radicado`) REFERENCES `radicados` (`id_radicado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reasignaciones`
--

LOCK TABLES `reasignaciones` WRITE;
/*!40000 ALTER TABLE `reasignaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `reasignaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remitentes_externos`
--

DROP TABLE IF EXISTS `remitentes_externos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remitentes_externos` (
  `id_remitente` bigint NOT NULL AUTO_INCREMENT,
  `tipo_identificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_identificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT (now()),
  PRIMARY KEY (`id_remitente`),
  UNIQUE KEY `uq_remitente_identificacion` (`tipo_identificacion`,`num_identificacion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remitentes_externos`
--

LOCK TABLES `remitentes_externos` WRITE;
/*!40000 ALTER TABLE `remitentes_externos` DISABLE KEYS */;
INSERT INTO `remitentes_externos` VALUES (1,'CC','9988776655','Carlos','Mendoza','carlos.mendoza@test.com','3001234567','Calle 10 # 20-30','2026-10-09 10:16:30'),(2,'CC','1020304050','Ana Maria Torres','','ana.torres@example.com','','','2026-10-09 10:18:31'),(3,'CC','1117352119','Nicol Alomía','','nicol@sena.edu.co','123456','Tulua','2026-10-16 11:17:35');
/*!40000 ALTER TABLE `remitentes_externos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_password_tokens`
--

DROP TABLE IF EXISTS `reset_password_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reset_password_tokens` (
  `id_token` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_token`),
  KEY `fk_reset_usuario` (`id_usuario`),
  CONSTRAINT `fk_reset_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_password_tokens`
--

LOCK TABLES `reset_password_tokens` WRITE;
/*!40000 ALTER TABLE `reset_password_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `reset_password_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permisos`
--

DROP TABLE IF EXISTS `rol_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_permisos` (
  `id_rol` bigint NOT NULL,
  `nivel_acceso` int DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_permiso` bigint NOT NULL,
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `fk_rol_permisos_permiso` (`id_permiso`),
  CONSTRAINT `fk_rol_permisos_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  CONSTRAINT `fk_rol_permisos_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
INSERT INTO `rol_permisos` VALUES (1,10,'ver_radicados','Activo','Ver listado y detalle de radicados',1),(1,10,'crear_radicado','Activo','Crear nuevos radicados',2),(1,10,'editar_radicado','Activo','Editar radicados',3),(1,10,'trasladar_radicado','Activo','Trasladar radicados entre dependencias',4),(1,10,'finalizar_radicado','Activo','Finalizar radicados',5),(1,10,'ver_usuarios','Activo','Ver usuarios del sistema',6),(1,10,'admin_usuarios','Activo','Gestionar usuarios y roles',7),(1,10,'ver_reportes','Activo','Acceder a reportes y estadísticas',8),(1,10,'admin_ccd','Activo','Gestionar Cuadro de Clasificación Documental',9),(1,10,'ver_tramites','Activo','Consultar trámites',10),(1,10,'gestionar_tramites','Activo','Gestionar trámites y cambiar su estado',11),(1,10,'asignar_tramites','Activo','Asignar y reasignar trámites',12),(1,10,'ver_bitacora','Activo','Consultar bitácora y trazabilidad',13),(1,10,'gestionar_documentos','Activo','Gestionar documentos y anexos',14),(1,10,'ver_dependencias','Activo','Consultar dependencias',15),(1,10,'admin_roles','Activo','Administrar roles y permisos',16),(2,5,'ver_radicados','Activo','Ver listado y detalle de radicados',1),(2,5,'crear_radicado','Activo','Crear nuevos radicados',2),(2,5,'editar_radicado','Activo','Editar radicados',3),(2,5,'trasladar_radicado','Activo','Trasladar radicados entre dependencias',4),(2,5,'finalizar_radicado','Activo','Finalizar radicados',5),(2,5,'ver_tramites','Activo','Consultar trámites',10),(2,5,'gestionar_tramites','Activo','Gestionar trámites y cambiar su estado',11),(2,5,'gestionar_documentos','Activo','Gestionar documentos y anexos',14),(2,5,'ver_dependencias','Activo','Consultar dependencias',15),(3,7,'ver_radicados','Activo','Ver listado y detalle de radicados',1),(3,7,'crear_radicado','Activo','Crear nuevos radicados',2),(3,7,'editar_radicado','Activo','Editar radicados',3),(3,7,'trasladar_radicado','Activo','Trasladar radicados entre dependencias',4),(3,7,'finalizar_radicado','Activo','Finalizar radicados',5),(3,7,'ver_reportes','Activo','Acceder a reportes y estadísticas',8),(3,7,'ver_tramites','Activo','Consultar trámites',10),(3,7,'gestionar_tramites','Activo','Gestionar trámites y cambiar su estado',11),(3,7,'asignar_tramites','Activo','Asignar y reasignar trámites',12),(3,7,'ver_bitacora','Activo','Consultar bitácora y trazabilidad',13),(3,7,'gestionar_documentos','Activo','Gestionar documentos y anexos',14),(3,7,'ver_dependencias','Activo','Consultar dependencias',15),(4,1,'ver_radicados','Activo','Ver listado y detalle de radicados',1),(4,1,'ver_reportes','Activo','Acceder a reportes y estadísticas',8),(4,1,'ver_tramites','Activo','Consultar trámites',10),(4,1,'ver_bitacora','Activo','Consultar bitácora y trazabilidad',13),(4,1,'ver_dependencias','Activo','Consultar dependencias',15);
/*!40000 ALTER TABLE `rol_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` bigint NOT NULL AUTO_INCREMENT,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Activo','Administrador','admin'),(2,'Activo','Operario / Recepción','operario'),(3,'Activo','Gestor de Trámites','gestor'),(4,'Activo','Consulta','consulta'),(35,'Activo','Admin','admin');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_usuario`
--

DROP TABLE IF EXISTS `sesiones_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_usuario` (
  `id_sesion` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `navegador` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8mb4_unicode_ci,
  `ultima_actividad` datetime DEFAULT CURRENT_TIMESTAMP,
  `dispositivo` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_sesion`),
  KEY `fk_sesion_usuario` (`id_usuario`),
  CONSTRAINT `fk_sesion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_usuario`
--

LOCK TABLES `sesiones_usuario` WRITE;
/*!40000 ALTER TABLE `sesiones_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tramites`
--

DROP TABLE IF EXISTS `tramites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tramites` (
  `id_tramite` bigint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_dependencia_responsable` bigint DEFAULT NULL,
  `id_estado_inicial` bigint DEFAULT NULL,
  `dias_respuesta` int DEFAULT '15',
  `prioridad_default` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'MEDIA',
  `activo` tinyint(1) DEFAULT '1',
  `requiere_respuesta` tinyint(1) DEFAULT '1',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tramite`),
  KEY `fk_tramite_dependencia` (`id_dependencia_responsable`),
  KEY `fk_tramite_estado` (`id_estado_inicial`),
  CONSTRAINT `fk_tramite_dependencia` FOREIGN KEY (`id_dependencia_responsable`) REFERENCES `dependencias` (`id_dependencia`),
  CONSTRAINT `fk_tramite_estado` FOREIGN KEY (`id_estado_inicial`) REFERENCES `estados` (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tramites`
--

LOCK TABLES `tramites` WRITE;
/*!40000 ALTER TABLE `tramites` DISABLE KEYS */;
INSERT INTO `tramites` VALUES (1,'Derecho de Petición','Solicitud formal en ejercicio del derecho de petición',2,1,15,'MEDIA',1,1,'2026-10-09 10:11:43',NULL,NULL),(2,'Queja / Reclamo (PQRSF)','Manifestación de inconformidad o queja ciudadana',2,1,15,'ALTA',1,1,'2026-10-09 10:11:43',NULL,NULL),(3,'Solicitud de Información','Petición de copias o consulta documental',1,1,10,'MEDIA',1,1,'2026-10-09 10:11:43',NULL,NULL),(4,'Comunicación Oficial Interna','Memorandos y traslados internos interdependencias',1,1,5,'MEDIA',1,0,'2026-10-09 10:11:43',NULL,NULL),(6,'Certificación Documental','Expedición de certificados o constancias',2,1,10,'BAJA',1,1,'2026-10-09 10:11:43',NULL,NULL);
/*!40000 ALTER TABLE `tramites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` bigint NOT NULL AUTO_INCREMENT,
  `id_rol` bigint DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (now()),
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_dependencia` bigint NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_identificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_identificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'Administrador General (Dios DB)','$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS','Activo','2026-09-25 09:05:24','admin@odin.gov.co',1,'Sede Central ODIN','admin','3001234567','CC'),(2,2,'Carlos Mendoza','$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS','Activo','2026-09-25 10:14:14','carlos.mendoza@odin.gov.co',1,'Calle Principal # 10-20','1098765432','3001234567','CC'),(3,3,'Laura Gómez','$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS','Activo','2026-09-25 10:14:14','laura.gomez@odin.gov.co',1,'Calle Principal # 10-20','1020304050','3001234567','CC'),(4,4,'Andrés Morales','$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS','Inactivo','2026-09-25 10:14:14','andres.morales@odin.gov.co',1,'Calle Principal # 10-20','80123456','3001234567','CC'),(5,2,'Patricia Silva','$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS','Activo','2026-09-25 10:14:14','patricia.silva@odin.gov.co',1,'Calle Principal # 10-20','52345678','3001234567','CC');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `validation_audit`
--

DROP TABLE IF EXISTS `validation_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `validation_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha_validacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `warning_count` int DEFAULT NULL,
  `detalles` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `validation_audit`
--

LOCK TABLES `validation_audit` WRITE;
/*!40000 ALTER TABLE `validation_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `validation_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `validation_audit_full`
--

DROP TABLE IF EXISTS `validation_audit_full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `validation_audit_full` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha_validacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `error_count` int DEFAULT NULL,
  `warning_count` int DEFAULT NULL,
  `resultados` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `validation_audit_full`
--

LOCK TABLES `validation_audit_full` WRITE;
/*!40000 ALTER TABLE `validation_audit_full` DISABLE KEYS */;
/*!40000 ALTER TABLE `validation_audit_full` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `versiones_documento`
--

DROP TABLE IF EXISTS `versiones_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `versiones_documento` (
  `id_version` bigint NOT NULL AUTO_INCREMENT,
  `id_documento` bigint NOT NULL,
  `version_numero` int NOT NULL,
  `ruta_archivo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `fecha_version` datetime DEFAULT CURRENT_TIMESTAMP,
  `creado_por` bigint DEFAULT NULL,
  PRIMARY KEY (`id_version`),
  UNIQUE KEY `uq_documento_version` (`id_documento`,`version_numero`),
  KEY `fk_version_usuario` (`creado_por`),
  CONSTRAINT `fk_version_documento` FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`id_documento`),
  CONSTRAINT `fk_version_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versiones_documento`
--

LOCK TABLES `versiones_documento` WRITE;
/*!40000 ALTER TABLE `versiones_documento` DISABLE KEYS */;
/*!40000 ALTER TABLE `versiones_documento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'odin_api_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-23  8:30:23
