<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';
// Requerir PhpSpreadsheet
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'previsualizar':
        if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
            $errCode = isset($_FILES['archivo_excel']) ? $_FILES['archivo_excel']['error'] : 'NO_FILE';
            echo json_encode(['success' => false, 'mensaje' => "Error al subir el archivo (Cod: $errCode). Revise post_max_size o enctype."]);
            exit;
        }

        $tipo_archivo = $_POST['tipo_archivo'] ?? ''; // 'cas' o 'docente'
        if (!in_array($tipo_archivo, ['cas', 'docente'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Tipo de archivo no válido.']);
            exit;
        }

        try {
            $inputFileName = $_FILES['archivo_excel']['tmp_name'];
            $reader = IOFactory::createReaderForFile($inputFileName);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $previewData = [];
            $totalRows = 0;
            
            // Iterar filas (saltamos la cabecera en fila 1)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('A', 'K');
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Verificar si la fila está vacía (usando la columna del DNI)
                $dniIndex = ($tipo_archivo === 'cas') ? 3 : 0;
                if (empty(trim($rowData[$dniIndex] ?? ''))) {
                    continue;
                }

                $totalRows++;
                
                // Extraer solo los 5 primeros para la previsualización
                if (count($previewData) < 5) {
                    $paternoIndex = ($tipo_archivo === 'cas') ? 5 : 1;
                    $maternoIndex = ($tipo_archivo === 'cas') ? 6 : 2;
                    $nombresIndex = ($tipo_archivo === 'cas') ? 7 : 3;
                    $depIndex = ($tipo_archivo === 'cas') ? 9 : 4;

                    $previewData[] = [
                        'dni' => trim($rowData[$dniIndex]),
                        'paterno' => trim($rowData[$paternoIndex] ?? ''),
                        'materno' => trim($rowData[$maternoIndex] ?? ''),
                        'nombres' => trim($rowData[$nombresIndex] ?? ''),
                        'dependencia' => trim($rowData[$depIndex] ?? '')
                    ];
                }
            }

            echo json_encode([
                'success' => true, 
                'total_filas' => $totalRows,
                'preview' => $previewData
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
        break;

    case 'importar':
        if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
            $errCode = isset($_FILES['archivo_excel']) ? $_FILES['archivo_excel']['error'] : 'NO_FILE';
            echo json_encode(['success' => false, 'mensaje' => "Error al subir el archivo (Cod: $errCode). Revise post_max_size o enctype."]);
            exit;
        }

        $tipo_archivo = $_POST['tipo_archivo'] ?? ''; // 'cas' o 'docente'
        if (!in_array($tipo_archivo, ['cas', 'docente'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Tipo de archivo no válido.']);
            exit;
        }

        $db = Conexion::singleton()->getConexion();
        
        try {
            $inputFileName = $_FILES['archivo_excel']['tmp_name'];
            $reader = IOFactory::createReaderForFile($inputFileName);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();

            $id_tipo_trabajador = ($tipo_archivo === 'cas') ? 2 : 1;
            
            $db->beginTransaction();

            $nuevos = 0;
            $actualizados = 0;
            $errores = 0;

            // Preparar sentencias
            $stmtBuscaDep = $db->prepare("SELECT id_dependencia FROM dependencias WHERE nombre = ?");
            $stmtInsertaDep = $db->prepare("INSERT INTO dependencias (nombre, estado) VALUES (?, 1)");
            
            $stmtBuscaPersona = $db->prepare("SELECT id_persona FROM personas WHERE numero_documento = ?");
            $stmtInsertaPersona = $db->prepare("INSERT INTO personas (tipo_documento, numero_documento, nombres_razon_social, apellidos, estado) VALUES (1, ?, ?, ?, 1)");
            $stmtActualizaPersona = $db->prepare("UPDATE personas SET nombres_razon_social = ?, apellidos = ? WHERE id_persona = ?");
            
            $stmtBuscaTrab = $db->prepare("SELECT id_trabajador FROM trabajadores WHERE id_persona = ?");
            $stmtInsertaTrab = $db->prepare("INSERT INTO trabajadores (id_persona, id_tipo_trabajador, id_dependencia, estado) VALUES (?, ?, ?, 1)");
            $stmtActualizaTrab = $db->prepare("UPDATE trabajadores SET id_tipo_trabajador = ?, id_dependencia = ? WHERE id_persona = ?");

            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator('A', 'K');
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                $dniIndex = ($tipo_archivo === 'cas') ? 3 : 0;
                $dni = trim($rowData[$dniIndex] ?? '');
                
                if (empty($dni)) continue;

                $paternoIndex = ($tipo_archivo === 'cas') ? 5 : 1;
                $maternoIndex = ($tipo_archivo === 'cas') ? 6 : 2;
                $nombresIndex = ($tipo_archivo === 'cas') ? 7 : 3;
                $depIndex = ($tipo_archivo === 'cas') ? 9 : 4;

                $apellidos = trim(trim($rowData[$paternoIndex] ?? '') . ' ' . trim($rowData[$maternoIndex] ?? ''));
                $nombres = trim($rowData[$nombresIndex] ?? '');
                $dependenciaNombre = trim($rowData[$depIndex] ?? '');

                // 1. Manejar Dependencia
                $id_dependencia = null;
                if (!empty($dependenciaNombre)) {
                    $stmtBuscaDep->execute([$dependenciaNombre]);
                    $depRow = $stmtBuscaDep->fetch();
                    if ($depRow) {
                        $id_dependencia = $depRow['id_dependencia'];
                    } else {
                        $stmtInsertaDep->execute([$dependenciaNombre]);
                        $id_dependencia = $db->lastInsertId();
                    }
                }

                // 2. Manejar Persona
                $stmtBuscaPersona->execute([$dni]);
                $personaRow = $stmtBuscaPersona->fetch();
                $id_persona = null;

                if ($personaRow) {
                    $id_persona = $personaRow['id_persona'];
                    $stmtActualizaPersona->execute([$nombres, $apellidos, $id_persona]);
                } else {
                    $stmtInsertaPersona->execute([$dni, $nombres, $apellidos]);
                    $id_persona = $db->lastInsertId();
                }

                // 3. Manejar Trabajador
                $stmtBuscaTrab->execute([$id_persona]);
                $trabRow = $stmtBuscaTrab->fetch();

                if ($trabRow) {
                    $stmtActualizaTrab->execute([$id_tipo_trabajador, $id_dependencia, $id_persona]);
                    $actualizados++;
                } else {
                    $stmtInsertaTrab->execute([$id_persona, $id_tipo_trabajador, $id_dependencia]);
                    $nuevos++;
                }
            }

            $db->commit();
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Importación exitosa.',
                'nuevos' => $nuevos,
                'actualizados' => $actualizados
            ]);

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'mensaje' => 'Error al importar: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
?>
