# Validación de NIF con VIES y HubSpot

Script en PHP que recorre las empresas de HubSpot con `nif_cif_vat` informado, valida cada número contra la API pública de VIES y guarda el resultado en la propia empresa. Al terminar muestra un resumen y lo guarda en un informe.

## Requisitos

- PHP 8.2 
- [Composer](https://getcomposer.org/)
- Una service key de HubSpot con los scopes `crm.schemas.companies.write`, `crm.objects.companies.read` y `crm.objects.companies.write`


## Instalación

Instalar las dependencias y asignar variables de entorno. 

```bash
composer install
cp .env.example .env
```

Edita `.env` y pon tu token:

```
SERVICE_KEY=pat-eu1-xxxxxxxx
```

## Ejecución

Todos los comandos se lanzan desde la raíz del proyecto.

**1. Crear el grupo y las propiedades en HubSpot** (solo la primera vez; se puede repetir sin problema, un 409 se trata como "ya existe"):

Bootstrap crea el grupo `Ejercicio Vexco – Ruben` y las propiedades `vies_Ruben_estado`, `vies_Ruben_fecha` y `vies_Ruben_motivo` en Companies. El nombre se define en `bootstrap.php`.

```bash
php bootstrap.php
```
**2. Ejecución de Prueba:**

Esto ejecuta toda la lógica excepto escribir en las propiedades de HubSpot.

```bash
php run.php --dry-run
```

**3. Ejecución Real:**

```bash
php run.php
```
Se puede lanzar más de una vez: solo se procesan las empresas sin estado y las que quedaron en `ERROR_TEMPORAL`. 

La salida por consola incluye una línea por empresa y el resumen final. El resumen también se guarda en `logs/INFORME-<fecha>.txt`.

## Estructura

```
bootstrap.php   crea grupo y propiedades
run.php         flujo principal
config.php      carga .env y opciones de los clientes HTTP
src/
  Http/         ApiClient (reintento de 429) y ApiResponse
  Hubspot/      búsqueda de pendientes con paginación y escritura
  Vies/         cliente de VIES
  Validation/   normalizador, clasificador y resultados
  Service/      procesador por NIF y resumen
```

## Posibles Errores
En Windows, si aparece `cURL error 60: unable to get local issuer certificate`, descarga `cacert.pem` y apunta `curl.cainfo` y `openssl.cafile` hacia él en tu `php.ini`.
