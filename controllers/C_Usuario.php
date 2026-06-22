<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/entities/Usuario.php';
require_once dirname(__DIR__) . '/models/M_Usuario.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Usuario::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listarUsuarios());
        break;

    case 'crear':
        $tipo_documento = isset($input['tipo_documento']) ? intval($input['tipo_documento']) : 1;
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        $nombres_razon_social = isset($input['nombres_razon_social']) ? trim($input['nombres_razon_social']) : '';
        $apellidos = isset($input['apellidos']) ? trim($input['apellidos']) : '';
        $direccion = isset($input['direccion']) ? trim($input['direccion']) : '';
        $telefono = isset($input['telefono']) ? trim($input['telefono']) : '';
        $id_rol = isset($input['id_rol']) ? intval($input['id_rol']) : 2; // Default to Vendedor
        $username = isset($input['username']) ? trim($input['username']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';

        if (empty($numero_documento) || empty($nombres_razon_social) || empty($username) || empty($password)) {
            echo json_encode(["success" => false, "mensaje" => "Documento, nombre, usuario y contraseña son obligatorios."]);
            exit;
        }

        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $usuario = new Usuario($tipo_documento, $numero_documento, $nombres_razon_social, $apellidos, $direccion, $telefono, $id_rol, $username, $hashedPassword);
        
        if ($model->registrarUsuario($usuario)) {
            echo json_encode(["success" => true, "mensaje" => "Usuario registrado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al registrar el usuario. El usuario o documento podría ya existir."]);
        }
        break;

    case 'actualizar':
        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;
        $tipo_documento = isset($input['tipo_documento']) ? intval($input['tipo_documento']) : 1;
        $numero_documento = isset($input['numero_documento']) ? trim($input['numero_documento']) : '';
        $nombres_razon_social = isset($input['nombres_razon_social']) ? trim($input['nombres_razon_social']) : '';
        $apellidos = isset($input['apellidos']) ? trim($input['apellidos']) : '';
        $direccion = isset($input['direccion']) ? trim($input['direccion']) : '';
        $telefono = isset($input['telefono']) ? trim($input['telefono']) : '';
        $id_rol = isset($input['id_rol']) ? intval($input['id_rol']) : 2;
        $username = isset($input['username']) ? trim($input['username']) : '';

        if ($id_usuario <= 0 || empty($numero_documento) || empty($nombres_razon_social) || empty($username)) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o incompletos."]);
            exit;
        }

        $usuario = new Usuario($tipo_documento, $numero_documento, $nombres_razon_social, $apellidos, $direccion, $telefono, $id_rol, $username, '');
        $usuario->id_usuario = $id_usuario;

        if ($model->actualizarUsuario($usuario)) {
            // Optional: If password is provided, we can update it separately. Let's add password update if not empty!
            $password = isset($input['password']) ? trim($input['password']) : '';
            if (!empty($password)) {
                try {
                    $dbh = Conexion::singleton()->getConexion();
                    $stmt = $dbh->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                    $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id_usuario]);
                } catch (Exception $e) {
                    // Ignore or log error
                }
            }
            echo json_encode(["success" => true, "mensaje" => "Usuario actualizado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar el usuario."]);
        }
        break;

    case 'eliminar':
        $id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : 0;

        if ($id_usuario <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de usuario inválido."]);
            exit;
        }

        // Logic delete (set estado to 0 in personas table)
        if ($model->EliminarUsuario($id_usuario, 0)) {
            echo json_encode(["success" => true, "mensaje" => "Usuario eliminado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar el usuario."]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
