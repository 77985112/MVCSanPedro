<?php
require_once '../Conexion/Conexion.php';

class Personal
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function registrarNuevoPersonal($data)
    {
        $usuario = trim((string) ($data['UsuarioPer'] ?? ''));
        $clave = trim((string) ($data['ClavePer'] ?? ''));
        if ($usuario === '' || $clave === '') {
            return false;
        }
        if (strtolower($data['Sexo']) === 'mujer' && in_array($data['CodCar'], [2, 3])) {
            $data['CodCar'] = 1;
        }
        $correo = $data['Correo'] ?? null;
        $fechaNac = ($data['FechaNac'] ?? '') !== '' ? $data['FechaNac'] : null;
        $fechaOrdenacion = ($data['FechaOrdenacion'] ?? '') !== '' ? $data['FechaOrdenacion'] : null;
        $stmt = $this->conn->prepare("CALL RegistrarNuevoPersonal(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssssi",
            $data['Nombre'],
            $data['Paterno'],
            $data['Materno'],
            $data['CiPersonal'],
            $data['Sexo'],
            $fechaNac,
            $data['ContactoPer'],
            $correo,
            $fechaOrdenacion,
            $data['CodCar']
        );

        $this->conn->begin_transaction();
        try {
            if (!$stmt->execute()) {
                $this->conn->rollback();
                return false;
            }
            $stmt->close();

            // El procedimiento guarda el CI y el contacto como credenciales
            // provisionales; se reemplazan por las escritas en el formulario.
            $stmt = $this->conn->prepare(
                "SELECT CodPer FROM `personal` WHERE CiPersonal = ? ORDER BY CodPer DESC LIMIT 1"
            );
            $stmt->bind_param("s", $data['CiPersonal']);
            $stmt->execute();
            $registro = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$registro) {
                $this->conn->rollback();
                return false;
            }

            $stmt = $this->conn->prepare(
                "UPDATE `personal` SET UsuarioPer = ?, ClavePer = ?, DebeCambiarClave = 1 WHERE CodPer = ?"
            );
            $stmt->bind_param("ssi", $usuario, $clave, $registro['CodPer']);
            if (!$stmt->execute()) {
                $this->conn->rollback();
                return false;
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function buscarPersonalPorCi($ci)
    {
        $stmt = $this->conn->prepare("CALL BuscarPersonalPorCi(?)");
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function buscarPersonalPorTexto($texto)
    {
        $stmt = $this->conn->prepare("CALL BuscarPersonalPorTexto(?)");
        $stmt->bind_param("s", $texto);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function contarPersonal()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM `personal`");
        $fila = $result->fetch_assoc();
        return (int) $fila['total'];
    }

    public function listarPersonal($inicio, $limite)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.CiPersonal, p.Nombre, p.Paterno, p.Materno, p.FechaNac, p.FechaOrdenacion,
                    p.ContactoPer, p.Correo, p.Estado, p.FechaIng, p.FechaFinGestion,
                    p.CodCar, c.DescripCar
             FROM `personal` p
             LEFT JOIN `cargo` c ON c.CodCar = p.CodCar
             ORDER BY p.CodCar ASC, p.Paterno ASC, p.Nombre ASC
             LIMIT ?, ?"
        );
        $stmt->bind_param("ii", $inicio, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function PersonalDemas($ciPersonal)
    {
        $stmt = $this->conn->prepare("CALL capturarPersonalExt(?)");
        $stmt->bind_param("s", $ciPersonal);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerOCrearCargo($nombre)
    {
        $stmt = $this->conn->prepare("SELECT CodCar FROM `cargo` WHERE LOWER(DescripCar) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        if ($fila) {
            return (int) $fila['CodCar'];
        }

        $stmt = $this->conn->prepare("INSERT INTO `cargo` (DescripCar) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        if (!$stmt->execute()) {
            return 0;
        }
        return (int) $this->conn->insert_id;
    }

    /** Reemplaza la clave por una nueva de un solo uso y obliga a cambiarla. */
    public function restablecerClave($ciPersonal, $claveHash)
    {
        $stmt = $this->conn->prepare(
            "UPDATE `personal` SET ClavePer = ?, DebeCambiarClave = 1 WHERE CiPersonal = ?"
        );
        $stmt->bind_param("ss", $claveHash, $ciPersonal);
        return $stmt->execute();
    }

    public function modificarPersonalPorCi($data)
    {
        $correo = $data['Correo'] ?? null;
        $fechaNac = ($data['FechaNac'] ?? '') !== '' ? $data['FechaNac'] : null;
        $fechaOrdenacion = ($data['FechaOrdenacion'] ?? '') !== '' ? $data['FechaOrdenacion'] : null;

        // Si el formulario no envía contraseña, se conserva la actual.
        $clave = trim((string) ($data['ClavePer'] ?? ''));
        if ($clave === '') {
            $stmt = $this->conn->prepare("SELECT ClavePer FROM `personal` WHERE CiPersonal = ? ORDER BY CodPer DESC LIMIT 1");
            $stmt->bind_param("s", $data['CiPersonal']);
            $stmt->execute();
            $registroClave = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $clave = (string) ($registroClave['ClavePer'] ?? '');
        }

        $stmt = $this->conn->prepare("CALL ModificarPersonalPorCi(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssssssssssi",
            $data['CiPersonal'],
            $clave,
            $data['Nombre'],
            $data['Paterno'],
            $data['Materno'],
            $data['Sexo'],
            $data['ContactoPer'],
            $correo,
            $fechaNac,
            $fechaOrdenacion,
            $data['UsuarioPer'],
            $data['PerfilPer'],
            $data['FondoPer'],
            $data['FrasePer'],
            $data['Estado'],
            $data['CodCar_2']
        );

        try {
            if (!$stmt->execute()) {
                return false;
            }
        } catch (mysqli_sql_exception $e) {
            return false;
        }

        return true;
    }
}
?>
