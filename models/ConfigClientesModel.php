<?php
// models/ConfigClientesModel.php
require_once __DIR__ . '/../QueryDB.php';

class ConfigClientesModel {
    private $DB;
    private $tableName = 'empresas_configuracion_clientes';

    public function __construct() {
        $this->DB = new QueryDB();
    }

    public function findByEmpresaId($empresa_id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE empresa_id = ?";
        $this->DB->prepare($sql);
        $this->DB->stmt->bind_param('i', $empresa_id);
        $this->DB->execute();
        return mysqli_fetch_assoc($this->DB->getResults());
    }
    
    public function update($empresa_id, $datos) {
        return $this->DB->update($this->tableName, $datos, [
            'clause' => 'empresa_id = ?',
            'values' => [$empresa_id],
            'type'   => 'i'
        ]);
    }
}