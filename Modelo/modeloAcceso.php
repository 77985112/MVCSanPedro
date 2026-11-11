<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

/**
 * Acceso a los permisos de módulos por persona.
 * La tabla permiso_acceso es independiente del resto del esquema: solo
 * guarda una fila por personal con los módulos habilitados.
 */
class ModeloAcceso {
    private $conexion;

    /** Módulos administrables, con la columna real que los representa. */
    public const MODULOS = [
        'inicio' => ['columna' => 'Inicio', 'nombre' => 'Inicio', 'detalle' => 'Panel principal', 'icono' => 'bi-house-door'],
        'persona' => ['columna' => 'Persona', 'nombre' => 'Persona', 'detalle' => 'Datos de personas', 'icono' => 'bi-people'],
        'celebracion' => ['columna' => 'Celebracion', 'nombre' => 'Celebración', 'detalle' => 'Sacramentos y reservas', 'icono' => 'bi-calendar2-check'],
        'coordinador' => ['columna' => 'Coordinador', 'nombre' => 'Coordinador', 'detalle' => 'Catequesis y coordinación', 'icono' => 'bi-person-badge'],
        'certificado' => ['columna' => 'Certificado', 'nombre' => 'Certificado', 'detalle' => 'Emisión de certificados', 'icono' => 'bi-file-earmark-text'],
        'emitidos' => ['columna' => 'Emitidos', 'nombre' => 'Certificado Emitido', 'detalle' => 'Historial de certificados', 'icono' => 'bi-patch-check'],
        'personal' => ['columna' => 'Personal', 'nombre' => 'Personal', 'detalle' => 'Gestión de personal', 'icono' => 'bi-person-gear'],
    ];

    public function __construct() {
        $conexion = new Conexion();
        $this->conexion = $conexion->getConnection();
    }

    /**
     * Permisos iniciales según el cargo, manteniendo el comportamiento que
     * tenía el sistema antes de existir esta pantalla.
     */
    public static function permisosPorDefecto($cargo) {
        $cargo = trim((string) $cargo);
        $esParroco = $cargo === 'Párroco';
        $esPersonal = $cargo === 'Personal';

        return [
            'inicio' => 1,
            'persona' => 1,
            'celebracion' => 1,
            'personal' => $esParroco ? 1 : 0,
            'coordinador' => $esParroco ? 1 : 0,
            'certificado' => 1,
            'emitidos' => $esPersonal ? 0 : 1,
        ];
    }

    /** Devuelve los permisos guardados o los iniciales si todavía no existen. */
    public function obtener($codPer, $cargo = '') {
        $codPer = (int) $codPer;
        // El Párroco es administrador general y siempre conserva todos los módulos.
        if (trim((string) $cargo) === 'Párroco') {
            return self::permisosPorDefecto('Párroco');
        }
        if ($codPer <= 0) {
            return self::permisosPorDefecto($cargo);
        }

        $stmt = $this->conexion->prepare(
            'SELECT Inicio, Persona, Celebracion, Personal, Coordinador, Certificado, Emitidos
             FROM permiso_acceso WHERE CodPer = ? LIMIT 1'
        );
        $stmt->bind_param('i', $codPer);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$fila) {
            return self::permisosPorDefecto($cargo);
        }

        $permisos = [];
        foreach (self::MODULOS as $clave => $meta) {
            $permisos[$clave] = (int) ($fila[$meta['columna']] ?? 0);
        }
        return $permisos;
    }

    /** Guarda los permisos de una persona (crea la fila si no existe). */
    public function guardar($codPer, array $permisos, $cargo = '') {
        $codPer = (int) $codPer;
        if ($codPer <= 0) {
            return false;
        }

        $valores = [];
        foreach (self::MODULOS as $clave => $meta) {
            $valores[$clave] = !empty($permisos[$clave]) ? 1 : 0;
        }

        // Protección adicional del modelo ante llamadas fuera del controlador.
        if (trim((string) $cargo) === 'Párroco') {
            foreach (array_keys(self::MODULOS) as $clave) {
                $valores[$clave] = 1;
            }
        }

        // Inicio siempre queda habilitado (es la página de llegada).
        $valores['inicio'] = 1;
        // Personal solo se puede habilitar para un Párroco.
        if (trim((string) $cargo) !== 'Párroco') {
            $valores['personal'] = 0;
        }

        $stmt = $this->conexion->prepare(
            'INSERT INTO permiso_acceso
                (CodPer, Inicio, Persona, Celebracion, Personal, Coordinador, Certificado, Emitidos, ActualizadoEn)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                Inicio = VALUES(Inicio),
                Persona = VALUES(Persona),
                Celebracion = VALUES(Celebracion),
                Personal = VALUES(Personal),
                Coordinador = VALUES(Coordinador),
                Certificado = VALUES(Certificado),
                Emitidos = VALUES(Emitidos),
                ActualizadoEn = NOW()'
        );
        $stmt->bind_param(
            'iiiiiiii',
            $codPer,
            $valores['inicio'],
            $valores['persona'],
            $valores['celebracion'],
            $valores['personal'],
            $valores['coordinador'],
            $valores['certificado'],
            $valores['emitidos']
        );
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /** Busca la persona por CI para mostrar sus datos en el panel. */
    public function obtenerPersonaPorCi($ci) {
        $stmt = $this->conexion->prepare(
            'SELECT p.CodPer, p.CiPersonal, p.Nombre, p.Paterno, p.Materno, p.UsuarioPer, p.CodCar, c.DescripCar
             FROM `personal` p
             LEFT JOIN `cargo` c ON c.CodCar = p.CodCar
             WHERE p.CiPersonal = ? LIMIT 1'
        );
        $ci = (string) $ci;
        $stmt->bind_param('s', $ci);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $fila ?: null;
    }
}
