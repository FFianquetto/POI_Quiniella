# Guía para Migrar Tablas a Railway MySQL

## ⚠️ IMPORTANTE: Actualizar Variable NIXPACKS_BUILD_CMD

En Railway, ve a tu servicio **POI_Quiniella** → **Settings** → **Variables** y actualiza la variable `NIXPACKS_BUILD_CMD` con:

```
composer install --no-dev --optimize-autoloader && npm install --include=dev && npm run build && php artisan storage:link && php artisan migrate --force && php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**Nota**: La diferencia es que agregamos `php artisan migrate --force` después de `php artisan storage:link`.

Después de actualizar esta variable, haz un **redeploy** del servicio para que se ejecuten las migraciones automáticamente.

## Opción 1: Ejecutar Migraciones desde Railway CLI (Recomendado)

### Paso 1: Instalar Railway CLI
```bash
npm i -g @railway/cli
```

### Paso 2: Iniciar sesión en Railway
```bash
railway login
```

### Paso 3: Conectarte a tu proyecto
```bash
railway link
```

### Paso 4: Configurar Variables de Entorno en Railway
En el panel de Railway, ve a tu servicio MySQL → Variables y copia estas variables:
- `MYSQL_HOST`
- `MYSQL_PORT`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`

Luego, en tu servicio de aplicación (POI_Quiniella), agrega estas variables:
```
DB_CONNECTION=mysql
DB_HOST=[valor de MYSQL_HOST]
DB_PORT=[valor de MYSQL_PORT]
DB_DATABASE=[valor de MYSQL_DATABASE]
DB_USERNAME=[valor de MYSQL_USER]
DB_PASSWORD=[valor de MYSQL_PASSWORD]
```

### Paso 5: Ejecutar Migraciones
```bash
railway run php artisan migrate --force
```

### Paso 6: (Opcional) Ejecutar Seeders
```bash
railway run php artisan db:seed --force
```

## Opción 2: Ejecutar Migraciones desde el Panel de Railway

1. Ve a tu servicio **POI_Quiniella** en Railway
2. Haz clic en la pestaña **"Deployments"**
3. Haz clic en el deployment más reciente
4. Haz clic en **"View Logs"** o **"Shell"**
5. Ejecuta:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

## Opción 3: Configurar Migraciones Automáticas en el Build

Las migraciones ya están configuradas para ejecutarse automáticamente en el build (ver `railway.json`).

## Verificar que las Migraciones se Ejecutaron

1. Ve a tu servicio **MySQL** en Railway
2. Haz clic en la pestaña **"Database"**
3. Deberías ver todas las tablas creadas

## Notas Importantes

- El flag `--force` es necesario en producción para ejecutar migraciones sin confirmación
- Asegúrate de que las variables de entorno estén correctamente configuradas antes de ejecutar las migraciones
- Si tienes datos importantes en tu base de datos local, considera hacer un backup primero

