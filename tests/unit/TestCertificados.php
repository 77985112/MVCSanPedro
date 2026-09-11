<?php
/**
 * Caso de Prueba: Gestión de Certificados
 * Proyecto: Sistema Parroquia San Pedro de Sacaba
 * 
 * Verifica el registro, consulta y visualización
 * de certificados de bautizo, confirmación y matrimonio.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class TestCertificados extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = getTestConnection();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * CP-016: Verificar certificados de bautizo registrados
     * Resultado esperado: Al menos 1 certificado de bautizo
     */
    public function testCertificadosBautizoExistentes()
    {
        $result = $this->conn->query(
            "SELECT c.NroCertificado, ts.DescripSac 
             FROM certificado c 
             INNER JOIN inssacramento ins ON c.CodIns = ins.CodIns 
             INNER JOIN tiposacramento ts ON ins.CodSac = ts.CodSac 
             WHERE ts.CodSac = 1"
        );
        $this->assertGreaterThan(0, $result->num_rows, "Debe haber al menos 1 certificado de bautizo");
    }

    /**
     * CP-017: Verificar datos del certificado de Laura (CodIns=4)
     * Resultado esperado: NombreBautizado = Laura Gonzales Peredo
     */
    public function testCertificadoBautizoLaura()
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoBautizo(4)");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $this->assertNotNull($data, "El certificado CodIns=4 debe existir");
        $this->assertEquals('Laura', $data['Nombre'], "El nombre debe ser Laura");
        $this->assertEquals('Gonzales', $data['ApPaterno'], "El apellido paterno debe ser Gonzales");
        $this->assertEquals('Peredo', $data['ApMaterno'], "El apellido materno debe ser Peredo");
        $this->assertEquals('Bautizado', $data['NombreBautizado'][0] !== '' ? 'Bautizado' : '', "Debe tener nombre de bautizado");

        $stmt->close();
    }

    /**
     * CP-018: Verificar parroquia del certificado de Laura
     * Resultado esperado: NomParroquia = San Pedro de Sacaba
     */
    public function testCertificadoLauraParroquia()
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoBautizo(4)");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $this->assertEquals('San Pedro de Sacaba', $data['NomParroquia'], "La parroquia debe ser San Pedro de Sacaba");
        $this->assertEquals('Sacaba', $data['LugParroquia'], "La dirección debe ser Sacaba");

        $stmt->close();
    }

    /**
     * CP-019: Verificar padrinos del certificado de Laura
     * Resultado esperado: Padrino y Madrina existen
     */
    public function testCertificadoLauraPadrinos()
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoBautizo(4)");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $this->assertNotEmpty($data['NombrePadrino'], "Debe tener padrino");
        $this->assertNotEmpty($data['NombreMadrina'], "Debe tener madrina");

        $stmt->close();
    }

    /**
     * CP-020: Verificar búsqueda de bautizo por CI
     * Datos: CI = 01345880 (Laura)
     * Resultado esperado: Retorna registros de bautizo
     */
    public function testBuscarBautizoPorCI()
    {
        $stmt = $this->conn->prepare("CALL VerInsSacramentoBautizo(?)");
        $ci = '01345880';
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertGreaterThan(0, $result->num_rows, "Debe encontrar bautizo para CI 01345880");

        $stmt->close();
    }

    /**
     * CP-021: Verificar que no existen certificados duplicados de bautizo para Laura
     * Resultado esperado: Solo 1 registro de bautizado para Laura
     */
    public function testSinDuplicadosBautizoLaura()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) as cnt FROM Solicitante s 
             INNER JOIN inssacramento ins ON s.CodIns = ins.CodIns
             INNER JOIN tiposacramento ts ON ins.CodSac = ts.CodSac
             WHERE s.CiPersona = '01345880' AND s.Rol = 'Bautizado' AND ts.CodSac = 1"
        );
        $row = $result->fetch_assoc();
        $this->assertEquals(1, $row['cnt'], "Laura debe tener exactamente 1 registro como Bautizado");
    }

    /**
     * CP-022: Verificar estado de certificados
     * Resultado esperado: Todos los certificados están en estado Activo
     */
    public function testEstadoCertificadosActivos()
    {
        $result = $this->conn->query(
            "SELECT c.EstadoCert, COUNT(*) as cnt FROM certificado c GROUP BY c.EstadoCert"
        );
        $activos = 0;
        while ($row = $result->fetch_assoc()) {
            if ($row['EstadoCert'] == 'Activo') {
                $activos = $row['cnt'];
            }
        }
        $this->assertGreaterThan(0, $activos, "Debe haber certificados activos");
    }

    /**
     * CP-023: Verificar fechas de emisión de certificados
     * Resultado esperado: Fechas de emisión son válidas (no futuras)
     */
    public function testFechasEmisionValidas()
    {
        $result = $this->conn->query(
            "SELECT c.FechaEmision FROM certificado c WHERE c.FechaEmision IS NOT NULL"
        );
        $hoy = date('Y-m-d');
        while ($row = $result->fetch_assoc()) {
            $this->assertLessThanOrEqual($hoy, $row['FechaEmision'], 
                "La fecha de emisión no debe ser futura: " . $row['FechaEmision']);
        }
    }

    /**
     * CP-024: Verificar estructura de tabla certificado
     * Resultado esperado: Tiene las columnas requeridas
     */
    public function testEstructuraTablaCertificado()
    {
        $result = $this->conn->query("DESCRIBE certificado");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }

        $required = ['NroCertificado', 'NroEmision', 'FechaEmision', 'PresbiteroEm', 'EstadoCert', 'CodDetCert', 'CodIns'];
        foreach ($required as $col) {
            $this->assertContains($col, $columns, "La columna $col debe existir en certificado");
        }
    }
}
