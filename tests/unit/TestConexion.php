<?php
/**
 * Caso de Prueba: Conexión a Base de Datos
 * Proyecto: Sistema Parroquia San Pedro de Sacaba
 * 
 * Verifica que la conexión a la base de datos funcione correctamente
 * y que las tablas principales existan.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class TestConexion extends TestCase
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
     * CP-001: Verificar conexión a la base de datos
     * Resultado esperado: Conexión exitosa sin errores
     */
    public function testConexionExitosa()
    {
        $this->assertNotNull($this->conn, "La conexión no debe ser nula");
        $this->assertEquals(0, $this->conn->connect_errno, "No debe haber errores de conexión");
    }

    /**
     * CP-002: Verificar charset utf8mb4
     * Resultado esperado: Charset configurado correctamente
     */
    public function testCharsetUtf8mb4()
    {
        $result = $this->conn->query("SELECT @@character_set_connection as charset");
        $row = $result->fetch_assoc();
        $this->assertEquals('utf8mb4', $row['charset'], "El charset debe ser utf8mb4");
    }

    /**
     * CP-003: Verificar existencia de tabla persona
     * Resultado esperado: La tabla existe
     */
    public function testTablaPersonaExiste()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'persona'");
        $this->assertGreaterThan(0, $result->num_rows, "La tabla persona debe existir");
    }

    /**
     * CP-004: Verificar existencia de tabla catequista
     * Resultado esperado: La tabla existe
     */
    public function testTablaCatequistaExiste()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'catequista'");
        $this->assertGreaterThan(0, $result->num_rows, "La tabla catequista debe existir");
    }

    /**
     * CP-005: Verificar existencia de tabla certificado
     * Resultado esperado: La tabla existe
     */
    public function testTablaCertificadoExiste()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'certificado'");
        $this->assertGreaterThan(0, $result->num_rows, "La tabla certificado debe existir");
    }

    /**
     * CP-006: Verificar existencia de tabla mensajepublico
     * Resultado esperado: La tabla existe
     */
    public function testTablaMensajePublicoExiste()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'mensajepublico'");
        $this->assertGreaterThan(0, $result->num_rows, "La tabla mensajepublico debe existir");
    }

    /**
     * CP-007: Verificar número de tablas esperadas
     * Resultado esperado: Al menos 30 tablas en la BD
     */
    public function testNumeroTablas()
    {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $row = $result->fetch_assoc();
        $this->assertGreaterThanOrEqual(30, $row['total'], "Debe haber al menos 30 tablas");
    }
}
