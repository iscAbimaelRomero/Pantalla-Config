<?php
class ExecutionTimer
{
    private $startTime;
    private $logDir;
    private $exceptionOccurred = false;
    private $exceptionDetails = [];
    private static $instance = null;
    private $typel;

    public function __construct($init_handler_err = true)
    {
        $this->typel = 'php';
        $this->rta = strlen($_SERVER['DOCUMENT_ROOT']) > 0 ? $_SERVER['DOCUMENT_ROOT'] . '/' : '/home/syserv/public_html/';

        if (self::$instance === null) {
            self::$instance = $this;
        }

        $projectRoot = __DIR__ . '/../'; 
        $this->logDir = $projectRoot . 'tmp/logs'; 

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        $this->startTime = microtime(true);

        if ($init_handler_err) {
            set_exception_handler([$this, 'handleUncaughtException']);
            set_error_handler([$this, 'handleError']);
        }
    }

    public static function hasInstance() { return self::$instance !== null; }

    public function __destruct()
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI/unknown';
        $logEntry = [
            'FECHA'            => date('Y-m-d H:i:s'),
            'URL'              => $url,
            'TIEMPO_EJECUCION' => number_format($executionTime, 4, '.', '')
        ];
        if ($this->exceptionOccurred) {
            $this->saveLog(array_merge($logEntry, $this->exceptionDetails), 'err_');
        }
    }

    public function handleUncaughtException($exception)
    {
        $this->exceptionOccurred = true;
        $this->exceptionDetails = [
            "MENSAJE" => $exception->getMessage(),
            "ARCHIVO" => $exception->getFile(),
            "LINEA"   => $exception->getLine(),
        ];
    }

    public function handleError($severity, $message, $file, $line)
    {
        try {
            throw new ErrorException($message, 0, $severity, $file, $line);
        } catch (ErrorException $e) {
            $this->handleUncaughtException($e);
        }
        return true;
    }

    public function saveLog($logEntry = [], $prefix = 'err_', $typel = '')
    {
        if ($typel == '') $typel = $this->typel;
        $date = date('Y-m-d');
        $logFileName = $prefix . $date . '.json';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
        $filePath = $this->logDir . '/' . $logFileName;
        $existingData = [];
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $existingData = json_decode($content, true);
            if (!is_array($existingData)) {
                $existingData = [];
            }
        }

        $POSTy = !empty($_POST) ? $_POST : [];

        // Inicia la sesión si no está activa para poder leer las variables de $_SESSION
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // ======================================================================
        // ----> 2. CAMBIO OBLIGATORIO: Adapta las variables de sesión <----
        // ======================================================================
        // Las siguientes claves de $_SESSION son del proyecto original.
        // REEMPLÁZALAS por las que tú usas en tu sistema de login.
        // Si no usas sesiones o no quieres guardar estos datos, puedes eliminar estas líneas.
        
        $existingData[] = [
            "TYPE"          => $typel,
            "HOUR"          => date("H:i:s"),
            "DETAILS_ERR"   => $logEntry,
            "POST"          => $POSTy,
            
            // --- EJEMPLO DE CÓMO DEBERÍAS ADAPTARLO ---
            "ID_USUARIO"    => !empty($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'N/A',
            "EMAIL_USUARIO" => !empty($_SESSION['email']) ? $_SESSION['email'] : 'N/A',
            "ROL_USUARIO"   => !empty($_SESSION['rol']) ? $_SESSION['rol'] : 'N/A',

            /* --- CLAVES ORIGINALES (BORRA O REEMPLAZA ESTE BLOQUE) ---
            "ID_SUCURSAL"    => !empty($_SESSION['handel']) ? $_SESSION['handel'] : '',
            "ID_EMPRESA"     => !empty($_SESSION['id_empresa_base']) ? $_SESSION['id_empresa_base'] : '',
            "ID_USUARIO_ORIG" => !empty($_SESSION['ID_PERSONAL']) ? $_SESSION['ID_PERSONAL'] : '',
            "NAME_SUC"       => !empty($_SESSION['nombreSucursal_Sel']) ? $_SESSION['nombreSucursal_Sel'] : '',
            "NAME_EMP"       => !empty($_SESSION['nombreEmpresa_Sel']) ? $_SESSION['nombreEmpresa_Sel'] : '',
            "NAME_PERMISO"   => !empty($_SESSION['nombrePermiso']) ? $_SESSION['nombrePermiso'] : '',
            "ALIAS_USR"      => !empty($_SESSION['usuario_Sel']) ? $_SESSION['usuario_Sel'] : '',
            "NAME_USR"       => !empty($_SESSION["datos_sys"]['nombre_usuario']) ? $_SESSION["datos_sys"]['nombre_usuario'] : '',
            */
        ];

        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
    }
}