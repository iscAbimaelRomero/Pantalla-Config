<?php
// Establecemos la cabecera para indicar que la respuesta siempre será en formato JSON.
header('Content-Type: application/json');

// --- 1. INCLUIR TODOS LOS MODELOS ---
require_once __DIR__ . '/models/EmpresaPModel.php';
require_once __DIR__ . '/models/ConfigClientesModel.php';
require_once __DIR__ . '/models/ConfigImpresionModel.php';
require_once __DIR__ . '/models/ConfigInventarioModel.php';
require_once __DIR__ . '/models/ConfigNotificacionesModel.php';
require_once __DIR__ . '/QueryDB.php'; // Asegúrate de que QueryDB también esté incluido

// --- 2. LEER LA PETICIÓN ENTRANTE ---
$input_data = json_decode(file_get_contents('php://input'), true);

if (!$input_data || !isset($input_data['index'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Petición no válida o índice faltante.']);
    exit();
}

// =========================================================================
// ---- ID DE LA EMPRESA A EDITAR ----
// Como la empresa siempre existirá, usamos un ID fijo.
// En una aplicación real, este ID vendría de la sesión del usuario.
$id_empresa_actual = 1; 
// =========================================================================

$index = $input_data['index'];

// --- 3. DECIDIR QUÉ ACCIÓN REALIZAR ---

if ($index == "2") { // --- ACCIÓN: CARGAR DATOS ---
    try {
        $datos_principal = (new EmpresaPModel())->findById($id_empresa_actual);
        if (!$datos_principal) {
            throw new Exception("La empresa con ID $id_empresa_actual no fue encontrada.");
        }

        // Obtenemos los datos de cada tabla de configuración
        $datos_clientes = (new ConfigClientesModel())->findByEmpresaId($id_empresa_actual);
        $datos_impresion = (new ConfigImpresionModel())->findByEmpresaId($id_empresa_actual);
        $datos_inventario = (new ConfigInventarioModel())->findByEmpresaId($id_empresa_actual);
        $datos_notificaciones = (new ConfigNotificacionesModel())->findByEmpresaId($id_empresa_actual);

        // Unimos todos los arrays en uno solo, verificando que no sean nulos para evitar errores
        $respuesta_completa = $datos_principal;
        if (is_array($datos_clientes)) $respuesta_completa = array_merge($respuesta_completa, $datos_clientes);
        if (is_array($datos_impresion)) $respuesta_completa = array_merge($respuesta_completa, $datos_impresion);
        if (is_array($datos_inventario)) $respuesta_completa = array_merge($respuesta_completa, $datos_inventario);
        if (is_array($datos_notificaciones)) $respuesta_completa = array_merge($respuesta_completa, $datos_notificaciones);

        echo json_encode($respuesta_completa);

    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} elseif ($index == "1") { // --- ACCIÓN: ACTUALIZAR DATOS ---
    $dbTransaction = new QueryDB();
    $dbTransaction->autocommit(false);

    try {
        // Recopilar todos los datos del formulario en el formato requerido por QueryDB
        $datos_principal = [
            'nombre_empresa'    => ['value' => $input_data['nombre_empresa'], 'type' => 's'],
            'nit_empresa'       => ['value' => $input_data['nit_empresa'], 'type' => 's'],
            'domicilio_empresa' => ['value' => $input_data['domicilio_empresa'], 'type' => 's'],
            'nombre_contacto'   => ['value' => $input_data['nombre_contacto'], 'type' => 's'],
            'tel1_contacto'     => ['value' => $input_data['tel1_contacto'], 'type' => 's'],
            'tel2_contacto'     => ['value' => $input_data['tel2_contacto'], 'type' => 's'],
            'email_contacto'    => ['value' => $input_data['email_contacto'], 'type' => 's'],
            'nota_especial'     => ['value' => $input_data['nota_especial'], 'type' => 's'],
            'activa'            => ['value' => $input_data['activa'], 'type' => 'i']
        ];
        $datos_clientes = [
            'dias_ctespr'  => ['value' => $input_data['dias_ctespr'], 'type' => 'i'],
            'nventa_ctespr' => ['value' => ($input_data['notif_ctespr'] ? $input_data['nventa_ctespr'] : '-1'), 'type' => 'i']
        ];
        $datos_impresion = [
            'marginPrint' => ['value' => $input_data['marginPrint'], 'type' => 's']
        ];
        $datos_inventario = [
            'seg_insumos' => ['value' => $input_data['seg_insumos'], 'type' => 'i'],
            'seg_rf'      => ['value' => $input_data['seg_rf'], 'type' => 'i'],
            'seg_crf'     => ['value' => $input_data['seg_crf'], 'type' => 'i']
        ];
        $datos_notificaciones = [
            'notif_cumple'          => ['value' => $input_data['notif_cumple'], 'type' => 'i'],
            'notif_inactiv'         => ['value' => $input_data['notif_inactiv'], 'type' => 'i'],
            'periodSinActiv'        => ['value' => $input_data['periodSinActiv'], 'type' => 'i'],
            'tiempo_notificacion_1' => ['value' => $input_data['tiempo_notificacion_1'], 'type' => 's'],
            'status_send_1'         => ['value' => $input_data['status_send_1'], 'type' => 's'],
            'send_type_1'           => ['value' => $input_data['send_type_1'], 'type' => 's'],
            'tiempo_notificacion_2' => ['value' => $input_data['tiempo_notificacion_2'], 'type' => 's'],
            'status_send_2'         => ['value' => $input_data['status_send_2'], 'type' => 's'],
            'send_type_2'           => ['value' => $input_data['send_type_2'], 'type' => 's']
        ];

        // Ejecutar las actualizaciones en la base de datos para cada tabla
        (new EmpresaPModel())->update($id_empresa_actual, $datos_principal);
        (new ConfigClientesModel())->update($id_empresa_actual, $datos_clientes);
        (new ConfigImpresionModel())->update($id_empresa_actual, $datos_impresion);
        (new ConfigInventarioModel())->update($id_empresa_actual, $datos_inventario);
        (new ConfigNotificacionesModel())->update($id_empresa_actual, $datos_notificaciones);
        
        $dbTransaction->commit();
        echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente.']);

    } catch (Exception $e) {
        $dbTransaction->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Índice de acción no reconocido.']);
}