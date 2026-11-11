<?php
require_once '../Conexion/Conexion.php';

class CelebranteAdmin
{
    private $conn;

    public function __construct()
    {
        $database = new Conexion();
        $this->conn = $database->getConnection();
    }

    public function verificarIns($SacramentoIns)
    {
        $sql = "SELECT * FROM fechassacramento WHERE Sacramento = ? AND EstadoIns = 'Online' AND ActividadSac = 'Inscripción'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $SacramentoIns);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCeleGR($sacCel)
    {
        $sql = "CALL CelebranteSinGrupo(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sacCel);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
        }

        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if ($sacCel === 'Primera Comunión') {
            $filas = array_filter($filas, function ($fila) {
                return in_array($fila['DescripItem'] ?? '', ['Primera Comunión', 'Primera Comunión - Bautizo'], true);
            });
        } elseif ($sacCel === 'Confirmación') {
            $filas = array_filter($filas, function ($fila) {
                return strpos($fila['DescripItem'] ?? '', 'Confirmación') !== false;
            });
        }

        return array_values($filas);
    }

    public function buscarCelebrante($ciCel, $sacramentoCel)
    {
        $sql = "CALL BuscarCelebrante(?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("ss", $ciCel, $sacramentoCel);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }

    public function buscarCelebrantes($termino, $sacramentoCel)
    {
        $filtrarPorTipo = in_array($sacramentoCel, ['Primera Comunión', 'Confirmación'], true);

        if ($filtrarPorTipo) {
            $filtroSacramento = $sacramentoCel === 'Confirmación'
                ? 'ti.CodTipoItem IN (3, 4, 5)'
                : 'ti.CodTipoItem IN (1, 2)';
        } else {
            $filtroSacramento = 'c.Sacramento = ?';
        }

        $sql = "SELECT c.CiCel, c.Sacramento, c.PerfilCel, c.FondoCel, c.EstadoCel,
                       c.UsuarioCel,
                       p.Nombre, p.ApPaterno, p.ApMaterno,
                       COALESCE(NULLIF(p.Contacto, ''), p.TelefonoAlternativo, '') AS Contacto,
                       CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto,
                       ins.IdInscripcion, ins.IdGrupo, ins.PreValor,
                       g.NombreGrupo, g.SacramentoGrupo, ti.DescripItem, ti.Valor, ti.CodTipoItem
                FROM celebrante c
                INNER JOIN persona p ON p.CiPersona = c.CiCel
                INNER JOIN inscripcion ins ON ins.CiCel = c.CiCel
                INNER JOIN grupo g ON g.IdGrupo = ins.IdGrupo
                LEFT JOIN Detalletipoins dti ON dti.IdInscripcion = ins.IdInscripcion
                LEFT JOIN TipoIns ti ON ti.CodTipoItem = dti.CodTipoItem
                WHERE $filtroSacramento
                  AND (c.CiCel LIKE ?
                       OR p.Nombre LIKE ?
                       OR p.ApPaterno LIKE ?
                       OR p.ApMaterno LIKE ?
                       OR CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) LIKE ?)
                ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre
                LIMIT 100";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $like = '%' . $termino . '%';

        if ($filtrarPorTipo) {
            $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
        } else {
            $stmt->bind_param("ssssss", $sacramentoCel, $like, $like, $like, $like, $like);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $resultados = [];

        foreach ($filas as $fila) {
            if (!isset($resultados[$fila['IdInscripcion']])) {
                $resultados[$fila['IdInscripcion']] = $fila;
            }
        }

        return array_values($resultados);
    }

    public function registrarCelebrante($datos)
    {
        $sql = "CALL RegistrarCelebrante(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssssisi",
            $datos['CiCel'],
            $datos['SacramentoCel'],
            $datos['UsuarioCel'],
            $datos['ClaveCel'],
            $datos['IdGrupo'],
            $datos['PreValor'],
            $datos['CodTipoItem']
        );

        $this->conn->begin_transaction();
        try {
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
            }
            $stmt->close();
            while ($this->conn->more_results()) {
                $this->conn->next_result();
                $resultado = $this->conn->store_result();
                if ($resultado) {
                    $resultado->free();
                }
            }

            $actualizar = $this->conn->prepare(
                'UPDATE `persona` SET SacramentoSolicita = ? WHERE CiPersona = ?'
            );
            $actualizar->bind_param('ss', $datos['SacramentoCel'], $datos['CiCel']);
            if (!$actualizar->execute()) {
                throw new RuntimeException('No se pudo actualizar el seguimiento de la persona.');
            }
            $actualizar->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /** Reemplaza la clave por una nueva de un solo uso y obliga a cambiarla. */
    public function restablecerClave($ciCel, $claveHash)
    {
        $stmt = $this->conn->prepare(
            "UPDATE celebrante SET ClaveCel = ?, DebeCambiarClave = 1 WHERE CiCel = ?"
        );
        $stmt->bind_param("ss", $claveHash, $ciCel);
        return $stmt->execute();
    }

    public function modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion)
    {
        $sql = "CALL ModificarCelebrante(?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssssssii",
            $ciCel,
            $datosCelebrante['Sacramento'],
            $datosCelebrante['EstadoCel'],
            $datosCelebrante['PerfilCel'],
            $datosCelebrante['FondoCel'],
            $datosInscripcion['IdGrupo'],
            $datosInscripcion['PreValor'],
            $datosInscripcion['CodTipoItem']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

class DocumentoAdm
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerDocumentosPorInscripcion($idInscripcion)
    {
        $sql = "SELECT * FROM `documento` WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerDatosInscripcion($idInscripcion)
    {
        $sql = "SELECT i.CiCel, c.Sacramento, p.FechaNac
                FROM inscripcion i
                INNER JOIN celebrante c ON c.CiCel = i.CiCel
                INNER JOIN persona p ON p.CiPersona = i.CiCel
                WHERE i.IdInscripcion = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ?: [];
    }

    public function obtenerBautizoLocalPorCi($ciCel)
    {
        $sql = "SELECT dc.NroLibro, dc.NroPag, dc.NroPart, dc.FechaReal,
                       TRIM(p.NomParroquia) AS NomParroquia, p.LugParroquia,
                       c.NroCertificado, c.NroEmision
                FROM inssacramento ins
                INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac
                INNER JOIN certificado c ON c.CodIns = ins.CodIns
                INNER JOIN detallecert dc ON dc.CodDetCert = c.CodDetCert
                INNER JOIN parroquia p ON p.CodParr = dc.CodParr
                INNER JOIN Solicitante s ON s.CodIns = ins.CodIns AND s.Rol = 'Bautizado'
                WHERE ins.CodSac = 1
                  AND s.CiPersona = ?
                  AND TRIM(p.NomParroquia) LIKE '%San Pedro%'
                  AND p.LugParroquia LIKE '%Sacaba%'
                ORDER BY dc.FechaReal DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciCel);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($fila) {
            return $fila;
        }

        $sql = "SELECT Libro AS NroLibro, Pagina AS NroPag, Partida AS NroPart, Fecha AS FechaReal,
                       TRIM(Parroquia) AS NomParroquia, Municipio AS LugParroquia,
                       '' AS NroCertificado, '' AS NroEmision
                FROM antecedente_sacramental
                WHERE CiPersona = ?
                  AND Sacramento = 'Bautizo'
                  AND (TRIM(Parroquia) LIKE '%San Pedro%' OR TRIM(Municipio) LIKE '%Sacaba%')
                ORDER BY Fecha DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciCel);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }

    public function registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc, $fechaDoc = '')
    {
        $sql = "INSERT INTO `documento` (Gestion, CiCat, IdGrupo, IdInscripcion, NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc, FechaDoc)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiisssssss", $gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc, $fechaDoc);
        return $stmt->execute();
    }

    public function actualizarDocumentosPresentados($gestion, $ciCat, $idGrupo, $idInscripcion, array $presentados, $fechaBautizo = '')
    {
        $permitidos = [
            'Certificado de Nacimiento',
            'Bautizo',
            'Recuerdo de Primera Comunión',
            'Certificado de Confirmación (Padrino soltero)',
            'Certificado de Matrimonio Religioso (Casado)',
            'Certificado de Defunción o CI de Viudo (Padrino)',
        ];
        $presentados = array_values(array_intersect($permitidos, $presentados));

        $sql = "SELECT i.CiCel
                FROM inscripcion i
                INNER JOIN persona p ON p.CiPersona = i.CiCel
                WHERE i.IdInscripcion = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $datosCatequizando = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();

        $ciCel = $datosCatequizando['CiCel'] ?? '';
        $bautizo = $ciCel !== '' ? $this->obtenerBautizoLocalPorCi($ciCel) : null;

        $sql = "SELECT DISTINCT DetalleDoc FROM documento WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $existentes = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'DetalleDoc');
        $stmt->close();

        foreach ($existentes as $detalle) {
            if (in_array($detalle, $permitidos, true) && !in_array($detalle, $presentados, true)) {
                $stmt = $this->conn->prepare("DELETE FROM documento WHERE IdInscripcion = ? AND DetalleDoc = ?");
                $stmt->bind_param("is", $idInscripcion, $detalle);
                $stmt->execute();
                $stmt->close();
            }
        }

        foreach ($presentados as $detalle) {
            $fechaDoc = $this->fechaDocumentoPresentado($detalle, $fechaBautizo, $bautizo);
            $parroquiaDoc = ($detalle === 'Bautizo' && $bautizo) ? ($bautizo['NomParroquia'] ?? '') : '';

            if (in_array($detalle, $existentes, true)) {
                if ($fechaDoc !== '') {
                    $stmt = $this->conn->prepare("UPDATE documento SET FechaDoc = ? WHERE IdInscripcion = ? AND DetalleDoc = ?");
                    $stmt->bind_param("sis", $fechaDoc, $idInscripcion, $detalle);
                    $stmt->execute();
                    $stmt->close();
                }
                if ($parroquiaDoc !== '') {
                    $stmt = $this->conn->prepare("UPDATE documento SET ParroquiaDoc = ? WHERE IdInscripcion = ? AND DetalleDoc = ?");
                    $stmt->bind_param("sis", $parroquiaDoc, $idInscripcion, $detalle);
                    $stmt->execute();
                    $stmt->close();
                }
                continue;
            }

            $numero = 'Presentado';
            $libro = '';
            $pagina = '';
            $partida = '';
            $sql = "INSERT INTO documento (Gestion, CiCat, IdGrupo, IdInscripcion, NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc, FechaDoc)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''))";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssiisssssss", $gestion, $ciCat, $idGrupo, $idInscripcion, $numero, $detalle, $libro, $pagina, $partida, $parroquiaDoc, $fechaDoc);
            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
            $stmt->close();
        }

        return true;
    }

    private function fechaDocumentoPresentado($detalle, $fechaBautizo, $bautizo)
    {
        if ($detalle === 'Bautizo') {
            if (trim((string) $fechaBautizo) !== '') {
                return $fechaBautizo;
            }
            return $bautizo['FechaReal'] ?? '';
        }

        return date('Y-m-d');
    }

    public function eliminarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion)
    {
        $sql = "DELETE FROM `documento` WHERE Gestion = ? AND CiCat = ? AND IdGrupo = ? AND IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $gestion, $ciCat, $idGrupo, $idInscripcion);
        return $stmt->execute();
    }
}

class FamiliarAdm
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerFamiliarPorCi($ciFamiliar)
    {
        $sql = "SELECT * FROM `familiar` WHERE CiFamiliar = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerFamiliaresPorInscripcion($idInscripcion)
    {
        $sql = "SELECT f.CiFamiliar, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
                       insf.RolFam, insf.Parentesco, p.Contacto 
                FROM insfamiliar insf 
                JOIN `familiar` f ON insf.CiFamiliar = f.CiFamiliar 
                JOIN `persona` p ON f.CiFamiliar = p.CiPersona 
                WHERE insf.IdInscripcion = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarFamiliar($ciFamiliar, $idInscripcion, $rolFam, $parentesco)
    {
        $sql = "INSERT IGNORE INTO `familiar` (CiFamiliar) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();

        $sql = "INSERT INTO insfamiliar (CiFamiliar, IdInscripcion, RolFam, Parentesco) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siss", $ciFamiliar, $idInscripcion, $rolFam, $parentesco);
        return $stmt->execute();
    }

    public function eliminarFamiliar($ciFamiliar, $idInscripcion)
    {
        $sql = "DELETE FROM insfamiliar WHERE CiFamiliar = ? AND IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ciFamiliar, $idInscripcion);
        return $stmt->execute();
    }

    public function obtenerPadrinosManuales($idInscripcion)
    {
        $sql = "SELECT IdPadrino, IdInscripcion, RolFam, Nombre, Apellido, EstadoCivil
                FROM padrinocel
                WHERE IdInscripcion = ?
                ORDER BY IdPadrino";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarPadrinoManual($idInscripcion, $rolFam, $nombre, $apellido, $estadoCivil)
    {
        $sql = "INSERT INTO padrinocel (IdInscripcion, RolFam, Nombre, Apellido, EstadoCivil)
                VALUES (?, ?, ?, ?, NULLIF(?, ''))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issss", $idInscripcion, $rolFam, $nombre, $apellido, $estadoCivil);
        return $stmt->execute();
    }

    public function eliminarPadrinoManual($idPadrino, $idInscripcion)
    {
        $sql = "DELETE FROM padrinocel WHERE IdPadrino = ? AND IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idPadrino, $idInscripcion);
        return $stmt->execute();
    }
}
class Inscripcion
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerInscripcionPorId($idInscripcion)
    {
        $sql = "SELECT * FROM inscripcion WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
