# Gestión Segura — Cotizador de Seguro de Viaje

Sistema web para cotizar y contratar seguros de viaje. El cliente elige un destino,
selecciona sus fechas y completa sus datos en un flujo progresivo tipo wizard; el
backend calcula el precio, persiste la cotización y permite contratarla y descargar
tanto la cotización como el comprobante en PDF. Incluye además un panel
administrativo para consultar las cotizaciones y contrataciones generadas.

## Stack

- **Backend:** Laravel 13 (PHP 8.3+)
- **Frontend:** Vue 3 + Vite, integrado en el mismo proyecto Laravel
- **Estilos:** Tailwind CSS 4
- **Base de datos:** MySQL
- **PDF:** `barryvdh/laravel-dompdf`
- **Teléfono:** `giggsey/libphonenumber-for-php` (puerto de Google libphonenumber)
- **Tests:** Pest (sobre PHPUnit)

## Requisitos

- PHP 8.3 o superior
- Composer 2
- Node.js 20 o superior y npm
- MySQL 8 (o compatible)

## Instalación

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

## Configuración

Editar `.env` con los datos de conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cotizadorseguroviaje
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

La aplicación no requiere ninguna credencial adicional: la integración con
países no usa API key (ver [Integración de países](#integración-de-países)).

Opcionalmente, definir las credenciales del usuario administrador inicial
(ver [Autenticación y cuentas de usuario](#autenticación-y-cuentas-de-usuario)):

```env
ADMIN_EMAIL=admin@tudominio.com
ADMIN_PASSWORD=una-contraseña-segura
```

Si se omiten, el seeder genera una contraseña aleatoria y la muestra una sola
vez en la consola (nunca se guarda en ningún archivo).

`MAIL_MAILER=log` (valor por defecto) escribe el correo de "crear
contraseña" en `storage/logs/laravel.log` en vez de enviarlo — suficiente
para desarrollo. En producción, configurar un mailer real (`smtp`, `ses`,
etc.) para que ese correo llegue de verdad a cada cliente que cotiza.

## Migraciones

```bash
php artisan migrate --seed
```

`--seed` crea el usuario administrador inicial (ver
[Autenticación y cuentas de usuario](#autenticación-y-cuentas-de-usuario)).
Si ya migraste sin `--seed`, ejecuta `php artisan db:seed` por separado.

Modelos principales: `Insured` (asegurado) y `Quote` (cotización), en relación
`hasMany` / `belongsTo`. Los valores monetarios se almacenan como `DECIMAL`.
Además de las claves foráneas y los `unique`, el esquema incluye `CHECK`
constraints a nivel de MySQL (detalle en [Seguridad e integridad de
datos](#seguridad-e-integridad-de-datos)); estas restricciones se omiten
automáticamente sobre SQLite (usada solo por la suite de tests).

## Ejecución

Desarrollo local (levanta `php artisan serve`, la cola y Vite en un solo comando):

```bash
composer run dev
```

o manualmente:

```bash
php artisan serve
npm run dev
```

La aplicación queda disponible en `http://localhost:8000`. El panel
administrativo está en `/admin/quotes`.

Para producción, o para automatizar cualquiera de estos pasos (instalación,
build de assets, migraciones, cachés), ver [`DEPLOY.md`](DEPLOY.md) y los
scripts en [`deploy/`](deploy/).

## Tests

```bash
php artisan test
# o
vendor/bin/pest
```

La suite cubre el cálculo de cotizaciones (incluyendo los recargos por
región), la validación de fechas, teléfono, cédula/pasaporte y demás datos del
viajero, la creación y el pago/contratación de cotizaciones (incluyendo
idempotencia y bloqueo de doble contratación), los distintos estados de una
cotización, el manejo de fallas del proveedor de países, el listado
administrativo (incluyendo sus indicadores) y la generación de PDF.
`tests/Feature/Security/` agrupa las pruebas específicas de seguridad (ver la
sección siguiente) y `tests/Feature/Auth/` cubre el login (por correo o por
documento de identidad), el rechazo de cuentas no-admin, el aprovisionamiento
automático de cuentas al contratar (usuario/contraseña = documento, sin
duplicar cuentas en contrataciones repetidas, con desambiguación de
colisiones) y el flujo completo de restablecimiento de contraseña de punta a
punta.

Para las verificaciones que no tiene sentido automatizar en la suite (por
ejemplo, que un `CHECK constraint` de MySQL realmente exista en la base de
datos real, algo que no se puede probar sobre el SQLite en memoria que usan
los tests) se usó `php artisan tinker` de forma interactiva contra la base de
datos de desarrollo; el resultado de esas verificaciones está documentado en
[Seguridad e integridad de datos](#seguridad-e-integridad-de-datos) y en
`PROJECT_PROGRESS.md`.

## Arquitectura

El backend separa responsabilidades siguiendo las convenciones de Laravel:

| Capa | Responsabilidad |
| --- | --- |
| `Controller` | Manejo de peticiones HTTP |
| `FormRequest` | Validación de entrada |
| `Action` | Casos de uso (`CreateQuoteAction`, `ProcessSimulatedPaymentAction`, `ProvisionCustomerAccountAction`) |
| `Service` | Lógica de negocio e integraciones externas |
| `Model` | Persistencia (Eloquent) |
| `Resource` | Serialización de respuestas JSON |

El cálculo de precios está centralizado en `QuoteCalculatorService`: dado un
rango de fechas y una región, devuelve días, tarifa diaria, recargo y total.
Ningún controlador ni componente Vue recalcula estos valores; el frontend solo
muestra lo que el backend ya calculó y persistió.

El wizard en Vue (`resources/js/wizard`) mantiene el estado del flujo en un
store reactivo simple (sin librerías adicionales, dado que es un único flujo
lineal) y se comunica con el backend a través de `/api/countries` y
`/api/quotes`. El panel administrativo (`/admin/quotes`) es una vista Blade
renderizada en el servidor, sin necesidad de Vue.

### Endpoints principales

```
GET  /api/countries                     Listado/búsqueda de países
POST /api/phone/validate                Validación de teléfono en vivo (sin crear nada)
POST /api/quotes                        Crear una cotización
GET  /api/quotes/{reference}            Consultar una cotización
POST /api/quotes/{reference}/payment    Pagar (simulado) y contratar una cotización
GET  /api/quotes/{reference}/pdf        Descargar el PDF de una cotización

GET  /login                             Formulario de acceso
POST /login                             Autenticar (solo cuentas con rol admin)
POST /logout                            Cerrar sesión
GET  /forgot-password                   Formulario para solicitar el enlace de recuperación
POST /forgot-password                   Enviar el enlace de recuperación
GET  /password/reset/{token}            Formulario para crear/restablecer contraseña
POST /password/reset                    Guardar la nueva contraseña

GET  /admin/quotes                      Panel administrativo (Blade, requiere sesión + rol admin)
```

Las cotizaciones se identifican públicamente por su `reference`
(`SEG-{año}-{consecutivo}`), nunca por su `id` interno. Todas las rutas de
`api/*` pasan por el `RateLimiter` `api` (60 solicitudes por minuto por IP);
`/login` tiene su propio límite independiente (5 intentos por combinación
email+IP, ver [Autenticación y cuentas de usuario](#autenticación-y-cuentas-de-usuario)).

`GET /api/countries` devuelve, por país, `name`, `code` (ISO alpha-2), `flag`,
`region`, `dial_code` (código de marcación, ej. `+593`) y `phone_length`
(`{min, max}` de dígitos del número nacional) — estos dos últimos se calculan
con `PhoneNumberService`, no vienen del dataset de países.

`POST /api/quotes` además de destino, fechas y datos del viajero, requiere
`phone_country_code` (ISO alpha-2) y `phone_number` (solo dígitos); el backend
valida el número con `PhoneNumberService::isValidNumber()` para ese país
específico y lo persiste normalizado en formato E.164 (ej. `+593987654321`).

`POST /api/quotes/{reference}/payment` procesa un pago simulado
(`ProcessSimulatedPaymentAction`): valida tarjeta, vencimiento, CVV y
términos, es idempotente por `idempotency_key` (reintentar con la misma clave
nunca duplica el cobro) y usa un bloqueo pesimista (`lockForUpdate()`) para
que dos pagos concurrentes sobre la misma cotización nunca la contraten dos
veces. Un pago aprobado marca la cotización como `contracted` y aprovisiona
la cuenta del cliente (ver siguiente sección); un pago rechazado (tarjeta de
prueba `4000 0000 0000 0002`) devuelve `402` sin tocar el estado de la
cotización ni crear ninguna cuenta.

### Panel administrativo — consulta de contrataciones

`/admin/quotes` muestra, como mínimo, Cliente, Identificación, Destino,
Fecha de salida, Fecha de regreso, Valor, Estado y Fecha de creación de cada
registro (además de la referencia); incluye búsqueda (referencia, cliente,
identificación o destino), filtro por estado y paginación (15 por página).
Encima de la tabla se muestran indicadores agregados calculados en el
controlador (`Admin\QuoteController::index()`): total de cotizaciones, total
contratadas, tasa de conversión y monto total contratado.

Cada fila (o tarjeta, en móvil) abre un modal con el detalle completo del
registro: datos del asegurado, todos los destinos del viaje (incluye viajes a
varios países), desglose de precio, datos del pago simulado si existe, la
cuenta de cliente asociada (usuario) si ya fue aprovisionada, y las fechas de
creación/contratación. El detalle de cada cotización visible en la página se
serializa con el mismo `QuoteResource` que usa la API (`resolve()`, para que
los bloques condicionales `payment`/`account` se omitan correctamente cuando
no aplican) y se incrusta como JSON en un atributo `data-quote-detail`; un
script sin dependencias (sin Vue, consistente con que esta vista es Blade
puro) lo lee al hacer clic y llena el modal — no hay una petición AJAX
adicional ni un endpoint nuevo que proteger.

### Cálculo de precios

```
Tarifa: USD 3 por día (inclusive el día de salida y el de regreso)

Recargo por región:
  Sudamérica    0%
  Norteamérica  15%
  Europa        20%
  Asia          25%
  África        20%
  Oceanía       25%
```

## Integración de países

El listado de países se obtiene siempre a través del backend
(`CountriesService`), nunca directamente desde Vue:

```
Vue → Laravel → CountriesService → fuente de datos externa
```

La API pública histórica de REST Countries (v3.1, sin autenticación) fue
descontinuada; su reemplazo (v5) exige una API key de pago. Para no depender
de credenciales externas, `CountriesService` consume en su lugar el conjunto
de datos abierto de [`mledoze/countries`](https://github.com/mledoze/countries)
—la misma fuente de datos sobre la que se construyó originalmente REST
Countries—, publicado como un JSON estático sin autenticación ni límites de
uso. El servicio normaliza cada país (nombre en español, código ISO, bandera
y región) y cachea el resultado por 24 horas para no depender de una llamada
externa en cada búsqueda.

## Autenticación y cuentas de usuario

### Cuenta automática al contratar

La cuenta de cliente no se crea al cotizar, sino al **contratar**: cuando
`POST /api/quotes/{reference}/payment` aprueba el pago simulado,
`ProcessSimulatedPaymentAction` marca la cotización como `contracted` y llama
a `ProvisionCustomerAccountAction` para crear (o reutilizar) el `User`
vinculado a ese asegurado (`insured_id`, único), con rol `customer`:

- **Usuario y contraseña son, ambos, el número de documento de identidad del
  asegurado** (cédula o pasaporte) — una decisión de negocio explícita, no un
  secreto generado por el sistema. Si dos asegurados distintos comparten el
  mismo número de documento (tipos distintos), el segundo recibe un sufijo
  (`documento-2`, `documento-3`, ...) para no colisionar.
- Si el asegurado ya tenía cuenta (misma persona contratando de nuevo), se
  reutiliza la misma cuenta y solo se actualizan nombre/correo — nunca se
  duplica ni se reinicia la contraseña ya establecida por el cliente.
- La respuesta JSON de `/payment` incluye un bloque `data.account` (solo
  cuando el pago fue aprobado) con `username` y `password` en texto plano
  para que el frontend se los muestre al cliente en la pantalla de
  confirmación — es información que el cliente ya conoce (su propio
  documento), no una fuga de un secreto generado por el servidor. El cliente
  puede cambiarla después con "¿Olvidaste tu contraseña?"
  (`/forgot-password` → `/password/reset/{token}`).
- Si el aprovisionamiento de la cuenta falla por cualquier motivo, el error
  se registra en el log pero **nunca** revierte un pago ya aprobado — es una
  operación auxiliar dentro de la misma transacción de base de datos que crea
  el registro de pago, no el flujo principal.

Un login con rol `customer` es válido (`LoginRequest` acepta el documento
como usuario o el correo como identificador, ambos vía el campo unificado
`login`), pero `AuthenticatedSessionController` solo deja la sesión abierta
para cuentas `admin`; ver la sección siguiente. Hoy no existe un panel para
que un cliente autenticado vea sus propias cotizaciones (no fue solicitado);
la cuenta ya queda lista para cuando se construya esa pantalla. Ver "Mejoras
futuras".

### Acceso al panel administrativo

`/admin/quotes` requiere sesión (`auth`) y rol `admin` (middleware `admin` →
`EnsureUserIsAdmin`); una cuenta `customer` autenticada recibe `403`, no
`200`, si intenta acceder — probado explícitamente en
`tests/Feature/Admin/QuoteControllerTest.php`. El formulario de `/login`
(`AuthenticatedSessionController`) solo deja la sesión abierta si la cuenta
es `admin`; si una cuenta `customer` inicia sesión ahí, se cierra la sesión
de inmediato y se muestra un mensaje explicando que esa cuenta no tiene
acceso a esa sección — un mismo formulario de login no es una puerta trasera
al panel administrativo.

El intento de login está protegido contra fuerza bruta independientemente
del `RateLimiter` de `api/*`: 5 intentos por combinación de correo + IP
(`LoginRequest::ensureIsNotRateLimited()`, patrón estándar de Laravel).

### Usuario administrador inicial

`database/seeders/DatabaseSeeder.php` crea un único usuario `admin` (rol
`admin`) leyendo `ADMIN_EMAIL`/`ADMIN_PASSWORD` de `.env`; si no están
definidas, genera una contraseña aleatoria de 16 caracteres y la imprime una
sola vez en la consola — nunca queda hardcodeada ni en ningún archivo del
repositorio. El seeder es idempotente: si el correo ya existe, no hace nada.

```bash
php artisan db:seed
```

### Datos de ejemplo y usuarios de prueba

`database/seeders/ContractsDemoSeeder.php` (encadenado desde
`DatabaseSeeder`, se ejecuta con el mismo `php artisan db:seed`) llena el
módulo de contrataciones con datos realistas: 45 cotizaciones repartidas en
las seis regiones (incluyendo viajes a varios destinos), con fechas variadas
y aproximadamente un 60% ya contratadas. Cada cotización contratada incluye
su pago simulado aprobado y su cuenta de cliente aprovisionada de la misma
forma que en producción (usuario y contraseña = documento de identidad),
reutilizando `ProvisionCustomerAccountAction`. Es idempotente: si ya existen
registros de ejemplo (identificados por un marcador en el correo del
asegurado), se omite en una segunda ejecución.

Al terminar, el seeder imprime en consola una tabla con credenciales de
clientes de prueba (documento = usuario = contraseña) listas para probar el
login. Para volver a verlas sin re-sembrar:

```bash
php artisan tinker --execute '
App\Models\User::where("role", "customer")->whereNotNull("username")
    ->limit(10)->get(["username"])
    ->each(fn ($u) => print("usuario/contraseña: {$u->username}\n"));
'
```

El usuario administrador inicial (`ADMIN_EMAIL`/`ADMIN_PASSWORD` en `.env`,
ver arriba) es la única cuenta con acceso al panel `/admin/quotes`; las
cuentas `customer` sembradas sirven para probar el flujo de login con
documento de identidad y el restablecimiento de contraseña, no el panel
administrativo.

## Manejo de errores

- **Proveedor de países no disponible** (timeout, error HTTP o respuesta
  inesperada): `CountriesUnavailableException` → `503` con un mensaje claro
  para el usuario.
- **Datos inválidos** (fechas, destino inexistente, campos del viajero,
  teléfono no válido para el país seleccionado): `422` con los mensajes de
  validación por campo.
- **Intento de recontratar una cotización ya contratada**:
  `QuoteAlreadyContractedException` → `409`.
- **Cotización inexistente**: `404` estándar de Laravel vía route model
  binding.

En el frontend, ningún error se muestra con `alert()`: los errores de
validación aparecen junto al campo correspondiente y los errores de servidor
se muestran como texto dentro del mismo paso del wizard, conservando los
datos ya ingresados.

## Seguridad e integridad de datos

### Validación de entrada

Todo dato que llega del cliente pasa por un `FormRequest` con una lista
explícita de reglas (allow-list), nunca por asignación directa de lo que
envía la petición:

- **Identificación:** `document_type` solo acepta `cedula` o `passport`
  (`Rule::enum`); una cédula ecuatoriana se valida con su dígito verificador
  real (`EcuadorianIdentityService`, algoritmo módulo 10 oficial), no solo su
  longitud; un pasaporte exige 6–20 caracteres alfanuméricos.
- **Teléfono:** se valida con `PhoneNumberService` (basado en
  `libphonenumber`) que el número sea genuinamente válido para el país
  seleccionado — no una simple verificación de longitud — y se normaliza a
  E.164 antes de guardarlo.
- **Nombres:** solo letras, espacios, apóstrofes y guiones (rechaza dígitos o
  símbolos de control).
- **Edad mínima:** la fecha de nacimiento debe corresponder a un mayor de 18
  años.
- **Destino:** el código de país debe existir en `CountriesService`; un
  código inventado se rechaza con `422`, nunca se procesa "a medias".

### Protección contra manipulación de peticiones (mass assignment)

`Insured` y `Quote` usan `$fillable` (allow-list), no `$guarded = []`.
Cualquier campo enviado por el cliente que no esté en las reglas del
`FormRequest` correspondiente —`id`, `insured_id`, `status`, `total`,
`contracted_at`, etc.— se descarta antes de llegar al modelo. El precio
siempre lo recalcula `QuoteCalculatorService` en el servidor; un cliente no
puede alterar el total enviando un valor distinto en el payload. Cubierto por
`tests/Feature/Security/ApiSecurityTest.php`.

### Inyección SQL

Todas las consultas —incluida la búsqueda del panel administrativo y la de
`/api/countries`— usan el query builder de Eloquent con parámetros
enlazados (`where(..., 'like', ...)`), nunca concatenación de strings ni
`DB::raw()`/`whereRaw()` con datos de usuario. Se verificó explícitamente
enviando metacaracteres de SQL (`' OR '1'='1`, etc.) como término de
búsqueda: se tratan como texto literal.

### XSS (Cross-Site Scripting)

Blade escapa automáticamente todo lo interpolado con `{{ }}`. Se probó
guardando un nombre con un payload `<script>` y verificando que tanto el
panel administrativo como el PDF lo muestran escapado (texto plano), nunca
como HTML ejecutable.

### Control de acceso a recursos (IDOR)

Las cotizaciones se resuelven públicamente solo por `reference`
(`Quote::getRouteKeyName()`), nunca por su `id` autoincremental interno; una
petición a `/api/quotes/{id-numérico}` devuelve `404`, no la cotización. El
`id` interno tampoco se serializa en `QuoteResource`.

### Límite de peticiones (rate limiting)

Todas las rutas de `api/*` están protegidas por el `RateLimiter` `api` (60
peticiones por minuto por IP, ver `AppServiceProvider::boot()` y
`bootstrap/app.php`). Esto no viene gratis al registrar `routes/api.php` a
mano en vez de con `php artisan install:api` — quedó documentado como hallazgo
en `PROJECT_PROGRESS.md` porque `POST /api/quotes` estuvo sin límite alguno
hasta corregirlo. `/login` tiene su propio límite de 5 intentos contra fuerza
bruta (ver [Autenticación y cuentas de usuario](#autenticación-y-cuentas-de-usuario)).

### Autenticación y control de acceso

Las contraseñas se hashean (`Hash::make`, cast `'password' => 'hashed'`),
nunca se comparan ni se guardan en texto plano. `role` e `insured_id` en
`User` están deliberadamente fuera de la lista de atributos asignables en
masa (`#[Fillable]`) — solo se pueden establecer por asignación directa en
código de servidor de confianza, nunca por un payload de request, cerrando
cualquier vector de escalación de privilegios por mass assignment. El panel
administrativo exige `auth` + rol `admin` (`EnsureUserIsAdmin`); ver el
detalle completo en [Autenticación y cuentas de
usuario](#autenticación-y-cuentas-de-usuario).

### Manejo de errores sin fuga de información

Con `APP_DEBUG=false` (obligatorio en producción, ver `.env.example`), las
respuestas de error de la API no incluyen stack trace, ni rutas del
filesystem del servidor — verificado con un test dedicado que fuerza
`config(['app.debug' => false])` e inspecciona la respuesta.

### Integridad de datos en la base de datos

- **Transacciones:** `CreateQuoteAction` envuelve la creación/actualización
  del asegurado y de la cotización en `DB::transaction()`. Si la creación de
  la cotización falla, la actualización del asegurado también se revierte —
  nunca queda un asegurado modificado sin su cotización correspondiente.
- **Bloqueo de fila (row lock):** `ProcessSimulatedPaymentAction` relee la
  cotización con `lockForUpdate()` dentro de una transacción antes de
  comprobar su estado, cerrando la condición de carrera en la que dos pagos
  simultáneos sobre la misma cotización podrían pasar ambos la verificación
  de "no contratada todavía"; la clave de idempotencia (`idempotency_key`) se
  comprueba dentro del mismo bloqueo, así que reintentar un pago con la misma
  clave nunca crea un segundo cobro.
- **Restricciones a nivel de base de datos, no solo en PHP:** además de las
  claves foráneas (`ON DELETE CASCADE` de `quotes.insured_id`; `ON DELETE SET
  NULL` de `users.insured_id`, para no destruir una cuenta si el asegurado se
  elimina) y los índices `unique` (`quotes.reference`,
  `(insureds.document_type, insureds.document_id)`, `users.insured_id`), se
  agregaron `CHECK` constraints de MySQL para que
  un valor inválido sea rechazado por la base de datos aunque no pase por
  Eloquent:
  - `quotes.status` solo admite `'quoted'` o `'contracted'`.
  - `insureds.document_type` solo admite `'cedula'` o `'passport'`.
  - `users.role` solo admite `'admin'` o `'customer'`.
  - `quotes.days >= 1` y todos los montos (`daily_rate`, `subtotal`,
    `surcharge_amount`, `total`, `surcharge_percentage`) `>= 0`.

  Estas restricciones se aplican solo sobre MySQL (`Schema::getConnection()
  ->getDriverName()`); SQLite —usado únicamente por la suite de tests— no
  soporta agregar `CHECK` constraints vía `ALTER TABLE`, así que las
  migraciones las omiten ahí sin afectar los tests. Se verificaron de forma
  manual con `php artisan tinker` contra la base de datos MySQL real:
  intentar escribir un `status` inválido, un `document_type` inválido, un
  `total` negativo o `days = 0` directamente con el query builder (evitando
  Eloquent) es rechazado por MySQL con
  `SQLSTATE[HY000]: ... Check constraint ... is violated`. El detalle
  completo de cada verificación está en `PROJECT_PROGRESS.md`.
- **Precisión monetaria:** todos los valores de dinero son `DECIMAL`, nunca
  `FLOAT`/`DOUBLE`, evitando errores de redondeo de punto flotante.

### Recomendaciones para producción (fuera del alcance del código)

- Usar un usuario de MySQL de mínimo privilegio, nunca `root`, con permisos
  limitados a la base de datos de la aplicación (ya se usa un usuario
  dedicado en desarrollo).
- `APP_DEBUG=false` y `APP_ENV=production` siempre en producción.
- Servir la aplicación exclusivamente por HTTPS.
- Backups periódicos y cifrado en reposo de la base de datos (a nivel de
  infraestructura/proveedor, no de la aplicación).
- Mantener `.env` fuera del control de versiones (ya cubierto por
  `.gitignore`) y de cualquier directorio servido públicamente.

## Identidad visual

La aplicación usa la marca **Gestión Segura**: paleta navy/dorado/ámbar como
colores primarios, grises neutros, y acentos secundarios para estados de
éxito y error. Los tokens de color y tipografía están centralizados en
`resources/css/app.css` (`@theme`), junto con clases de componente
compartidas (`field-control`, `primary-action`/`secondary-action`,
`soft-card`, `step-title`, etc.) para que el wizard, el panel administrativo
y el PDF compartan un mismo lenguaje visual. Los títulos usan Archivo como
sustituto libre de Akzidenz-Grotesk-BQ —una fuente comercial no disponible
para auto-hospedar— en los mismos pesos (Light/Medium) especificados por la
guía de marca. Los íconos son SVG propios (`AppIcon.vue`), sin depender de
una librería externa de iconografía.

## Mejoras futuras

- Panel para que un cliente autenticado (cuenta creada automáticamente al
  cotizar) vea sus propias cotizaciones y contrataciones.
- Notificación por correo al confirmar una contratación.
- Internacionalización (actualmente la interfaz solo está en español).
- Soporte de múltiples monedas.
- Licenciar e integrar la tipografía de marca definitiva en reemplazo del
  sustituto libre usado actualmente para los títulos.
- Suite de pruebas de frontend (Vitest / Vue Test Utils) para la lógica del
  wizard, hoy verificada solo de forma manual.
