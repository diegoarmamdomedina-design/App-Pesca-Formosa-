# Manual de exposición — Backend App Pesca Formosa

Alumno: Diego Medina. Materia: Programación III.  
Repo: `c:\xampp\htdocs\AppPescaFormosa`. Base: `pesca_formosa` (XAMPP / MariaDB).  
Base URL: `http://localhost/AppPescaFormosa/api`

`_docs_repan` **no es de este proyecto**. No se explica.

---

## 1. Qué es y qué no es

Esto es la **capa de lógica y datos** de una app Android de pesca en Formosa. El celular (React Native, todavía no) habla HTTP/JSON con esta API. La API habla SQL con MySQL.

**No es:** frontend, pasarela de pagos, iOS, chat, nube. Eso el Word lo excluye.

**Tres capas (cliente-servidor):**

1. Presentación — app (después).
2. Negocio — PHP 8.2 sobre Apache (XAMPP).
3. Datos — MySQL `pesca_formosa`.

El DET del Word nombraba Node + Express y puerto 3000. El grupo corre **PHP en Apache** porque el backend debe vivir en XAMPP (Apache + MySQL), sin nube. Las **rutas** sí son las de la sección 8 (`/auth/login`, no `/usuarios/login.php` hacia afuera). `firebase/php-jwt` sustituye a `jsonwebtoken`. `password_hash` (bcrypt) sustituye a `bcryptjs`. Axios queda para el frontend.

---

## 2. Recorrido de un request (el “esqueleto”)

Ejemplo: `POST /api/auth/login`

1. Apache recibe `http://localhost/AppPescaFormosa/api/auth/login`.
2. `.htaccess`: si no es un archivo real, reescribe a `index.php`. Pasa el header `Authorization` (Apache a veces lo come).
3. `index.php` saca la ruta (`auth/login`) y el método (`POST`).
4. `require` de `api/auth/login.php`.
5. Ese script: JSON de entrada → SQL preparado → bcrypt → JWT 24 h → JSON de salida.
6. Thunder / Axios leen el JSON.

Si la URL fuera un archivo existente (`especies/listar.php`), Apache lo sirve directo. El contrato público es el del Word **sin** `.php`.

---

## 3. Tecnologías (qué, por qué, dónde)

| Tecnología | Para qué | Dónde se ve |
|---|---|---|
| PHP 8.2 | Lógica de la API | todos los `.php` |
| Apache + `mod_rewrite` | Front controller | `api/.htaccess` |
| MySQLi | Acceso a `pesca_formosa` | `config/database.php` + cada endpoint |
| Prepared statements `?` | Evitar SQL injection | `bind_param` |
| `password_hash` / `password_verify` | bcrypt (RNF03) | `register.php`, `login.php` |
| JWT HS256, exp 24 h | Sesión sin guardar token en SQL | `config/jwt.php` |
| `firebase/php-jwt` | Verificar JWT | Composer `vendor/` |
| Composer | Dependencias PHP | `composer.json` |
| CORS + OPTIONS | Futura app React Native | `config/respuesta.php` |
| Thunder Client | Probar API | colección en la raíz |
| OpenWeatherMap / Open-Meteo | Clima (Word: OWM) | `config/clima.php` |
| cURL | Llamar APIs externas | clima y Google |
| Google tokeninfo | Validar `id_token` | `config/google.php` |

**Por qué MySQLi y no PDO:** el curso y XAMPP usan MySQLi; una sola conexión `$conn`.

**Por qué no se guarda el JWT en la base:** el token es autovalidante (firma + `exp`). Logout = el cliente lo tira. No hay tabla de sesiones.

---

## 4. Carpetas y archivos

### Raíz

- `composer.json` / `composer.lock` — única lib: `firebase/php-jwt`.
- `vendor/` — código de Composer. **No se edita.**
- `.gitignore` — no versionar `clima.local.php` ni `google.local.php` (secretos).
- `thunder-collection_*.json` / `thunder-environment_*.json` — pruebas.
- `sql/seed_formosa.php` — carga datos de prueba (no crea las 10 tablas; esas ya estaban).

### `config/` — compartido, no es un endpoint

- `database.php` — `mysqli` a `pesca_formosa`, charset `utf8mb4`. Si falla: JSON 500.
- `respuesta.php` — `enviarJson`, `leerJson` (`php://input`), CORS, preflight OPTIONS 204.
- `jwt.php` — `crearTokenJWT` (HMAC-SHA256 a mano) y `verificarTokenJWT` (librería). Payload: `iat`, `exp`, `id_usuario`, `email`, `rol`.
- `auth.php` — lee `Authorization: Bearer …`, 401 si falta/mal/vencido, `requiereRol("admin")` → 403.
- `clima.php` — si hay API key OWM la usa; si no, Open-Meteo (para poder probar en local).
- `google.php` — Client ID + `tokeninfo` de Google.
- `*.local.php` / `*.example.php` — claves locales.

**Por qué encode JWT a mano:** `JWT::encode` de php-jwt v7 + PHP 8.2 rompía la clave (`SensitiveParameter`). El decode con `new Key(...)` sí funciona. El token sigue siendo HS256 estándar.

### `api/` — un archivo = una operación

| Archivo | Ruta Word | Auth |
|---|---|---|
| `index.php` | router | — |
| `.htaccess` | rewrite | — |
| `auth/register.php` | POST `/auth/register` | no |
| `auth/login.php` | POST `/auth/login` | no |
| `auth/forgot-password.php` | POST `/auth/forgot-password` | no |
| `auth/google.php` | POST `/auth/google` | no (valida Google) |
| `lugares/listar.php` | GET `/lugares` | no |
| `lugares/detalle.php` | GET `/lugares/:id` | no |
| `especies/listar.php` | GET `/especies` | no |
| `caminos/listar.php` | GET `/caminos` | no |
| `caminos/detalle.php` | GET `/caminos/:id` | no |
| `capturas/registrar.php` | POST `/capturas` | JWT |
| `capturas/mis.php` | GET `/capturas/mis` | JWT |
| `reportes/camino.php` | POST `/reportes/camino` | JWT |
| `reportes/agua.php` | POST `/reportes/agua` | JWT |
| `valoraciones/crear.php` | POST `/valoraciones` | JWT |
| `admin/info_util_*.php` | CRUD `/admin/info-util` | JWT + rol admin |
| `clima/actual.php` etc. | GET `/clima/...` | no |
| `luna/fases.php` | GET `/luna/fases` | no |

`api/usuarios/*.php` — rutas viejas (`/api/usuarios/login.php`). El login nuevo **incluye** el de `auth/`. Sirven de compatibilidad; el contrato del Word es `/auth/...`.

`test_conexion.php` — humo: “conexión exitosa”. No es del Word.

### Patrón de cada endpoint

```
require config (respuesta, database, auth si hace falta)
manejarPreflight()
leerJson() o $_GET
validar → 400/401/403/404
prepare + bind_param + execute
enviarJson(..., código HTTP)
```

Códigos del Word: 201 alta, 400 datos, 401 no autenticado, 403 autenticado sin permiso, 404 no existe.

---

## 5. Auth (el bloque que más preguntan)

**Registro:** valida nombre/email/password → email único → `password_hash(..., PASSWORD_DEFAULT)` (bcrypt) → inserta `rol=usuario` → 201. Nadie se registra como admin por la API.

**Login:** mismo mensaje si el email no existe o la clave es mala (no filtrar usuarios). `password_verify`. Si el usuario es solo Google, `password_hash` es NULL → 401 en login email. JWT 86400 s.

**Forgot-password:** email no existe → 404 (Word). Si existe, `random_bytes(32)` hex a `usuarios.token_reset`. En XAMPP **no hay SMTP**; Thunder recibe el token para probar. El Word no documenta `/reset`.

**Google:** el celular manda `id_token`. El servidor llama a Google `tokeninfo`, exige `aud` = Client ID. Busca `google_id`, si no `email` (vincula), si no inserta con `password_hash NULL`. Sin Client ID → 503. No se “inventa” un login Google falso.

**Rutas protegidas:** `obtenerUsuarioAutenticado()` usa el `id_usuario` del JWT, no un id del body (si no, un usuario cargaría capturas a nombre de otro).

**401 vs 403:** 401 = no hay sesión válida. 403 = hay sesión, rol incorrecto (pescador vs admin).

---

## 6. Módulos de negocio (cómo pegan a SQL)

**Lugares / especies / caminos:** GET públicos. El detalle de lugar hace JOIN `lugar_especie` + `especies` y lista `fotos` tipo lugar. El detalle de camino lista `reportes` tipo camino.

**Captura:** JWT → `id_usuario` del token. `especie_id` obligatorio (Word). `lugar_id` y `peso` opcionales (DER: lugar puede ser NULL). INSERT en `capturas`. `GET /capturas/mis` filtra por el usuario del token, JOIN especie y lugar.

**Reporte camino:** INSERT `reportes` (`tipo_reporte=camino`, `id_referencia` = id del camino). Si el estado es del ENUM de `caminos`, también **UPDATE** `caminos.estado_actual`. Así el listado de caminos muestra el último estado.

**Reporte agua:** igual, `tipo_reporte=agua`, `id_referencia` = `id_lugar`. No hay columna “nivel” en `lugares_pesca`; el nivel vive en el reporte.

**Valoración:** `entidad_tipo` lugar|camino, puntaje 1–5. Recalcula `lugares_pesca.puntaje_promedio` con AVG.

**Admin `info_util`:** solo `requiereRol("admin")`. CRUD sobre vedas, avisos, teléfonos, etc. `id_admin` = usuario del JWT.

**Clima:** query `lat`/`lng` (Formosa por defecto). Respuesta Word: `{temp, descripcion, viento}` y pronóstico `{dia, temp_min, temp_max}`. Alertas se derivan de códigos de mal tiempo.

**Luna:** no hay API. Edad lunar respecto a un novilunio conocido (ciclo 29.53 días). `?mes=&anio=`.

**Fotos:** tabla lista, endpoint `POST /fotos` no implementado (alcance v2.0). `foto` NULL en capturas es coherente.

---

## 7. Base de datos

Motor: MariaDB 10.4 (XAMPP). El Word dice MySQL 8; en la práctica es el MySQL de este XAMPP. Charset `utf8mb4`. PK autoincrement. Email UNIQUE.

```
usuarios 1──N capturas
usuarios 1──N reportes
usuarios 1──N valoraciones
usuarios 1──N fotos
usuarios 1──N info_util          (id_admin)
lugares_pesca N──N especies      (tabla lugar_especie)
capturas N──1 especies
capturas N──1 lugares_pesca      (opcional)
reportes: id_referencia polimórfico (camino O lugar según tipo_reporte)
valoraciones: entidad_id polimórfico (lugar O camino según entidad_tipo)
```

**Por qué polimórfico en reportes/valoraciones:** el DER del Word lo define así (`tipo_reporte` + `id_referencia`). No hay FK a `caminos` ni a `lugares_pesca` en `reportes`. La integridad la cubre la API (SELECT previo, 404).

**Por qué `lugar_especie`:** N:N. Un río tiene varias especies; el Dorado está en varios lugares.

**Enums importantes:**  
`usuarios.rol`: usuario | admin  
`lugares.tipo`: laguna | rio | riacho | bañados  
`caminos.estado_actual`: transitable | barro | cortado | precaucion  
`info_util.tipo`: reglamentacion | aviso | veda | telefono | permiso | fase_lunar  
`especies.protegida`: 0/1 (Manguruyú = 1, cupo 0).

**Datos actuales (prueba, no censo provincial):** 5 usuarios, 6 especies, 5 lugares, 12 relaciones, 3 caminos, capturas/reportes/valoraciones de Thunder, 1 veda, 0 fotos.

Usuarios de prueba: `admin@pescaformosa.local` / `Admin1234` (rol admin); `pescador@pescaformosa.local` / `Pescador1234`.

---

## 8. Calidad de software (cómo se defiende)

- **SQL injection:** nunca se concatena input en el SQL. Siempre `prepare` + `bind_param` con tipos (`s`, `i`, `d`).
- **XSS:** no hay HTML de negocio; JSON con charset UTF-8.
- **Contraseñas:** bcrypt; nunca se SELECT para devolver el hash al cliente. Login no revela si el email existe.
- **IDOR:** capturas “mías” y altas usan el id del JWT.
- **HTTP semántico:** 201/400/401/403/404; Thunder lo verificó.
- **Validación:** email `FILTER_VALIDATE_EMAIL`, puntaje 1–5, existencia de FK lógicas.
- **Secretos:** JWT y API keys no van en Git (`.local.php`). *Debilidad a admitir:* la clave JWT está en código (`jwt.php`); en producción iría a variable de entorno.
- **CORS `*`:** válido en desarrollo local; en producción se restringe al origen de la app.
- **Sin framework:** deliberado (curva baja, XAMPP, materia). El router es un `if` por ruta. Cohesión: un archivo por caso de uso.

---

## 9. Preguntas de tribunal (pregunta → respuesta corta)

**¿Por qué PHP y no Node como el DET?**  
Restricción del Word: backend local XAMPP Apache+MySQL. PHP corre dentro de Apache. Las rutas y el JSON son los de la sección 8.

**¿Dónde está Express?**  
No hay. El equivalente es `index.php` + `.htaccess`.

**¿Qué es un front controller?**  
Un solo script (`index.php`) recibe todas las URLs y despacha. Evita 20 URLs distintas con `.php` a la vista.

**¿Para qué `DirectorySlash Off` y `Options -Indexes`?**  
Había carpetas `api/lugares` que Apache listaba en vez de rutar `GET /lugares`. Sin índices y sin forzar `/` al final, gana el rewrite.

**¿Qué es JWT y por qué 24 h?**  
JSON Web Token: header.payload.firma. RNF03. HS256 = HMAC con clave simétrica. `exp = iat + 86400`.

**¿Qué pasa si copio el JWT de otro?**  
Hasta que venza, el servidor lo acepta. Por eso HTTPS en un entorno real y no filtrar tokens. Aquí es localhost.

**¿Dónde está la sesión PHP `$_SESSION`?**  
No hay. API REST stateless: cada request trae Bearer.

**¿Cómo se prueba el rol admin?**  
Login pescador → GET `/admin/info-util` → 403. Login admin → 200.

**¿Por qué `bind_param("iiidss", ...)`?**  
Tipos: int, int, int, decimal, string, string. El orden tiene que coincidir con los `?`.

**¿Qué es utf8mb4?**  
Charset que cubre tildes y ñ (`Bañado`, `Surubí`). `JSON_UNESCAPED_UNICODE` no convierte a `\u00e1`.

**¿OpenWeatherMap o Open-Meteo?**  
El Word pide OWM. Sin API key el proxy cae a Open-Meteo para no dejar el módulo muerto en XAMPP. Con key en `clima.local.php` usa OWM.

**¿La luna está en `info_util` tipo fase_lunar?**  
El admin puede cargar fases ahí (RF19). `GET /luna/fases` las **calcula** (no lee esa tabla). Dos canales: contenido editorial vs cálculo.

**¿Por qué reportes no tiene FK a caminos?**  
Diseño del Word: una tabla, dos tipos. El `id_referencia` cambia de significado. Trade-off: menos tablas, menos integridad declarativa; la API valida.

**¿Qué es N:N?**  
`lugar_especie`: PK compuesta `(id_lugar, id_especie)`, dos FK.

**¿Se puede borrar un usuario con capturas?**  
La FK `capturas.id_usuario → usuarios` lo impide si no hay ON DELETE CASCADE (no lo hay). Correcto: no se pierden capturas huérfanas.

**¿Dónde está Multer?**  
Node. En PHP sería `$_FILES` + carpeta `uploads/`. No está: fotos v2.0.

**¿Axios en el backend?**  
No. Cliente HTTP del frontend. Aquí Thunder.

**¿Qué prueba el 401 de captura sin token?**  
Que el recurso no es público. Calidad: autorización en el borde.

**¿Código 503 en Google?**  
Servicio no configurado (falta Client ID). Distinto de 401 (token inválido).

**¿Inyección en `$_GET["id"]`?**  
Se castea `(int)` y se pasa por placeholder. `"1; DROP TABLE"` queda `1`.

**¿Por qué no hay transacciones en captura?**  
Un solo INSERT. En valoración sí hay dos pasos (INSERT + UPDATE promedio) sin transacción: debilidad menor a mencionar; el promedio se puede recalcular.

**¿REST?**  
Recursos (`/lugares`, `/capturas`), verbos HTTP, JSON, sin estado de servidor.

**¿Cómo escala RNF06 (500 usuarios)?**  
InnoDB + índices en PK/FK. 500 usuarios y 10k capturas es trivial. No hay caché; no hace falta aún.

**¿Qué no defenderías?**  
Clave JWT en código; CORS `*`; forgot-password que devuelve el token en JSON (solo local); Google/fotos incompletos según calendario.

---

## 10. Frase de cierre (oral)

“El backend es una API REST PHP sobre XAMPP. Apache reescribe a un router, cada caso de uso es un PHP con MySQLi preparado, bcrypt y JWT de 24 horas. La base `pesca_formosa` tiene las diez tablas del DER: usuarios en el centro, N:N lugares-especies, reportes y valoraciones polimórficos como el Word. Lo público se lista sin token; capturas, reportes y admin exigen Bearer y rol. Thunder comprobó 201, 401 y 403. Google espera Client ID; las fotos el documento las deja para la v2.0.”
