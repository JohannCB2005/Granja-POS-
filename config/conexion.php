<?php
class Conexion {
    private static $instancia = null;
    private $dbh;

    private function __construct() {
        try {
            $host = 'localhost';
            $dbname = 'granja_pos';
            $user = 'root';
            $pass = '';
            
            $opciones = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
            );

            $this->dbh = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, $opciones);

        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function singleton() {
        if (!isset(self::$instancia)) {
            $miclase = __CLASS__;
            self::$instancia = new $miclase;
        }
        return self::$instancia;
    }

    public function getConexion() {
        return $this->dbh;
    }
}
?>