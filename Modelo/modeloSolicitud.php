<?php
require_once '../Conexion/Conexion.php';

class ModeloInsSacramento
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    private function actualizarSolicitudCertificado($ciPersona, $tipo)
    {
        $solicitud = 'Certificado de ' . $tipo;
        $stmt = $this->conn->prepare('UPDATE `persona` SET SacramentoSolicita = ? WHERE CiPersona = ?');
        $stmt->bind_param('ss', $solicitud, $ciPersona);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo actualizar el seguimiento del certificado.');
        }
        $stmt->close();
    }

    public function validarClaveParroco($clave)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM `personal` WHERE ClavePer = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }


    public function RegistrarCertificado($dataCer)
    {
        $certFirmado = ($dataCer['CertFirmado'] ?? '') !== '' ? $dataCer['CertFirmado'] : null;
        // La hora ya no se pide en los formularios: se guarda como NULL.
        $horaReal = trim((string) ($dataCer['HoraReal'] ?? ''));
        $horaReal = $horaReal === '' ? null : $horaReal;
        // El lugar de la parroquia ya no se pide ni se imprime: se guarda con el valor por defecto.
        $lugParroquia = trim((string) ($dataCer['LugParroquia'] ?? ''));
        if ($lugParroquia === '') {
            $lugParroquia = 'Sacaba, Cochabamba';
        }
        $stmt = $this->conn->prepare("CALL RegistrarCertificado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssssssssss",
            $dataCer['CodIns'],
            $dataCer['NomParroquia'],
            $lugParroquia,
            $dataCer['NroLibro'],
            $dataCer['NroPag'],
            $dataCer['NroPart'],
            $dataCer['LugarNac'],
            $dataCer['FechaReal'],
            $horaReal,
            $dataCer['NroEmision'],
            $dataCer['FechaEmision'],
            $dataCer['PresbiteroEm'],
            $certFirmado,
            $dataCer['Observacion'],
            $dataCer['CelebrantePres'],
            $dataCer['EstadoCert']
        );
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Cambia el número de emisión de un certificado ya registrado, para que
     * una nueva emisión del mismo certificado no repita el número anterior.
     *
     * @throws InvalidArgumentException Si el certificado no existe o el número no es válido.
     */
    public function ActualizarNroEmision($codIns, $nroEmision)
    {
        $codIns = (int) $codIns;
        $nroEmision = trim((string) $nroEmision);

        if ($codIns <= 0) {
            throw new InvalidArgumentException('Certificado no válido.');
        }
        if ($nroEmision === '' || !ctype_digit($nroEmision) || strlen($nroEmision) > 10) {
            throw new InvalidArgumentException('Ingrese un número de emisión válido (hasta 10 dígitos).');
        }

        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM certificado WHERE CodIns = ?');
        $stmt->bind_param('i', $codIns);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();
        if (!$existe) {
            throw new InvalidArgumentException('Este certificado aún no tiene número de emisión.');
        }

        $stmt = $this->conn->prepare('UPDATE certificado SET NroEmision = ? WHERE CodIns = ?');
        $stmt->bind_param('si', $nroEmision, $codIns);
        $resultado = $stmt->execute();
        $stmt->close();
        if (!$resultado) {
            throw new RuntimeException('No se pudo actualizar el número de emisión.');
        }
        return true;
    }

    /**
     * Cambia la fecha de emisión de un certificado ya registrado para que una
     * reimpresión muestre la fecha de la nueva emisión (por defecto, hoy).
     *
     * @throws InvalidArgumentException Si el certificado no existe o la fecha no es válida.
     */
    public function ActualizarFechaEmision($codIns, $fecha = null)
    {
        $codIns = (int) $codIns;
        $fecha = ($fecha === null || $fecha === '') ? date('Y-m-d') : trim((string) $fecha);

        if ($codIns <= 0) {
            throw new InvalidArgumentException('Certificado no válido.');
        }
        $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('La fecha de emisión no es válida.');
        }

        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM certificado WHERE CodIns = ?');
        $stmt->bind_param('i', $codIns);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();
        if (!$existe) {
            throw new InvalidArgumentException('Este certificado aún no está registrado.');
        }

        $stmt = $this->conn->prepare('UPDATE certificado SET FechaEmision = ? WHERE CodIns = ?');
        $stmt->bind_param('si', $fecha, $codIns);
        $resultado = $stmt->execute();
        $stmt->close();
        if (!$resultado) {
            throw new RuntimeException('No se pudo actualizar la fecha de emisión.');
        }
        return true;
    }

    /** ----------------------------------------------------------------------------------- */

    public function registrarBautizo($dataBau)
    {
        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoBautizo(?,?,?,?,?,?)");
        $stmt->bind_param(
            "sissss",
            $dataBau['CiPersonaCelebranteB'],
            $dataBau['CodPer'],
            $dataBau['CiPapa'],
            $dataBau['CiMama'],
            $dataBau['CiPadrino'],
            $dataBau['CiMadrina']
        );

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }


    public function registrarBautizoCompleto($datos)
    {
        $limites = [
            'CiPersonaCelebranteB' => 15, 'CiPapa' => 15, 'CiMama' => 15,
            'CiPadrino' => 15, 'CiMadrina' => 15, 'NomParroquia' => 50,
            'LugParroquia' => 50, 'NroLibro' => 5, 'NroPag' => 5, 'NroPart' => 5,
            'LugarNac' => 50, 'PresbiteroEm' => 50, 'CelebrantePres' => 50,
            'Observacion' => 80, 'OficialiaCivil' => 30, 'LibroCivil' => 10,
            'PartidaCivil' => 10, 'FolioCivil' => 10, 'CertFirmado' => 60
        ];
        $opcionales = ['CiPapa', 'CiMama', 'CiPadrino', 'CiMadrina', 'Observacion', 'FolioCivil', 'LugParroquia', 'CertFirmado'];
        foreach ($limites as $campo => $limite) {
            if (isset($datos[$campo]) && !is_scalar($datos[$campo])) {
                throw new InvalidArgumentException('Revise los datos del formulario.');
            }
            $datos[$campo] = trim((string) ($datos[$campo] ?? ''));
            if ($datos[$campo] === '' && !in_array($campo, $opcionales, true)) {
                throw new InvalidArgumentException('Complete todos los campos obligatorios del certificado.');
            }
            if (mb_strlen($datos[$campo], 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
        }
        // El lugar de la parroquia ya no se pide en el formulario.
        if ($datos['LugParroquia'] === '') {
            $datos['LugParroquia'] = 'Sacaba, Cochabamba';
        }
        foreach (['FechaNac', 'FechaReal', 'FechaEmision'] as $campo) {
            $valor = $datos[$campo] ?? '';
            $fecha = is_string($valor) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
            if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('Complete las fechas con valores válidos.');
            }
        }
        // La fecha de registro civil ya no se pide en el formulario; si llega
        // vacía se guarda la fecha del bautismo para no perder la referencia.
        $fechaCivil = trim((string) ($datos['FechaRegistroCivil'] ?? ''));
        if ($fechaCivil === '') {
            $datos['FechaRegistroCivil'] = $datos['FechaReal'];
        } else {
            $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaCivil);
            if (!$fecha || $fecha->format('Y-m-d') !== $fechaCivil) {
                throw new InvalidArgumentException('Complete las fechas con valores válidos.');
            }
        }
        if ($datos['FechaNac'] > $datos['FechaReal'] || $datos['FechaReal'] > $datos['FechaEmision']) {
            throw new InvalidArgumentException('El nacimiento debe ser anterior o igual al bautizo y el bautizo anterior o igual a la emisión.');
        }
        if ($datos['FechaReal'] > date('Y-m-d')) {
            throw new InvalidArgumentException('El certificado solo puede emitirse después de realizado el bautizo.');
        }
        $hora = trim((string) ($datos['HoraReal'] ?? ''));
        if ($hora === '') {
            // La hora ya no se pide en el formulario.
            $datos['HoraReal'] = null;
        } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora)) {
            throw new InvalidArgumentException('Ingrese una hora válida para el bautizo.');
        }
        $numeroEmision = filter_var($datos['NroEmision'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
        if ($numeroEmision === false || empty($datos['CodPer'])) {
            throw new InvalidArgumentException('Ingrese un número de emisión válido.');
        }
        $datos['NroEmision'] = $numeroEmision;
        $datos['EstadoCert'] = 'Activo';
        $personas = array_filter(array_intersect_key($datos, array_flip([
            'CiPersonaCelebranteB', 'CiPapa', 'CiMama', 'CiPadrino', 'CiMadrina'
        ])), function ($ci) { return $ci !== ''; });
        if (count($personas) !== count(array_unique($personas))) {
            throw new InvalidArgumentException('Cada participante debe tener una identificación distinta.');
        }

        $this->conn->begin_transaction();
        try {
            foreach ($personas as $ci) {
                $stmt = $this->conn->prepare('SELECT CiPersona FROM `persona` WHERE CiPersona = ? FOR UPDATE');
                $stmt->bind_param('s', $ci);
                $stmt->execute();
                $existe = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$existe) {
                    throw new InvalidArgumentException('Registre primero a todas las personas indicadas por su CI.');
                }
            }
            $this->asegurarSinCertificado($datos['CiPersonaCelebranteB'], 1);
            $stmt = $this->conn->prepare('UPDATE `persona` SET FechaNac = ? WHERE CiPersona = ?');
            $stmt->bind_param('ss', $datos['FechaNac'], $datos['CiPersonaCelebranteB']);
            $stmt->execute();
            $stmt->close();

            if (!$this->registrarBautizo($datos)) {
                throw new RuntimeException('No se pudo registrar el bautizo.');
            }
            // El procedimiento inserta la inscripción; solicitante no tiene auto_increment.
            $datos['CodIns'] = (int) $this->conn->query('SELECT LAST_INSERT_ID() AS CodIns')->fetch_assoc()['CodIns'];
            if ($datos['CodIns'] <= 0 || !$this->RegistrarCertificado($datos)) {
                throw new RuntimeException('No se pudo guardar el certificado.');
            }

            $dequien = 'Celebrante';
            $tipoDocumento = 2; // Certificado de nacimiento.
            $stmt = $this->conn->prepare('INSERT INTO presentaciondoc (FechaPres, dequien, CodificDoc, CodTDoc, CodIns) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssii', $datos['FechaEmision'], $dequien, $datos['PartidaCivil'], $tipoDocumento, $datos['CodIns']);
            $stmt->execute();
            $codPres = $this->conn->insert_id;
            $stmt->close();
            $stmt = $this->conn->prepare('INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssi', $datos['OficialiaCivil'], $datos['LibroCivil'], $datos['FolioCivil'], $datos['PartidaCivil'], $datos['FechaRegistroCivil'], $codPres);
            $stmt->execute();
            $stmt->close();
            $this->actualizarSolicitudCertificado($datos['CiPersonaCelebranteB'], 'Bautizo');
            $this->conn->commit();
            return $datos['CodIns'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Certificados de Bautizo de una persona. La búsqueda acepta CI, número de
     * documento, nombres, apellidos, el nombre completo o la partida (la del
     * libro de bautizos y la del certificado de nacimiento del bautizado).
     */
    public function VerInsSacramentoBautizo($busqueda)
    {
        $texto = '%' . trim((string) $busqueda) . '%';
        $sql = "SELECT
                    CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', IFNULL(p.ApMaterno, '')) AS NombreBautizado,
                    s.Rol,
                    ins.CodIns,
                    ins.CodSac,
                    ins.CodPer,
                    ins.FechaIns,
                    ts.DescripSac
                FROM `solicitante` s
                INNER JOIN `persona` p ON p.CiPersona = s.CiPersona
                INNER JOIN inssacramento ins ON ins.CodIns = s.CodIns
                INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac
                LEFT JOIN presentaciondoc pd ON pd.CodIns = ins.CodIns
                LEFT JOIN docpresbau dpb ON dpb.CodPres = pd.CodPres
                WHERE s.Rol IN ('Bautizado', 'Celebrante')
                  AND ts.CodSac = 1
                  AND (
                    p.CiPersona LIKE ?
                    OR p.NumeroDocumento LIKE ?
                    OR p.Nombre LIKE ?
                    OR p.ApPaterno LIKE ?
                    OR p.ApMaterno LIKE ?
                    OR CONCAT_WS(' ', p.Nombre, p.ApPaterno, p.ApMaterno) LIKE ?
                    OR CONCAT_WS(' ', p.ApPaterno, p.ApMaterno, p.Nombre) LIKE ?
                    OR p.id_certificado_nacimiento LIKE ?
                    OR dpb.NrPart LIKE ?
                  )
                GROUP BY ins.CodIns
                ORDER BY ins.FechaIns DESC, ins.CodIns DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssss", $texto, $texto, $texto, $texto, $texto, $texto, $texto, $texto, $texto);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function VerCertificadoBautizo($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoBautizo(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $certificado = $result->fetch_assoc();
        if (!$certificado) {
            return $certificado;
        }
        // Los nombres transcritos manualmente tienen prioridad sobre los
        // vinculados por CI, tanto en el detalle como en el PDF.
        $manuales = [
            'NombrePadre' => 'NombrePadreManual',
            'NombreMadre' => 'NombreMadreManual',
            'NombrePadrino' => 'NombrePadrinoManual',
            'NombreMadrina' => 'NombreMadrinaManual',
        ];
        foreach ($manuales as $campo => $manual) {
            if (trim((string) ($certificado[$manual] ?? '')) !== '') {
                $certificado[$campo] = $certificado[$manual];
            }
        }
        return $certificado;
    }

    /**
     * Guarda los datos que se completan manualmente en el certificado de
     * Bautizo. Los nombres transcritos de la partida se conservan aparte de
     * los vinculados por CI, y la partida, la parroquia y el antecedente civil
     * se actualizan en una sola transacción.
     */
    public function ActualizarDatosBautizo($codIns, $datos)
    {
        $codIns = (int) $codIns;
        if ($codIns <= 0) {
            throw new InvalidArgumentException('Certificado no válido.');
        }

        $texto = function ($campo, $limite) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if (mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
            return $valor === '' ? null : $valor;
        };
        $fecha = function ($campo) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if ($valor === '') {
                return null;
            }
            $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            if (!$fechaValida || $fechaValida->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('La fecha ' . $campo . ' no es válida.');
            }
            return $valor;
        };

        $padre = $texto('NombrePadreManual', 100);
        $madre = $texto('NombreMadreManual', 100);
        $padrino = $texto('NombrePadrinoManual', 100);
        $madrina = $texto('NombreMadrinaManual', 100);
        $nomParroquia = $texto('NomParroquia', 50);
        $nroLibro = $texto('NroLibro', 10);
        $nroPag = $texto('NroPag', 10);
        $nroPart = $texto('NroPart', 10);
        $lugarNac = $texto('LugarNac', 50);
        $fechaReal = $fecha('FechaReal');
        $presbitero = $texto('PresbiteroEm', 50);
        $certFirmado = $texto('CertFirmado', 60);
        $celebrantePres = $texto('CelebrantePres', 50);
        $observacion = $texto('Observacion', 80);
        $parroquiaBau = $texto('ParroquiaBau', 30) ?? '';
        $nrLib = $texto('NrLib', 10) ?? '';
        $nrPart = $texto('NrPart', 10) ?? '';

        if ($fechaReal !== null && $fechaReal > date('Y-m-d')) {
            throw new InvalidArgumentException('La fecha del bautizo no puede ser posterior a hoy.');
        }

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                'SELECT c.CodDetCert, dc.CodParr
                 FROM certificado c
                 INNER JOIN detallecert dc ON dc.CodDetCert = c.CodDetCert
                 INNER JOIN inssacramento i ON i.CodIns = c.CodIns
                 WHERE c.CodIns = ? AND i.CodSac = 1
                 FOR UPDATE'
            );
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $referencia = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$referencia) {
                throw new InvalidArgumentException('El certificado de bautizo no existe.');
            }

            $stmt = $this->conn->prepare(
                'UPDATE certificado
                 SET NombrePadreManual = ?, NombreMadreManual = ?,
                     NombrePadrinoManual = ?, NombreMadrinaManual = ?,
                     PresbiteroEm = COALESCE(?, PresbiteroEm),
                     CertFirmado = ?,
                     CelebrantePres = COALESCE(?, CelebrantePres),
                     Observacion = COALESCE(?, Observacion)
                 WHERE CodIns = ?'
            );
            $stmt->bind_param(
                'ssssssssi',
                $padre, $madre, $padrino, $madrina,
                $presbitero, $certFirmado, $celebrantePres, $observacion,
                $codIns
            );
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos del certificado.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE detallecert
                 SET NroLibro = COALESCE(?, NroLibro),
                     NroPag = COALESCE(?, NroPag),
                     NroPart = COALESCE(?, NroPart),
                     LugarNac = COALESCE(?, LugarNac),
                     FechaReal = COALESCE(?, FechaReal)
                 WHERE CodDetCert = ?'
            );
            $stmt->bind_param('sssssi', $nroLibro, $nroPag, $nroPart, $lugarNac, $fechaReal, $referencia['CodDetCert']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos de la partida.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE parroquia SET NomParroquia = COALESCE(?, NomParroquia) WHERE CodParr = ?'
            );
            $stmt->bind_param('si', $nomParroquia, $referencia['CodParr']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo guardar el nombre de la parroquia.');
            }
            $stmt->close();

            // Antecedente de bautismo civil de esta misma inscripción.
            $stmt = $this->conn->prepare(
                'SELECT pre.CodPres
                 FROM presentaciondoc pre
                 INNER JOIN docpresbau doc ON doc.CodPres = pre.CodPres
                 WHERE pre.CodIns = ? AND pre.CodTDoc = 2
                   AND pre.dequien IN (\'Celebrante\', \'Bautizado\')
                 ORDER BY pre.CodPres DESC LIMIT 1'
            );
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $presentacion = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($presentacion) {
                $stmt = $this->conn->prepare(
                    'UPDATE docpresbau
                     SET ParroquiaBau = ?, NrLib = ?, NrPart = ?
                     WHERE CodPres = ?'
                );
                $stmt->bind_param('sssi', $parroquiaBau, $nrLib, $nrPart, $presentacion['CodPres']);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo guardar el antecedente de bautismo.');
                }
                $stmt->close();
            } elseif ($parroquiaBau !== '' || $nrLib !== '' || $nrPart !== '') {
                $stmt = $this->conn->prepare(
                    'SELECT Rol FROM `solicitante` WHERE CodIns = ? AND Rol IN (\'Celebrante\', \'Bautizado\') ORDER BY Rol = \'Bautizado\' DESC LIMIT 1'
                );
                $stmt->bind_param('i', $codIns);
                $stmt->execute();
                $dequien = $stmt->get_result()->fetch_assoc()['Rol'] ?? 'Celebrante';
                $stmt->close();

                $stmt = $this->conn->prepare(
                    'INSERT INTO presentaciondoc (FechaPres, dequien, CodTDoc, CodIns)
                     VALUES (CURDATE(), ?, 2, ?)'
                );
                $stmt->bind_param('si', $dequien, $codIns);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo registrar la presentación del antecedente.');
                }
                $codPres = $stmt->insert_id;
                $stmt->close();

                $fechaBau = $fechaReal ?? date('Y-m-d');
                $stmt = $this->conn->prepare(
                    "INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres)
                     VALUES (?, ?, '', ?, ?, ?)"
                );
                $stmt->bind_param('ssssi', $parroquiaBau, $nrLib, $nrPart, $fechaBau, $codPres);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo registrar el antecedente de bautismo.');
                }
                $stmt->close();
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function VerCertificadoExtra($CiNacimiento, $codIns = null)
    {
        if ($codIns !== null) {
            $stmt = $this->conn->prepare("SELECT doc.*
                FROM presentaciondoc pre
                INNER JOIN docpresbau doc ON doc.CodPres = pre.CodPres
                INNER JOIN `solicitante` s ON s.CodIns = pre.CodIns
                WHERE pre.CodIns = ? AND pre.CodTDoc = 2
                  AND pre.dequien IN ('Celebrante', 'Bautizado')
                  AND s.CiPersona = ? AND s.Rol IN ('Celebrante', 'Bautizado')
                ORDER BY pre.CodPres DESC LIMIT 1");
            $stmt->bind_param('is', $codIns, $CiNacimiento);
        } else {
            $stmt = $this->conn->prepare("CALL VerCertificadoExtN(?)");
            $stmt->bind_param("s", $CiNacimiento);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    /** ----------------------------------------------------------------------------------- */

    /**
     * Validación común de los formularios de certificado (confirmación y matrimonio).
     * Devuelve los datos normalizados o lanza InvalidArgumentException con el motivo.
     */
    private function validarDatosCertificado(array $datos, array $limites, array $opcionales)
    {
        foreach ($limites as $campo => $limite) {
            if (isset($datos[$campo]) && !is_scalar($datos[$campo])) {
                throw new InvalidArgumentException('Revise los datos del formulario.');
            }
            $datos[$campo] = trim((string) ($datos[$campo] ?? ''));
            if ($datos[$campo] === '' && !in_array($campo, $opcionales, true)) {
                throw new InvalidArgumentException('Complete todos los campos obligatorios del certificado.');
            }
            if (mb_strlen($datos[$campo], 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
        }
        foreach (['FechaReal', 'FechaEmision'] as $campo) {
            $valor = $datos[$campo] ?? '';
            $fecha = is_string($valor) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
            if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('Complete las fechas con valores válidos.');
            }
        }
        if ($datos['FechaReal'] > $datos['FechaEmision']) {
            throw new InvalidArgumentException('La fecha del sacramento no puede ser posterior a la fecha de emisión.');
        }
        if ($datos['FechaReal'] > date('Y-m-d')) {
            throw new InvalidArgumentException('El certificado solo puede emitirse después de realizado el sacramento.');
        }
        $hora = trim((string) ($datos['HoraReal'] ?? ''));
        if ($hora === '') {
            // La hora ya no se pide en el formulario.
            $datos['HoraReal'] = null;
        } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora)) {
            throw new InvalidArgumentException('Ingrese una hora válida para el sacramento.');
        }
        // El lugar de la parroquia ya no se pide en el formulario.
        if (($datos['LugParroquia'] ?? '') === '') {
            $datos['LugParroquia'] = 'Sacaba, Cochabamba';
        }
        $numeroEmision = filter_var($datos['NroEmision'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
        if ($numeroEmision === false || empty($datos['CodPer'])) {
            throw new InvalidArgumentException('Ingrese un número de emisión válido.');
        }
        $datos['NroEmision'] = $numeroEmision;
        $datos['EstadoCert'] = 'Activo';
        return $datos;
    }

    /** Comprueba que todas las identificaciones indicadas existan (con bloqueo). */
    private function verificarPersonas(array $personas)
    {
        foreach (array_unique($personas) as $ci) {
            $stmt = $this->conn->prepare('SELECT CiPersona FROM `persona` WHERE CiPersona = ? FOR UPDATE');
            $stmt->bind_param('s', $ci);
            $stmt->execute();
            $existe = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$existe) {
                throw new InvalidArgumentException('Registre primero a todas las personas indicadas por su CI.');
            }
        }
    }

    /** Evita emitir dos certificados activos del mismo sacramento a la misma persona. */
    private function asegurarSinCertificado($ciPersona, $codSac)
    {
        $stmt = $this->conn->prepare("SELECT cert.NroCertificado
            FROM `solicitante` s
            INNER JOIN inssacramento ins ON ins.CodIns = s.CodIns
            INNER JOIN certificado cert ON cert.CodIns = ins.CodIns
            WHERE s.CiPersona = ? AND ins.CodSac = ? AND cert.EstadoCert = 'Activo'
              AND ((? = 1 AND s.Rol IN ('Bautizado', 'Celebrante'))
                OR (? = 3 AND s.Rol = 'Confirmante')
                OR (? = 4 AND s.Rol IN ('Novio', 'Novia')))
            LIMIT 1");
        $stmt->bind_param('siiii', $ciPersona, $codSac, $codSac, $codSac, $codSac);
        $stmt->execute();
        $existente = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($existente) {
            throw new InvalidArgumentException('Esta persona ya tiene un certificado activo para este sacramento.');
        }
    }

    /** Obtiene el CodIns recién creado por el procedimiento del sacramento. */
    private function ultimaInscripcion()
    {
        $resultado = $this->conn->query('SELECT LAST_INSERT_ID() AS CodIns')->fetch_assoc();
        return (int) ($resultado['CodIns'] ?? 0);
    }

    public function registrarMatrimonio($dataMat)
    {
        // Los campos opcionales vacíos se envían como NULL para que el
        // procedimiento no intente insertar una persona inexistente.
        $opcional = function ($ci) {
            $ci = trim((string) ($ci ?? ''));
            return $ci === '' ? null : $ci;
        };
        $ciPadrino = $opcional($dataMat['CiPadrino'] ?? '');
        $ciMadrina = $opcional($dataMat['CiMadrina'] ?? '');
        $ciPaNovio = $opcional($dataMat['CiPaNovio'] ?? '');
        $ciMaNovio = $opcional($dataMat['CiMaNovio'] ?? '');
        $ciPaNovia = $opcional($dataMat['CiPaNovia'] ?? '');
        $ciMaNovia = $opcional($dataMat['CiMaNovia'] ?? '');

        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoMatrimonio(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssissssssss",
            $dataMat['CiPersonaNovio'],
            $dataMat['CiPersonaNovia'],
            $dataMat['CodPer'],
            $ciPadrino,
            $ciMadrina,
            $dataMat['CiTestigoNovio'],
            $dataMat['CiTestigoNovia'],
            $ciPaNovio,
            $ciMaNovio,
            $ciPaNovia,
            $ciMaNovia
        );

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Registra el matrimonio y su certificado en una sola transacción,
     * igual que el certificado de bautizo.
     */
    public function registrarMatrimonioCompleto($datos)
    {
        // La plantilla trae la parroquia impresa y no muestra número de emisión.
        $datos['NomParroquia'] = 'San Pedro de Sacaba';
        $datos['LugarNac'] = 'Ninguno';
        $datos['NroEmision'] = 1;
        $datos = $this->validarDatosCertificado($datos, [
            'CiPersonaNovio' => 15, 'CiPersonaNovia' => 15,
            'CiPadrino' => 15, 'CiMadrina' => 15,
            'CiTestigoNovio' => 15, 'CiTestigoNovia' => 15,
            'CiPaNovio' => 15, 'CiMaNovio' => 15, 'CiPaNovia' => 15, 'CiMaNovia' => 15,
            'NomParroquia' => 50, 'LugParroquia' => 50,
            'NroLibro' => 5, 'NroPag' => 5, 'NroPart' => 5, 'LugarNac' => 50,
            'PresbiteroEm' => 50, 'CelebrantePres' => 50, 'Observacion' => 80,
            'CertFirmado' => 60,
        ], ['CiPadrino', 'CiMadrina', 'CiPaNovio', 'CiMaNovio', 'CiPaNovia', 'CiMaNovia', 'Observacion', 'LugParroquia', 'CertFirmado']);

        $datos = array_replace($datos, $this->validarComplementosMatrimonio($datos));

        $personas = array_values(array_filter([
            $datos['CiPersonaNovio'], $datos['CiPersonaNovia'], $datos['CiPadrino'], $datos['CiMadrina'],
            $datos['CiTestigoNovio'], $datos['CiTestigoNovia'], $datos['CiPaNovio'], $datos['CiMaNovio'],
            $datos['CiPaNovia'], $datos['CiMaNovia']
        ], function ($ci) { return $ci !== ''; }));
        if (count($personas) !== count(array_unique($personas))) {
            throw new InvalidArgumentException('Cada participante debe tener una identificación distinta.');
        }

        $this->conn->begin_transaction();
        try {
            $this->verificarPersonas($personas);
            $this->asegurarSinCertificado($datos['CiPersonaNovio'], 4);
            $this->asegurarSinCertificado($datos['CiPersonaNovia'], 4);
            // Las edades se obtienen de las fichas, a la fecha del matrimonio.
            foreach (['CiPersonaNovio' => 'novio', 'CiPersonaNovia' => 'novia'] as $campo => $persona) {
                $stmt = $this->conn->prepare('SELECT FechaNac FROM `persona` WHERE CiPersona = ?');
                $stmt->bind_param('s', $datos[$campo]);
                $stmt->execute();
                $nacimiento = $stmt->get_result()->fetch_assoc()['FechaNac'] ?? '';
                $stmt->close();
                $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $nacimiento);
                if (!$fecha || $fecha->format('Y-m-d') !== $nacimiento || $nacimiento > $datos['FechaReal']) {
                    throw new InvalidArgumentException('Revise la fecha de nacimiento del ' . $persona . ' en Persona; debe ser válida y anterior al matrimonio.');
                }
            }
            if (!$this->registrarMatrimonio($datos)) {
                throw new RuntimeException('No se pudo registrar el matrimonio.');
            }
            // El procedimiento inserta la inscripción; solicitante no tiene auto_increment.
            $datos['CodIns'] = $this->ultimaInscripcion();
            if ($datos['CodIns'] <= 0 || !$this->RegistrarCertificado($datos)) {
                throw new RuntimeException('No se pudo guardar el certificado.');
            }
            // Ya estamos dentro de la transacción de este método.
            $this->ActualizarDatosMatrimonio($datos['CodIns'], $datos, false);
            $this->actualizarSolicitudCertificado($datos['CiPersonaNovio'], 'Matrimonio');
            $this->actualizarSolicitudCertificado($datos['CiPersonaNovia'], 'Matrimonio');
            $this->conn->commit();
            return $datos['CodIns'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function VerInsSacramentoMatrimonio($busqueda)
    {
        $texto = '%' . trim((string) $busqueda) . '%';
        $sql = "SELECT
                    ts.DescripSac,
                    CONCAT(novio.Nombre, ' ', novio.ApPaterno, ' ', IFNULL(novio.ApMaterno, '')) AS NombreNovio,
                    CONCAT(novia.Nombre, ' ', novia.ApPaterno, ' ', IFNULL(novia.ApMaterno, '')) AS NombreNovia,
                    ins.FechaIns,
                    ins.CodIns
                FROM inssacramento ins
                INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac AND ts.CodSac = 4
                LEFT JOIN `solicitante` sn ON sn.CodIns = ins.CodIns AND sn.Rol = 'Novio'
                LEFT JOIN `persona` novio ON novio.CiPersona = sn.CiPersona
                LEFT JOIN `solicitante` sv ON sv.CodIns = ins.CodIns AND sv.Rol = 'Novia'
                LEFT JOIN `persona` novia ON novia.CiPersona = sv.CiPersona
                WHERE novio.CiPersona IS NOT NULL
                  AND (
                    novio.CiPersona LIKE ?
                    OR novio.NumeroDocumento LIKE ?
                    OR novio.Nombre LIKE ?
                    OR novio.ApPaterno LIKE ?
                    OR novio.ApMaterno LIKE ?
                    OR CONCAT_WS(' ', novio.Nombre, novio.ApPaterno, novio.ApMaterno) LIKE ?
                    OR CONCAT_WS(' ', novio.ApPaterno, novio.ApMaterno, novio.Nombre) LIKE ?
                    OR novia.CiPersona LIKE ?
                    OR novia.NumeroDocumento LIKE ?
                    OR novia.Nombre LIKE ?
                    OR novia.ApPaterno LIKE ?
                    OR novia.ApMaterno LIKE ?
                    OR CONCAT_WS(' ', novia.Nombre, novia.ApPaterno, novia.ApMaterno) LIKE ?
                    OR CONCAT_WS(' ', novia.ApPaterno, novia.ApMaterno, novia.Nombre) LIKE ?
                  )
                ORDER BY ins.FechaIns DESC, ins.CodIns DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssss",
            $texto, $texto, $texto, $texto, $texto, $texto, $texto,
            $texto, $texto, $texto, $texto, $texto, $texto, $texto
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function RegistrarCertificadoMatrimonio($datos)
    {
        $datos['NomParroquia'] = 'San Pedro de Sacaba';
        $datos['LugarNac'] = 'Ninguno';
        $datos['NroEmision'] = 1;
        $datos = $this->validarDatosCertificado($datos, [
            'NroLibro' => 5, 'NroPag' => 5, 'NroPart' => 5,
            'PresbiteroEm' => 50, 'CelebrantePres' => 50,
            'CertFirmado' => 60, 'Observacion' => 80,
        ], ['CertFirmado', 'Observacion']);
        $datos = array_replace($datos, $this->validarComplementosMatrimonio($datos));
        $codIns = (int) ($datos['CodIns'] ?? 0);
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare('SELECT CodIns FROM inssacramento WHERE CodIns = ? AND CodSac = 4 FOR UPDATE');
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $inscripcion = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $stmt = $this->conn->prepare('SELECT NroCertificado FROM certificado WHERE CodIns = ?');
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $existente = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$inscripcion || $existente) {
                throw new InvalidArgumentException('La inscripción no es válida o ya tiene certificado.');
            }
            if (!$this->RegistrarCertificado($datos) || !$this->ActualizarDatosMatrimonio($codIns, $datos, false)) {
                throw new RuntimeException('No se pudo guardar el certificado de matrimonio.');
            }
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function validarComplementosMatrimonio(array $datos)
    {
        $valores = [];
        foreach (['ImpedimentoMat' => 80, 'FiliacionNovio' => 30, 'FiliacionNovia' => 30] as $campo => $limite) {
            if (isset($datos[$campo]) && !is_scalar($datos[$campo])) {
                throw new InvalidArgumentException('Revise los datos de impedimentos y filiación.');
            }
            $valor = trim((string) ($datos[$campo] ?? ''));
            if (mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
            $valores[$campo] = $valor === '' ? null : $valor;
        }
        // Es el estado que consta antes de la boda, no el estado actual en Persona.
        foreach (['EstadoCivilNovio' => ['Soltero', 'Viudo'], 'EstadoCivilNovia' => ['Soltera', 'Viuda']] as $campo => $opciones) {
            $valor = $datos[$campo] ?? '';
            if (!is_string($valor) || !in_array($valor, $opciones, true)) {
                throw new InvalidArgumentException('Seleccione el estado civil del novio y de la novia antes del matrimonio, según la partida.');
            }
            $valores[$campo] = $valor;
        }
        return $valores;
    }

    /**
     * Guarda los datos que se completan manualmente en el certificado de
     * Matrimonio. Los nombres transcritos de la partida se conservan aparte de
     * los vinculados por CI, y la partida, la parroquia y los datos del
     * certificado se actualizan en una sola transacción.
     *
     * @param bool $usarTransaccion false cuando ya se está dentro de otra
     *                              transacción (registro inicial del certificado).
     */
    public function ActualizarDatosMatrimonio($codIns, $datos, $usarTransaccion = true)
    {
        $codIns = (int) $codIns;
        if ($codIns <= 0) {
            throw new InvalidArgumentException('Certificado no válido.');
        }

        $texto = function ($campo, $limite) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if (mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
            return $valor === '' ? null : $valor;
        };
        $fecha = function ($campo) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if ($valor === '') {
                return null;
            }
            $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            if (!$fechaValida || $fechaValida->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('La fecha ' . $campo . ' no es válida.');
            }
            return $valor;
        };

        $padreNovio = $texto('NombrePadreNovioManual', 100);
        $madreNovio = $texto('NombreMadreNovioManual', 100);
        $padreNovia = $texto('NombrePadreNoviaManual', 100);
        $madreNovia = $texto('NombreMadreNoviaManual', 100);
        $testigoPresencial1 = $texto('NombreTestigoPresencial1Manual', 100);
        $testigoPresencial2 = $texto('NombreTestigoPresencial2Manual', 100);
        $testigoInfoNovio = $texto('NombreTestigoInfoNovioManual', 100);
        $testigoInfoNovia = $texto('NombreTestigoInfoNoviaManual', 100);
        $nomParroquia = $texto('NomParroquia', 50);
        $nroLibro = $texto('NroLibro', 10);
        $nroPag = $texto('NroPag', 10);
        $nroPart = $texto('NroPart', 10);
        $fechaReal = $fecha('FechaReal');
        $presbitero = $texto('PresbiteroEm', 50);
        $certFirmado = $texto('CertFirmado', 60);
        $celebrantePres = $texto('CelebrantePres', 50);
        $observacion = $texto('Observacion', 80);
        $complementos = $this->validarComplementosMatrimonio($datos);

        if ($usarTransaccion) {
            $this->conn->begin_transaction();
        }
        try {
            $stmt = $this->conn->prepare(
                'SELECT c.CodDetCert, dc.CodParr
                 FROM certificado c
                 INNER JOIN detallecert dc ON dc.CodDetCert = c.CodDetCert
                 INNER JOIN inssacramento i ON i.CodIns = c.CodIns
                 WHERE c.CodIns = ? AND i.CodSac = 4
                 FOR UPDATE'
            );
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $referencia = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$referencia) {
                throw new InvalidArgumentException('El certificado de matrimonio no existe.');
            }

            $stmt = $this->conn->prepare(
                'UPDATE certificado
                 SET NombrePadreNovioManual = ?, NombreMadreNovioManual = ?,
                     NombrePadreNoviaManual = ?, NombreMadreNoviaManual = ?,
                     NombreTestigoPresencial1Manual = ?, NombreTestigoPresencial2Manual = ?,
                     NombreTestigoInfoNovioManual = ?, NombreTestigoInfoNoviaManual = ?,
                     PresbiteroEm = ?, CertFirmado = ?, CelebrantePres = ?,
                     Observacion = ?, ImpedimentoMat = ?, FiliacionNovio = ?,
                     FiliacionNovia = ?, EstadoCivilNovio = ?, EstadoCivilNovia = ?
                 WHERE CodIns = ?'
            );
            $stmt->bind_param(
                'sssssssssssssssssi',
                $padreNovio, $madreNovio, $padreNovia, $madreNovia,
                $testigoPresencial1, $testigoPresencial2,
                $testigoInfoNovio, $testigoInfoNovia,
                $presbitero, $certFirmado, $celebrantePres,
                $observacion,
                $complementos['ImpedimentoMat'], $complementos['FiliacionNovio'],
                $complementos['FiliacionNovia'],
                $complementos['EstadoCivilNovio'], $complementos['EstadoCivilNovia'],
                $codIns
            );
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos del certificado.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE detallecert
                 SET NroLibro = ?, NroPag = ?, NroPart = ?,
                     FechaReal = COALESCE(?, FechaReal)
                 WHERE CodDetCert = ?'
            );
            $stmt->bind_param('ssssi', $nroLibro, $nroPag, $nroPart, $fechaReal, $referencia['CodDetCert']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos de la partida.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE parroquia SET NomParroquia = COALESCE(?, NomParroquia) WHERE CodParr = ?'
            );
            $stmt->bind_param('si', $nomParroquia, $referencia['CodParr']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo guardar el nombre de la parroquia.');
            }
            $stmt->close();

            if ($usarTransaccion) {
                $this->conn->commit();
            }
            return true;
        } catch (Throwable $e) {
            if ($usarTransaccion) {
                $this->conn->rollback();
            }
            throw $e;
        }
    }

    public function VerCertificadoMatrimonio($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoMatrimonio(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $certificado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$certificado) {
            return $certificado;
        }
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            if ($pendiente = $this->conn->store_result()) {
                $pendiente->free();
            }
        }
        // CONCAT_WS conserva el nombre aunque falte el apellido materno.
        $campos = [
            'Novio' => 'NombreNovio', 'Novia' => 'NombreNovia',
            'Papá Novio' => 'NombrePadreNovio', 'Mamá Novio' => 'NombreMadreNovio',
            'Papá Novia' => 'NombrePadreNovia', 'Mamá Novia' => 'NombreMadreNovia',
            'Padrino' => 'NombrePadrino', 'Madrina' => 'NombreMadrina',
            'Testigo Novio' => 'NombreTestigoNovio', 'Testigo Novia' => 'NombreTestigoNovia',
        ];
        $stmt = $this->conn->prepare("SELECT s.Rol, CONCAT_WS(' ', p.Nombre, p.ApPaterno, p.ApMaterno) AS NombreCompleto FROM `solicitante` s INNER JOIN `persona` p ON p.CiPersona = s.CiPersona WHERE s.CodIns = ?");
        $stmt->bind_param('i', $codIns);
        $stmt->execute();
        $personas = $stmt->get_result();
        while ($persona = $personas->fetch_assoc()) {
            if (isset($campos[$persona['Rol']])) {
                $certificado[$campos[$persona['Rol']]] = $persona['NombreCompleto'];
            }
        }
        $stmt->close();
        // Los nombres transcritos manualmente tienen prioridad sobre los
        // vinculados por CI, tanto en el detalle como en los PDF.
        $manuales = [
            'NombrePadreNovio' => 'NombrePadreNovioManual',
            'NombreMadreNovio' => 'NombreMadreNovioManual',
            'NombrePadreNovia' => 'NombrePadreNoviaManual',
            'NombreMadreNovia' => 'NombreMadreNoviaManual',
            'NombrePadrino' => 'NombreTestigoPresencial1Manual',
            'NombreMadrina' => 'NombreTestigoPresencial2Manual',
            'NombreTestigoNovio' => 'NombreTestigoInfoNovioManual',
            'NombreTestigoNovia' => 'NombreTestigoInfoNoviaManual',
        ];
        foreach ($manuales as $campo => $manual) {
            if (trim((string) ($certificado[$manual] ?? '')) !== '') {
                $certificado[$campo] = $certificado[$manual];
            }
        }
        return $certificado;
    }

    public function VerCertificadoExtraB($CiNovio, $Novio, $codIns = null)
    {
        if ($codIns !== null) {
            // El antecedente debe pertenecer a esta inscripción y a este cónyuge.
            $stmt = $this->conn->prepare("SELECT d.* FROM docpresbau d
                INNER JOIN presentaciondoc p ON p.CodPres = d.CodPres
                INNER JOIN `solicitante` s ON s.CodIns = p.CodIns AND s.Rol = p.dequien
                WHERE s.CiPersona = ? AND p.dequien = ? AND p.CodIns = ? AND p.CodTDoc = 3
                ORDER BY p.CodPres DESC LIMIT 1");
            $stmt->bind_param('ssi', $CiNovio, $Novio, $codIns);
        } else {
            $stmt = $this->conn->prepare("CALL VerCertificadoExtB(?,?)");
            $stmt->bind_param("ss", $CiNovio, $Novio);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function VerCertificadoExtraN($CiNovia,$Novia)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoExtN(?)");
        $stmt->bind_param("s", $CiNovia);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }


    /** ----------------------------------------------------------------------------------- */

    public function registrarConfirmacion($dataConf)
    {
        $ciPadrino = trim((string) ($dataConf['CiPadrino'] ?? ''));
        $ciMadrina = trim((string) ($dataConf['CiMadrina'] ?? ''));
        $ciPadrino = $ciPadrino === '' ? null : $ciPadrino;
        $ciMadrina = $ciMadrina === '' ? null : $ciMadrina;
        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoConfirmacion(?,?,?,?)");
        $stmt->bind_param(
            "siss",
            $dataConf['CiPersonaCelebranteC'],
            $dataConf['CodPer'],
            $ciPadrino,
            $ciMadrina
        );

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Registra la confirmación y su certificado en una sola transacción,
     * igual que el certificado de bautizo.
     */
    public function registrarConfirmacionCompleto($datos)
    {
        $datos = $this->validarDatosCertificado($datos, [
            'CiPersonaCelebranteC' => 15, 'CiPapa' => 15, 'CiMama' => 15,
            'CiPadrino' => 15, 'CiMadrina' => 15,
            'NomParroquia' => 50, 'LugParroquia' => 50,
            'NroLibro' => 5, 'NroPag' => 5, 'NroPart' => 5, 'LugarNac' => 50,
            'PresbiteroEm' => 50, 'CelebrantePres' => 50, 'Observacion' => 80,
            'ParroquiaBau' => 30, 'LibroBau' => 10, 'PaginaBau' => 10, 'PartidaBau' => 10,
        ], ['CiPapa', 'CiMama', 'CiPadrino', 'CiMadrina', 'Observacion', 'LugParroquia', 'LibroBau', 'PaginaBau', 'PartidaBau']);
        // El suscrito y quien firma son el mismo presbítero en confirmación.
        $datos['CertFirmado'] = $datos['PresbiteroEm'];
        $valor = $datos['FechaBau'] ?? '';
        $fecha = is_string($valor) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
        if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
            throw new InvalidArgumentException('Ingrese una fecha válida de bautismo.');
        }

        $personas = array_values(array_filter([
            $datos['CiPersonaCelebranteC'], $datos['CiPapa'], $datos['CiMama'], $datos['CiPadrino'], $datos['CiMadrina']
        ], function ($ci) { return $ci !== ''; }));
        if (count($personas) !== count(array_unique($personas))) {
            throw new InvalidArgumentException('Cada participante debe tener una identificación distinta.');
        }

        $this->conn->begin_transaction();
        try {
            $this->verificarPersonas($personas);
            $this->asegurarSinCertificado($datos['CiPersonaCelebranteC'], 3);
            // El nacimiento se consulta en la ficha existente, no en los datos enviados.
            $stmt = $this->conn->prepare('SELECT FechaNac FROM `persona` WHERE CiPersona = ?');
            $stmt->bind_param('s', $datos['CiPersonaCelebranteC']);
            $stmt->execute();
            $persona = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $datos['FechaNac'] = $persona['FechaNac'] ?? '';
            $nacimiento = DateTimeImmutable::createFromFormat('!Y-m-d', $datos['FechaNac']);
            if (!$nacimiento || $nacimiento->format('Y-m-d') !== $datos['FechaNac']) {
                throw new InvalidArgumentException('El confirmante no tiene una fecha de nacimiento válida guardada. Complete ese dato en Persona.');
            }
            if ($datos['FechaNac'] > $datos['FechaBau'] || $datos['FechaBau'] > $datos['FechaReal']) {
                throw new InvalidArgumentException('El nacimiento debe ser anterior o igual al bautismo y el bautismo anterior o igual a la confirmación.');
            }
            if (!$this->registrarConfirmacion($datos)) {
                throw new RuntimeException('No se pudo registrar la confirmación.');
            }
            // El procedimiento inserta la inscripción; solicitante no tiene auto_increment.
            $datos['CodIns'] = $this->ultimaInscripcion();
            if ($datos['CodIns'] <= 0 || !$this->RegistrarCertificado($datos)) {
                throw new RuntimeException('No se pudo guardar el certificado.');
            }
            foreach (['CiPapa' => 'Padre', 'CiMama' => 'Madre'] as $campo => $rol) {
                if ($datos[$campo] === '') {
                    continue;
                }
                $stmt = $this->conn->prepare('INSERT INTO `solicitante` (CiPersona, CodIns, Rol) VALUES (?, ?, ?)');
                $stmt->bind_param('sis', $datos[$campo], $datos['CodIns'], $rol);
                $stmt->execute();
                $stmt->close();
            }

            $dequien = 'Celebrante';
            $tipoDocumento = 3; // Antecedente de bautismo del confirmante.
            $stmt = $this->conn->prepare('INSERT INTO presentaciondoc (FechaPres, dequien, CodificDoc, CodTDoc, CodIns) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssii', $datos['FechaEmision'], $dequien, $datos['PartidaBau'], $tipoDocumento, $datos['CodIns']);
            $stmt->execute();
            $codPres = $this->conn->insert_id;
            $stmt->close();
            $stmt = $this->conn->prepare('INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssi', $datos['ParroquiaBau'], $datos['LibroBau'], $datos['PaginaBau'], $datos['PartidaBau'], $datos['FechaBau'], $codPres);
            $stmt->execute();
            $stmt->close();
            $this->actualizarSolicitudCertificado($datos['CiPersonaCelebranteC'], 'Confirmación');
            $this->conn->commit();
            return $datos['CodIns'];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function VerInsSacramentoConfirmacion($busqueda)
    {
        $texto = '%' . trim((string) $busqueda) . '%';
        $sql = "SELECT
                    CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', IFNULL(p.ApMaterno, '')) AS Confirmante,
                    s.Rol,
                    ins.CodIns,
                    ins.CodSac,
                    ins.CodPer,
                    ins.FechaIns,
                    ts.DescripSac
                FROM `solicitante` s
                INNER JOIN `persona` p ON p.CiPersona = s.CiPersona
                INNER JOIN inssacramento ins ON ins.CodIns = s.CodIns
                INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac
                WHERE s.Rol = 'Confirmante'
                  AND ts.CodSac = 3
                  AND (
                    p.CiPersona LIKE ?
                    OR p.NumeroDocumento LIKE ?
                    OR p.Nombre LIKE ?
                    OR p.ApPaterno LIKE ?
                    OR p.ApMaterno LIKE ?
                    OR CONCAT_WS(' ', p.Nombre, p.ApPaterno, p.ApMaterno) LIKE ?
                    OR CONCAT_WS(' ', p.ApPaterno, p.ApMaterno, p.Nombre) LIKE ?
                  )
                ORDER BY ins.FechaIns DESC, ins.CodIns DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", $texto, $texto, $texto, $texto, $texto, $texto, $texto);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function VerCertificadoConfirmacion($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoConfirmacion(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $result = $stmt->get_result();
        $certificado = $result->fetch_assoc();
        $stmt->close();
        if (!$certificado) {
            return $certificado;
        }

        // La nueva plantilla incluye a los padres. Usar únicamente relaciones
        // explícitas del certificado o del bautizo de la misma persona.
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            if ($pendiente = $this->conn->store_result()) {
                $pendiente->free();
            }
        }
        $sql = "SELECT CASE WHEN familiar.Rol IN ('Papá', 'Padre')
                            THEN 'NombrePadre' ELSE 'NombreMadre' END AS Campo,
                       CONCAT_WS(' ', p.Nombre, p.ApPaterno, p.ApMaterno) AS NombreCompleto
                FROM `solicitante` titular
                INNER JOIN inssacramento ins ON ins.CodIns = titular.CodIns
                INNER JOIN `solicitante` familiar ON familiar.CodIns = titular.CodIns
                INNER JOIN `persona` p ON p.CiPersona = familiar.CiPersona
                WHERE titular.CiPersona = ?
                  AND (titular.CodIns = ? OR
                       (ins.CodSac = 1 AND titular.Rol IN ('Bautizado', 'Celebrante')))
                  AND familiar.Rol IN ('Papá', 'Padre', 'Mamá', 'Madre')
                ORDER BY (titular.CodIns = ?) DESC, ins.FechaIns DESC, ins.CodIns DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $certificado['CiPersona'], $codIns, $codIns);
        $stmt->execute();
        $familiares = $stmt->get_result();
        $certificado['NombrePadre'] = '';
        $certificado['NombreMadre'] = '';
        while ($familiar = $familiares->fetch_assoc()) {
            $campo = $familiar['Campo'];
            if ($certificado[$campo] === '') {
                $certificado[$campo] = $familiar['NombreCompleto'];
            }
        }
        $stmt->close();

        // Si el certificado no tiene padrinos propios, usar los registrados
        // manualmente en la ficha de catequesis del confirmante.
        $stmtPadrinos = $this->conn->prepare(
            "SELECT pmd.RolFam, pmd.Nombre, pmd.Apellido
             FROM padrinocel pmd
             INNER JOIN inscripcion i ON i.IdInscripcion = pmd.IdInscripcion
             WHERE i.CiCel = ?
             ORDER BY pmd.IdPadrino"
        );
        $stmtPadrinos->bind_param("s", $certificado['CiPersona']);
        $stmtPadrinos->execute();
        $padrinosManuales = $stmtPadrinos->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtPadrinos->close();

        if (!empty($padrinosManuales)) {
            $porRol = ['Padrino' => [], 'Madrina' => []];
            foreach ($padrinosManuales as $padrinoManual) {
                $rolPadrino = $padrinoManual['RolFam'] ?? '';
                if (isset($porRol[$rolPadrino])) {
                    $nombreCompletoPadrino = trim(($padrinoManual['Nombre'] ?? '') . ' ' . ($padrinoManual['Apellido'] ?? ''));
                    if ($nombreCompletoPadrino !== '') {
                        $porRol[$rolPadrino][] = $nombreCompletoPadrino;
                    }
                }
            }
            if (trim((string) ($certificado['NombrePadrino'] ?? '')) === '' && !empty($porRol['Padrino'])) {
                $certificado['NombrePadrino'] = implode(' y ', $porRol['Padrino']);
            }
            if (trim((string) ($certificado['NombreMadrina'] ?? '')) === '' && !empty($porRol['Madrina'])) {
                $certificado['NombreMadrina'] = implode(' y ', $porRol['Madrina']);
            }
        }

        return $certificado;
    }

    /**
     * Guarda los datos que se completan manualmente en el certificado de
     * Confirmación: nombres de padre, madre, padrino y madrina, el presbítero
     * que certifica, el celebrante y las observaciones, además de la partida y
     * el antecedente de bautismo. Todo se actualiza en una sola transacción para
     * no dejar el certificado a medias.
     */
    public function ActualizarDatosConfirmacion($codIns, $datos)
    {
        $codIns = (int) $codIns;
        if ($codIns <= 0) {
            throw new InvalidArgumentException('Certificado no válido.');
        }

        $texto = function ($campo, $limite) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if (mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException('El campo ' . $campo . ' supera el tamaño permitido.');
            }
            return $valor === '' ? null : $valor;
        };
        $fecha = function ($campo) use ($datos) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if ($valor === '') {
                return null;
            }
            $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            if (!$fechaValida || $fechaValida->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('La fecha ' . $campo . ' no es válida.');
            }
            return $valor;
        };

        $padre = $texto('NombrePadreManual', 100);
        $madre = $texto('NombreMadreManual', 100);
        $padrino = $texto('NombrePadrinoManual', 100);
        $madrina = $texto('NombreMadrinaManual', 100);
        $parroquiaBau = $texto('ParroquiaBau', 30);
        $fechaBau = $fecha('FechaBau');
        $nomParroquia = $texto('NomParroquia', 50);
        $nroLibro = $texto('NroLibro', 10);
        $nroPag = $texto('NroPag', 10);
        $nroPart = $texto('NroPart', 10);
        $lugarNac = $texto('LugarNac', 50);
        $fechaReal = $fecha('FechaReal');
        $presbitero = $texto('PresbiteroEm', 50);
        $celebrantePres = $texto('CelebrantePres', 50);
        $observacion = $texto('Observacion', 80);

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                'SELECT c.CodDetCert, dc.CodParr
                 FROM certificado c
                 INNER JOIN detallecert dc ON dc.CodDetCert = c.CodDetCert
                 INNER JOIN inssacramento i ON i.CodIns = c.CodIns
                 WHERE c.CodIns = ? AND i.CodSac = 3
                 FOR UPDATE'
            );
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $referencia = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$referencia) {
                throw new InvalidArgumentException('El certificado de confirmación no existe.');
            }

            $stmt = $this->conn->prepare(
                'UPDATE certificado
                 SET NombrePadreManual = ?, NombreMadreManual = ?,
                     NombrePadrinoManual = ?, NombreMadrinaManual = ?,
                     PresbiteroEm = COALESCE(?, PresbiteroEm),
                     CelebrantePres = COALESCE(?, CelebrantePres),
                     Observacion = COALESCE(?, Observacion)
                 WHERE CodIns = ?'
            );
            $stmt->bind_param('sssssssi', $padre, $madre, $padrino, $madrina, $presbitero, $celebrantePres, $observacion, $codIns);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos del certificado.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE detallecert
                 SET NroLibro = COALESCE(?, NroLibro),
                     NroPag = COALESCE(?, NroPag),
                     NroPart = COALESCE(?, NroPart),
                     LugarNac = COALESCE(?, LugarNac),
                     FechaReal = COALESCE(?, FechaReal)
                 WHERE CodDetCert = ?'
            );
            $stmt->bind_param('sssssi', $nroLibro, $nroPag, $nroPart, $lugarNac, $fechaReal, $referencia['CodDetCert']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudieron guardar los datos de la partida.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                'UPDATE parroquia SET NomParroquia = COALESCE(?, NomParroquia) WHERE CodParr = ?'
            );
            $stmt->bind_param('si', $nomParroquia, $referencia['CodParr']);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo guardar el nombre de la parroquia.');
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                "SELECT pre.CodPres
                 FROM presentaciondoc pre
                 INNER JOIN docpresbau doc ON doc.CodPres = pre.CodPres
                 WHERE pre.CodIns = ? AND pre.CodTDoc = 3
                   AND pre.dequien IN ('Celebrante', 'Confirmante')
                 ORDER BY pre.CodPres DESC LIMIT 1"
            );
            $stmt->bind_param('i', $codIns);
            $stmt->execute();
            $presentacion = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($presentacion) {
                $stmt = $this->conn->prepare(
                    'UPDATE docpresbau
                     SET ParroquiaBau = COALESCE(?, ParroquiaBau),
                         FechaBau = COALESCE(?, FechaBau)
                     WHERE CodPres = ?'
                );
                $stmt->bind_param('ssi', $parroquiaBau, $fechaBau, $presentacion['CodPres']);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo guardar el antecedente de bautismo.');
                }
                $stmt->close();
            } elseif ($parroquiaBau !== null || $fechaBau !== null) {
                if ($parroquiaBau === null || $fechaBau === null) {
                    throw new InvalidArgumentException('Para registrar el antecedente de bautismo complete la parroquia y la fecha.');
                }
                $stmt = $this->conn->prepare(
                    "INSERT INTO presentaciondoc (FechaPres, dequien, CodTDoc, CodIns)
                     VALUES (CURDATE(), 'Confirmante', 3, ?)"
                );
                $stmt->bind_param('i', $codIns);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo registrar la presentación del antecedente.');
                }
                $codPres = $stmt->insert_id;
                $stmt->close();

                $stmt = $this->conn->prepare(
                    "INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres)
                     VALUES (?, '', '', '', ?, ?)"
                );
                $stmt->bind_param('ssi', $parroquiaBau, $fechaBau, $codPres);
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo registrar el antecedente de bautismo.');
                }
                $stmt->close();
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /** Partida de San Pedro de Sacaba del mismo confirmante, nunca por nombre. */
    public function buscarBautizoConfirmante($ciPersona)
    {
        $stmt = $this->conn->prepare("SELECT ins.CodIns,
                'San Pedro de Sacaba' AS ParroquiaBau, dc.FechaReal AS FechaBau,
                dc.NroLibro AS LibroBau, dc.NroPag AS PaginaBau, dc.NroPart AS PartidaBau,
                (SELECT familiar.CiPersona FROM `solicitante` familiar
                 WHERE familiar.CodIns = ins.CodIns AND familiar.Rol IN ('Padre', 'Papá')
                 ORDER BY familiar.CiPersona LIMIT 1) AS CiPapa,
                (SELECT familiar.CiPersona FROM `solicitante` familiar
                 WHERE familiar.CodIns = ins.CodIns AND familiar.Rol IN ('Madre', 'Mamá')
                 ORDER BY familiar.CiPersona LIMIT 1) AS CiMama
            FROM `solicitante` titular
            INNER JOIN inssacramento ins ON ins.CodIns = titular.CodIns
            INNER JOIN certificado cert ON cert.CodIns = ins.CodIns
            INNER JOIN detallecert dc ON dc.CodDetCert = cert.CodDetCert
            INNER JOIN parroquia parr ON parr.CodParr = dc.CodParr
            WHERE titular.CiPersona = ? AND titular.Rol IN ('Bautizado', 'Celebrante')
              AND ins.CodSac = 1 AND cert.EstadoCert = 'Activo'
              AND parr.NomParroquia LIKE '%San Pedro%'
              AND (parr.NomParroquia LIKE '%Sacaba%' OR parr.LugParroquia LIKE '%Sacaba%')
            ORDER BY dc.FechaReal DESC, cert.NroCertificado DESC LIMIT 1");
        $stmt->bind_param('s', $ciPersona);
        $stmt->execute();
        $bautizo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $bautizo;
    }

    public function VerCertificadoExtraC($CiCelebrante, $Cel, $codIns = null)
    {
        if ($codIns !== null) {
            $stmt = $this->conn->prepare("SELECT doc.*
                FROM presentaciondoc pre
                INNER JOIN docpresbau doc ON doc.CodPres = pre.CodPres
                INNER JOIN `solicitante` s ON s.CodIns = pre.CodIns
                WHERE pre.CodIns = ? AND pre.CodTDoc = 3
                  AND pre.dequien IN ('Celebrante', 'Confirmante')
                  AND s.CiPersona = ? AND s.Rol = 'Confirmante'
                ORDER BY pre.CodPres DESC LIMIT 1");
            $stmt->bind_param('is', $codIns, $CiCelebrante);
        } else {
            $stmt = $this->conn->prepare("CALL VerCertificadoExtB(?,?)");
            $stmt->bind_param("ss", $CiCelebrante, $Cel);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
}
