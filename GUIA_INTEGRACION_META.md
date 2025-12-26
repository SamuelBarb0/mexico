# 🚀 GUÍA DE INTEGRACIÓN CON META WHATSAPP API

## ✅ PASO 1: CREDENCIALES CONFIGURADAS

Ya están agregadas en el archivo `.env`:

```env
META_APP_ID=854810716899922
META_ACCESS_TOKEN=EAAMJciEhilIBQFmRw3kOyH96SucW7jh6kZCRYfSdjWvxmeZBnb6s2dxB9ziXJpKckZBIdYuB1ftoN8IFEzOSe7ZA5EBU2dFIyLCnVkabFKinqiXrEhyp3ZBZAlCy2WBCNlZANXq3ZBwup5PSZB6FVZArfwkEE6hXxXVFtNGgIZCusj3bpWu37cZCLazGcZBmiRF9IiXguWUpTPDW014WZCk6Xlklyk8cXTSgHwUNfMjIzTZBHbuUCvK1qaZBTzZC6zo8jtumQZA21JHAZCasKIo7SIDs7mRtsQTBUqLubbn5YsZD
META_GRAPH_API_URL=https://graph.facebook.com
META_API_VERSION=v21.0
```

---

## 📋 PASO 2: INFORMACIÓN QUE NECESITAS DE META

Para completar la integración, necesitas obtener de tu cuenta de Meta Business:

### **A. WABA ID (WhatsApp Business Account ID)**
- Ve a: https://business.facebook.com/
- Selecciona tu cuenta de WhatsApp Business
- El ID está en la URL o en Configuración

### **B. Phone Number ID**
- En Meta Business Suite > WhatsApp > Configuración de API
- Busca "Phone Number ID" del número que usarás para enviar

### **C. Verificar que el Access Token tenga permisos:**
- `whatsapp_business_messaging`
- `whatsapp_business_management`

---

## 🔧 PASO 3: CREAR TU PRIMERA WABA ACCOUNT EN EL SISTEMA

1. **Ve a:** http://localhost/mexico/public/waba-accounts/create

2. **Completa el formulario:**
   - **Nombre**: Mi WhatsApp Business
   - **WABA ID**: [El que obtuviste de Meta]
   - **Phone Number ID**: [El que obtuviste de Meta]
   - **Access Token**: Usa el mismo del .env o uno específico
   - **Phone Number**: +52XXXXXXXXXX (el número de WhatsApp)
   - **Business Account ID**: 854810716899922 (App ID)
   - **Estado**: Activa

3. **Guardar**

---

## 📝 PASO 4: CREAR TU PRIMERA PLANTILLA

### **Opción A: Crear desde la interfaz (Recomendado para pruebas)**

1. **Ve a:** http://localhost/mexico/public/templates/create

2. **Completa:**
   - **Nombre**: `prueba_bienvenida` (solo minúsculas, números y guiones bajos)
   - **Categoría**: UTILITY
   - **Idioma**: es (español)
   - **WABA Account**: Selecciona la que creaste

3. **Cuerpo del mensaje:**
   ```
   Hola {{1}}, bienvenido a nuestro servicio de WhatsApp!
   ```

4. **Guardar como Borrador**

5. **Enviar a Meta** (botón en la vista de detalle)

6. **Esperar aprobación** (puede tomar 5 min - 24 horas)

7. **Sincronizar estado** para verificar si fue aprobada

### **Opción B: Crear directamente en Meta Business**

1. Ve a Meta Business Suite > WhatsApp > Plantillas de mensajes
2. Crea la plantilla allí
3. En el sistema: Click en "Sincronizar todas las plantillas"

---

## 🎯 PASO 5: CREAR Y EJECUTAR CAMPAÑA DE PRUEBA

### **5.1 Asegúrate de tener contactos**

1. **Ve a:** http://localhost/mexico/public/contacts
2. **Crea al menos 1 contacto de prueba:**
   - Nombre: Test User
   - Teléfono: +52XXXXXXXXXX (tu número de WhatsApp personal para pruebas)
   - Email: test@test.com
   - Estado: activo

### **5.2 Crear la campaña**

1. **Ve a:** http://localhost/mexico/public/campaigns/create

2. **Configurar:**
   - **Nombre**: Prueba Bienvenida
   - **Descripción**: Primera prueba de envío
   - **WABA Account**: La que creaste
   - **Tipo**: Broadcast
   - **Plantilla**: Selecciona la plantilla aprobada
   - **Audiencia**: Todos los contactos

3. **Crear Campaña**

### **5.3 Preparar mensajes**

1. En la página de detalle de la campaña
2. Click en **"Preparar Campaña"**
3. El sistema creará mensajes individuales para cada contacto

### **5.4 Configurar Laravel Queue**

**Opción Simple (Database):**
```bash
# Ya está configurado en .env:
QUEUE_CONNECTION=database

# Crear tabla de jobs si no existe
php artisan queue:table
php artisan migrate

# Iniciar worker (en una terminal separada)
php artisan queue:work --tries=3
```

**Opción Avanzada (Redis) - MEJOR RENDIMIENTO:**
```bash
# Instalar Redis en Windows
# Descargar: https://github.com/tporadowski/redis/releases

# En .env cambiar:
QUEUE_CONNECTION=redis

# Iniciar worker
php artisan queue:work redis --tries=3
```

### **5.5 Ejecutar campaña**

1. Con el queue worker corriendo
2. En la campaña, click en **"Ejecutar Campaña"**
3. Los mensajes se enviarán en segundo plano
4. Refresca la página para ver métricas actualizarse

---

## 🔍 PASO 6: VERIFICAR LOGS

Si algo falla, revisa:

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs del queue worker
# (Se muestran en la terminal donde corre queue:work)
```

---

## 🐛 TROUBLESHOOTING COMÚN

### **Error: "Invalid OAuth access token"**
- El token expiró (tokens de prueba duran ~60 días)
- Genera uno nuevo en Meta Developer Console
- Actualiza `.env` y reinicia servidor

### **Error: "Phone number not verified"**
- El número debe estar verificado en Meta Business
- Verifica en Meta Business Suite > WhatsApp

### **Error: "Template not approved"**
- Solo puedes usar plantillas APPROVED
- Sincroniza estado en el sistema
- Espera aprobación de Meta

### **Error: "Unable to deliver message"**
- El número del contacto debe estar registrado en WhatsApp
- El formato debe ser E.164: +52XXXXXXXXXX
- El contacto debe aceptar mensajes de negocios

### **Error: "Queue not processing"**
- Asegúrate que `queue:work` esté corriendo
- Verifica tabla `jobs` en la base de datos
- Reinicia el worker si cambias código

---

## 📊 PASO 7: VERIFICAR MÉTRICAS

1. **Ve a la campaña**
2. Click en **"Ver Métricas"**
3. Verás:
   - Estados de mensajes (PENDING, SENT, DELIVERED, READ)
   - Tasas de entrega y lectura
   - Mensajes fallidos con errores

---

## 🎓 CONCEPTOS IMPORTANTES

### **Estados de Mensaje:**
1. **PENDING**: Creado, esperando envío
2. **QUEUED**: En cola del sistema
3. **SENT**: Enviado a WhatsApp API
4. **DELIVERED**: Entregado al dispositivo del usuario
5. **READ**: Leído por el usuario
6. **FAILED**: Error en envío

### **Límites de Meta:**
- **Sandbox**: ~250 mensajes/día
- **Tier 1**: 1,000 conversaciones/día
- **Tier 2**: 10,000 conversaciones/día
- **Tier 3**: 100,000 conversaciones/día
- **Tier 4**: Ilimitado

### **Rate Limits:**
- ~80 mensajes/segundo
- El sistema ya implementa rate limiting (0.1s entre mensajes)

---

## 🔐 SEGURIDAD - IMPORTANTE

### **Token de Acceso:**
⚠️ **NUNCA compartas el access token públicamente**
- Es como una contraseña
- Tiene acceso completo a tu WhatsApp Business
- Rótalo periódicamente

### **Producción:**
- Usa System User Token (no expira)
- Implementa refresh token rotation
- Usa variables de entorno (.env)
- NO commites el .env al repositorio

---

## 📞 SOPORTE META

- **Documentación**: https://developers.facebook.com/docs/whatsapp
- **API Reference**: https://developers.facebook.com/docs/graph-api/reference/whatsapp-business-account
- **Soporte**: https://business.facebook.com/business/help

---

## ✅ CHECKLIST DE INTEGRACIÓN

- [x] Credenciales en `.env`
- [x] Config en `services.php`
- [ ] WABA Account creada en el sistema
- [ ] Phone Number ID obtenido
- [ ] Primera plantilla creada y aprobada
- [ ] Contacto de prueba agregado
- [ ] Queue worker configurado y corriendo
- [ ] Primera campaña creada
- [ ] Campaña preparada (mensajes generados)
- [ ] Campaña ejecutada
- [ ] Mensaje de prueba recibido en WhatsApp
- [ ] Métricas verificadas

---

## 🎉 SIGUIENTE PASO PARA PRODUCCIÓN

Una vez que todo funcione:

1. **Webhook para actualizaciones de estado**
   - Configurar endpoint en Laravel
   - Registrar en Meta Business
   - Recibir callbacks de DELIVERED/READ

2. **Sistema de Listas y Tags**
   - Segmentación avanzada
   - Grupos de contactos

3. **Variables Dinámicas**
   - Mapeo de campos de contacto
   - Personalización avanzada

4. **Dashboard de Análisis**
   - Gráficas de rendimiento
   - Comparación de campañas

---

¿Listo para empezar? Sigue los pasos en orden y estarás enviando mensajes en minutos! 🚀
