# 🔍 Guía de Verificación de CSS en Railway

## 📋 Métodos de Verificación

### 1. ✅ Verificar en los Logs de Railway (Método Principal)

**Pasos:**
1. Ve a tu proyecto en Railway
2. Selecciona tu servicio (POI_Quiniella)
3. Ve a la pestaña **"Deployments"**
4. Haz clic en el último deployment
5. Haz clic en **"View logs"** o **"View build logs"**

**Busca estas líneas en los logs:**

#### ✅ Señales de Éxito:
```
npm install --include=dev
...
added 1234 packages in 45s
...
@esbuild/linux-x64@0.25.8
@esbuild/darwin-arm64@0.25.8
...
vite v7.0.4 building for production...
✓ 4 modules transformed.
dist/assets/app-abc123.css   123.45 kB
dist/assets/futbol-xyz789.css  45.67 kB
dist/assets/app-def456.js     89.01 kB
✓ built in 2.5s
```

#### ❌ Señales de Error:
```
Error: Cannot find module '@esbuild/linux-x64'
Error: spawn esbuild ENOENT
npm ERR! missing: @esbuild/linux-x64@0.25.8
```

---

### 2. 🌐 Verificar en el Navegador

**Pasos:**
1. Abre tu sitio: `https://poiquiniella-production.up.railway.app`
2. Presiona **F12** para abrir DevTools
3. Ve a la pestaña **"Network"** (Red)
4. Filtra por **"CSS"** o **"Stylesheet"**
5. Recarga la página con **Ctrl+Shift+R** (recarga forzada)

**Debes ver:**
- ✅ `app-[hash].css` - Estado 200 (OK)
- ✅ `futbol-[hash].css` - Estado 200 (OK)
- ✅ `app-[hash].js` - Estado 200 (OK)

**Si ves errores 404:**
- ❌ Los archivos CSS no se compilaron correctamente
- ❌ El build falló

---

### 3. 🔎 Inspeccionar el HTML Generado

**Pasos:**
1. Abre tu sitio en el navegador
2. Presiona **F12** → Pestaña **"Elements"** (Elementos)
3. Expande la etiqueta `<head>`

**Debes ver algo como:**
```html
<link rel="stylesheet" href="/build/assets/app-abc123.css">
<link rel="stylesheet" href="/build/assets/futbol-xyz789.css">
<script type="module" src="/build/assets/app-def456.js"></script>
```

**Si ves:**
```html
<!-- Solo CDN, sin archivos de build -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
```
❌ Los archivos de Vite no se están cargando

---

### 4. 🎨 Verificar Estilos Aplicados

**Prueba visual:**
1. Abre tu sitio
2. Inspecciona el navbar (debe tener fondo oscuro/gradiente)
3. Inspecciona las cards (debe tener bordes redondeados y sombras)
4. Verifica los colores (deben ser los verdes futboleros definidos)

**Si los estilos NO se aplican:**
- ❌ El CSS no se está cargando
- ❌ El build falló

---

### 5. 📁 Verificar Archivos en Railway (SSH)

Si tienes acceso SSH a Railway:

```bash
# Conectarte a Railway (si está disponible)
railway shell

# Verificar que los archivos compilados existen
ls -la public/build/assets/

# Debe mostrar:
# app-abc123.css
# futbol-xyz789.css
# app-def456.js
```

---

### 6. 🧪 Verificar Localmente (Antes de Deploy)

**Ejecuta localmente:**
```bash
# Instalar dependencias
npm install --include=dev

# Verificar que esbuild está instalado
npm list @esbuild/win32-x64  # Windows
# o
npm list @esbuild/linux-x64   # Linux

# Compilar assets
npm run build

# Verificar que se crearon los archivos
ls public/build/assets/
```

---

## 🚨 Problemas Comunes y Soluciones

### Problema: "Cannot find module '@esbuild/...'"
**Solución:**
- Verifica que `NIXPACKS_BUILD_CMD` incluye `--include=dev`
- Verifica que `railway.json` tiene `--include=dev` en el buildCommand

### Problema: CSS no se carga (404)
**Solución:**
- Verifica que `npm run build` se ejecutó correctamente
- Verifica que `public/build` existe y tiene archivos
- Verifica que `APP_ENV=production` en Railway

### Problema: Estilos no se aplican
**Solución:**
- Limpia la caché del navegador (Ctrl+Shift+R)
- Verifica que `@vite` está en el layout
- Verifica los logs de Railway para errores de build

---

## ✅ Checklist de Verificación

- [ ] Logs de Railway muestran `@esbuild/...` instalado
- [ ] Logs de Railway muestran `vite build` exitoso
- [ ] Navegador muestra archivos CSS en Network (200 OK)
- [ ] HTML contiene enlaces a archivos `/build/assets/...`
- [ ] Estilos visuales se aplican correctamente
- [ ] No hay errores en la consola del navegador

---

## 📞 Si Todo Falla

1. **Revisa los logs completos** de Railway
2. **Verifica las variables de entorno** (especialmente `NIXPACKS_BUILD_CMD`)
3. **Prueba localmente** con `npm run build`
4. **Verifica que `railway.json`** tiene la configuración correcta

