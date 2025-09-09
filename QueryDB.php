<?php
$rta= strlen($_SERVER['DOCUMENT_ROOT']) > 0 ? $_SERVER['DOCUMENT_ROOT'] . '/' : '/home/syserv/public_html/';
include_once('ExecutionTimer.php');

// Verifica el dominio antes de habilitar mysqli_report
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === "syserv.org") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

class ConexionDB{
    private $con;
    var $server;
    var $user;
    var $pass;
    var $base;
    private $logErr;

    //Información para conectar con la base de datos
    function DatConnect(){
        $this->server="localhost"; 
        $this->user="root";
        $this->base="pantalla";
        $this->pass="";        
    }

    public function __construct(){
        $this->logErr = new ExecutionTimer(false);
        try {
            $this->DatConnect();
            $this->con = new mysqli($this->server, $this->user, $this->pass, $this->base);
            $this->con->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            $this->logError('Error en la conexión a la base de datos', $e);
            die("Error de conexión.");
        }
    }

    # Guarda Registro de errores
    function logError($message, $exception ,$texQuery='N/A'){
        $error = [
            'query' => $texQuery,
            'n_error' => $exception->getCode(),
            'MENSAJE' => $message . ': ' . $exception->getMessage(),
            'url' => $exception->getFile(),
            'backtrace' => $exception->getTraceAsString(),
        ];
        $this->logErr->saveLog($error, 'err_', 'mysql');
    }

    function getConexion(){ 
        return $this->con; 
    }

    public function Close(){
        if ($this->con instanceof mysqli) {
            $this->con->close();
        }
    }

}

//Ejecuta consultas SQL tradicionales, No validan parametros de entrada por usuarios
class QueryDB   
{
    private $conexion;
    private $consulta;
    var $stmt;
    private $typeQuer;
    private $texQuery;    

    function __construct(){ 
        $this->conexion= new ConexionDB();      
        $this->consulta = false;
        $this->texQuery='';
    }

    //Cierra la conexión de base de datos
    function __destruct(){ $this->Close(); }

     # cierra la conexion
     public function Close(){
        if (isset($this->stmt) && $this->stmt instanceof mysqli_stmt) {
            $this->stmt->close();
        }
        $this->conexion->Close();
    }

    # Transacciones
    function autocommit($trueOfalse){ $this->conexion->getConexion()->autocommit($trueOfalse); }
    function commit(){ $this->conexion->getConexion()->commit(); }
    function rollback(){ $this->conexion->getConexion()->rollback(); }
    # Fin Transacciones

    # Prepara una consulta parametrizada
    public function prepare($query){
        try {
            $this->texQuery = $query;
            $this->stmt = $this->conexion->getConexion()->prepare($query);            
        } catch (mysqli_sql_exception $e) {
            $this->conexion->logError('Error al preparar consulta', $e, $this->texQuery);
            throw $e;
        }
    }

    # Consulta Parametrizada 
    function execute(){
        $this->consulta = false; 
        try {
            if (!$this->stmt->execute()) {
                throw new mysqli_sql_exception($this->stmt->error, $this->stmt->errno);
            }
            $this->consulta = $this->stmt->get_result();
        } catch (mysqli_sql_exception $e) {
            $this->conexion->logError('Error al ejecutar consulta', $e, $this->texQuery);
            throw $e;
        }        
    }   
    
    # retorna la consulta en forma de result.
    function getResults(){ 
        return $this->consulta; 
    }

    # Devuelve las cantidad de filas afectadas
    public function getAffect(){
        return $this->conexion->getConexion()->affected_rows;
    } 

    # devuelve las cantidad de columnas de la consulta
    public function getNumColumn(){
        return $this->consulta ? $this->consulta->field_count : 0;
    }

    # Datos de Conexión a la base de datos
    function getUsuario()
    { return $this->conexion->user; }

    function getDB()
    { return $this->conexion->base; }

    function getContra()
    { return $this->conexion->pass; }

    function getServidor()
    { return $this->conexion->server; }

    # libera la consulta
    public function Clean(){
        if ($this->consulta instanceof mysqli_result) {
            $this->consulta->free();
        }
    }
    
    # Recupera el último ID insertado (General)
    public function getLastId(){
        return $this->conexion->getConexion()->insert_id;
    }

    # Recupera el último ID insertado (Solo mediante consultas preparadas)
    public function getLasId(){     
        return $this->stmt->insert_id;
    }

    private function sanitizeNumeric($value, $type) {
        if ($type === 'i') {
            return is_numeric($value) ? intval($value) : null;
        } elseif ($type === 'd') {
            return is_numeric($value) ? floatval($value) : null;
        }
        return $value;
    }
    
    # Construye y ejecuta una consulta de Actualización o inserción parametrizada
    private function executePreparedQuery($tableName, $data, $where = null, $isUpdate = false) {
        $columns = [];
        $placeholders = [];
        $types = "";
        $values = [];
    
        foreach ($data as $column => $info) {
            // Validar que $info sea un array y tenga las claves 'value' y 'type'
            if (!is_array($info) || !isset($info['value']) || !isset($info['type'])) {
                // Puedes loggear el error o lanzar una excepción más específica
                $errorMsg = "Datos de columna '$column' mal formados. Se espera ['value' => ..., 'type' => ...]. Recibido: " . var_export($info, true);
                $this->conexion->logError($errorMsg, new Exception($errorMsg), $tableName);
                throw new InvalidArgumentException($errorMsg);
            }

            $columns[] = $column;
            $placeholders[] = "?";
            $types .= $info['type'];
            $values[] = $this->sanitizeNumeric($info['value'],$info['type']);
        }
    
        if (empty($types) && empty($where)) { // Evitar error de bind_param si no hay parámetros
            if ($isUpdate && empty($data)) {
                return 0; // No hay nada que actualizar
            }
            if (!$isUpdate && empty($data)) {
                 throw new InvalidArgumentException("No se proporcionaron datos para la inserción.");
            }
            // Si llega aquí con tipos vacíos pero hay datos, es un error de lógica
            // o un tipo de consulta que no requiere bind_param (pero esto es para preparadas)
        }

        if ($isUpdate) {
            $setClause = implode(", ", array_map(function ($col) {
                return "$col = ?";
            }, $columns));
            $query = "UPDATE $tableName SET $setClause";
        } else {
            $columnsList = implode(", ", $columns);
            $placeholdersList = implode(", ", $placeholders);
            $query = "INSERT INTO $tableName ($columnsList) VALUES ($placeholdersList)";
        }
    
        if ($where) {
            if( $where['clause'] != '' ){
                $query .= " WHERE " . $where['clause'];
                $types .= isset($where['type']) ? $where['type'] : '';
                // Asegúrate de que $where['values'] sea un array, incluso si está vacío
                $values = array_merge($values, isset($where['values']) ? (array)$where['values'] : []);                
            }
        }
    
        $this->prepare($query);
    
        try {        
            // Solo hacer bind_param si hay tipos definidos
            if (!empty($types)) {
                $bindParams = [$types];
                foreach ($values as $key => $valueParam) {
                    $bindParams[] = &$values[$key];
                }
                call_user_func_array([$this->stmt, "bind_param"], $bindParams );
            }
        } catch (mysqli_sql_exception $e) {
            $this->conexion->logError('Error al hacer bind_param en executePreparedQuery', $e, $this->texQuery);
            throw $e;
        } 

        $this->execute();
    
        return $this->getAffect();
    }

    # Ejecuta un comando de inserción con consulta parametrizadas
    function insert( $tabla , $data ){ return $this->executePreparedQuery($tabla, $data); }
    
    # Ejecuta un comando de actualización con consulta parametrizada
    function update( $tabla , $data, $where = null ){ return $this->executePreparedQuery( $tabla , $data, $where, true); }
    
    # Muestra un String con la descripción del último error
    public function getLastError() {
        if ($this->stmt instanceof mysqli_stmt && $this->stmt->errno) {
            return sprintf(
                '[%d] %s (SQLSTATE %s) %s',
                $this->stmt->errno,
                $this->stmt->error,
                $this->stmt->sqlstate ?? 'N/A',
                $this->texQuery ?: ''
            );
        }

        $mysqli = $this->conexion->getConexion();
        if ($mysqli instanceof mysqli && $mysqli->errno) {
            return sprintf(
                '[%d] %s (SQLSTATE %s) %s',
                $mysqli->errno,
                $mysqli->error,
                $mysqli->sqlstate ?? 'N/A',
                $this->texQuery ?: ''
            );
        }

        return '';
    }
}

//Limpia una cadena, de caracteres que pudieran ser inseguros en Base de datos
function cls($str_cad){
    return filter_var(str_replace(array("'",'"','|','\\','`'), '', $str_cad), FILTER_SANITIZE_STRING);
}