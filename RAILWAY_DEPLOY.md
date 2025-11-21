# Guía de Deploy en Railway

## Problema: CSS no se carga en producción

En producción, Vite necesita compilar los assets antes de servir la aplicación. Este documento explica cómo solucionarlo.

## Solución

### 1. Configuración de Variables de Entorno en Railway

Asegúrate de configurar estas variables en el panel de Railway:

- `APP_URL`: Debe ser la URL de tu aplicación en Railway (ej: `https://tu-app.railway.app`)
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`

### 2. Archivo nixpacks.toml

Ya se ha creado el archivo `nixpacks.toml` que configura Railway para:
- Instalar dependencias de Composer y NPM
- Compilar los assets con `npm run build`
- Cachear configuraciones de Laravel

### 3. Proceso de Build

Railway ejecutará automáticamente:
1. `composer install --no-dev --optimize-autoloader`
2. `npm ci` (instala dependencias de Node)
3. `npm run build` (compila los assets de Vite)
4. Cachea las configuraciones de Laravel

### 4. Verificación

Después del deploy, verifica que:
- Los archivos estén en `public/build/`
- El archivo `public/build/manifest.json` exista
- La variable `APP_URL` esté configurada correctamente

### 5. Si el problema persiste

1. **Verifica los logs de Railway**: Revisa si `npm run build` se ejecutó correctamente
2. **Verifica APP_URL**: Asegúrate de que coincida con tu dominio de Railway
3. **Limpia el cache**: En Railway, ejecuta:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```
4. **Reconstruye el proyecto**: En Railway, haz un redeploy completo

### 6. Comandos útiles para debugging

Si necesitas acceder a la consola de Railway:

```bash
# Verificar que los archivos build existen
ls -la public/build/

# Verificar el manifest
cat public/build/manifest.json

# Recompilar manualmente
npm run build
```

## Notas Importantes

- El directorio `public/build/` está en `.gitignore` porque se genera durante el build
- En producción, Vite usa los archivos compilados, no el servidor de desarrollo
- Asegúrate de que `APP_URL` tenga el protocolo correcto (`https://`)

## ⚠️ Problema Común: npm install --production

**IMPORTANTE**: Si estás usando `NIXPACKS_BUILD_CMD` personalizado, **NO uses** `npm install --production` porque:
- Las dependencias de Vite (vite, laravel-vite-plugin, sass, tailwindcss) están en `devDependencies`
- `npm install --production` NO instala `devDependencies`
- El build fallará porque no encontrará Vite

**Solución**: Usa `npm install` (sin `--production`) o `npm ci` en tu comando de build.

### Ejemplo correcto de NIXPACKS_BUILD_CMD:

```
composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan optimize && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**O mejor aún**: Elimina `NIXPACKS_BUILD_CMD` y deja que Railway use el `nixpacks.toml` que ya está configurado correctamente.

