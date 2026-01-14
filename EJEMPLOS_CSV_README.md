# Ejemplos de CSV para Importación de Contactos

He creado varios archivos CSV de ejemplo para probar la funcionalidad de importación de contactos.

## Archivos Disponibles

### 1. `ejemplo_contactos.csv` (Completo)
**Contiene**: 20 contactos con teléfono, nombre y email
**Columnas**: `telefono`, `nombre`, `email`
**Uso**: Para probar la importación completa con todos los campos

```csv
telefono,nombre,email
+5215551234567,Juan Pérez,juan.perez@example.com
+5215559876543,María García,maria.garcia@example.com
...
```

---

### 2. `ejemplo_contactos_simple.csv` (Solo Teléfono y Nombre)
**Contiene**: 10 contactos con teléfono y nombre
**Columnas**: `telefono`, `nombre`
**Uso**: Para probar importación sin email (campo opcional)

```csv
telefono,nombre
+5215551234567,Juan Pérez
+5215559876543,María García
...
```

---

### 3. `ejemplo_contactos_variantes.csv` (Nombres de Columnas Alternos)
**Contiene**: 5 contactos
**Columnas**: `numero`, `nombre`, `correo` (en lugar de telefono/email)
**Uso**: Para probar el mapeo automático con nombres de columnas diferentes

```csv
numero,nombre,correo
+5215551234567,Juan Pérez,juan.perez@example.com
...
```

---

### 4. `ejemplo_contactos_sin_email.csv` (Celular)
**Contiene**: 15 contactos
**Columnas**: `celular`, `nombre` (sin email)
**Uso**: Para probar mapeo con "celular" en lugar de "telefono"

```csv
celular,nombre
+5215551234567,Juan Pérez
+5215559876543,María García
...
```

---

## Cómo Probar la Importación

### Paso 1: Acceder al Módulo de Importación
1. Ve a tu aplicación: `http://127.0.0.1:8000/contacts-import`
2. O desde Contactos → Botón "Importar Contactos"

### Paso 2: Subir el Archivo
1. Haz clic en "Selecciona tu archivo CSV"
2. Elige uno de los archivos de ejemplo creados
3. Haz clic en "Subir y Continuar"

### Paso 3: Mapear Columnas
El sistema debería detectar automáticamente:
- `telefono`, `teléfono`, `phone`, `celular`, `movil`, `numero` → Campo "Teléfono"
- `nombre`, `name` → Campo "Nombre"
- `email`, `correo`, `e-mail`, `mail` → Campo "Email"

Si el mapeo no es correcto, ajústalo manualmente en los dropdowns.

### Paso 4: Iniciar Importación
1. Verifica que la columna de teléfono esté mapeada (requerido)
2. Haz clic en "Iniciar Importación"
3. La importación se procesará en segundo plano

### Paso 5: Verificar Resultados
1. Ve a la sección de Contactos
2. Deberías ver los nuevos contactos importados
3. Verifica que los campos se hayan importado correctamente

---

## Formato de Números de Teléfono

Todos los números en los ejemplos están en formato internacional mexicano:
- **Formato**: `+52 15 XXXX XXXX`
- **Ejemplo**: `+5215551234567`

### Para Otros Países

Si necesitas importar contactos de otros países, usa el código de país correspondiente:

- **Colombia**: `+57 3XX XXX XXXX` → `+573001234567`
- **Argentina**: `+54 9 11 XXXX XXXX` → `+5491112345678`
- **España**: `+34 6XX XXX XXX` → `+34612345678`
- **Estados Unidos**: `+1 XXX XXX XXXX` → `+11234567890`

---

## Escenarios de Prueba Recomendados

### ✅ Prueba 1: Importación Completa
**Archivo**: `ejemplo_contactos.csv`
**Objetivo**: Verificar que todos los campos se importen correctamente

### ✅ Prueba 2: Solo Campos Requeridos
**Archivo**: `ejemplo_contactos_simple.csv`
**Objetivo**: Verificar que funciona sin el campo opcional de email

### ✅ Prueba 3: Mapeo Automático
**Archivo**: `ejemplo_contactos_variantes.csv`
**Objetivo**: Verificar que el sistema detecta automáticamente "numero" y "correo"

### ✅ Prueba 4: Columna "Celular"
**Archivo**: `ejemplo_contactos_sin_email.csv`
**Objetivo**: Verificar que funciona con diferentes nombres de columnas

### ✅ Prueba 5: Duplicados
**Archivo**: `ejemplo_contactos.csv` (importarlo dos veces)
**Objetivo**: Verificar que el sistema no crea duplicados (debe actualizar existentes)

---

## Notas Importantes

1. **Formato del CSV**: Asegúrate de que el archivo esté codificado en UTF-8 para caracteres especiales (á, é, í, ó, ú, ñ)

2. **Tamaño Máximo**: 10 MB por archivo

3. **Primera Fila**: Siempre debe contener los nombres de las columnas

4. **Teléfonos Únicos**: El sistema usa el número de teléfono como identificador único

5. **Procesamiento en Background**: La importación se procesa en segundo plano mediante jobs/queues

---

## Solución de Problemas

### Error: "Debes mapear al menos la columna de Teléfono"
**Solución**: Asegúrate de seleccionar el campo "Teléfono (requerido)" en al menos una columna

### Error: "Field 'name' doesn't have a default value"
**Solución**: Este error ya fue corregido en el código. Si persiste, actualiza el código del WebhookService

### Los contactos no aparecen después de importar
**Posibles causas**:
1. El job no se está procesando → Ejecuta manualmente: `php artisan queue:work --once`
2. Error en el formato de teléfonos → Verifica que tengan el prefijo `+` y código de país
3. Revisa los logs: `storage/logs/laravel.log`

---

## Crear tus Propios CSV

Puedes crear tus propios archivos CSV con Excel, Google Sheets o un editor de texto:

### Con Excel/Google Sheets:
1. Crea una tabla con las columnas: `telefono`, `nombre`, `email`
2. Llena los datos
3. Exporta como CSV (Delimitado por comas)
4. Asegúrate de usar UTF-8

### Con Editor de Texto:
1. Crea un archivo .txt
2. Escribe la primera línea con los nombres de las columnas separados por comas
3. Agrega cada contacto en una nueva línea
4. Guarda con extensión .csv

---

## Estructura de la Base de Datos

Los contactos se guardan en la tabla `contacts` con estos campos:

- `id`: ID único
- `tenant_id`: ID del tenant (multi-tenancy)
- `phone`: Número de teléfono (único por tenant)
- `name`: Nombre del contacto
- `email`: Email (opcional)
- `whatsapp_verified`: Si el número está verificado en WhatsApp
- `last_message_at`: Fecha del último mensaje
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

---

¡Listo para importar! 🚀
