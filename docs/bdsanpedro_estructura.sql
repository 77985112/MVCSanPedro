
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actividades` (
  `idActividad` int(11) NOT NULL AUTO_INCREMENT,
  `TituloActividad` varchar(150) NOT NULL,
  `FechaActividad` date NOT NULL,
  `DetalleActividad` varchar(255) NOT NULL,
  `ImagenActividad` varchar(30) NOT NULL,
  `LugarAct` varchar(30) NOT NULL,
  `HoraAct` time NOT NULL,
  `CiCat` varchar(15) NOT NULL,
  `EstActividad` varchar(10) NOT NULL DEFAULT 'Activo',
  `TipoActividad` varchar(30) NOT NULL,
  `Fecha_Public` date DEFAULT current_timestamp(),
  PRIMARY KEY (`idActividad`),
  KEY `CiCat` (`CiCat`),
  CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`CiCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignacion` (
  `Gestion` date NOT NULL DEFAULT current_timestamp(),
  `CiCat` varchar(15) NOT NULL,
  `IdGrupo` int(11) NOT NULL,
  `FechaAsigCat` date NOT NULL,
  `FechaIniCat` date NOT NULL,
  `FechaFinCat` date DEFAULT NULL,
  `RolCat` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Gestion`,`CiCat`,`IdGrupo`),
  KEY `CiCat` (`CiCat`),
  KEY `IdGrupo` (`IdGrupo`),
  CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`CiCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`IdGrupo`) REFERENCES `grupo` (`IdGrupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistenciaapoderado` (
  `IdInscripcion` int(11) NOT NULL,
  `IdReunion` int(11) NOT NULL,
  `DetalleAsis` varchar(15) NOT NULL,
  `MontoAsis` int(11) NOT NULL,
  PRIMARY KEY (`IdInscripcion`,`IdReunion`),
  KEY `IdReunion` (`IdReunion`),
  CONSTRAINT `asistenciaapoderado_ibfk_1` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asistenciaapoderado_ibfk_2` FOREIGN KEY (`IdReunion`) REFERENCES `reunioncel` (`IdReunion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistenciacat` (
  `IdAsisCat` int(11) NOT NULL AUTO_INCREMENT,
  `Gestion` date NOT NULL,
  `CiCat` varchar(15) NOT NULL,
  `IdGrupo` int(11) NOT NULL,
  `DetalleAsis` varchar(15) NOT NULL,
  `FechaAsisCat` date NOT NULL,
  PRIMARY KEY (`IdAsisCat`),
  KEY `asistencias` (`CiCat`,`Gestion`,`IdGrupo`),
  CONSTRAINT `asistencias` FOREIGN KEY (`CiCat`, `Gestion`, `IdGrupo`) REFERENCES `asignacion` (`CiCat`, `Gestion`, `IdGrupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistenciacel` (
  `CodAsis` int(11) NOT NULL AUTO_INCREMENT,
  `IdInscripcion` int(11) NOT NULL,
  `FechaAsisConf` date NOT NULL,
  `HoraAsisConf` time NOT NULL,
  `DetalleAsis` varchar(50) DEFAULT NULL,
  `TipoAsis` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`CodAsis`),
  KEY `IdInscripcion` (`IdInscripcion`),
  CONSTRAINT `asistenciacel_ibfk_1` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargo` (
  `CodCar` int(11) NOT NULL AUTO_INCREMENT,
  `DescripCar` varchar(30) NOT NULL,
  PRIMARY KEY (`CodCar`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catequista` (
  `CiCat` varchar(15) NOT NULL,
  `Sacramento` varchar(20) NOT NULL,
  `UsuarioCat` varchar(10) NOT NULL,
  `ClaveCat` varchar(10) NOT NULL,
  `EstadoCat` varchar(10) NOT NULL,
  `ImagenCat` varchar(25) NOT NULL,
  `FondoCat` varchar(25) NOT NULL,
  `PoderCat` varchar(15) NOT NULL,
  `FraseCat` varchar(250) NOT NULL,
  `FechaRegCat` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`CiCat`),
  CONSTRAINT `catequista_ibfk_1` FOREIGN KEY (`CiCat`) REFERENCES `persona` (`CiPersona`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `celebrante` (
  `CiCel` varchar(15) NOT NULL,
  `Sacramento` varchar(20) NOT NULL,
  `UsuarioCel` varchar(10) NOT NULL,
  `ClaveCel` varchar(10) NOT NULL,
  `PerfilCel` varchar(25) NOT NULL,
  `FondoCel` varchar(25) NOT NULL,
  `EstadoCel` varchar(10) NOT NULL,
  PRIMARY KEY (`CiCel`),
  CONSTRAINT `celebrante_ibfk_1` FOREIGN KEY (`CiCel`) REFERENCES `persona` (`CiPersona`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `celeexamen` (
  `IdCelEx` int(11) NOT NULL AUTO_INCREMENT,
  `NotaExamen1` int(3) DEFAULT 0,
  `NotaExamen2` int(3) DEFAULT 0,
  `NotaExamen3` int(3) DEFAULT 0,
  `NotaExamen4` int(3) DEFAULT 0,
  `EstadoExamen` varchar(10) NOT NULL DEFAULT 'REPROBADO',
  `IdInscripcion` int(11) NOT NULL,
  PRIMARY KEY (`IdCelEx`),
  KEY `IdInscripcion` (`IdInscripcion`),
  CONSTRAINT `celeexamen_ibfk_1` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificado` (
  `NroCertificado` int(11) NOT NULL AUTO_INCREMENT,
  `NroEmision` varchar(10) NOT NULL,
  `FechaEmision` date DEFAULT current_timestamp(),
  `PresbiteroEm` varchar(50) DEFAULT NULL,
  `Observacion` varchar(80) DEFAULT NULL,
  `CelebrantePres` varchar(50) DEFAULT NULL,
  `EstadoCert` varchar(15) NOT NULL,
  `CodDetCert` int(11) NOT NULL,
  `CodIns` int(11) NOT NULL,
  PRIMARY KEY (`NroCertificado`),
  KEY `CodDetCert` (`CodDetCert`),
  KEY `CodIns` (`CodIns`),
  CONSTRAINT `certificado_ibfk_1` FOREIGN KEY (`CodDetCert`) REFERENCES `detallecert` (`CodDetCert`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `certificado_ibfk_2` FOREIGN KEY (`CodIns`) REFERENCES `inssacramento` (`CodIns`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colaborador` (
  `idColaboradorCat` int(11) NOT NULL AUTO_INCREMENT,
  `CiCatCat` varchar(15) NOT NULL,
  `Opcion1Cat` tinyint(1) NOT NULL,
  `Opcion2Cat` tinyint(1) NOT NULL,
  `Opcion3Cat` tinyint(1) NOT NULL,
  `Opcion4Cat` tinyint(1) NOT NULL,
  `Opcion5Cat` tinyint(1) NOT NULL,
  PRIMARY KEY (`idColaboradorCat`),
  KEY `CiCatCat` (`CiCatCat`),
  CONSTRAINT `colaborador_ibfk_1` FOREIGN KEY (`CiCatCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detallecert` (
  `CodDetCert` int(11) NOT NULL AUTO_INCREMENT,
  `NroLibro` varchar(10) DEFAULT NULL,
  `NroPag` varchar(10) DEFAULT NULL,
  `NroPart` varchar(10) DEFAULT NULL,
  `LugarNac` varchar(50) DEFAULT NULL,
  `FechaReal` date NOT NULL,
  `HoraReal` time NOT NULL,
  `CodParr` int(11) NOT NULL,
  PRIMARY KEY (`CodDetCert`),
  KEY `CodParr` (`CodParr`),
  CONSTRAINT `detallecert_ibfk_1` FOREIGN KEY (`CodParr`) REFERENCES `parroquia` (`CodParr`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalleexamen` (
  `IdCelEx` int(11) NOT NULL,
  `CodExamen` int(11) NOT NULL,
  PRIMARY KEY (`IdCelEx`,`CodExamen`),
  KEY `CodExamen` (`CodExamen`),
  CONSTRAINT `detalleexamen_ibfk_1` FOREIGN KEY (`IdCelEx`) REFERENCES `celeexamen` (`IdCelEx`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalleexamen_ibfk_2` FOREIGN KEY (`CodExamen`) REFERENCES `examen` (`CodExamen`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalletipoins` (
  `CodTipoItem` int(11) NOT NULL,
  `IdInscripcion` int(11) NOT NULL,
  PRIMARY KEY (`CodTipoItem`,`IdInscripcion`),
  KEY `IdInscripcion` (`IdInscripcion`),
  CONSTRAINT `detalletipoins_ibfk_1` FOREIGN KEY (`CodTipoItem`) REFERENCES `tipoins` (`CodTipoItem`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalletipoins_ibfk_2` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docpresbau` (
  `IdPresDocBau` int(11) NOT NULL AUTO_INCREMENT,
  `ParroquiaBau` varchar(30) NOT NULL,
  `NrLib` varchar(10) NOT NULL,
  `NrPag` varchar(10) NOT NULL,
  `NrPart` varchar(10) NOT NULL,
  `FechaBau` date NOT NULL,
  `CodPres` int(11) NOT NULL,
  PRIMARY KEY (`IdPresDocBau`),
  KEY `CodPres` (`CodPres`),
  CONSTRAINT `docpresbau_ibfk_1` FOREIGN KEY (`CodPres`) REFERENCES `presentaciondoc` (`CodPres`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documento` (
  `idRegistro` int(11) NOT NULL AUTO_INCREMENT,
  `Gestion` date NOT NULL,
  `CiCat` varchar(15) NOT NULL,
  `IdGrupo` int(11) NOT NULL,
  `IdInscripcion` int(11) NOT NULL,
  `NumeroDoc` varchar(15) NOT NULL,
  `DetalleDoc` varchar(50) NOT NULL,
  `LibroDoc` varchar(5) DEFAULT NULL,
  `PaginaDoc` varchar(5) DEFAULT NULL,
  `PartidaDoc` varchar(5) DEFAULT NULL,
  `ParroquiaDoc` varchar(20) NOT NULL,
  `FechaPres` date NOT NULL DEFAULT current_timestamp(),
  `FechaDoc` date DEFAULT NULL,
  PRIMARY KEY (`idRegistro`),
  KEY `IdInscripcion` (`IdInscripcion`),
  KEY `Gestion` (`Gestion`,`CiCat`,`IdGrupo`),
  CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `documento_ibfk_2` FOREIGN KEY (`Gestion`, `CiCat`, `IdGrupo`) REFERENCES `asignacion` (`Gestion`, `CiCat`, `IdGrupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estrellacat` (
  `idEstrella` int(11) NOT NULL AUTO_INCREMENT,
  `CiCatEst` varchar(15) NOT NULL,
  `ApoyoEst` varchar(15) NOT NULL,
  `LimiteEst` tinyint(5) NOT NULL,
  PRIMARY KEY (`idEstrella`),
  KEY `CiCatEst` (`CiCatEst`),
  CONSTRAINT `estrellacat_ibfk_1` FOREIGN KEY (`CiCatEst`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examen` (
  `CodExamen` int(11) NOT NULL AUTO_INCREMENT,
  `TituloExamen` varchar(20) NOT NULL,
  `DetalleExamen` varchar(50) DEFAULT NULL,
  `FechaExamen` date NOT NULL,
  PRIMARY KEY (`CodExamen`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `familiar` (
  `CiFamiliar` varchar(15) NOT NULL,
  PRIMARY KEY (`CiFamiliar`),
  CONSTRAINT `familiar_ibfk_1` FOREIGN KEY (`CiFamiliar`) REFERENCES `persona` (`CiPersona`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fechassacramento` (
  `idSacramento` int(11) NOT NULL AUTO_INCREMENT,
  `CiCatCat` varchar(15) NOT NULL,
  `EstadoIns` varchar(10) DEFAULT NULL,
  `Fecha_Ini` datetime DEFAULT NULL,
  `Fecha_Fin` datetime DEFAULT NULL,
  `Sacramento` varchar(20) NOT NULL,
  `ActividadSac` varchar(20) NOT NULL,
  PRIMARY KEY (`idSacramento`),
  KEY `CiCatCat` (`CiCatCat`),
  CONSTRAINT `fechassacramento_ibfk_1` FOREIGN KEY (`CiCatCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo` (
  `IdGrupo` int(11) NOT NULL AUTO_INCREMENT,
  `NombreGrupo` varchar(20) NOT NULL,
  `Color_Grupo` varchar(12) NOT NULL,
  `Santo_Grupo` varchar(25) NOT NULL,
  `Imagen_Grupo` varchar(25) NOT NULL,
  `Frase_Santo` varchar(200) DEFAULT '-',
  PRIMARY KEY (`IdGrupo`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inscripcion` (
  `IdInscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `CiCel` varchar(15) NOT NULL,
  `IdGrupo` int(11) NOT NULL,
  `PreValor` varchar(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdInscripcion`),
  KEY `CiCel` (`CiCel`),
  KEY `IdGrupo` (`IdGrupo`),
  CONSTRAINT `inscripcion_ibfk_1` FOREIGN KEY (`CiCel`) REFERENCES `celebrante` (`CiCel`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inscripcion_ibfk_2` FOREIGN KEY (`IdGrupo`) REFERENCES `grupo` (`IdGrupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insfamiliar` (
  `CiFamiliar` varchar(15) NOT NULL,
  `IdInscripcion` int(11) NOT NULL,
  `RolFam` varchar(10) NOT NULL,
  `Parentesco` varchar(50) NOT NULL,
  PRIMARY KEY (`CiFamiliar`,`IdInscripcion`),
  KEY `IdInscripcion` (`IdInscripcion`),
  CONSTRAINT `insfamiliar_ibfk_1` FOREIGN KEY (`CiFamiliar`) REFERENCES `familiar` (`CiFamiliar`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `insfamiliar_ibfk_2` FOREIGN KEY (`IdInscripcion`) REFERENCES `inscripcion` (`IdInscripcion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inssacramento` (
  `CodIns` int(11) NOT NULL AUTO_INCREMENT,
  `FechaIns` date NOT NULL DEFAULT current_timestamp(),
  `CodPer` int(11) NOT NULL,
  `CodSac` int(11) NOT NULL,
  PRIMARY KEY (`CodIns`),
  KEY `CodPer` (`CodPer`),
  KEY `CodSac` (`CodSac`),
  CONSTRAINT `inssacramento_ibfk_1` FOREIGN KEY (`CodPer`) REFERENCES `personal` (`CodPer`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inssacramento_ibfk_2` FOREIGN KEY (`CodSac`) REFERENCES `tiposacramento` (`CodSac`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajepublico` (
  `idMensaje` int(11) NOT NULL AUTO_INCREMENT,
  `NombreMensaje` varchar(20) NOT NULL,
  `NumCorrMensaje` varchar(20) NOT NULL,
  `ConceptoMensaje` varchar(30) NOT NULL,
  `DetalleMensaje` varchar(50) NOT NULL,
  `FechaMensaje` datetime NOT NULL,
  `FechaLeido` datetime DEFAULT NULL,
  `EstadoMensaje` varchar(10) NOT NULL DEFAULT 'Pendiente',
  PRIMARY KEY (`idMensaje`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `idNotificacion` int(11) NOT NULL AUTO_INCREMENT,
  `CiNotificador` varchar(15) NOT NULL,
  `TituloNot` varchar(30) NOT NULL,
  `DetalleNot` varchar(150) NOT NULL,
  `FechaNot` datetime NOT NULL,
  `FechaNotFin` datetime NOT NULL,
  `EstadoNot` varchar(10) NOT NULL,
  PRIMARY KEY (`idNotificacion`),
  KEY `CiNotificador` (`CiNotificador`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`CiNotificador`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parroquia` (
  `CodParr` int(11) NOT NULL AUTO_INCREMENT,
  `NomParroquia` varchar(50) NOT NULL,
  `LugParroquia` varchar(50) NOT NULL,
  PRIMARY KEY (`CodParr`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persona` (
  `CiPersona` varchar(15) NOT NULL,
  `Nombre` varchar(25) NOT NULL,
  `ApPaterno` varchar(20) NOT NULL,
  `ApMaterno` varchar(20) DEFAULT NULL,
  `Sexo` varchar(6) NOT NULL,
  `FechaNac` date DEFAULT NULL,
  `Direccion` varchar(40) DEFAULT NULL,
  `Contacto` varchar(10) DEFAULT NULL,
  `id_certificado_nacimiento` varchar(50) DEFAULT NULL,
  `Estado_per` varchar(15) NOT NULL,
  PRIMARY KEY (`CiPersona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal` (
  `CodPer` int(11) NOT NULL AUTO_INCREMENT,
  `UsuarioPer` varchar(20) NOT NULL,
  `ClavePer` varchar(30) NOT NULL,
  `CiPersonal` varchar(15) NOT NULL,
  `Nombre` varchar(25) NOT NULL,
  `Paterno` varchar(20) NOT NULL,
  `Materno` varchar(20) DEFAULT NULL,
  `Sexo` varchar(6) NOT NULL,
  `ContactoPer` varchar(15) NOT NULL,
  `PerfilPer` varchar(25) NOT NULL DEFAULT 'Ninguno.png',
  `FondoPer` varchar(25) NOT NULL DEFAULT 'Ninguno.jpg',
  `FrasePer` varchar(50) NOT NULL,
  `Estado` varchar(15) DEFAULT NULL,
  `FechaIng` date DEFAULT NULL,
  `CodCar` int(11) NOT NULL,
  PRIMARY KEY (`CodPer`),
  KEY `CodCar` (`CodCar`),
  CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`CodCar`) REFERENCES `cargo` (`CodCar`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `presentaciondoc` (
  `CodPres` int(11) NOT NULL AUTO_INCREMENT,
  `FechaPres` date NOT NULL,
  `dequien` varchar(20) NOT NULL,
  `CodificDoc` varchar(20) DEFAULT NULL,
  `CodTDoc` int(11) NOT NULL,
  `CodIns` int(11) NOT NULL,
  PRIMARY KEY (`CodPres`),
  KEY `CodTDoc` (`CodTDoc`),
  KEY `presentaciondoc_ibfk_2` (`CodIns`),
  CONSTRAINT `presentaciondoc_ibfk_1` FOREIGN KEY (`CodTDoc`) REFERENCES `tipodocumento` (`CodTDoc`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `presentaciondoc_ibfk_2` FOREIGN KEY (`CodIns`) REFERENCES `inssacramento` (`CodIns`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `publicidadcat` (
  `idPublicidad` int(11) NOT NULL AUTO_INCREMENT,
  `CiCat` varchar(15) NOT NULL,
  `Fecha_pub` date NOT NULL,
  `Descripcion_pub` varchar(250) NOT NULL,
  `Imagen_pub` varchar(25) NOT NULL,
  `Reaccion_pub` int(11) NOT NULL,
  `Estado_pub` varchar(10) NOT NULL,
  PRIMARY KEY (`idPublicidad`),
  KEY `CiCat` (`CiCat`),
  CONSTRAINT `publicidadcat_ibfk_1` FOREIGN KEY (`CiCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rcatpublic` (
  `idPublicidad` int(11) NOT NULL,
  `CiCat` varchar(15) NOT NULL,
  `FechaReac` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idPublicidad`,`CiCat`),
  KEY `CiCat` (`CiCat`),
  CONSTRAINT `rcatpublic_ibfk_1` FOREIGN KEY (`idPublicidad`) REFERENCES `publicidadcat` (`idPublicidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rcatpublic_ibfk_2` FOREIGN KEY (`CiCat`) REFERENCES `catequista` (`CiCat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reserva` (
  `CodRes` int(11) NOT NULL AUTO_INCREMENT,
  `FechaReal` date NOT NULL,
  `HoraReal` time NOT NULL,
  `EstadoRes` varchar(20) DEFAULT NULL,
  `CiPersona` varchar(15) NOT NULL,
  `TipoCel` varchar(20) NOT NULL,
  `Realizacion` varchar(30) NOT NULL,
  `CodIns` int(11) NOT NULL,
  `Quien` varchar(255) DEFAULT NULL,
  `CorreoSolicitante` varchar(254) DEFAULT NULL,
  `HorarioOcupado` tinyint(4) GENERATED ALWAYS AS (case when `EstadoRes` = 'Cancelado' or `Realizacion` = 'Comunitario' then NULL else 1 end) VIRTUAL,
  PRIMARY KEY (`CodRes`),
  UNIQUE KEY `uq_reserva_fecha_hora` (`FechaReal`,`HoraReal`,`HorarioOcupado`),
  KEY `CiPersona` (`CiPersona`),
  CONSTRAINT `reserva_ibfk_1` FOREIGN KEY (`CiPersona`) REFERENCES `persona` (`CiPersona`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reunioncel` (
  `IdReunion` int(11) NOT NULL AUTO_INCREMENT,
  `FechaReu` date NOT NULL,
  `HoraReu` time NOT NULL,
  `Sacramento` varchar(25) DEFAULT NULL,
  `Estado` varchar(10) DEFAULT NULL,
  `DetalleReu` varchar(50) NOT NULL,
  `Gestion` date NOT NULL,
  `CiCat` varchar(15) NOT NULL,
  `IdGrupo` int(11) NOT NULL,
  PRIMARY KEY (`IdReunion`),
  KEY `Gestion` (`Gestion`,`CiCat`,`IdGrupo`),
  CONSTRAINT `reunioncel_ibfk_1` FOREIGN KEY (`Gestion`, `CiCat`, `IdGrupo`) REFERENCES `asignacion` (`Gestion`, `CiCat`, `IdGrupo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitante` (
  `CiPersona` varchar(15) NOT NULL,
  `CodIns` int(11) NOT NULL,
  `Rol` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`CiPersona`,`CodIns`),
  KEY `CodIns` (`CodIns`),
  CONSTRAINT `solicitante_ibfk_1` FOREIGN KEY (`CiPersona`) REFERENCES `persona` (`CiPersona`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `solicitante_ibfk_2` FOREIGN KEY (`CodIns`) REFERENCES `inssacramento` (`CodIns`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipodocumento` (
  `CodTDoc` int(11) NOT NULL AUTO_INCREMENT,
  `DescripTDoc` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`CodTDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipoins` (
  `CodTipoItem` int(11) NOT NULL AUTO_INCREMENT,
  `DescripItem` varchar(50) NOT NULL,
  `Valor` varchar(3) NOT NULL,
  PRIMARY KEY (`CodTipoItem`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiposacramento` (
  `CodSac` int(11) NOT NULL AUTO_INCREMENT,
  `DescripSac` varchar(20) NOT NULL,
  `CostoSac` decimal(10,2) NOT NULL,
  PRIMARY KEY (`CodSac`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizarDocumentoCelebrante`(IN `p_idRegistro` INT, IN `p_Dequien` VARCHAR(50), IN `p_DetalleDoc` VARCHAR(50), IN `p_NumeroDoc` VARCHAR(15), IN `p_LibroDoc` VARCHAR(5), IN `p_PaginaDoc` VARCHAR(5), IN `p_PartidaDoc` VARCHAR(5), IN `p_ParroquiaDoc` VARCHAR(50))
BEGIN
    UPDATE Documento
    SET Dequien = p_Dequien,
    	DetalleDoc = p_DetalleDoc,
        NumeroDoc = p_NumeroDoc,
        LibroDoc = p_LibroDoc,
        PaginaDoc = p_PaginaDoc,
        PartidaDoc = p_PartidaDoc,
        ParroquiaDoc = p_ParroquiaDoc
    WHERE idRegistro = p_idRegistro;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCatequista`(IN `p_CiCat` VARCHAR(15), IN `p_Sacramento` VARCHAR(20))
BEGIN
    SELECT c.*, CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', pe.ApMaterno) AS NombreCompleto, 
           asig.*, gr.*
    FROM catequista c
    INNER JOIN persona pe ON pe.CiPersona = c.CiCat
    INNER JOIN asignacion asig ON asig.CiCat = c.CiCat
    INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo
    WHERE c.CiCat = p_CiCat AND c.Sacramento = p_Sacramento;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCelebrante`(IN `p_CiCel` VARCHAR(15), IN `p_Sacramento` VARCHAR(20))
BEGIN
    SELECT c.*, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto,
           ins.*, g.NombreGrupo, ti.DescripItem, ti.Valor, ti.CodTipoItem
    FROM celebrante c
    INNER JOIN persona p ON p.CiPersona = c.CiCel
    INNER JOIN inscripcion ins ON ins.CiCel = c.CiCel
    INNER JOIN grupo g ON g.IdGrupo = ins.IdGrupo
    LEFT JOIN Detalletipoins dti ON dti.IdInscripcion = ins.IdInscripcion
    LEFT JOIN TipoIns ti ON ti.CodTipoItem = dti.CodTipoItem
    WHERE c.CiCel = p_CiCel AND c.Sacramento = p_Sacramento;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCelebrantePorCI`(IN `ciPersona` VARCHAR(15))
BEGIN
    SELECT c.CiCel, p.Nombre, p.ApPaterno, p.ApMaterno, i.IdInscripcion, t.DescripItem
    FROM celebrante c
    JOIN persona p ON c.CiCel = p.CiPersona
    LEFT JOIN inscripcion i ON c.CiCel = i.CiCel
    LEFT JOIN detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
    LEFT JOIN TipoIns t ON dti.CodTipoItem = t.CodTipoItem
    WHERE c.CiCel = ciPersona;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCertificadoConfirmacion`(IN `p_CiPersonaConfirmante` VARCHAR(15))
BEGIN
    SELECT 
        cert.NroEmision,
        cert.EstadoCert,
        dc.NroLibro,
        dc.NroPag,
        dc.NroPart,
        dc.FechaReal,
        dc.HoraReal,
        parr.NomParroquia,
        parr.LugParroquia,
        cert.PresbiteroEm,
        cert.Observacion,
        cert.CelebrantePres,
        confirmante.CiPersona AS CiConfirmante,
        CONCAT(confirmante.Nombre, ' ', confirmante.ApPaterno, ' ', confirmante.ApMaterno) AS NombreCompletoConfirmante,
        padrino.CiPersona AS CiPadrino,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombreCompletoPadrino,
        madrina.CiPersona AS CiMadrina,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreCompletoMadrina
    FROM 
        certificado cert
        INNER JOIN DetalleCert dc ON cert.CodDetCert = dc.CodDetCert
        INNER JOIN parroquia parr ON parr.CodDetCert = dc.CodDetCert
        INNER JOIN inssacramento ins ON cert.CodIns = ins.CodIns
        INNER JOIN Solicitante sol_confirmante ON sol_confirmante.CodIns = ins.CodIns AND sol_confirmante.Rol = 'Confirmante'
        INNER JOIN persona confirmante ON confirmante.CiPersona = sol_confirmante.CiPersona
        LEFT JOIN Solicitante sol_padrino ON sol_padrino.CodIns = ins.CodIns AND sol_padrino.Rol = 'Padrino'
        LEFT JOIN persona padrino ON padrino.CiPersona = sol_padrino.CiPersona
        LEFT JOIN Solicitante sol_madrina ON sol_madrina.CodIns = ins.CodIns AND sol_madrina.Rol = 'Madrina'
        LEFT JOIN persona madrina ON madrina.CiPersona = sol_madrina.CiPersona
    WHERE 
        confirmante.CiPersona = p_CiPersonaConfirmante;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCertificadoMatrimonio`(IN `p_CiPersona` VARCHAR(15))
BEGIN
    SELECT
        cert.NroCertificado,
        cert.NroEmision,
        cert.FechaEmision,
        cert.PresbiteroEm,
        cert.Observacion,
        cert.CelebrantePres,
        cert.EstadoCert,
        det.NroLibro,
        det.NroPag,
        det.NroPart,
        det.FechaReal,
        det.HoraReal,
        parr.NomParroquia,
        parr.LugParroquia,
        p_novio.CiPersona AS CiPersonaNovio,
        CONCAT(p_novio.Nombre, ' ', p_novio.ApPaterno, ' ', p_novio.ApMaterno) AS NombreCompletoNovio,
        p_novia.CiPersona AS CiPersonaNovia,
        CONCAT(p_novia.Nombre, ' ', p_novia.ApPaterno, ' ', p_novia.ApMaterno) AS NombreCompletoNovia,
        CONCAT(p_padrenovio.Nombre, ' ', p_padrenovio.ApPaterno, ' ', p_padrenovio.ApMaterno) AS NombreCompletoPadreNovio,
        CONCAT(p_madrenovio.Nombre, ' ', p_madrenovio.ApPaterno, ' ', p_madrenovio.ApMaterno) AS NombreCompletoMadreNovio,
        CONCAT(p_padrenovia.Nombre, ' ', p_padrenovia.ApPaterno, ' ', p_padrenovia.ApMaterno) AS NombreCompletoPadreNovia,
        CONCAT(p_madrenovia.Nombre, ' ', p_madrenovia.ApPaterno, ' ', p_madrenovia.ApMaterno) AS NombreCompletoMadreNovia,
        CONCAT(p_padrino.Nombre, ' ', p_padrino.ApPaterno, ' ', p_padrino.ApMaterno) AS NombreCompletoPadrino,
        CONCAT(p_madrina.Nombre, ' ', p_madrina.ApPaterno, ' ', p_madrina.ApMaterno) AS NombreCompletoMadrina,
        CONCAT(p_testigoA.Nombre, ' ', p_testigoA.ApPaterno, ' ', p_testigoA.ApMaterno) AS NombreCompletoTestigoA,
        CONCAT(p_testigoB.Nombre, ' ', p_testigoB.ApPaterno, ' ', p_testigoB.ApMaterno) AS NombreCompletoTestigoB
    FROM
        certificado cert
        INNER JOIN DetalleCert det ON cert.CodDetCert = det.CodDetCert
        INNER JOIN parroquia parr ON parr.CodDetCert = det.CodDetCert
        INNER JOIN inssacramento ins ON cert.CodIns = ins.CodIns
        INNER JOIN Solicitante s_novio ON s_novio.CodIns = ins.CodIns AND s_novio.Rol = 'Novio'
        INNER JOIN persona p_novio ON s_novio.CiPersona = p_novio.CiPersona
        INNER JOIN Solicitante s_novia ON s_novia.CodIns = ins.CodIns AND s_novia.Rol = 'Novia'
        INNER JOIN persona p_novia ON s_novia.CiPersona = p_novia.CiPersona
        LEFT JOIN Solicitante s_padrenovio ON s_padrenovio.CodIns = ins.CodIns AND s_padrenovio.Rol = 'Pap├í del Novio'
        LEFT JOIN persona p_padrenovio ON s_padrenovio.CiPersona = p_padrenovio.CiPersona
        LEFT JOIN Solicitante s_madrenovio ON s_madrenovio.CodIns = ins.CodIns AND s_madrenovio.Rol = 'Mam├í del Novio'
        LEFT JOIN persona p_madrenovio ON s_madrenovio.CiPersona = p_madrenovio.CiPersona
        LEFT JOIN Solicitante s_padrenovia ON s_padrenovia.CodIns = ins.CodIns AND s_padrenovia.Rol = 'Pap├í de la Novia'
        LEFT JOIN persona p_padrenovia ON s_padrenovia.CiPersona = p_padrenovia.CiPersona
        LEFT JOIN Solicitante s_madrenovia ON s_madrenovia.CodIns = ins.CodIns AND s_madrenovia.Rol = 'Mam├í de la Novia'
        LEFT JOIN persona p_madrenovia ON s_madrenovia.CiPersona = p_madrenovia.CiPersona
        LEFT JOIN Solicitante s_padrino ON s_padrino.CodIns = ins.CodIns AND s_padrino.Rol = 'Padrino'
        LEFT JOIN persona p_padrino ON s_padrino.CiPersona = p_padrino.CiPersona
        LEFT JOIN Solicitante s_madrina ON s_madrina.CodIns = ins.CodIns AND s_madrina.Rol = 'Madrina'
        LEFT JOIN persona p_madrina ON s_madrina.CiPersona = p_madrina.CiPersona
        LEFT JOIN Solicitante s_testigoA ON s_testigoA.CodIns = ins.CodIns AND s_testigoA.Rol = 'Testigo1'
        LEFT JOIN persona p_testigoA ON s_testigoA.CiPersona = p_testigoA.CiPersona
        LEFT JOIN Solicitante s_testigoB ON s_testigoB.CodIns = ins.CodIns AND s_testigoB.Rol = 'Testigo2'
        LEFT JOIN persona p_testigoB ON s_testigoB.CiPersona = p_testigoB.CiPersona
    WHERE
        (p_novio.CiPersona = p_CiPersona OR p_novia.CiPersona = p_CiPersona)
        AND ins.CodSac = 4
        AND cert.EstadoCert = 'Vigente';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarCertificadosPorFechaYSacramento`(IN `p_fecha` DATE, IN `p_descripcion` VARCHAR(255))
BEGIN
    IF p_descripcion = 'todos' THEN
        SELECT 
        c.EstadoCert,
        ts.DescripSac,
        c.NroEmision,
        c.FechaEmision,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        certificado c
    INNER JOIN 
        inssacramento ins ON c.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        c.FechaEmision = p_fecha;
    ELSE
        SELECT 
        c.EstadoCert,
        ts.DescripSac,
        c.NroEmision,
        c.FechaEmision,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        certificado c
    INNER JOIN 
        inssacramento ins ON c.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        c.FechaEmision = p_fecha AND ts.DescripSac = p_descripcion;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarPersonalPorCi`(IN `ci` VARCHAR(15))
BEGIN
    SELECT 
        p.*,c.*
    FROM 
        personal p 
    INNER JOIN 
        cargo c 
    ON 
        p.CodCar = c.CodCar 
    WHERE 
        p.CiPersonal = ci;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarPorDescripcionSacramento`(IN `p_descripcion` VARCHAR(20))
BEGIN
    SELECT 
        c.NroEmision,
        ts.DescripSac,
        c.FechaEmision,
        c.PresbiteroEm,
        c.EstadoCert
    FROM 
        certificado c
    INNER JOIN 
        inssacramento ins ON c.CodIns = ins.CodIns
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    WHERE 
        ts.DescripSac = p_descripcion;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarPorFechaEmision`(IN `p_fecha` DATE)
BEGIN
    SELECT 
        c.NroEmision,
        ts.DescripSac,
        c.FechaEmision,
        c.PresbiteroEm,
        c.EstadoCert
    FROM 
        certificado c
    INNER JOIN 
        inssacramento ins ON c.CodIns = ins.CodIns
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    WHERE 
        c.FechaEmision = p_fecha;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `BuscarReservasPorFecha`(IN `p_Fecha` DATE)
BEGIN
    SELECT 
        ts.*, r.*, 
        CONCAT(novio.Nombre, ' ', novio.ApPaterno, ' ', novio.ApMaterno) AS NombresApellidosNovio,
        CONCAT(novia.Nombre, ' ', novia.ApPaterno, ' ', novia.ApMaterno) AS NombresApellidosNovia
    FROM 
        Reserva r
    INNER JOIN 
        Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
    INNER JOIN 
        inssacramento i ON s.CodIns = i.CodIns
    INNER JOIN 
        tiposacramento ts ON ts.CodSac = i.CodSac
    INNER JOIN 
        persona novio ON s.CiPersona = novio.CiPersona
    INNER JOIN 
        Solicitante sv ON i.CodIns = sv.CodIns AND sv.Rol = 'Novia'
    INNER JOIN 
        persona novia ON sv.CiPersona = novia.CiPersona
    WHERE 
        r.FechaReal = p_Fecha AND i.CodSac = 4;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CapturarCatCoor`(IN `p_CiCat` VARCHAR(12))
BEGIN
SELECT cat.CiCat, CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', pe.ApMaterno) AS NombreCompleto, asig.RolCat, cat.Sacramento, cat.ImagenCat, gr.NombreGrupo FROM catequista cat 

INNER JOIN persona pe ON cat.CiCat = pe.CiPersona
INNER JOIN asignacion asig ON asig.CiCat = cat.CiCat
INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo

WHERE (cat.CiCat != p_CiCat) AND (Asig.RolCat != 'Catequista' AND asig.RolCat != 'Titular') LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `capturarPersonalExt`(IN `p_PersonalCi` VARCHAR(12))
BEGIN
SELECT pl.*, CONCAT(pl.Nombre, ' ', pl.Paterno, ' ', pl.Materno) AS NombreCompleto, cr.DescripCar FROM 
personal pl

INNER JOIN cargo cr ON cr.CodCar = pl.CodCar

WHERE (pl.CiPersonal != p_PersonalCi) AND pl.Estado = 'Activo';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CatequistaSinGrupo`(IN p_Sacramento VARCHAR(20))
BEGIN
    IF p_Sacramento = 'Primera Comunión' THEN
        SELECT c.CiCat, CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', pe.ApMaterno) AS NombreCompleto, asig.RolCat
        FROM catequista c
        INNER JOIN persona pe ON pe.CiPersona = c.CiCat
        INNER JOIN asignacion asig ON asig.CiCat = c.CiCat
        INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo
        WHERE gr.IdGrupo = 1;
    ELSEIF p_Sacramento = 'Confirmación' THEN
        SELECT c.CiCat, CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', pe.ApMaterno) AS NombreCompleto, asig.RolCat
        FROM catequista c
        INNER JOIN persona pe ON pe.CiPersona = c.CiCat
        INNER JOIN asignacion asig ON asig.CiCat = c.CiCat
        INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo
        WHERE gr.IdGrupo = 22;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CatSacramento`(IN `p_SacCat` VARCHAR(20))
BEGIN
SELECT cat.ImagenCat FROM catequista cat WHERE  cat.Sacramento = p_SacCat ORDER BY rand() LIMIT 4;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CatSacramentoCoor`(IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT cat.CiCat, CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', pe.ApMaterno) AS NombreCompleto, asig.RolCat, cat.Sacramento, cat.ImagenCat, cat.EstadoCat, gr.NombreGrupo FROM catequista cat 
INNER JOIN persona pe ON cat.CiCat = pe.CiPersona
INNER JOIN asignacion asig ON asig.CiCat = cat.CiCat
INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo

WHERE cat.Sacramento = p_Sacramento ORDER BY gr.IdGrupo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CatsActCoor`(IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT 
	scat.ApoyoEst,
    a.RolCat,
    CONCAT(p_cat.Nombre, ' ',p_cat.ApPaterno,' ',p_cat.ApMaterno) AS DatosCatequistaCoor,
    p_cat.Contacto AS ContactoCatequista,
    gr.NombreGrupo
FROM 
    catequista c
INNER JOIN 
    asignacion a ON a.CiCat = c.CiCat
INNER JOIN
	grupo gr ON gr.IdGrupo = a.IdGrupo
INNER JOIN 
    persona p_cat ON p_cat.CiPersona = c.CiCat
INNER JOIN
	estrellacat scat ON scat.CiCatEst = c.CiCat
WHERE 
    c.Sacramento = p_Sacramento AND c.EstadoCat = 'Activo' ORDER BY gr.IdGrupo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CatsInCoor`(IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT 
    CONCAT(p_cat.Nombre, ' ',p_cat.ApPaterno,' ',p_cat.ApMaterno) AS DatosCatequistaCoor,
    p_cat.Contacto AS ContactoCatequista,
    gr.NombreGrupo
FROM 
    catequista c
INNER JOIN 
    asignacion a ON a.CiCat = c.CiCat
INNER JOIN
	grupo gr ON gr.IdGrupo = a.IdGrupo
INNER JOIN 
    persona p_cat ON p_cat.CiPersona = c.CiCat
WHERE 
    c.Sacramento = p_Sacramento AND c.EstadoCat = 'Inactivo' ORDER BY gr.IdGrupo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CelebranteSinGrupo`(IN p_Sacramento VARCHAR(50))
BEGIN
    IF p_Sacramento = 'Primera Comunión' THEN
        SELECT 
            I.IdInscripcion,
            c.CiCel, 
            CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
            t.DescripItem,
            TIMESTAMPDIFF(YEAR, p.FechaNac, CURDATE()) AS Edad
        FROM 
            inscripcion i
        INNER JOIN 
            celebrante c ON i.CiCel = c.CiCel
        INNER JOIN 
            persona p ON c.CiCel = p.CiPersona
        INNER JOIN 
            detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
        INNER JOIN 
            TipoIns t ON dti.CodTipoItem = t.CodTipoItem
        WHERE 
            i.IdGrupo = 1;
    ELSEIF p_Sacramento = 'Confirmación' THEN
        SELECT  
            I.IdInscripcion,
            c.CiCel, 
            CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
            t.DescripItem,
            TIMESTAMPDIFF(YEAR, p.FechaNac, CURDATE()) AS Edad
        FROM 
            inscripcion i
        INNER JOIN 
            celebrante c ON i.CiCel = c.CiCel
        INNER JOIN 
            persona p ON c.CiCel = p.CiPersona
        INNER JOIN 
            detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
        INNER JOIN 
            TipoIns t ON dti.CodTipoItem = t.CodTipoItem
        WHERE 
            i.IdGrupo = 22;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Compromiso_Cel`(IN p_CiFamiliar VARCHAR(15))
BEGIN
    SELECT 
        CONCAT(per.Nombre, ' ', per.ApPaterno, ' ', per.ApMaterno) AS Apoderado,
        CONCAT(perC.Nombre, ' ', perC.ApPaterno, ' ', perC.ApMaterno) AS Celebrante,
        cel.UsuarioCel AS Usuario,
        cel.ClaveCel AS Clave
    FROM familiar f
    INNER JOIN persona per ON f.CiFamiliar = per.CiPersona
    INNER JOIN insfamiliar inf ON f.CiFamiliar = inf.CiFamiliar
    INNER JOIN inscripcion ins ON inf.IdInscripcion = ins.IdInscripcion
    INNER JOIN celebrante cel ON ins.CiCel = cel.CiCel
    INNER JOIN persona perC ON cel.CiCel = perC.CiPersona
    WHERE f.CiFamiliar = p_CiFamiliar;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosEmitidos`()
BEGIN
    SELECT 
        c.EstadoCert,
        ts.DescripSac,
        c.NroEmision,
        c.FechaEmision,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        certificado c
    INNER JOIN 
        inssacramento ins ON c.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        YEAR(c.FechaEmision) = YEAR(CURDATE());
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosGCats`(IN `p_Grupo` INT, IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT CONCAT(p_cat.Nombre,' ', p_cat.ApPaterno,' ',p_cat.ApMaterno) AS Datoscat, p_cat.Contacto, gr.NombreGrupo
FROM 
	catequista cat
INNER JOIN 
    asignacion asig ON asig.CiCat = cat.CiCat
INNER JOIN 
	grupo gr ON gr.IdGrupo = asig.IdGrupo
INNER JOIN 
    persona p_cat ON cat.CiCat = p_cat.CiPersona
WHERE 
    gr.IdGrupo = p_Grupo AND cat.Sacramento = p_Sacramento AND cat.EstadoCat = 'Activo';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosGFinal`(IN `p_Grupo` INT, IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT 
    p_cel.Nombre AS NombreCelebrante,
    CONCAT(p_cel.ApPaterno, ' ' ,p_cel.ApMaterno) AS ApellidoCelebrante,
    d.ParroquiaDoc,  
    d.LibroDoc, 
    d.PaginaDoc, 
    d.PartidaDoc,
    d.FechaDoc,
    CONCAT(p_Pad.Nombre, ' ', p_Pad.ApPaterno, ' ', p_Pad.ApMaterno) AS NombrePadrino,
    CONCAT(p_Mad.Nombre, ' ', p_Mad.ApPaterno, ' ', p_Mad.ApMaterno) AS NombreMadrina,
    p_cel.FechaNac
FROM 
    celebrante ce
INNER JOIN 
    inscripcion i ON i.CiCel = ce.CiCel
INNER JOIN 
	grupo gr ON gr.IdGrupo = i.IdGrupo
INNER JOIN
	celeexamen cl ON cl.IdInscripcion = i.IdInscripcion
INNER JOIN 
    persona p_cel ON ce.CiCel = p_cel.CiPersona
INNER JOIN 
    documento d ON i.IdInscripcion = d.IdInscripcion
LEFT JOIN 
    insfamiliar inf ON i.IdInscripcion = inf.IdInscripcion
LEFT JOIN 
    familiar f ON inf.CiFamiliar = f.CiFamiliar
LEFT JOIN 
    persona p_Pad ON f.CiFamiliar = p_Pad.CiPersona AND (inf.RolFam = 'Padrino')
LEFT JOIN 
    persona p_Mad ON f.CiFamiliar = p_Mad.CiPersona AND (inf.RolFam = 'Madrina')

WHERE 
    gr.IdGrupo = p_Grupo AND ce.Sacramento = p_Sacramento AND ce.EstadoCel = 'Activo' AND cl.EstadoExamen = 'APROBADO'

GROUP BY 
    p_cel.ApPaterno;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosGFinalR`(IN `p_Grupo` INT, IN `p_Sacramento` VARCHAR(20))
BEGIN
SELECT p_cel.Nombre, CONCAT(p_cel.ApPaterno,' ',p_cel.ApMaterno) AS ApellidosCel FROM 
	celebrante ce
INNER JOIN 
    inscripcion i ON i.CiCel = ce.CiCel
INNER JOIN 
	grupo gr ON gr.IdGrupo = i.IdGrupo
INNER JOIN 
    persona p_cel ON ce.CiCel = p_cel.CiPersona
INNER JOIN
	celeexamen cl ON cl.IdInscripcion = i.IdInscripcion

WHERE 
    gr.IdGrupo = p_Grupo AND ce.Sacramento = p_Sacramento AND (cl.EstadoExamen = 'REPROBADO' OR ce.EstadoCel = 'Inactivo');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosReserva`()
BEGIN
    SELECT 
        ts.DescripSac,
        r.EstadoRes,
        r.FechaReal,
        r.HoraReal,
        r.TipoCel,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        Reserva r
    INNER JOIN 
        inssacramento ins ON r.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        YEAR(ins.FechaIns) = YEAR(CURDATE());
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosReservaPorDia`(IN `anio` INT, IN `mes` INT, IN `dia` INT)
BEGIN
    SELECT 
        ts.DescripSac,
        r.EstadoRes,
        r.FechaReal,
        r.HoraReal,
        r.TipoCel,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        Reserva r
    INNER JOIN 
        inssacramento ins ON r.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        YEAR(r.FechaReal) = anio AND MONTH(r.FechaReal) = mes AND DAY(r.FechaReal) = dia;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `DatosReservaPorMes`(IN `anio` INT, IN `mes` INT)
BEGIN
    SELECT 
        ts.DescripSac,
        r.EstadoRes,
        r.FechaReal,
        r.HoraReal,
        r.TipoCel,
        car.DescripCar,
        CONCAT(p.Nombre, ' ', p.Paterno, ' ',' (', p.CiPersonal, ')') AS DatosEm
    FROM 
        Reserva r
    INNER JOIN 
        inssacramento ins ON r.CodIns = ins.CodIns
    INNER JOIN 
        personal p ON ins.CodPer = p.CodPer
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        cargo car ON p.CodCar = car.CodCar
    WHERE 
        YEAR(r.FechaReal) = anio AND MONTH(r.FechaReal) = mes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `EliminarDocumentoCelebrante`(IN `p_idRegistro` INT)
BEGIN
    DELETE FROM Documento WHERE idRegistro = p_idRegistro;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertarActividad`(IN `p_TituloActividad` VARCHAR(20), IN `p_FechaActividad` DATE, IN `p_DetalleActividad` VARCHAR(80), IN `p_ImagenActividad` VARCHAR(30), IN `p_LugarAct` VARCHAR(30), IN `p_HoraAct` TIME, IN `p_CiCat` VARCHAR(15), IN `p_TipoActividad` VARCHAR(30))
BEGIN
    INSERT INTO actividades (TituloActividad, FechaActividad, DetalleActividad, ImagenActividad, LugarAct, HoraAct, CiCat, TipoActividad)
    VALUES 
    (p_TituloActividad, p_FechaActividad, p_DetalleActividad, p_ImagenActividad, p_LugarAct, p_HoraAct, p_CiCat, p_TipoActividad);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertarDocumento`(IN `p_Gestion` DATE, IN `p_CiCat` VARCHAR(15), IN `p_IdGrupo` INT, IN `p_IdInscripcion` INT, IN `p_Dequien` VARCHAR(50), IN `p_NumeroDoc` VARCHAR(15), IN `p_DetalleDoc` VARCHAR(50), IN `p_LibroDoc` VARCHAR(5), IN `p_PaginaDoc` VARCHAR(5), IN `p_PartidaDoc` VARCHAR(5), IN `p_ParroquiaDoc` VARCHAR(50))
BEGIN
    INSERT INTO Documento (Gestion, CiCat, IdGrupo, IdInscripcion, Dequien, NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc)
    VALUES (p_Gestion, p_CiCat, p_IdGrupo, p_IdInscripcion, p_Dequien, p_NumeroDoc, p_DetalleDoc, p_LibroDoc, p_PaginaDoc, p_PartidaDoc, p_ParroquiaDoc);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `MiReporte`(IN `p_IdInscripcion` INT)
BEGIN
    SELECT 
    cl.Sacramento,
    	gr.NombreGrupo,
        CONCAT(pr.Nombre,' ',pr.ApPaterno, ' ',pr.ApMaterno) AS DatoCelebrante,
        ce.IdCelEx,
        ce.NotaExamen1,
        ce.NotaExamen2,
        ce.NotaExamen3,
        ce.NotaExamen4,
        ce.EstadoExamen
    FROM 
        inscripcion i
    INNER JOIN 
        CeleExamen ce ON ce.IdInscripcion = i.IdInscripcion
    INNER JOIN 
    	grupo gr ON gr.IdGrupo = i.IdGrupo
    INNER JOIN 
    	celebrante cl ON cl.CiCel = i.CiCel
    INNER JOIN 
    	persona pr ON pr.CiPersona = cl.CiCel
    WHERE 
        i.IdInscripcion = p_IdInscripcion;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarAsistencia`(IN `p_CodAsis` INT, IN `p_TipoAsis` VARCHAR(15))
BEGIN
    UPDATE asistenciacel 
    SET TipoAsis = p_TipoAsis 
    WHERE CodAsis = p_CodAsis;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarAsistenciaApoderado`(IN `p_IdInscripcion` INT, IN `p_IdReunion` INT, IN `p_DetalleAsis` VARCHAR(15), IN `p_MontoAsis` INT)
BEGIN
    UPDATE asistenciaapoderado
    SET 
        DetalleAsis = p_DetalleAsis,
        MontoAsis = p_MontoAsis
    WHERE 
        IdInscripcion = p_IdInscripcion
        AND IdReunion = p_IdReunion;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarCatequista`(IN `p_CiCat` VARCHAR(15), IN `p_Sacramento` VARCHAR(20), IN `p_EstadoCat` VARCHAR(10), IN `p_ImagenCat` VARCHAR(25), IN `p_FondoCat` VARCHAR(25), IN `p_PoderCat` VARCHAR(15), IN `p_FraseCat` VARCHAR(250), IN `p_IdGrupo` INT, IN `p_FechaIniCat` DATE, IN `p_FechaFinCat` DATE, IN `p_RolCat` VARCHAR(20))
BEGIN
    
    UPDATE catequista 
    SET Sacramento = p_Sacramento, EstadoCat = p_EstadoCat, ImagenCat = p_ImagenCat, FondoCat = p_FondoCat, 
        PoderCat = p_PoderCat, FraseCat = p_FraseCat
    WHERE CiCat = p_CiCat;

    
    UPDATE asignacion 
    SET IdGrupo = p_IdGrupo, FechaIniCat = p_FechaIniCat, FechaFinCat = p_FechaFinCat, RolCat = p_RolCat
    WHERE CiCat = p_CiCat;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarCelebrante`(IN `p_CiCel` VARCHAR(15), IN `p_Sacramento` VARCHAR(20), IN `p_EstadoCel` VARCHAR(10), IN `p_PerfilCel` VARCHAR(25), IN `p_FondoCel` VARCHAR(25), IN `p_IdGrupo` INT, IN `p_PreValor` VARCHAR(3), IN `p_CodTipoItem` INT)
BEGIN
    DECLARE v_IdInscripcion INT;

    UPDATE celebrante 
    SET Sacramento = p_Sacramento, 
        EstadoCel = p_EstadoCel, 
        PerfilCel = p_PerfilCel, 
        FondoCel = p_FondoCel
    WHERE CiCel = p_CiCel;

    UPDATE inscripcion
    SET IdGrupo = p_IdGrupo, 
        PreValor = p_PreValor
    WHERE CiCel = p_CiCel;

    SELECT IdInscripcion 
    INTO v_IdInscripcion
    FROM inscripcion 
    WHERE CiCel = p_CiCel;

    UPDATE Detalletipoins 
    SET CodTipoItem = p_CodTipoItem
    WHERE IdInscripcion = v_IdInscripcion;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarPersonalPorCi`(IN `ci_Personal` VARCHAR(15), IN `nuevaClave` VARCHAR(30), IN `nuevoNombre` VARCHAR(25), IN `nuevoPaterno` VARCHAR(20), IN `nuevoMaterno` VARCHAR(20), IN `nuevoSexo` VARCHAR(6), IN `nuevoContactoPer` VARCHAR(15), IN `nuevoPerfil` VARCHAR(25), IN `nuevoFondo` VARCHAR(25), IN `nuevaFrase` VARCHAR(50), IN `nuevoEstado` VARCHAR(50), IN `nuevoCodCar` INT)
BEGIN
    UPDATE personal 
    SET 
		ClavePer = nuevaClave,
		Nombre = nuevoNombre,
		Paterno = nuevoPaterno,
		Materno = nuevoMaterno,
		Sexo = nuevoSexo,
		ContactoPer = nuevoContactoPer,
		PerfilPer = nuevoPerfil,
		FondoPer = nuevoFondo,
		FrasePer = nuevaFrase,
		Estado = nuevoEstado,
		CodCar = nuevoCodCar
    WHERE CiPersonal = ci_Personal;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarReservaBautizo`(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarReservaMatrimonio`(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ModificarReservaMisa`(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerActividadesPorTipo`(IN `p_TipoActividad` VARCHAR(30))
BEGIN
    SELECT * FROM actividades 
    WHERE TipoActividad = p_TipoActividad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerAsistenciaPorFecha`(IN `p_fecha` DATE)
BEGIN
    SELECT ac.CodAsis, i.IdInscripcion, c.CiCel, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, ti.DescripItem, ac.TipoAsis
    FROM asistenciacel ac
    INNER JOIN inscripcion i ON ac.IdInscripcion = i.IdInscripcion
    INNER JOIN celebrante c ON i.CiCel = c.CiCel
    INNER JOIN persona p ON c.CiCel = p.CiPersona
    LEFT JOIN detalleTipoins dt ON i.IdInscripcion = dt.IdInscripcion
    LEFT JOIN TipoIns ti ON dt.CodTipoItem = ti.CodTipoItem
    WHERE ac.FechaAsisConf = p_fecha;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerAsistenciaPorFechaYGrupo`(IN `fecha` DATE, IN `idGrupo` INT)
BEGIN
    SELECT 
    reu.*,
        i.IdInscripcion,
        c.CiCel,
        CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto,
        ti.DescripItem AS DescripItem,
        COALESCE(a.MontoAsis, 0) AS MontoAsis,
        COALESCE(a.DetalleAsis, 'Ninguno') AS DetalleAsis
    FROM 
        inscripcion i
    INNER JOIN 
        celebrante c ON i.CiCel = c.CiCel
    INNER JOIN 
        persona p ON c.CiCel = p.CiPersona
    INNER JOIN 
        detalleTipoins di ON i.IdInscripcion = di.IdInscripcion
    INNER JOIN 
        TipoIns ti ON di.CodTipoItem = ti.CodTipoItem
    INNER JOIN 
        asistenciaapoderado a ON i.IdInscripcion = a.IdInscripcion 
	INNER JOIN 
    	reunioncel reu ON a.IdReunion = reu.IdReunion
    WHERE 
        i.IdGrupo = idGrupo AND reu.FechaReu = fecha ;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerCelebrantes`(IN `idGrupo` INT)
BEGIN
    SELECT 
        i.*, 
        c.CiCel, 
        CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
        t.DescripItem
    FROM 
        inscripcion i
    JOIN 
        celebrante c ON i.CiCel = c.CiCel
    JOIN 
        persona p ON c.CiCel = p.CiPersona
    JOIN 
        detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
    JOIN 
        TipoIns t ON dti.CodTipoItem = t.CodTipoItem
    WHERE 
        i.IdGrupo = idGrupo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerDocumentos`(IN `p_IdInscripcion` INT)
BEGIN
    SELECT 
        d.NumeroDoc, 
        d.DetalleDoc, 
        d.LibroDoc, 
        d.PaginaDoc, 
        d.PartidaDoc, 
        d.ParroquiaDoc, 
        d.FechaPres
    FROM Documento d
    WHERE d.IdInscripcion = p_IdInscripcion;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerDocumentosCelebrante`(IN `ciPersona` VARCHAR(15))
BEGIN
    SELECT f.CiFamiliar, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
                       insf.RolFam, insf.Parentesco, p.Contacto, doc.*
                FROM insfamiliar insf 
                INNER JOIN familiar f ON insf.CiFamiliar = f.CiFamiliar 
                INNER JOIN persona p ON f.CiFamiliar = p.CiPersona 
                INNER JOIN inscripcion insac ON insac.IdInscripcion = insf.IdInscripcion
                INNER JOIN documento doc ON doc.IdInscripcion = insac.IdInscripcion
                WHERE insac.CiCel =  ciPersona;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerIdReunionPorFechaYGrupo`(IN `p_fecha` DATE)
BEGIN
    SELECT IdReunion
    FROM ReunionCel
    WHERE FechaReu = p_fecha;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `PruebaAsisPorGrupo`(IN `p_IdGrupo` INT)
BEGIN
    SELECT 
        c.CiCel AS "C├®dula del Celebrante",
        CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS "Nombre Completo",
        GROUP_CONCAT(CONCAT(a.FechaAsisConf, ' (', a.TipoAsis, ')') ORDER BY a.FechaAsisConf SEPARATOR '|') AS "Asistencias"
    FROM 
        celebrante c
    INNER JOIN 
        persona p ON c.CiCel = p.CiPersona
    INNER JOIN 
        inscripcion i ON i.CiCel = c.CiCel
    INNER JOIN 
        grupo g ON g.IdGrupo = i.IdGrupo
    LEFT JOIN 
        asistenciacel a ON i.IdInscripcion = a.IdInscripcion
    WHERE 
        g.IdGrupo = p_IdGrupo
    GROUP BY 
        c.CiCel, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno);
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarAsistencia`(IN `p_IdInscripcion` INT, IN `p_FechaAsisConf` DATE, IN `p_HoraAsisConf` TIME, IN `p_DetalleAsis` VARCHAR(50), IN `p_TipoAsis` VARCHAR(15))
BEGIN
    INSERT INTO asistenciacel (IdInscripcion, FechaAsisConf, HoraAsisConf, DetalleAsis, TipoAsis)
    VALUES (p_IdInscripcion, p_FechaAsisConf, p_HoraAsisConf, p_DetalleAsis, p_TipoAsis);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarAsistenciaApoderado`(IN `p_IdInscripcion` INT, IN `p_IdReunion` INT, IN `p_DetalleAsis` VARCHAR(15), IN `p_MontoAsis` INT)
BEGIN
    INSERT INTO asistenciaapoderado (IdInscripcion, IdReunion, DetalleAsis, MontoAsis)
    VALUES (p_IdInscripcion, p_IdReunion, p_DetalleAsis, p_MontoAsis)
    ON DUPLICATE KEY UPDATE 
        DetalleAsis = p_DetalleAsis,
        MontoAsis = p_MontoAsis;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarCatequista`(IN `p_CiCat` VARCHAR(15), IN `p_Sacramento` VARCHAR(20), IN `p_UsuarioCat` VARCHAR(20), IN `p_ClaveCat` VARCHAR(30), IN `p_EstadoCat` VARCHAR(10), IN `p_ImagenCat` VARCHAR(25), IN `p_FondoCat` VARCHAR(25), IN `p_PoderCat` VARCHAR(15), IN `p_FraseCat` VARCHAR(250), IN `p_Gestion` DATE, IN `p_IdGrupo` INT, IN `p_FechaAsigCat` DATE, IN `p_FechaIniCat` DATE, IN `p_FechaFinCat` DATE, IN `p_RolCat` VARCHAR(20))
BEGIN
    
    INSERT INTO catequista (
        CiCat, Sacramento, UsuarioCat, ClaveCat, EstadoCat,
        ImagenCat, FondoCat, PoderCat, FraseCat, FechaRegCat
    )
    VALUES (
        p_CiCat, p_Sacramento, p_UsuarioCat, p_ClaveCat, p_EstadoCat,
        p_ImagenCat, p_FondoCat, p_PoderCat, p_FraseCat, NOW()  
    );

    
    INSERT INTO asignacion (
        Gestion, CiCat, IdGrupo, FechaAsigCat, FechaIniCat,
        FechaFinCat, RolCat
    )
    VALUES (
        p_Gestion, p_CiCat, p_IdGrupo, p_FechaAsigCat,
        p_FechaIniCat, p_FechaFinCat, p_RolCat
    );
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarCelebrante`(IN `p_CiCel` VARCHAR(15), IN `p_Sacramento` VARCHAR(20), IN `p_UsuarioCel` VARCHAR(10), IN `p_ClaveCel` VARCHAR(10), IN `p_IdGrupo` INT, IN `p_PreValor` VARCHAR(3), IN `p_CodTipoItem` INT)
BEGIN
    DECLARE v_IdInscripcion INT;

    START TRANSACTION;

    INSERT INTO celebrante (CiCel, Sacramento, UsuarioCel, ClaveCel, PerfilCel, FondoCel, EstadoCel)
    VALUES (p_CiCel, p_Sacramento, p_UsuarioCel, p_ClaveCel, 'Ninguno', 'Ninguno', 'Activo');

    INSERT INTO inscripcion (CiCel, IdGrupo, PreValor)
    VALUES (p_CiCel, p_IdGrupo, p_PreValor);

    SET v_IdInscripcion = LAST_INSERT_ID();

    INSERT INTO Detalletipoins (CodTipoItem, IdInscripcion)
    VALUES (p_CodTipoItem, v_IdInscripcion);

    INSERT INTO CeleExamen (IdInscripcion)
    VALUES (v_IdInscripcion);

    COMMIT;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarCertificado`(IN `p_CodIns` INT, IN `p_NomParroquia` VARCHAR(50), IN `p_LugParroquia` VARCHAR(50), IN `p_NroLibro` VARCHAR(5), IN `p_NroPag` VARCHAR(5), IN `p_NroPart` VARCHAR(5), IN `p_LugarNac` VARCHAR(50), IN `p_FechaReal` DATE, IN `p_HoraReal` TIME, IN `p_NroEmision` INT, IN `p_FechaEmision` DATE, IN `p_PresbiteroEm` VARCHAR(50), IN `p_Observacion` VARCHAR(80), IN `p_CelebrantePres` VARCHAR(50), IN `p_EstadoCert` VARCHAR(15))
BEGIN
    DECLARE v_CodParr INT;
    DECLARE v_CodDetCert INT;

    INSERT INTO parroquia (NomParroquia, LugParroquia)
    VALUES (p_NomParroquia, p_LugParroquia);

    SET v_CodParr = LAST_INSERT_ID();

    INSERT INTO DetalleCert (NroLibro, NroPag, NroPart, LugarNac, FechaReal, HoraReal, CodParr)
    VALUES (p_NroLibro, p_NroPag, p_NroPart, p_LugarNac, p_FechaReal, p_HoraReal, v_CodParr);

    SET v_CodDetCert = LAST_INSERT_ID();

    INSERT INTO certificado (NroEmision, FechaEmision, PresbiteroEm, Observacion, CelebrantePres, EstadoCert, CodDetCert, CodIns)
    VALUES (p_NroEmision, p_FechaEmision, p_PresbiteroEm, p_Observacion, p_CelebrantePres, p_EstadoCert, v_CodDetCert, p_CodIns);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarDocumentosPresentados`(IN `p_FechaPres` DATE, IN `p_CodificDoc` VARCHAR(20), IN `p_CodTDoc` INT, IN `p_CodIns` INT)
BEGIN
  INSERT INTO presentaciondoc (FechaPres, CodificDoc, CodTDoc, CodIns)
  VALUES (p_FechaPres, p_CodificDoc, p_CodTDoc, p_CodIns);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarInsSacramentoBautizo`(
    IN p_CiPersonaCelebrante VARCHAR(15),
    IN p_CodPer INT,
    IN p_CiPapa VARCHAR(15),
    IN p_CiMama VARCHAR(15),
    IN p_CiPadrino VARCHAR(15),
    IN p_CiMadrina VARCHAR(15)
)
BEGIN
    DECLARE v_CodIns INT;

    INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
    VALUES (CURRENT_DATE, p_CodPer, 1);

    SET v_CodIns = LAST_INSERT_ID();

    INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPersonaCelebrante, v_CodIns, 'Celebrante');

    IF p_CiPapa IS NOT NULL AND p_CiPapa <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPapa, v_CodIns, 0x506170C3A1);
    END IF;

    IF p_CiMama IS NOT NULL AND p_CiMama <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMama, v_CodIns, 0x4D616DC3A1);
    END IF;

    IF p_CiPadrino IS NOT NULL AND p_CiPadrino <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPadrino, v_CodIns, 'Padrino');
    END IF;

    IF p_CiMadrina IS NOT NULL AND p_CiMadrina <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMadrina, v_CodIns, 'Madrina');
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarInsSacramentoConfirmacion`(IN `p_CiPersonaConfirmante` VARCHAR(15), IN `p_CodPer` INT, IN `p_CiPadrino` VARCHAR(15), IN `p_CiMadrina` VARCHAR(15))
BEGIN
    DECLARE v_CodIns INT;

    INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
    VALUES (CURRENT_DATE, p_CodPer, 3);

    SET v_CodIns = LAST_INSERT_ID();

    INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPersonaConfirmante, v_CodIns, 'Confirmante');

    IF p_CiPadrino IS NOT NULL AND p_CiPadrino <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPadrino, v_CodIns, 'Padrino');
    END IF;

    IF p_CiMadrina IS NOT NULL AND p_CiMadrina <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMadrina, v_CodIns, 'Madrina');
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarInsSacramentoMatrimonio`(
    IN p_CiPersonaNovio VARCHAR(15),
    IN p_CiPersonaNovia VARCHAR(15),
    IN p_CodPer INT,
    IN p_CiPadrino VARCHAR(15),
    IN p_CiMadrina VARCHAR(15),
    IN p_CiTestigoNovio VARCHAR(15),
    IN p_CiTestigoNovia VARCHAR(15),
    IN p_CiPaNovio VARCHAR(15),
    IN p_CiMaNovio VARCHAR(15),
    IN p_CiPaNovia VARCHAR(15),
    IN p_CiMaNovia VARCHAR(15)
)
BEGIN
    DECLARE v_CodIns INT;
        SET @CodSac = 4;
        INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
        VALUES (CURRENT_DATE, p_CodPer, @CodSac);
        
        SET v_CodIns = LAST_INSERT_ID();
        
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES 
            (p_CiPersonaNovio, v_CodIns, 'Novio'),
            (p_CiPersonaNovia, v_CodIns, 'Novia');

        IF p_CiPaNovio IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPaNovio, v_CodIns, 0x506170C3A1204E6F76696F);
        END IF;

        IF p_CiMaNovio IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMaNovio, v_CodIns, 0x4D616DC3A1204E6F76696F);
        END IF;

        IF p_CiPaNovia IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPaNovia, v_CodIns, 0x506170C3A1204E6F766961);
        END IF;

        IF p_CiMaNovia IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMaNovia, v_CodIns, 0x4D616DC3A1204E6F766961);
        END IF;

        IF p_CiPadrino IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPadrino, v_CodIns, 'Padrino');
        END IF;

        IF p_CiMadrina IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMadrina, v_CodIns, 'Madrina');
        END IF;
        
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiTestigoNovio, v_CodIns, 'Testigo Novio');
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiTestigoNovia, v_CodIns, 'Testigo Novia');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarNuevoPersonal`(IN `p_Nombre` VARCHAR(100), IN `p_Paterno` VARCHAR(100), IN `p_Materno` VARCHAR(100), IN `p_CiPersonal` VARCHAR(15), IN `p_Sexo` VARCHAR(6), IN `p_ContactoPer` VARCHAR(20), IN `p_CodCar` INT)
BEGIN
    DECLARE v_FechaRegistro DATETIME;
    SET v_FechaRegistro = CURRENT_TIMESTAMP;

    INSERT INTO Personal (Nombre, Paterno, Materno, CiPersonal, Sexo, ContactoPer, UsuarioPer, ClavePer, PerfilPer, FondoPer, FrasePer, Estado, FechaIng, CodCar)
    
    VALUES (p_Nombre, p_Paterno, p_Materno, p_CiPersonal, p_Sexo, p_ContactoPer, p_CiPersonal, p_ContactoPer, 'Ninguno', 'Ninguno', '-', 'Activo', v_FechaRegistro, p_CodCar);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarTipoSacramento`(IN `p_DescripSac` VARCHAR(20), IN `p_CostoSac` DECIMAL(10,2))
BEGIN
  INSERT INTO tiposacramento (DescripSac, CostoSac)
  VALUES (p_DescripSac, p_CostoSac);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReservarBautizo`(
    IN p_CiPersonaCelebrante VARCHAR(15),
    IN p_CodPer INT,
    IN p_CiPapa VARCHAR(15),
    IN p_CiMama VARCHAR(15),
    IN p_Quien VARCHAR(255),
    IN p_FechaReal DATE,
    IN p_HoraReal TIME,
    IN p_CiPadrino VARCHAR(15),
    IN p_CiMadrina VARCHAR(15),
    IN p_Tipo VARCHAR(20),
    IN p_Realizacion VARCHAR(30)
)
BEGIN
    DECLARE v_CodIns INT;

    INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
    VALUES (CURRENT_DATE, p_CodPer, 1);

    SET v_CodIns = LAST_INSERT_ID();

    INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPersonaCelebrante, v_CodIns, 'Celebrante');

    IF p_CiPapa IS NOT NULL AND p_CiPapa <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPapa, v_CodIns, 0x506170C3A1);
    END IF;

    IF p_CiMama IS NOT NULL AND p_CiMama <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMama, v_CodIns, 0x4D616DC3A1);
    END IF;

    IF p_CiPadrino IS NOT NULL AND p_CiPadrino <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPadrino, v_CodIns, 'Padrino');
    END IF;

    IF p_CiMadrina IS NOT NULL AND p_CiMadrina <> '' THEN
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMadrina, v_CodIns, 'Madrina');
    END IF;

    INSERT INTO Reserva (FechaReal, HoraReal, EstadoRes, CiPersona, TipoCel, CodIns, Quien, Realizacion)
    VALUES (p_FechaReal, p_HoraReal, 'Reservado', p_CiPersonaCelebrante, p_Tipo, v_CodIns, p_Quien, p_Realizacion);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReservarMatrimonio`(
    IN p_ReservanteMat VARCHAR(255),
    IN p_CiPersonaNovio VARCHAR(15),
    IN p_CiPersonaNovia VARCHAR(15),
    IN p_CodPer INT,
    IN p_FechaReal DATE,
    IN p_HoraReal TIME,
    IN p_CiPadrino VARCHAR(15),
    IN p_CiMadrina VARCHAR(15),
    IN p_CiTestigoNovio VARCHAR(15),
    IN p_CiTestigoNovia VARCHAR(15),
    IN p_TipoCel VARCHAR(15),
    IN p_CiPaNovio VARCHAR(15),
    IN p_CiMaNovio VARCHAR(15),
    IN p_CiPaNovia VARCHAR(15),
    IN p_CiMaNovia VARCHAR(15),
    IN p_Realizacion VARCHAR(30)
)
BEGIN
    DECLARE v_CodIns INT;
    IF EXISTS (
        SELECT 1
        FROM Reserva r
        JOIN Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
        JOIN inssacramento i ON s.CodIns = i.CodIns
        WHERE (s.CiPersona = p_CiPersonaNovio OR s.CiPersona = p_CiPersonaNovia)
        AND i.CodSac = 4
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe una reserva de matrimonio para una de las personas.';
    ELSE
        SET @CodSac = 4;
        INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
        VALUES (CURRENT_DATE, p_CodPer, @CodSac);
        
        SET v_CodIns = LAST_INSERT_ID();
        
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES 
            (p_CiPersonaNovio, v_CodIns, 'Novio'),
            (p_CiPersonaNovia, v_CodIns, 'Novia');

        IF p_CiPaNovio IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPaNovio, v_CodIns, 0x506170C3A1204E6F76696F);
        END IF;

        IF p_CiMaNovio IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMaNovio, v_CodIns, 0x4D616DC3A1204E6F76696F);
        END IF;

        IF p_CiPaNovia IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPaNovia, v_CodIns, 0x506170C3A1204E6F766961);
        END IF;

        IF p_CiMaNovia IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMaNovia, v_CodIns, 0x4D616DC3A1204E6F766961);
        END IF;

        IF p_CiPadrino IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiPadrino, v_CodIns, 'Padrino');
        END IF;

        IF p_CiMadrina IS NOT NULL THEN
            INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiMadrina, v_CodIns, 'Madrina');
        END IF;

        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiTestigoNovio, v_CodIns, 'Testigo Novio');
        INSERT INTO Solicitante (CiPersona, CodIns, Rol) VALUES (p_CiTestigoNovia, v_CodIns, 'Testigo Novia');
        
        INSERT INTO Reserva (FechaReal, HoraReal, EstadoRes, CiPersona, TipoCel, CodIns, Quien, Realizacion)
        VALUES 
            (p_FechaReal, p_HoraReal, 'Reservado', p_CiPersonaNovio, p_TipoCel, v_CodIns, p_ReservanteMat, p_Realizacion);
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `ReservarMisa`(IN `p_CiPersona` VARCHAR(15), IN `p_FechaReal` DATE, IN `p_HoraReal` TIME, IN `p_CodPer` INT, IN `p_Quien` VARCHAR(255), IN `p_TipoMisa` VARCHAR(20), IN `p_Realizacion` VARCHAR(30))
BEGIN
    DECLARE v_CodIns INT;
    
    INSERT INTO inssacramento (FechaIns, CodPer, CodSac)
    VALUES (CURRENT_DATE, p_CodPer, 5);
    
    SET v_CodIns = LAST_INSERT_ID();
    
    INSERT INTO Solicitante (CiPersona, CodIns, Rol)
    VALUES (p_CiPersona, v_CodIns, 'Celebrante');
    
    INSERT INTO Reserva (FechaReal, HoraReal, EstadoRes, CiPersona, TipoCel, CodIns, Quien, realizacion)
    VALUES (p_FechaReal, p_HoraReal, 'Reservado', p_CiPersona, p_TipoMisa, v_CodIns, p_Quien, p_Realizacion);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_catequista`(IN `p_username` VARCHAR(20), IN `p_password` VARCHAR(30))
BEGIN
   SELECT 
        c.*,
        a.*,
        g.*,
        p.*
    FROM 
        catequista c
    INNER JOIN
        asignacion a ON c.CiCat = a.CiCat
    INNER JOIN
        grupo g ON g.IdGrupo = a.IdGrupo
    INNER JOIN
        persona p ON c.CiCat = p.CiPersona
    WHERE 
        c.UsuarioCat = p_username
        AND c.ClaveCat = p_password
        AND c.EstadoCat = 'Activo';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_celebrante`(IN `p_username` VARCHAR(10), IN `p_password` VARCHAR(10))
BEGIN
    SELECT 
        c.*,
        g.*,
        p.*,
        t.*,
        i.*
    FROM 
        celebrante c
    INNER JOIN
        persona p ON c.CiCel = p.CiPersona
    INNER JOIN
        inscripcion i ON c.CiCel = i.CiCel
    INNER JOIN
        grupo g ON i.IdGrupo = g.IdGrupo
    INNER JOIN
        detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
    INNER JOIN
        TipoIns t ON dti.CodTipoItem = t.CodTipoItem
    WHERE 
        c.UsuarioCel = p_username
        AND c.ClaveCel = p_password
        AND c.EstadoCel = 'Activo';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_personal`(IN `p_username` VARCHAR(20), IN `p_password` VARCHAR(30))
BEGIN
    SELECT 
        p.*,
        c.*
    FROM 
        personal p
    JOIN 
        cargo c ON p.CodCar = c.CodCar
    WHERE 
        p.UsuarioPer = p_username
        AND p.ClavePer = p_password
        AND p.Estado = 'Activo';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerCertificadoBautizo`(IN p_CodIns INT)
BEGIN
    SELECT 
        ts.DescripSac,
        p.NomParroquia,
        p.LugParroquia,
        dc.*,
        c.*,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.CiPersona ELSE celebrante.CiPersona END AS CiPersona,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.Nombre ELSE celebrante.Nombre END AS Nombre,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.ApPaterno ELSE celebrante.ApPaterno END AS ApPaterno,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.ApMaterno ELSE celebrante.ApMaterno END AS ApMaterno,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.Sexo ELSE celebrante.Sexo END AS Sexo,
        CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.FechaNac ELSE celebrante.FechaNac END AS FechaNac,
        CONCAT(CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.Nombre ELSE celebrante.Nombre END, ' ',
               CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.ApPaterno ELSE celebrante.ApPaterno END, ' ',
               CASE WHEN bautizado.CiPersona IS NOT NULL THEN bautizado.ApMaterno ELSE celebrante.ApMaterno END) AS NombreBautizado,
        CONCAT(celebrante.Nombre, ' ', celebrante.ApPaterno, ' ', celebrante.ApMaterno) AS NombreCelebrante,
        ins.FechaIns,
        CONCAT(padre.Nombre, ' ', padre.ApPaterno, ' ', padre.ApMaterno) AS NombrePadre,
        CONCAT(madre.Nombre, ' ', madre.ApPaterno, ' ', madre.ApMaterno) AS NombreMadre,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
        carg.DescripCar AS cargo,
        CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS Inscriptor
    FROM 
        inssacramento ins
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    INNER JOIN 
        personal per ON ins.CodPer = per.CodPer
    INNER JOIN 
        cargo carg ON per.CodCar = carg.CodCar
    INNER JOIN
        certificado c ON ins.CodIns = c.CodIns
    INNER JOIN 
        detallecert dc ON c.CodDetCert = dc.CodDetCert
    INNER JOIN 
        parroquia p ON dc.CodParr = p.CodParr
    LEFT JOIN 
        Solicitante sBaut ON sBaut.CodIns = ins.CodIns AND sBaut.Rol = 'Bautizado'
    LEFT JOIN 
        persona bautizado ON sBaut.CiPersona = bautizado.CiPersona
    LEFT JOIN 
        Solicitante sCele ON sCele.CodIns = ins.CodIns AND sCele.Rol = 'Celebrante'
    LEFT JOIN 
        persona celebrante ON sCele.CiPersona = celebrante.CiPersona
    LEFT JOIN 
        Solicitante spPadre ON spPadre.CodIns = ins.CodIns AND spPadre.Rol = 0x506170C3A1
    LEFT JOIN 
        persona padre ON spPadre.CiPersona = padre.CiPersona
    LEFT JOIN 
        Solicitante spMadre ON spMadre.CodIns = ins.CodIns AND spMadre.Rol = 0x4D616DC3A1
    LEFT JOIN 
        persona madre ON spMadre.CiPersona = madre.CiPersona
    LEFT JOIN 
        Solicitante spPadrino ON spPadrino.CodIns = ins.CodIns AND spPadrino.Rol = 'Padrino'
    LEFT JOIN 
        persona padrino ON spPadrino.CiPersona = padrino.CiPersona
    LEFT JOIN 
        Solicitante spMadrina ON spMadrina.CodIns = ins.CodIns AND spMadrina.Rol = 'Madrina'
    LEFT JOIN 
        persona madrina ON spMadrina.CiPersona = madrina.CiPersona
    WHERE 
        ins.CodIns = p_CodIns AND ins.CodSac = 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerCertificadoConfirmacion`(IN `p_CodIns` INT)
BEGIN
    SELECT 
    ts.DescripSac,
    p.NomParroquia,
    p.LugParroquia,
    dc.*,
    c.*,
    persona.*,
    CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS Celebrante,
    s.Rol,
    ins.FechaIns,
    CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
    CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
    carg.DescripCar AS cargo,
    CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS Inscriptor
FROM 
    Solicitante s
INNER JOIN 
    persona ON s.CiPersona = persona.CiPersona AND s.Rol = 'Confirmante'
INNER JOIN 
    inssacramento ins ON s.CodIns = ins.CodIns
INNER JOIN 
    personal per ON ins.CodPer = per.CodPer
INNER JOIN 
    cargo carg ON per.CodCar = carg.CodCar
INNER JOIN 
    tiposacramento ts ON ins.CodSac = ts.CodSac
LEFT JOIN 
    Solicitante spPadrino ON spPadrino.CodIns = s.CodIns AND spPadrino.Rol = 'Padrino'
LEFT JOIN 
    persona padrino ON spPadrino.CiPersona = padrino.CiPersona
LEFT JOIN 
    Solicitante spMadrina ON spMadrina.CodIns = s.CodIns AND spMadrina.Rol = 'Madrina'
LEFT JOIN 
    persona madrina ON spMadrina.CiPersona = madrina.CiPersona
INNER JOIN
    certificado c ON ins.CodIns = c.CodIns
INNER JOIN 
    detallecert dc ON c.CodDetCert = dc.CodDetCert
INNER JOIN 
    parroquia p ON dc.CodParr = p.CodParr
WHERE 
    ins.CodIns = p_CodIns AND ins.CodSac = 3;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerCertificadoExtB`(IN `p_CiPersona` VARCHAR(12), IN `p_quien` VARCHAR(20))
BEGIN

SELECT pb.*, pre.* FROM Persona p 
    INNER JOIN solicitante so ON so.CiPersona = p.CiPersona 
    INNER JOIN inssacramento ins ON ins.CodIns = so.CodIns 
    INNER JOIN presentaciondoc pre ON pre.CodIns = ins.CodIns 
    INNER JOIN tipodocumento td ON td.CodTDoc = pre.CodTDoc 
    INNER JOIN docpresbau pb ON pb.CodPres = pre.CodPres 
WHERE p.CiPersona = p_CiPersona AND pre.dequien = p_quien AND td.CodTDoc = 3;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerCertificadoExtN`(IN `p_CiPersona` VARCHAR(12))
BEGIN

SELECT pb.* FROM Persona p 
    INNER JOIN solicitante so ON so.CiPersona = p.CiPersona 
    INNER JOIN inssacramento ins ON ins.CodIns = so.CodIns 
    INNER JOIN presentaciondoc pre ON pre.CodIns = ins.CodIns 
    INNER JOIN tipodocumento td ON td.CodTDoc = pre.CodTDoc 
    INNER JOIN docpresbau pb ON pb.CodPres = pre.CodPres 
WHERE p.CiPersona = p_CiPersona AND td.CodTDoc = 2;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerCertificadoMatrimonio`(IN p_CodIns INT)
BEGIN
    SELECT
        p.NomParroquia,
        p.LugParroquia,
        dc.*,
        c.*,
        novio.FechaNac AS fechaNovio,
        novia.FechaNac AS fechaNovia,
        novio.CiPersona AS CiNovio,
        novia.CiPersona AS CiNovia,
        CONCAT(novio.Nombre, ' ', novio.ApPaterno, ' ', novio.ApMaterno) AS NombreNovio,
        CONCAT(novia.Nombre, ' ', novia.ApPaterno, ' ', novia.ApMaterno) AS NombreNovia,
        CONCAT(padreNovio.Nombre, ' ', padreNovio.ApPaterno, ' ', padreNovio.ApMaterno) AS NombrePadreNovio,
        CONCAT(madreNovio.Nombre, ' ', madreNovio.ApPaterno, ' ', madreNovio.ApMaterno) AS NombreMadreNovio,
        CONCAT(padreNovia.Nombre, ' ', padreNovia.ApPaterno, ' ', padreNovia.ApMaterno) AS NombrePadreNovia,
        CONCAT(madreNovia.Nombre, ' ', madreNovia.ApPaterno, ' ', madreNovia.ApMaterno) AS NombreMadreNovia,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
        CONCAT(testigoNovio.Nombre, ' ', testigoNovio.ApPaterno, ' ', testigoNovio.ApMaterno) AS NombreTestigoNovio,
        CONCAT(testigoNovia.Nombre, ' ', testigoNovia.ApPaterno, ' ', testigoNovia.ApMaterno) AS NombreTestigoNovia,
        ts.DescripSac,
        ins.FechaIns, ins.CodIns
    FROM
        certificado c
    INNER JOIN
        DetalleCert dc ON c.CodDetCert = dc.CodDetCert
    INNER JOIN
        parroquia p ON dc.CodParr = p.CodParr
    INNER JOIN
        inssacramento ins ON c.CodIns = ins.CodIns
    LEFT JOIN
        Solicitante sn ON ins.CodIns = sn.CodIns AND sn.Rol = 'Novio'
    LEFT JOIN
        persona novio ON sn.CiPersona = novio.CiPersona
    LEFT JOIN
        Solicitante sv ON ins.CodIns = sv.CodIns AND sv.Rol = 'Novia'
    LEFT JOIN
        persona novia ON sv.CiPersona = novia.CiPersona
    LEFT JOIN
        Solicitante spPadreNovio ON spPadreNovio.CodIns = ins.CodIns AND spPadreNovio.Rol = 0x506170C3A1204E6F76696F
    LEFT JOIN
        persona padreNovio ON spPadreNovio.CiPersona = padreNovio.CiPersona
    LEFT JOIN
        Solicitante spMadreNovio ON spMadreNovio.CodIns = ins.CodIns AND spMadreNovio.Rol = 0x4D616DC3A1204E6F76696F
    LEFT JOIN
        persona madreNovio ON spMadreNovio.CiPersona = madreNovio.CiPersona
    LEFT JOIN
        Solicitante spPadreNovia ON spPadreNovia.CodIns = ins.CodIns AND spPadreNovia.Rol = 0x506170C3A1204E6F766961
    LEFT JOIN
        persona padreNovia ON spPadreNovia.CiPersona = padreNovia.CiPersona
    LEFT JOIN
        Solicitante spMadreNovia ON spMadreNovia.CodIns = ins.CodIns AND spMadreNovia.Rol = 0x4D616DC3A1204E6F766961
    LEFT JOIN
        persona madreNovia ON spMadreNovia.CiPersona = madreNovia.CiPersona
    LEFT JOIN
        Solicitante spPadrino ON spPadrino.CodIns = ins.CodIns AND spPadrino.Rol = 'Padrino'
    LEFT JOIN
        persona padrino ON spPadrino.CiPersona = padrino.CiPersona
    LEFT JOIN
        Solicitante spMadrina ON spMadrina.CodIns = ins.CodIns AND spMadrina.Rol = 'Madrina'
    LEFT JOIN
        persona madrina ON spMadrina.CiPersona = madrina.CiPersona
    LEFT JOIN
        Solicitante spTestigoNovio ON spTestigoNovio.CodIns = ins.CodIns AND spTestigoNovio.Rol = 'Testigo Novio'
    LEFT JOIN
        persona testigoNovio ON spTestigoNovio.CiPersona = testigoNovio.CiPersona
    LEFT JOIN
        Solicitante spTestigoNovia ON spTestigoNovia.CodIns = ins.CodIns AND spTestigoNovia.Rol = 'Testigo Novia'
    LEFT JOIN
        persona testigoNovia ON spTestigoNovia.CiPersona = testigoNovia.CiPersona
    LEFT JOIN
        tiposacramento ts ON ins.CodSac = ts.CodSac
    WHERE
        ins.CodIns = p_CodIns AND ins.CodSac = 4
    LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerDocumentosPresentados`(IN `p_CiPersona` VARCHAR(15))
BEGIN
    SELECT
        pd.FechaPres,
        pd.Descripcion,
        td.DescripTDoc AS TipoDocumento
    FROM
        presentaciondoc pd
    INNER JOIN
        tipodocumento td ON pd.CodTDoc = td.CodTDoc
    INNER JOIN
        inssacramento i ON pd.CodIns = i.CodIns
    INNER JOIN
        personal p ON i.CodPer = p.CodPer
    WHERE
        p.CiPersonal = p_CiPersona;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarEstadoCatequista`(IN `p_Usuario` VARCHAR(15))
BEGIN
    SELECT 
        c.*,
        a.*,
        g.*,
        p.*
    FROM 
        catequista c
    INNER JOIN
        asignacion a ON c.CiCat = a.CiCat
    INNER JOIN
        grupo g ON g.IdGrupo = a.IdGrupo
    INNER JOIN
        persona p ON c.CiCat = p.CiPersona
    WHERE p.CiPersona = p_Usuario;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarEstadoCelebrante`(IN `p_Celebrante` VARCHAR(15))
BEGIN
    SELECT 
        c.*,
        p.*,
        g.NombreGrupo,
        g.Imagen_Grupo,
        g.Color_Grupo,
        g.Santo_Grupo,
        g.Frase_Santo,
        t.DescripItem,
        t.Valor,
        i.PreValor,
        insf.RolFam,
        insf.Parentesco
    FROM 
        celebrante c
    INNER JOIN
        persona p ON c.CiCel = p.CiPersona
    INNER JOIN
        inscripcion i ON c.CiCel = i.CiCel
    INNER JOIN
        grupo g ON i.IdGrupo = g.IdGrupo
    INNER JOIN
        detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
    INNER JOIN
        TipoIns t ON dti.CodTipoItem = t.CodTipoItem
    INNER JOIN
        insfamiliar insf ON i.IdInscripcion = insf.IdInscripcion
    WHERE 
        c.CiCel = p_Celebrante;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarEstadoCoordinador`(IN `p_Usuario` VARCHAR(15))
BEGIN
    SELECT 
        c.*,
        a.*,
        g.*,
        p.*
    FROM 
        catequista c
    INNER JOIN
        asignacion a ON c.CiCat = a.CiCat
    INNER JOIN
        grupo g ON g.IdGrupo = a.IdGrupo
    INNER JOIN
        persona p ON c.CiCat = p.CiPersona
    WHERE p.CiPersona = p_Usuario;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarEstadoPersonal`(IN `p_Usuario` VARCHAR(15))
BEGIN
    SELECT p.*, c.DescripCar
    FROM personal p
    INNER JOIN Cargo c ON c.CodCar = p.CodCar
    WHERE p.CiPersonal = p_Usuario;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerInsSacramentoBautizo`(IN ciPersona VARCHAR(15))
BEGIN
    SELECT 
        CONCAT(bautizado.Nombre, ' ', bautizado.ApPaterno, ' ', bautizado.ApMaterno) AS NombreBautizado,
        s.Rol,
        ins.CodIns,
        ins.CodSac,
        ins.CodPer,
        ins.FechaIns,
        ts.DescripSac,
        CONCAT(padre.Nombre, ' ', padre.ApPaterno, ' ', padre.ApMaterno) AS NombrePadre,
        CONCAT(madre.Nombre, ' ', madre.ApPaterno, ' ', madre.ApMaterno) AS NombreMadre,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
        carg.DescripCar AS cargo,
        CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS Inscriptor
    FROM 
        Solicitante s
    INNER JOIN 
        persona bautizado ON s.CiPersona = bautizado.CiPersona
    INNER JOIN 
        inssacramento ins ON s.CodIns = ins.CodIns
    INNER JOIN 
        personal per ON ins.CodPer = per.CodPer
    INNER JOIN 
        cargo carg ON per.CodCar = carg.CodCar
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    LEFT JOIN 
        Solicitante spPadre ON spPadre.CodIns = s.CodIns AND spPadre.Rol = 0x506170C3A1
    LEFT JOIN 
        persona padre ON spPadre.CiPersona = padre.CiPersona
    LEFT JOIN 
        Solicitante spMadre ON spMadre.CodIns = s.CodIns AND spMadre.Rol = 0x4D616DC3A1
    LEFT JOIN 
        persona madre ON spMadre.CiPersona = madre.CiPersona  
    LEFT JOIN 
        Solicitante spPadrino ON spPadrino.CodIns = s.CodIns AND spPadrino.Rol = 'Padrino'
    LEFT JOIN 
        persona padrino ON spPadrino.CiPersona = padrino.CiPersona
    LEFT JOIN 
        Solicitante spMadrina ON spMadrina.CodIns = s.CodIns AND spMadrina.Rol = 'Madrina'
    LEFT JOIN 
        persona madrina ON spMadrina.CiPersona = madrina.CiPersona
    WHERE 
        bautizado.CiPersona = ciPersona 
        AND s.Rol IN ('Bautizado', 'Celebrante')
        AND ts.CodSac = 1
    GROUP BY ins.CodIns;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerInsSacramentoConfirmacion`(IN `ciPersona` VARCHAR(15))
BEGIN
SELECT 
    CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS Confirmante,
    s.Rol,
    ins.*,
    ts.DescripSac,
    CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
    CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
    carg.DescripCar AS cargo,
    CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS Inscriptor
FROM 
    Solicitante s
INNER JOIN 
    persona p ON s.CiPersona = p.CiPersona AND s.Rol = 'Confirmante'
INNER JOIN 
    inssacramento ins ON s.CodIns = ins.CodIns
INNER JOIN 
    personal per ON ins.CodPer = per.CodPer
INNER JOIN 
    cargo carg ON per.CodCar = carg.CodCar
INNER JOIN 
    tiposacramento ts ON ins.CodSac = ts.CodSac 
LEFT JOIN 
    Solicitante spPadrino ON spPadrino.CodIns = s.CodIns AND spPadrino.Rol = 'Padrino'
LEFT JOIN 
    persona padrino ON spPadrino.CiPersona = padrino.CiPersona
LEFT JOIN 
    Solicitante spMadrina ON spMadrina.CodIns = s.CodIns AND spMadrina.Rol = 'Madrina'
LEFT JOIN 
    persona madrina ON spMadrina.CiPersona = madrina.CiPersona
WHERE 
    s.CiPersona = ciPersona AND ts.CodSac = 3;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerInsSacramentoMatrimonio`(IN p_CiPersona VARCHAR(15))
BEGIN
    SELECT 
        ts.DescripSac,
        CONCAT(pers.Nombre, ' ', pers.Paterno, ' ', pers.Materno) AS Inscriptor,
        carg.DescripCar AS cargo,
        CONCAT(novio.Nombre, ' ', novio.ApPaterno, ' ', novio.ApMaterno) AS NombreNovio,
        CONCAT(novia.Nombre, ' ', novia.ApPaterno, ' ', novia.ApMaterno) AS NombreNovia,
        CONCAT(padreNovio.Nombre, ' ', padreNovio.ApPaterno, ' ', padreNovio.ApMaterno) AS NombrePadreNovio,
        CONCAT(madreNovio.Nombre, ' ', madreNovio.ApPaterno, ' ', madreNovio.ApMaterno) AS NombreMadreNovio,
        CONCAT(padreNovia.Nombre, ' ', padreNovia.ApPaterno, ' ', padreNovia.ApMaterno) AS NombrePadreNovia,
        CONCAT(madreNovia.Nombre, ' ', madreNovia.ApPaterno, ' ', madreNovia.ApMaterno) AS NombreMadreNovia,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
        CONCAT(testigoNovio.Nombre, ' ', testigoNovio.ApPaterno, ' ', testigoNovio.ApMaterno) AS NombreTestigoNovio,
        CONCAT(testigoNovia.Nombre, ' ', testigoNovia.ApPaterno, ' ', testigoNovia.ApMaterno) AS NombreTestigoNovia,
        ins.FechaIns,
        ins.CodIns
    FROM 
        Solicitante s
    INNER JOIN inssacramento ins ON s.CodIns = ins.CodIns
    INNER JOIN personal pers ON ins.CodPer = pers.CodPer
    INNER JOIN cargo carg ON pers.CodCar = carg.CodCar
    LEFT JOIN tiposacramento ts ON ins.CodSac = ts.CodSac
    LEFT JOIN Solicitante sn ON s.CodIns = sn.CodIns AND sn.Rol = 'Novio'
    LEFT JOIN persona novio ON sn.CiPersona = novio.CiPersona
    LEFT JOIN Solicitante sv ON s.CodIns = sv.CodIns AND sv.Rol = 'Novia'
    LEFT JOIN persona novia ON sv.CiPersona = novia.CiPersona
    LEFT JOIN Solicitante spPadreNovio ON spPadreNovio.CodIns = s.CodIns AND spPadreNovio.Rol = 0x506170C3A1204E6F76696F
    LEFT JOIN persona padreNovio ON spPadreNovio.CiPersona = padreNovio.CiPersona
    LEFT JOIN Solicitante spMadreNovio ON spMadreNovio.CodIns = s.CodIns AND spMadreNovio.Rol = 0x4D616DC3A1204E6F76696F
    LEFT JOIN persona madreNovio ON spMadreNovio.CiPersona = madreNovio.CiPersona
    LEFT JOIN Solicitante spPadreNovia ON spPadreNovia.CodIns = s.CodIns AND spPadreNovia.Rol = 0x506170C3A1204E6F766961
    LEFT JOIN persona padreNovia ON spPadreNovia.CiPersona = padreNovia.CiPersona
    LEFT JOIN Solicitante spMadreNovia ON spMadreNovia.CodIns = s.CodIns AND spMadreNovia.Rol = 0x4D616DC3A1204E6F766961
    LEFT JOIN persona madreNovia ON spMadreNovia.CiPersona = madreNovia.CiPersona
    LEFT JOIN Solicitante spPadrino ON spPadrino.CodIns = s.CodIns AND spPadrino.Rol = 'Padrino'
    LEFT JOIN persona padrino ON spPadrino.CiPersona = padrino.CiPersona
    LEFT JOIN Solicitante spMadrina ON spMadrina.CodIns = s.CodIns AND spMadrina.Rol = 'Madrina'
    LEFT JOIN persona madrina ON spMadrina.CiPersona = madrina.CiPersona
    LEFT JOIN Solicitante spTestigoNovio ON spTestigoNovio.CodIns = s.CodIns AND spTestigoNovio.Rol = 'Testigo Novio'
    LEFT JOIN persona testigoNovio ON spTestigoNovio.CiPersona = testigoNovio.CiPersona
    LEFT JOIN Solicitante spTestigoNovia ON spTestigoNovia.CodIns = s.CodIns AND spTestigoNovia.Rol = 'Testigo Novia'
    LEFT JOIN persona testigoNovia ON spTestigoNovia.CiPersona = testigoNovia.CiPersona
    WHERE 
        novio.CiPersona = p_CiPersona OR novia.CiPersona = p_CiPersona AND ins.CodSac = 4 LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerReservasBautizo`()
BEGIN
    SELECT
    p.CiPersona,
    CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombresApellidos,
    t.DescripSac,
    r.Quien,
    r.FechaReal AS FechaCelebracion,
    r.HoraReal AS HoraCelebracion
FROM
    Reserva r
INNER JOIN
    Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
INNER JOIN
    inssacramento i ON s.CodIns = i.CodIns
INNER JOIN
    tiposacramento t ON t.CodSac = i.CodSac
INNER JOIN
    persona p ON s.CiPersona = p.CiPersona
WHERE
 r.EstadoRes='Reservado' AND i.CodSac = 1
        AND
    CONCAT(r.FechaReal, ' ', r.HoraReal) >= NOW()
ORDER BY
    r.FechaReal ASC,
    r.HoraReal ASC
LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerReservasMatrimonio`()
BEGIN
    SELECT
        sn.CiPersona AS CiPersonaNovio,
        CONCAT(novio.Nombre, ' ', novio.ApPaterno, ' ', novio.ApMaterno) AS NombresApellidosNovio,
        sv.CiPersona AS CiPersonaNovia,
        CONCAT(novia.Nombre, ' ', novia.ApPaterno, ' ', novia.ApMaterno) AS NombresApellidosNovia,
        t.DescripSac,
        r.Quien,
        r.FechaReal AS FechaCelebracion,
        r.HoraReal AS HoraCelebracion
    FROM
        Reserva r
    INNER JOIN
        Solicitante sn ON r.CiPersona = sn.CiPersona AND r.CodIns = sn.CodIns
    INNER JOIN
        inssacramento i ON sn.CodIns = i.CodIns
    INNER JOIN
        tiposacramento t ON t.CodSac = i.CodSac
    INNER JOIN
        Solicitante sv ON i.CodIns = sv.CodIns AND sv.Rol = 'Novia'
    INNER JOIN
        persona novio ON sn.CiPersona = novio.CiPersona
    INNER JOIN
        persona novia ON sv.CiPersona = novia.CiPersona
    WHERE
        r.EstadoRes = 'Reservado' AND i.CodSac = 4
        AND sn.Rol = 'Novio'
        AND CONCAT(r.FechaReal, ' ', r.HoraReal) >= NOW()
    ORDER BY
        r.FechaReal ASC,
        r.HoraReal ASC
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerReservasOracion`()
BEGIN
    SELECT
    p.CiPersona,
    CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombresApellidos,
    t.DescripSac,
    r.FechaReal AS FechaCelebracion,
    r.HoraReal AS HoraCelebracion
FROM
    Reserva r
INNER JOIN
    Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
INNER JOIN
    inssacramento i ON s.CodIns = i.CodIns
INNER JOIN
    tiposacramento t ON t.CodSac = i.CodSac
INNER JOIN
    persona p ON s.CiPersona = p.CiPersona
WHERE
 r.EstadoRes='Reservado' AND i.CodSac = 5
        AND
    CONCAT(r.FechaReal, ' ', r.HoraReal) >= NOW()
ORDER BY
    r.FechaReal ASC,
    r.HoraReal ASC
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VerTiposSacramento`()
BEGIN
  SELECT * FROM tiposacramento;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

