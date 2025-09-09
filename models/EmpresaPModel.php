<?php
// models/EmpresaPModel.php
require_once __DIR__ . '/../QueryDB.php';

class EmpresaPModel {
    private $DB;

    public function __construct() {
        $this->DB = new QueryDB();
    }

    public function findById($id_empresa) {
        $sql = "SELECT * FROM empresas_principal WHERE id = ?";
        $this->DB->prepare($sql);
        $this->DB->stmt->bind_param('i', $id_empresa);
        $this->DB->execute();
        return mysqli_fetch_assoc($this->DB->getResults());
    }

    public function update($id_empresa, $datos) {
        return $this->DB->update('empresas_principal', $datos, [
            'clause' => 'id = ?',
            'values' => [$id_empresa],
            'type'   => 'i'
        ]);
    }
}