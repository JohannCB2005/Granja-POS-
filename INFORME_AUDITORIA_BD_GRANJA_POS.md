# INFORME DE AUDITORÍA INFORMÁTICA: BASE DE DATOS "GRANJA_POS"

**Fecha de auditoría:** 2026-07-02  
**Auditor:** Sistema de Auditoría Automatizada (Agente Kimi Code CLI)  
**Motor de Base de Datos:** MariaDB 10.11.14 (Distribución Ubuntu 24.04 LTS)  
**Base de Datos Auditada:** `granja_pos`  
**Servidor:** `localhost` (127.0.0.1)  
**Puerto:** 3306  
**Alcance:** Diseño lógico y físico, administración de bases, seguridad (autenticación, autorizaciones, cifrado), copias de seguridad y recuperación, rendimiento, replicación y registros de auditoría (audit logs).

---

## (I). ASPECTOS GENERALES

### 1.1. Alcance (Alineado al proporcionado en las instrucciones)
La presente auditoría se centra en el análisis técnico del motor de base de datos MariaDB 10.11.14 que aloja la base de datos `granja_pos`, correspondiente al Sistema de Ventas de Insumos de Granja (POS). El alcance abarca los siguientes dominios de control:

- **Diseño lógico y físico:** Esquema relacional, integridad referencial, tipos de datos, motores de almacenamiento, índices y normalización.
- **Administración de bases de datos:** Gestión de instancias, configuración del servidor, parámetros operativos y mantenimiento.
- **Seguridad:** Autenticación (métodos y plugins), autorizaciones (privilegios y roles), cifrado en tránsito (SSL/TLS) y cifrado en reposo.
- **Copias de seguridad y recuperación:** Estrategias de respaldo, programación de tareas de backup, binlogs y políticas de retención.
- **Rendimiento:** Configuración de buffers, cachés, límites de conexión, logs de consultas lentas y monitoreo.
- **Replicación:** Configuración de topologías maestro-esclavo o grupos de replicación.
- **Registros de auditoría (Audit Logs):** Habilitación de logs generales, de errores, de consultas lentas y plugins de auditoría.

### 1.2. Objetivos
1. Identificar configuraciones inseguras, obsoletas o subóptimas en el motor de base de datos.
2. Evaluar el cumplimiento de estándares de seguridad (ISO/IEC 27001, COBIT, CIS Benchmarks para MySQL/MariaDB).
3. Detectar carencias en mecanismos de backup, recuperación ante desastres y trazabilidad de operaciones.
4. Verificar la integridad del diseño lógico y físico del esquema `granja_pos`.
5. Proporcionar recomendaciones técnicas para la mitigación de riesgos identificados.

### 1.3. Recursos
- **Herramientas de extracción:** Cliente de línea de comandos `mysql`/`mariadb`, utilidades del sistema (`find`, `ls`, `cat`).
- **Fuentes de evidencia:**
  - Variables de sistema de MariaDB (`SHOW VARIABLES`).
  - Esquema de información (`information_schema`).
  - Metadatos de usuarios (`mysql.user`, `mysql.db`).
  - Archivos de configuración (`/etc/mysql/mariadb.conf.d/50-server.cnf`, `/etc/mysql/mariadb.cnf`).
  - Scripts de la aplicación (`config/conexion.php`, `base_datos.sql`).
- **Estándares de referencia:**
  - CIS Benchmarks for MariaDB 10.x
  - ISO/IEC 27001:2022 (Anexos A.8, A.9, A.12, A.16)
  - COBIT 2019 (DSS05, DSS06, APO12, BAI01)
  - Buenas prácticas del proveedor (MariaDB Knowledge Base)

---

## (II). PLANEAMIENTO Y PROGRAMACIÓN DE AI

### 2.1. Recopilación de Información
Se ejecutaron consultas SQL directamente contra el motor de base de datos para extraer evidencia objetiva sobre:
- Versión del motor, ruta de datos (`datadir`) y arquitectura de almacenamiento.
- Configuración de red (`bind-address`, `port`, `skip-networking`).
- Usuarios, plugins de autenticación, hashes de contraseñas y privilegios globales.
- Estado de SSL/TLS (`have_ssl`, `ssl_cert`, `ssl_key`, `require_secure_transport`).
- Estado de logs (`general_log`, `slow_query_log`, `log_bin`, `log_error`, `audit_log`).
- Parámetros de rendimiento (`innodb_buffer_pool_size`, `max_connections`, `tmp_table_size`).
- Estado de replicación (`server_id`, `read_only`, `relay_log`).
- Esquema de la base de datos `granja_pos`: tablas, columnas, claves foráneas, procedimientos almacenados y triggers.
- Archivos de configuración del servidor y tareas programadas del sistema operativo relacionadas con backups.

### 2.2. Identificación de Riesgos Potenciales
| ID | Riesgo | Probabilidad | Impacto |
|----|--------|--------------|---------|
| R1 | Acceso no autorizado por credenciales débiles o ausencia de política de contraseñas | Alta | Crítico |
| R2 | Exposición de datos sensibles por transmisión no cifrada (sin SSL/TLS) | Media | Alto |
| R3 | Pérdida de información por ausencia de estrategia de backups | Alta | Crítico |
| R4 | Imposibilidad de trazabilidad por falta de logs de auditoría | Alta | Alto |
| R5 | Degradación del servicio por configuración de rendimiento por defecto | Media | Medio |
| R6 | Escalación de privilegios por uso de cuenta `root` desde la aplicación | Alta | Crítico |

### 2.3. Objetivos de Control
- **AC-1:** Garantizar que las credenciales de acceso cumplan con políticas de complejidad y rotación.
- **AC-2:** Asegurar que las comunicaciones cliente-servidor estén cifradas mediante TLS 1.2 o superior.
- **AC-3:** Implementar un plan de respaldo automatizado con política de retención y pruebas de restauración.
- **AC-4:** Habilitar mecanismos de registro y auditoría de operaciones críticas.
- **AC-5:** Optimizar la configuración del motor para la carga de trabajo esperada.
- **AC-6:** Aplicar el principio de mínimo privilegio en las cuentas de base de datos.

### 2.4. Determinación de los Procedimientos de Control
- Revisión de configuración de autenticación y plugins de seguridad.
- Verificación de estado de cifrado en tránsito (SSL/TLS).
- Inspección de tareas programadas (`cron`) y scripts de backup.
- Análisis de variables de sistema relacionadas con logging y auditoría.
- Evaluación de parámetros de rendimiento contra recomendaciones del proveedor.
- Revisión de permisos a nivel de usuario, base de datos y tabla.

### 2.5. Pruebas a Realizar
| Prueba | Descripción | Evidencia Esperada |
|--------|-------------|-------------------|
| P1 | Verificar usuarios y plugins de autenticación | `SELECT ... FROM mysql.user` |
| P2 | Verificar estado SSL/TLS | `SHOW VARIABLES LIKE '%ssl%'` |
| P3 | Verificar existencia de backups programados | Inspección de `cron` y directorios de backup |
| P4 | Verificar logs de auditoría habilitados | `SHOW VARIABLES LIKE 'general_log'`, `log_bin`, `audit_log` |
| P5 | Verificar parámetros de rendimiento | `SHOW VARIABLES LIKE 'innodb_buffer_pool_size'` |
| P6 | Verificar configuración de replicación | `SHOW VARIABLES LIKE 'server_id'`, `log_bin` |
| P7 | Verificar integridad referencial del esquema | `information_schema.key_column_usage` |
| P8 | Verificar cuenta de conexión de la aplicación | `config/conexion.php` |

---

## (III). EJECUCIÓN DE LA AI

### 3.1. Resultados Obtenidos

#### 3.1.1. Diseño Lógico y Físico
- **Motor de almacenamiento:** Todas las tablas de `granja_pos` utilizan `ENGINE=InnoDB` (evidencia verificada en `information_schema.tables`). Esto garantiza soporte para transacciones ACID, integridad referencial y recuperación ante fallos.
- **Integridad referencial:** Se identificaron 9 restricciones de clave foránea activas, vinculando correctamente las tablas transaccionales (`ventas`, `detalle_ventas`) con las tablas maestras (`personas`, `usuarios`, `clientes`, `insumos`, `categorias`, `roles`, `unidades_medida`).
- **Conjunto de caracteres:** El servidor y la base de datos están configurados con `utf8mb4` y `collation utf8mb4_general_ci`, lo cual es una buena práctica para soporte multilingüe.
- **Tipos de datos:** El campo `password` en la tabla `usuarios` es `VARCHAR(255)`, lo cual es adecuado para almacenar hashes modernos (se verificó que los hashes presentes utilizan el formato `$2y$10$...`, correspondiente a `bcrypt`).
- **Procedimientos almacenados:** Existen 4 procedimientos almacenados (`sp_anular_venta`, `sp_registrar_cliente`, `sp_registrar_usuario`, `sp_registrar_venta`). No se encontraron triggers ni funciones definidas por el usuario en el esquema.
- **Normalización:** El diseño presenta una normalización aceptable, separando entidades en tablas independientes (`personas`, `roles`, `categorias`, `unidades_medida`) y utilizando tablas de subtipo (`usuarios`, `clientes`) vinculadas a `personas`.

#### 3.1.2. Administración de Bases de Datos
- **Versión del motor:** MariaDB 10.11.14 (Distribución oficial de Ubuntu 24.04 LTS).
- **Directorio de datos (`datadir`):** `/var/lib/mysql/`
- **Configuración de red:**
  - `bind-address = 127.0.0.1` (solo escucha conexiones locales).
  - `port = 3306`.
  - `skip-networking = OFF` (permitiendo conexiones TCP/IP locales).
  - `skip-name-resolve = OFF` (resolución DNS habilitada, lo cual puede introducir latencia).
- **Parámetros de rendimiento clave:**
  - `innodb_buffer_pool_size = 134217728` (128 MB).
  - `key_buffer_size = 134217728` (128 MB).
  - `tmp_table_size = 16777216` (16 MB).
  - `max_heap_table_size = 16777216` (16 MB).
  - `max_connections = 151`.
  - `max_allowed_packet = 16777216` (16 MB).
  - `sort_buffer_size = 2097152` (2 MB).
- **Transacciones:**
  - `tx_isolation = REPEATABLE-READ`.
  - `autocommit = ON`.
  - `innodb_flush_log_at_trx_commit = 1` (buena práctica para durabilidad).

#### 3.1.3. Seguridad

##### 3.1.3.1. Autenticación
- **Usuarios existentes:**
  - `root@localhost` (plugin `mysql_native_password`, contraseña NO expirada, `authentication_string` vacío en la salida, pero el acceso fue posible, indicando configuración local sin contraseña o socket).
  - `mariadb.sys@localhost` (cuenta de sistema interna).
  - `mysql@localhost` (cuenta de sistema interna).
- **Plugin de autenticación:** `mysql_native_password` está en uso. Aunque funcional, MariaDB recomienda migrar a `ed25519` o `caching_sha2_password` para mayor seguridad.
- **Política de contraseñas:** No se detectó el plugin `validate_password` activo (`SHOW VARIABLES LIKE 'validate_password%'` no retornó resultados). No existe política de complejidad, longitud mínima ni rotación forzada.
- **Conexión de la aplicación:** El archivo `config/conexion.php` utiliza explícitamente el usuario `root` con contraseña vacía (`$pass = ''`) para conectarse a la base de datos. Esto representa una vulnerabilidad crítica de escalación de privilegios.

##### 3.1.3.2. Autorizaciones
- **Privilegios de `root@localhost`:** `GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION`.
- **Privilegios a nivel de tabla:** La consulta a `information_schema.table_privileges` para `granja_pos` no retornó filas, indicando que no existen permisos granulares definidos; todos los accesos se manejan a nivel global.
- **Principio de mínimo privilegio:** No se cumple. La aplicación se conecta con `root`, lo cual otorga capacidad de destrucción total de todas las bases de datos del servidor.

##### 3.1.3.3. Cifrado
- **SSL/TLS:**
  - `have_ssl = DISABLED`.
  - `ssl_ca`, `ssl_cert`, `ssl_key` están vacíos.
  - `require_secure_transport = OFF`.
  - En el archivo `50-server.cnf`, las directivas SSL están comentadas (`#ssl-ca = ...`, `#ssl-cert = ...`, `#ssl-key = ...`, `#require-secure-transport = on`).
- **Cifrado en reposo (TDE):** No se detectó configuración de cifrado de tablespaces InnoDB (`innodb_encrypt_tables`, `innodb_encrypt_log`, `innodb_encrypt_temporary_tables`).
- **Conclusión de cifrado:** Las comunicaciones entre la aplicación y la base de datos son completamente en texto plano. No existe cifrado de datos en reposo.

#### 3.1.4. Copias de Seguridad y Recuperación
- **Binlog (logs binarios):** `log_bin = OFF`. No se generan logs binarios, lo cual impide la recuperación a un punto en el tiempo (PITR) y la replicación.
- **Tareas programadas (`cron`):** No se encontraron tareas `cron` relacionadas con MySQL/MariaDB ni scripts de backup en `/etc/cron.d/`, `/etc/cron.daily/`, `/etc/cron.weekly/` ni `/var/spool/cron`.
- **Scripts de backup manuales:** No se detectaron scripts de respaldo en el sistema de archivos accesible.
- **Política de retención:** `expire_logs_days = 10` está configurado en `50-server.cnf`, pero al estar `log_bin = OFF`, esta directiva no tiene efecto.
- **Conclusión de backups:** No existe estrategia automatizada ni manual verificable de copias de seguridad para la base de datos `granja_pos`.

#### 3.1.5. Rendimiento
- **Buffer pool InnoDB:** Configurado en 128 MB (`innodb_buffer_pool_size = 134217728`). Para un entorno de producción, este valor es extremadamente bajo. MariaDB recomienda asignar aproximadamente el 70-80% de la RAM disponible al buffer pool.
- **Query cache:** `query_cache_type = OFF`, `query_cache_size = 1048576` (1 MB). El query cache está deshabilitado, lo cual es adecuado para MariaDB 10.11 (el query cache fue descontinuado en versiones recientes).
- **Logs de consultas lentas:** `slow_query_log = OFF`. No existe monitoreo de consultas de bajo rendimiento.
- **Conexiones:** `max_connections = 151` (valor por defecto), `back_log = 80`, `max_connect_errors = 100`.
- **Conclusión de rendimiento:** La configuración es esencialmente la de instalación por defecto, sin ajustes para carga de producción.

#### 3.1.6. Replicación
- **Server ID:** `server_id = 1` (valor por defecto).
- **Modo solo lectura:** `read_only = OFF`.
- **Relay log:** Vacío (`relay_log = `).
- **Workers de replicación paralela:** `slave_parallel_workers = 0`.
- **Conclusión de replicación:** No existe configuración de replicación maestro-esclavo ni topología de alta disponibilidad.

#### 3.1.7. Registros de Auditoría (Audit Logs)
- **Log general de consultas:** `general_log = OFF`.
- **Log de errores:** `log_error = ` (vacío). En sistemas con `systemd`, los errores se dirigen a `journald`, pero no existe archivo de log de errores dedicado en `/var/log/mysql/` (el directorio no existe).
- **Log de consultas lentas:** `slow_query_log = OFF`.
- **Log binario:** `log_bin = OFF`.
- **Plugin de auditoría:** No se detectó ningún plugin de tipo `AUDIT` en `information_schema.PLUGINS`. El directorio de plugins es `/usr/lib/mysql/plugin/`.
- **Conclusión de auditoría:** No existe ningún mecanismo de registro de operaciones SQL ni de auditoría de seguridad. Es imposible rastrear quién ejecutó qué consulta y cuándo.

---

### 3.2. Conclusiones y Comentarios

El análisis técnico revela que la instancia de MariaDB 10.11.14 que aloja la base de datos `granja_pos` se encuentra en una configuración de **instalación por defecto**, con múltiples carencias críticas en seguridad, disponibilidad y trazabilidad. Si bien el diseño lógico del esquema es funcional y utiliza InnoDB correctamente, la capa de administración y seguridad presenta vulnerabilidades graves que exponen al sistema a riesgos de confidencialidad, integridad y disponibilidad.

Los puntos más críticos identificados son:
1. **Conexión de la aplicación con usuario `root` y sin contraseña.**
2. **Ausencia total de cifrado SSL/TLS** para las comunicaciones.
3. **No existen copias de seguridad automatizadas** ni estrategia de recuperación.
4. **Todos los logs de auditoría y monitoreo están deshabilitados**, dejando al sistema ciego ante incidentes.
5. **Configuración de rendimiento por defecto**, inadecuada para un entorno productivo.

---

## (IV). ELABORACIÓN DEL INFORME

### 4.1. Redacción del Informe

#### 4.1.1. Hechos Encontrados (problemas, hallazgos o desviaciones técnicas)

---

##### HALLAZGO 1: Aplicación conectada con cuenta ROOT sin contraseña

**a) Descripción técnica de la carencia:**
El archivo de configuración de la aplicación (`config/conexion.php`) establece la conexión a la base de datos utilizando el usuario `root` con una contraseña vacía (`$pass = ''`). Además, el usuario `root@localhost` posee el privilegio `GRANT ALL PRIVILEGES ON *.* ... WITH GRANT OPTION`, lo cual otorga control total sobre todas las bases de datos del servidor, incluyendo la capacidad de crear, modificar y eliminar usuarios, bases de datos y tablas del sistema.

**b) Desviación normativa:**
- **CIS Benchmark for MariaDB 10.x:** Recomienda restringir el uso de la cuenta `root` y crear cuentas dedicadas con privilegios mínimos para cada aplicación.
- **ISO/IEC 27001:2022 (A.8.2 / A.9.2):** Exige la gestión de derechos de acceso y el principio de mínimo privilegio.
- **COBIT 2019 (DSS05.04):** Gestionar el acceso de seguridad para garantizar que solo usuarios autorizados tengan acceso.

**c) Recomendación de mejora o control propuesto:**
1. Crear un usuario de base de datos exclusivo para la aplicación (ej. `granja_app@localhost`).
2. Otorgar únicamente los privilegios `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `EXECUTE` sobre la base de datos `granja_pos`.
3. Revocar cualquier privilegio global innecesario.
4. Asignar una contraseña robusta (mínimo 16 caracteres, alfanumérica con símbolos) almacenada de forma segura (fuera del control de versiones, en variables de entorno o gestor de secretos).
5. Actualizar `config/conexion.php` para utilizar la nueva cuenta.

---

##### HALLAZGO 2: Ausencia de política de complejidad de contraseñas

**a) Descripción técnica de la carencia:**
No se detectó el plugin `validate_password` ni ninguna variable de configuración relacionada (`validate_password_length`, `validate_password_policy`, etc.) en el servidor. Esto permite la creación de usuarios con contraseñas débiles o vacías sin restricciones.

**b) Desviación normativa:**
- **CIS Benchmark for MariaDB:** Recomienda instalar y configurar `validate_password` para exigir complejidad mínima.
- **ISO/IEC 27001:2022 (A.8.5):** Requiere el uso de contraseñas seguras y su gestión adecuada.
- **COBIT 2019 (DSS05.05):** Gestionar las credenciales de seguridad.

**c) Recomendación de mejora o control propuesto:**
1. Instalar y activar el plugin `validate_password` (o `simple_password_check` en MariaDB).
2. Configurar una política mínima: longitud de 12+ caracteres, al menos una mayúscula, una minúscula, un número y un símbolo.
3. Implementar rotación periódica de contraseñas para cuentas administrativas.

---

##### HALLAZGO 3: Comunicaciones sin cifrado SSL/TLS

**a) Descripción técnica de la carencia:**
La variable `have_ssl` tiene el valor `DISABLED`. Los parámetros `ssl_ca`, `ssl_cert`, `ssl_key` y `require_secure_transport` están vacíos o en `OFF`. Las directivas correspondientes en `/etc/mysql/mariadb.conf.d/50-server.cnf` se encuentran comentadas. Esto implica que todas las conexiones cliente-servidor (incluyendo la de la aplicación PHP) se transmiten en texto plano, susceptible a ataques de escucha pasiva (sniffing) en la red local.

**b) Desviación normativa:**
- **MariaDB Knowledge Base:** "Securing Connections for Client and Server" recomienda habilitar SSL/TLS obligatoriamente.
- **ISO/IEC 27001:2022 (A.8.24):** Cifrado de la información para proteger la confidencialidad de los datos en tránsito.
- **COBIT 2019 (DSS05.02):** Gestionar los servicios de seguridad de la red.

**c) Recomendación de mejora o control propuesto:**
1. Generar un par de claves y certificado autofirmado (o utilizar un certificado de una CA interna/externa).
2. Configurar en `50-server.cnf`:
   ```ini
   ssl-ca   = /etc/mysql/cacert.pem
   ssl-cert = /etc/mysql/server-cert.pem
   ssl-key  = /etc/mysql/server-key.pem
   require-secure-transport = on
   ```
3. Asegurar que los permisos de los archivos `.pem` sean `600` y propiedad del usuario `mysql`.
4. Verificar con `SHOW STATUS LIKE 'Ssl_cipher';` que las conexiones activas utilizan un cifrado válido.

---

##### HALLAZGO 4: No existen copias de seguridad automatizadas

**a) Descripción técnica de la carencia:**
No se encontraron tareas programadas en `cron` (ni en `/etc/cron.d/`, `/etc/cron.daily/`, `/etc/cron.weekly/` ni `/var/spool/cron`) relacionadas con la ejecución de respaldos de la base de datos. No existen scripts de backup en rutas estándar. El motor tiene `log_bin = OFF`, lo cual impide la recuperación a un punto en el tiempo (PITR).

**b) Desviación normativa:**
- **ISO/IEC 27001:2022 (A.8.13):** Copias de seguridad de la información.
- **COBIT 2019 (DSS05.06):** Gestionar la continuidad de la seguridad.
- **CIS Benchmark for MariaDB:** Recomienda establecer un plan de backup y recuperación documentado y probado.

**c) Recomendación de mejora o control propuesto:**
1. Implementar un script de backup automatizado (ej. utilizando `mariabackup` o `mysqldump`) que se ejecute diariamente vía `cron`.
2. Habilitar `log_bin` para permitir PITR:
   ```ini
   log_bin = /var/log/mysql/mysql-bin.log
   expire_logs_days = 10
   max_binlog_size = 100M
   ```
3. Almacenar las copias de seguridad en un medio externo o en la nube (off-site).
4. Documentar y probar periódicamente los procedimientos de restauración (DR drills).

---

##### HALLAZGO 5: Logs de auditoría y monitoreo completamente deshabilitados

**a) Descripción técnica de la carencia:**
Todas las fuentes de registro operativo y de seguridad se encuentran apagadas:
- `general_log = OFF`
- `slow_query_log = OFF`
- `log_bin = OFF`
- `log_error = ` (vacío, sin archivo dedicado)
- No existe plugin de auditoría (`information_schema.PLUGINS` no retorna entradas de tipo `AUDIT`).
- El directorio `/var/log/mysql/` no existe en el sistema.
Esto impide cualquier tipo de trazabilidad forense, detección de intrusiones o análisis de rendimiento post-mortem.

**b) Desviación normativa:**
- **ISO/IEC 27001:2022 (A.8.15):** Registro de actividades.
- **COBIT 2019 (DSS05.07):** Gestionar la seguridad de los registros (logs).
- **CIS Benchmark for MariaDB:** Recomienda habilitar logs de errores, consultas lentas y considerar el uso de plugins de auditoría.

**c) Recomendación de mejora o control propuesto:**
1. Crear el directorio `/var/log/mysql/` y asignar permisos al usuario `mysql`.
2. Habilitar los logs esenciales en `50-server.cnf`:
   ```ini
   log_error                = /var/log/mysql/error.log
   general_log              = 1
   general_log_file         = /var/log/mysql/mysql.log
   slow_query_log           = 1
   slow_query_log_file      = /var/log/mysql/mariadb-slow.log
   long_query_time          = 2
   log_queries_not_using_indexes = 1
   ```
3. Evaluar la instalación del plugin `server_audit` (disponible en MariaDB) para registrar conexiones, consultas DDL y DML críticas.
4. Configurar la rotación de logs mediante `logrotate` para evitar el consumo excesivo de disco.

---

##### HALLAZGO 6: Configuración de rendimiento por defecto e inadecuada para producción

**a) Descripción técnica de la carencia:**
El parámetro `innodb_buffer_pool_size` está configurado en 128 MB, que es el valor mínimo por defecto de MariaDB. Para un entorno productivo, este valor debería representar entre el 70% y el 80% de la memoria RAM disponible del servidor. Adicionalmente, `slow_query_log = OFF` impide la identificación proactiva de cuellos de botella de rendimiento.

**b) Desviación normativa:**
- **MariaDB Knowledge Base:** "InnoDB System Variables" recomienda ajustar `innodb_buffer_pool_size` al mayor porcentaje posible de RAM.
- **COBIT 2019 (DSS05.01):** Gestionar el rendimiento y la capacidad.
- **ISO/IEC 27001:2022 (A.8.6):** Gestión de la capacidad.

**c) Recomendación de mejora o control propuesto:**
1. Calcular y ajustar `innodb_buffer_pool_size` según la RAM disponible (ej. si el servidor tiene 4 GB, asignar ~3 GB).
2. Habilitar `slow_query_log` con `long_query_time = 2` segundos para detectar consultas ineficientes.
3. Monitorear periódicamente el estado del buffer pool con `SHOW ENGINE INNODB STATUS;`.
4. Considerar el uso de `performance_schema` para diagnósticos avanzados.

---

##### HALLAZGO 7: Ausencia de replicación y alta disponibilidad

**a) Descripción técnica de la carencia:**
No existe configuración de replicación. `log_bin = OFF`, `relay_log` está vacío, `read_only = OFF` y `slave_parallel_workers = 0`. En caso de fallo catastrófico del servidor primario, no existe un nodo secundario al cual conmutar el servicio.

**b) Desviación normativa:**
- **ISO/IEC 27001:2022 (A.8.13 / A.8.14):** Copias de seguridad y redundancia de equipos de procesamiento de información.
- **COBIT 2019 (DSS05.06):** Gestionar la continuidad de la seguridad.

**c) Recomendación de mejora o control propuesto:**
1. Evaluar la implementación de una topología de replicación maestro-esclavo (asíncrona) o maestro-maestro.
2. Habilitar `log_bin` y asignar `server_id` únicos a cada nodo.
3. Considerar soluciones de clustering nativas de MariaDB como Galera Cluster para alta disponibilidad síncrona.
4. Documentar el procedimiento de failover manual o automatizado.

---

##### HALLAZGO 8: Ausencia de cifrado de datos en reposo (TDE)

**a) Descripción técnica de la carencia:**
No se detectó configuración de cifrado transparente de datos (TDE) para los tablespaces de InnoDB. Las variables `innodb_encrypt_tables`, `innodb_encrypt_log` y `innodb_encrypt_temporary_tables` no están configuradas o tienen valores por defecto que no activan el cifrado. Los archivos de datos en `/var/lib/mysql/` son legibles por el sistema operativo si se obtiene acceso físico o de superusuario.

**b) Desviación normativa:**
- **ISO/IEC 27001:2022 (A.8.24):** Cifrado de la información.
- **COBIT 2019 (DSS05.02):** Gestionar los servicios de seguridad de la red y almacenamiento.
- **Políticas internas de protección de datos:** Cualquier sistema que almacene información de clientes y transacciones debe cifrar los datos en reposo.

**c) Recomendación de mejora o control propuesto:**
1. Evaluar la habilitación de `innodb_encrypt_tables = ON` si la versión de MariaDB y la edición lo soportan (disponible desde MariaDB 10.1 con el plugin `file_key_management` o `aws_key_management`).
2. Como alternativa inmediata, implementar cifrado a nivel de sistema de archivos (LUKS/dm-crypt) para el volumen que contiene `/var/lib/mysql/`.
3. Asegurar que las copias de seguridad también estén cifradas.

---

##### HALLAZGO 9: Uso del plugin `mysql_native_password` (obsoleto)

**a) Descripción técnica de la carencia:**
Todas las cuentas de usuario (`root`, `mariadb.sys`, `mysql`) utilizan el plugin de autenticación `mysql_native_password`. Este método utiliza el algoritmo SHA-1 para el hash de la contraseña, el cual se considera criptográficamente débil frente a ataques de fuerza bruta modernos. MariaDB recomienda migrar a métodos más robustos.

**b) Desviación normativa:**
- **CIS Benchmark for MariaDB:** Recomienda utilizar plugins de autenticación modernos.
- **ISO/IEC 27001:2022 (A.8.5):** Uso de mecanismos de autenticación seguros.
- **NIST SP 800-63B:** Desaconseja el uso de algoritmos de hash débiles para credenciales.

**c) Recomendación de mejora o control propuesto:**
1. Migrar los usuarios al plugin `ed25519` (nativo de MariaDB y altamente seguro) o `caching_sha2_password`.
2. Actualizar los clientes/aplicaciones para que soporten el nuevo método de autenticación.
3. Forzar la actualización de contraseñas de todos los usuarios existentes tras la migración.

---

##### HALLAZGO 10: `local_infile` habilitado y `secure_file_priv` vacío

**a) Descripción técnica de la carencia:**
La variable `local_infile` está en `ON`, permitiendo a los clientes cargar archivos locales al servidor mediante la sentencia `LOAD DATA LOCAL INFILE`. Además, `secure_file_priv` está vacío, lo que significa que no hay restricción sobre los directorios desde los cuales se pueden leer o escribir archivos mediante `SELECT ... INTO OUTFILE` o `LOAD DATA INFILE`.

**b) Desviación normativa:**
- **CIS Benchmark for MySQL/MariaDB:** Recomienda deshabilitar `local_infile` y establecer `secure_file_priv` a un directorio controlado.
- **ISO/IEC 27001:2022 (A.8.1):** Activos de información responsables de la organización.
- **OWASP Top 10:** Riesgo de lectura arbitraria de archivos del sistema (Path Traversal / LFI).

**c) Recomendación de mejora o control propuesto:**
1. Establecer `local_infile = 0` en la configuración del servidor y del cliente.
2. Configurar `secure_file_priv = /var/lib/mysql-files/` (o un directorio dedicado y restringido).
3. Asegurar que el directorio especificado tenga permisos estrictos (`700`, propiedad `mysql:mysql`).

---

### 4.2. Presentación del Informe

El presente informe técnico preliminar ha sido elaborado con base en la evidencia extraída directamente del motor de base de datos MariaDB 10.11.14 y del sistema operativo subyacente (Ubuntu 24.04 LTS). Todos los hallazgos han sido verificados mediante comandos SQL y de sistema ejecutados en el entorno local.

**Resumen Ejecutivo de Hallazgos:**

| Categoría | Hallazgos Críticos | Hallazgos Altos | Hallazgos Medios |
|-----------|-------------------|-----------------|------------------|
| Seguridad | 3 (H1, H3, H10) | 2 (H2, H9) | 1 (H8) |
| Backup/Recuperación | 1 (H4) | 0 | 0 |
| Auditoría/Logs | 1 (H5) | 0 | 0 |
| Rendimiento | 0 | 1 (H6) | 0 |
| Replicación/HA | 0 | 1 (H7) | 0 |

**Próximos Pasos Recomendados:**
1. **Inmediato (0-7 días):** Crear un usuario dedicado para la aplicación, asignar contraseña a `root`, deshabilitar `local_infile`.
2. **Corto plazo (1-4 semanas):** Habilitar SSL/TLS, implementar backups automatizados, habilitar logs de errores y consultas lentas.
3. **Mediano plazo (1-3 meses):** Ajustar parámetros de rendimiento, implementar plugin de auditoría, evaluar replicación o clustering.
4. **Continuo:** Realizar revisiones periódicas de privilegios, pruebas de restauración de backups y análisis de logs de seguridad.

---

**Fin del Informe**
