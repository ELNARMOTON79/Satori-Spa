
# Satori-Spa

Aplicación web para la gestión de un centro de estética / spa. Proyecto construido con Node.js, Express y Handlebars, y con integración para Firebase (Firestore, Auth y Storage).

**Estado:** Código en repo. Documentación generada automáticamente.

## Tecnologías principales

- **Node.js** y **Express**: servidor y rutas.
- **Express Handlebars**: motor de plantillas (`.hbs`).
- **Firebase Admin SDK**: Firestore, Auth y Storage (backend).
- **Tailwind CSS** (dependencia presente): utilidad CSS.
- **Multer**: manejo de uploads.
- **PDFKit**: generación de PDFs.
- **Nodemon** (dev): reinicio automático en desarrollo.

## Requisitos

- Node.js 18+ (o versión compatible con las dependencias listadas).
- Cuenta de Firebase y un proyecto configurado (opcional: Storage, Auth, Firestore).
- Archivo de credenciales de servicio de Firebase (JSON) o entorno con credenciales aplicables.

## Instalación

1. Clona el repositorio y sitúate en la carpeta del proyecto:

```
git clone <repo-url>
cd Satori-Spa
```

2. Instala dependencias:

```
npm install
```

3. Variables de entorno necesarias (ver *Configuración*).

4. Ejecuta en modo desarrollo:

```
npm run dev
```

El servidor por defecto se inicia desde `src/index.js` y usa el puerto `3000`.

## Configuración (Firebase y entorno)

La app usa `firebase-admin` y `dotenv`. En `src/firebase.js` se inicializa con `applicationDefault()` y se referencia un `storageBucket`.

Opciones para proveer credenciales de Firebase:

- Establecer la variable de entorno `GOOGLE_APPLICATION_CREDENTIALS` apuntando al archivo JSON del service account:

	- PowerShell (temporal en la sesión):

		```powershell
		$env:GOOGLE_APPLICATION_CREDENTIALS = 'C:\path\to\serviceAccountKey.json'
		npm run dev
		```

	- Para persistir en Windows (setx):

		```powershell
		setx GOOGLE_APPLICATION_CREDENTIALS "C:\path\to\serviceAccountKey.json"
		```

- Alternativamente, configurar las credenciales por otros medios compatibles con `firebase-admin`.

Recomendaciones de variables `.env` (crear archivo `.env` en la raíz):

- `PORT` — puerto del servidor (por defecto 3000 si no se configura).
- `SESSION_SECRET` — secreto para `express-session` (evitar el valor hardcodeado en `src/app.js`).

Nota: Actualmente `src/app.js` contiene un `secret: 'mysecretkey'`. Es recomendable cambiarlo para usar `process.env.SESSION_SECRET` y guardar el valor en `.env`.

## Estructura del proyecto (resumen)

- `src/` - código fuente
	- `app.js` - configuración de Express, motor de vistas y middlewares
	- `index.js` - arranque del servidor
	- `firebase.js` - inicialización de Firebase Admin (Firestore/Auth/Storage)
	- `routes/` - definiciones de rutas (ej. `routes/index.js`)
	- `views/` - plantillas Handlebars (`.hbs`) y layouts
	- `public/` - archivos estáticos (css, imágenes, etc.)

Archivos raíz importantes:

- `package.json` - dependencias y script `dev` que ejecuta `nodemon src/index.js`.
- `firebase.json` - configuración de hosting/indexes (si aplica).

## Rutas y vistas

La aplicación registra sus rutas desde `./routes/index` (ver `src/routes/index.js`). Las vistas se encuentran en `src/views` y hay subcarpetas para `admin` y `secretaria` con plantillas específicas (`dashboard.hbs`, `servicios.hbs`, `usuarios.hbs`, etc.).

Los layouts y partials están configurados en `src/app.js`:

- Layout principal: `views/layouts/main.hbs`
- Partials: `views/partials/` (ej. `admin_sidebar.hbs`, `secretaria_sidebar.hbs`)

## Ejecutar localmente

1. Instalar dependencias: `npm install`
2. Configurar credenciales de Firebase (ver sección Configuración).
3. Ejecutar en desarrollo:

```
npm run dev
```

Accede a `http://localhost:3000` (o el `PORT` configurado).

## Notas de seguridad y despliegue

- No incluyas el archivo de credenciales de Firebase en el repositorio.
- Cambia el `SESSION_SECRET` por una cadena fuerte y mantenla en variables de entorno.
- Revisa reglas de seguridad de Firestore y Storage antes de poner el proyecto en producción.

## Posibles mejoras / tareas sugeridas

- Mover el `SESSION_SECRET` a variables de entorno y actualizar `src/app.js`.
- Añadir scripts para construir CSS (Tailwind) si se usa: p.ej. `build:css`/`dev:css`.
- Añadir pruebas y CI (GitHub Actions) para validación automática.

## Contribuir

Si quieres contribuir, crea un fork, abre una rama con tu cambio y envía un pull request. Describe el cambio en el PR y agrega instrucciones de testing si aplica.

## Licencia y contacto

Indica aquí la licencia del proyecto o el contacto del autor si aplica.

---

Si quieres, puedo:

- Actualizar `src/app.js` para leer `SESSION_SECRET` desde `.env`.
- Añadir un ejemplo de `tailwind` build script y un archivo `postcss.config.js`/`tailwind.config.js` si deseas usar Tailwind en desarrollo.

¿Qué prefieres que haga a continuación?
