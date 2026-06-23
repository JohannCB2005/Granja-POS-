<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/entities/Cliente.php';
require_once dirname(__DIR__) . '/models/M_Cliente.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Cliente::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listarClientes());
        break;

    case 'buscar_api_only':
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        
        if (empty($numero_documento) || (strlen($numero_documento) !== 8 && strlen($numero_documento) !== 11)) {
            echo json_encode(["success" => false, "mensaje" => "El número de documento debe tener exactamente 8 u 11 dígitos."]);
            exit;
        }

        // Call apiperu.dev
        $tipo = (strlen($numero_documento) === 8) ? 'dni' : 'ruc';
        $endpoint = "https://apiperu.dev/api/" . $tipo;
        
        // Load secure token
        $apiConfig = require dirname(__DIR__) . '/config/api.php';
        $token = isset($apiConfig['apiperu_token']) ? $apiConfig['apiperu_token'] : '';

        if (empty($token)) {
            echo json_encode(["success" => false, "mensaje" => "Error de configuración: Token de API no configurado en el servidor."]);
            exit;
        }

        $params = json_encode([$tipo => $numero_documento]);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo json_encode(["success" => false, "mensaje" => "Error de conexión con la API de consulta: " . $err]);
            exit;
        }

        $resData = json_decode($response, true);

        if (!$resData || !isset($resData['success']) || !$resData['success']) {
            $msg = isset($resData['message']) ? $resData['message'] : "Documento no encontrado en el padrón de SUNAT/RENIEC.";
            echo json_encode([
                "success" => false, 
                "mensaje" => $msg . " (El origen de datos es el padrón reducido de SUNAT y podría no estar actualizado para ingresos muy recientes)."
            ]);
            exit;
        }

        $apiData = $resData['data'];
        $mapped = [];

        if ($tipo === 'dni') {
            if (isset($apiData['nombre_completo']) && !empty($apiData['nombre_completo'])) {
                $mapped['nombre'] = $apiData['nombre_completo'];
            } else {
                $mapped['nombre'] = trim(($apiData['nombres'] ?? '') . ' ' . ($apiData['apellido_paterno'] ?? '') . ' ' . ($apiData['apellido_materno'] ?? ''));
            }
            $mapped['direccion'] = $apiData['direccion'] ?? '';
            $mapped['tipo_cliente'] = 1;
        } else {
            // RUC
            $estado = isset($apiData['estado']) ? strtoupper(trim($apiData['estado'])) : '';
            $condicion = isset($apiData['condicion']) ? strtoupper(trim($apiData['condicion'])) : '';

            if (!empty($estado) && $estado !== 'ACTIVO') {
                echo json_encode([
                    "success" => false, 
                    "mensaje" => "El contribuyente tiene estado no activo: " . $estado . "."
                ]);
                exit;
            }
            if (!empty($condicion) && $condicion !== 'HABIDO') {
                echo json_encode([
                    "success" => false, 
                    "mensaje" => "El contribuyente tiene condición no habida: " . $condicion . "."
                ]);
                exit;
            }

            $mapped['nombre'] = $apiData['nombre_o_razon_social'] ?? '';
            $mapped['direccion'] = $apiData['direccion'] ?? '';
            $mapped['tipo_cliente'] = strpos($numero_documento, '20') === 0 ? 2 : 1;
        }

        echo json_encode([
            "success" => true,
            "data" => $mapped
        ]);
        break;

    case 'consultar_api':
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        
        if (empty($numero_documento) || (strlen($numero_documento) !== 8 && strlen($numero_documento) !== 11)) {
            echo json_encode(["success" => false, "mensaje" => "El número de documento debe tener exactamente 8 u 11 dígitos."]);
            exit;
        }

        // 1. Search locally first
        $existente = $model->obtenerClientePorDocumento($numero_documento);
        if ($existente) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Cliente encontrado en la base de datos local.",
                "cliente" => $existente
            ]);
            exit;
        }

        // 2. Call apiperu.dev
        $tipo = (strlen($numero_documento) === 8) ? 'dni' : 'ruc';
        $endpoint = "https://apiperu.dev/api/" . $tipo;
        
        // Load secure token
        $apiConfig = require dirname(__DIR__) . '/config/api.php';
        $token = isset($apiConfig['apiperu_token']) ? $apiConfig['apiperu_token'] : '';

        if (empty($token)) {
            echo json_encode(["success" => false, "mensaje" => "Error de configuración: Token de API no configurado en el servidor."]);
            exit;
        }

        $params = json_encode([$tipo => $numero_documento]);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo json_encode(["success" => false, "mensaje" => "Error de conexión con la API de consulta: " . $err]);
            exit;
        }

        $resData = json_decode($response, true);

        if (!$resData || !isset($resData['success']) || !$resData['success']) {
            $msg = isset($resData['message']) ? $resData['message'] : "Documento no encontrado en el padrón de SUNAT/RENIEC.";
            echo json_encode([
                "success" => false, 
                "mensaje" => $msg . " (El origen de datos es el padrón reducido de SUNAT y podría no estar actualizado para ingresos muy recientes)."
            ]);
            exit;
        }

        // 3. Extract and map data based on DNI/RUC
        $apiData = $resData['data'];
        $tipo_documento = ($tipo === 'dni') ? 1 : 2;
        
        $nombres_razon_social = '';
        $apellidos = '';
        $direccion = '';
        $tipo_cliente = 1; // Persona Natural by default

        if ($tipo === 'dni') {
            // DNI mapping: use nombre_completo or concatenate names + paterno + materno
            if (isset($apiData['nombre_completo']) && !empty($apiData['nombre_completo'])) {
                $nombres_razon_social = $apiData['nombre_completo'];
            } else {
                $nombres_razon_social = $apiData['nombres'];
                $apellidos = trim(($apiData['apellido_paterno'] ?? '') . ' ' . ($apiData['apellido_materno'] ?? ''));
            }
            $direccion = $apiData['direccion'] ?? '';
            $tipo_cliente = 1;
        } else {
            // RUC mapping
            // Validar estado (debe ser "ACTIVO") y condicion (debe ser "HABIDO")
            $estado = isset($apiData['estado']) ? strtoupper(trim($apiData['estado'])) : '';
            $condicion = isset($apiData['condicion']) ? strtoupper(trim($apiData['condicion'])) : '';

            if (!empty($estado) && $estado !== 'ACTIVO') {
                echo json_encode([
                    "success" => false, 
                    "mensaje" => "No se puede registrar al cliente. El contribuyente tiene estado: " . $estado . "."
                ]);
                exit;
            }
            if (!empty($condicion) && $condicion !== 'HABIDO') {
                echo json_encode([
                    "success" => false, 
                    "mensaje" => "No se puede registrar al cliente. El contribuyente tiene condición de domicilio: " . $condicion . "."
                ]);
                exit;
            }

            $nombres_razon_social = $apiData['nombre_o_razon_social'] ?? '';
            $direccion = $apiData['direccion'] ?? '';
            // Si el RUC empieza con 20 es Persona Jurídica, si empieza con 10 es Persona Natural
            $tipo_cliente = strpos($numero_documento, '20') === 0 ? 2 : 1;
        }

        // 4. Automatically insert the new client into DB
        $cliente = new Cliente($tipo_documento, $numero_documento, $nombres_razon_social, $apellidos, $direccion, '', $tipo_cliente);
        
        $resultado = $model->registrarCliente($cliente);
        if ($resultado === true) {
            $nuevoCliente = $model->obtenerClientePorDocumento($numero_documento);
            echo json_encode([
                "success" => true,
                "mensaje" => "Cliente consultado y registrado automáticamente.",
                "cliente" => $nuevoCliente
            ]);
        } else {
            $errorMsg = "Se obtuvieron los datos pero no se pudo registrar al cliente en la base de datos local.";
            if (is_string($resultado) && stripos($resultado, 'Duplicate entry') !== false) {
                $errorMsg = "El número de documento '$numero_documento' ya se encuentra registrado.";
            } elseif (is_string($resultado)) {
                $errorMsg = $resultado;
            }
            echo json_encode(["success" => false, "mensaje" => $errorMsg]);
        }
        break;

    case 'crear':
        $tipo_documento = isset($input['tipo_documento']) ? intval($input['tipo_documento']) : 1; // 1 = DNI, 2 = RUC, etc.
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        $nombres_razon_social = isset($input['nombres_razon_social']) ? trim($input['nombres_razon_social']) : '';
        $apellidos = isset($input['apellidos']) ? trim($input['apellidos']) : '';
        $direccion = isset($input['direccion']) ? trim($input['direccion']) : '';
        $telefono = isset($input['telefono']) ? trim($input['telefono']) : '';
        $tipo_cliente = isset($input['tipo_cliente']) ? intval($input['tipo_cliente']) : 1; // e.g. 1 = Natural, 2 = Jurídico

        if (empty($numero_documento) || empty($nombres_razon_social)) {
            echo json_encode(["success" => false, "mensaje" => "N° de documento y Nombres/Razón Social son obligatorios."]);
            exit;
        }

        $cliente = new Cliente($tipo_documento, $numero_documento, $nombres_razon_social, $apellidos, $direccion, $telefono, $tipo_cliente);
        
        $resultado = $model->registrarCliente($cliente);
        if ($resultado === true) {
            $nuevoCliente = $model->obtenerClientePorDocumento($numero_documento);
            echo json_encode([
                "success" => true, 
                "mensaje" => "Cliente registrado con éxito.",
                "cliente" => $nuevoCliente
            ]);
        } else {
            // $resultado contains the DB error message string
            $errorMsg = "Error al registrar el cliente.";
            if (is_string($resultado) && stripos($resultado, 'Duplicate entry') !== false) {
                $errorMsg = "El número de documento '$numero_documento' ya se encuentra registrado. Si el cliente existe, búsquelo por su documento.";
            } elseif (is_string($resultado)) {
                $errorMsg = $resultado;
            }
            echo json_encode(["success" => false, "mensaje" => $errorMsg]);
        }
        break;

    case 'actualizar':
        $id_cliente = isset($input['id_cliente']) ? intval($input['id_cliente']) : 0;
        $tipo_documento = isset($input['tipo_documento']) ? intval($input['tipo_documento']) : 1;
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        $nombres_razon_social = isset($input['nombres_razon_social']) ? trim($input['nombres_razon_social']) : '';
        $apellidos = isset($input['apellidos']) ? trim($input['apellidos']) : '';
        $direccion = isset($input['direccion']) ? trim($input['direccion']) : '';
        $telefono = isset($input['telefono']) ? trim($input['telefono']) : '';
        $tipo_cliente = isset($input['tipo_cliente']) ? intval($input['tipo_cliente']) : 1;

        if ($id_cliente <= 0 || empty($numero_documento) || empty($nombres_razon_social)) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o incompletos."]);
            exit;
        }

        $cliente = new Cliente($tipo_documento, $numero_documento, $nombres_razon_social, $apellidos, $direccion, $telefono, $tipo_cliente);
        $cliente->id_cliente = $id_cliente;

        if ($model->actualizarCliente($cliente)) {
            echo json_encode(["success" => true, "mensaje" => "Cliente actualizado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar el cliente."]);
        }
        break;

    case 'eliminar':
        $id_cliente = isset($input['id_cliente']) ? intval($input['id_cliente']) : 0;

        if ($id_cliente <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de cliente inválido."]);
            exit;
        }

        if ($model->eliminarCliente($id_cliente)) {
            echo json_encode(["success" => true, "mensaje" => "Cliente eliminado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar el cliente."]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
