# Mejoras de usabilidad para usuarios novatos — AdminISP

Este documento recopila el análisis del proyecto y las recomendaciones para hacer el panel más intuitivo para usuarios con poca experiencia técnica o que usan el sistema por primera vez.

---

## Resumen ejecutivo

AdminISP es un panel multi-tenant completo para ISPs, con módulos como Clientes, Servicios, Red, Finanzas, etc. La estructura actual está orientada a usuarios con conocimiento del dominio (WISP, MikroTik, PPPoE). Para un **usuario novato** (nuevo administrador de un ISP pequeño, personal de atención, etc.) existen barreras de entrada que pueden reducir la adopción y generar errores.

Las recomendaciones se organizan en: **onboarding**, **navegación**, **formularios**, **estados vacíos**, **terminología**, **ayuda contextual**, **portal del cliente** y **feedback**.

---

## 1. Onboarding y primera experiencia

### Problema actual
- No hay **wizard de primer uso** tras crear o activar un ISP.
- Un ISP nuevo entra directo al dashboard con datos vacíos (clientes, recibos, etc.) sin guía de qué hacer primero.
- La landing pública (`onboarding/landing`) es muy básica: título + descripción + 3 botones.
- No existe **tour guiado** ni checklist de “primeros pasos”.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Wizard de primer login**: al primer acceso de un ISP nuevo, mostrar un modal o página “Configuración inicial” con 3–5 pasos: 1) Configurar router(s), 2) Crear planes de internet, 3) Registrar primer cliente, 4) Generar primer recibo. Permitir saltar con “Configurar después”. |
| **Alta** | **Checklist en el dashboard** (visible solo si está incompleto): “Para empezar: [ ] Añadir router, [ ] Crear plan, [ ] Registrar cliente”. Cada ítem enlaza a la acción correspondiente. |
| **Media** | Mejorar la **landing pública**: hero más claro, secciones (qué es, beneficios, precios), CTA destacado (“Probar gratis” / “Solicitar cuenta”). |
| **Media** | Indicador de **progreso de configuración** en el sidebar o navbar (ej. “Configuración 60% completa”) con link al wizard. |

---

## 2. Navegación y descubrimiento

### Problema actual
- Sidebar con **~11 módulos** en una sola columna, sin jerarquía visual ni agrupación semántica.
- Términos técnicos: “Red”, “Infraestructura”, “Mapa de Red”, “PPPoE”, “Almacén”, etc.
- El orden no refleja el flujo típico: un novato esperaría “Clientes” y “Planes/Servicios” antes de “Red” o “Infraestructura”.
- En **Clientes**, el primer paso obligatorio es **elegir un router**; si no hay routers, el usuario ve “Selecciona un router” sin contexto de dónde crearlos.
- En **Servicios** hay varias capas: Servicios → Internet → Planes → y luego seleccionar router. La ruta es larga.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Agrupar el menú** por flujos: “Operación” (Clientes, Tickets, Instalaciones), “Servicios y planes” (Servicios), “Red e infraestructura” (Red, Infraestructura, Mapa de Red), “Finanzas” (Finanzas), “Administración” (Sistema, Control de Acceso, Auditoría). Usar separadores o submenús colapsables. |
| **Alta** | En la vista Clientes, si **no hay routers** configurados: mostrar empty state con mensaje claro “Para listar clientes primero debes configurar al menos un router” y botón “Ir a Red → Routers” en lugar de un selector vacío. |
| **Media** | **Ordenar el sidebar** por frecuencia de uso: Dashboard, Clientes, Servicios, Tickets, Finanzas, Red, … |
| **Media** | **Tooltips en iconos del sidebar**: `title` descriptivo (ej. “Clientes: listado y gestión de clientes del ISP”). AdminLTE ya soporta tooltips; asegurar que estén inicializados. |
| **Baja** | Considerar un **modo simplificado** para roles con permisos limitados: menú reducido solo a lo que pueden usar. |

---

## 3. Estados vacíos y guía

### Problema actual
- Existe `<x-empty-state>` con icono, título, descripción y opcionalmente botón de acción.
- En Clientes sin router: “Selecciona un router” sin explicar que los routers se configuran en Red.
- Algunos listados vacíos no ofrecen acción clara (ej. “Crear primer cliente” sí; otros no).
- El dashboard con datos vacíos muestra tarjetas con “0” sin contexto de “por qué está vacío” ni siguiente paso.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Empty states contextuales** en cada módulo principal: Clientes, Planes, Recibos, Routers. Incluir siempre: mensaje claro, botón de acción principal y, si aplica, link a documentación o ayuda. |
| **Alta** | En **Clientes sin routers**: empty state con ilustración/texto “Configura tu primer router en Red” y enlace directo a la ruta de routers. |
| **Media** | **Dashboard inteligente**: si no hay clientes, mostrar card destacada “Tu panel está listo. Empieza registrando tu primer cliente” con CTA. Si hay clientes pero sin recibos, sugerir “Generar recibos del mes”. |
| **Media** | **Empty state consistente**: revisar todos los módulos y garantizar que nunca se muestre solo una tabla vacía sin mensaje ni acción. |

---

## 4. Formularios y complejidad

### Problema actual
- **Crear cliente**: muchos campos (tipo documento, documento, nombre, teléfonos dinámicos, fuente info). Hay ayuda para DNI/RUC y búsqueda automática, lo cual está bien.
- **Crear servicio**: formulario largo con opciones técnicas (tipo conexión, plan, router, ONU, serial, etc.). Usuarios novatos pueden no saber qué es “PPPoE”, “DHCP”, “ONU”, “OLT”.
- **Clientes → Nuevo Cliente** requiere haber elegido un router en el listado; la relación no es obvia.
- En general faltan **descripciones cortas** en campos técnicos y **valores por defecto** razonables.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Flujo guiado “Nuevo cliente + servicio”**: un asistente que combine en un solo flujo “Datos del cliente” → “Plan y servicio” → “Confirmar”, en lugar de crear cliente y luego añadir servicio por separado. |
| **Alta** | **Glosario o tooltips** en campos técnicos: “PPPoE: tipo de conexión común para fibra”, “ONU: dispositivo en la casa del cliente”, “Serial OLT: identificador en el equipo del ISP”. Usar `title` o `<small class="form-text">`. |
| **Media** | **Valores por defecto**: en formularios de servicio, pre-seleccionar el único plan/router si solo hay uno. |
| **Media** | **Campos opcionales colapsados**: agrupar campos avanzados (OLT, serial ONU, etc.) en una sección “Opciones técnicas” colapsable. |
| **Baja** | **Validación en tiempo real** con mensajes amigables: “El DNI debe tener 8 dígitos” en lugar de solo “El campo documento no es válido”. |

---

## 5. Terminología y etiquetas

### Problema actual
- Términos del dominio WISP: PPPoE, ONU, OLT, NAP, MUF, Caja NAP, Address-list, etc.
- “Cortar servicios” puede sonar agresivo; “Suspender por mora” es más claro.
- “Recibos” vs “Comprobantes” vs “Facturación”: puede haber confusión.
- En el portal del cliente: “Reportar pago” es correcto pero podría complementarse con “Ya pagué” o “Registrar mi pago”.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Media** | **Glosario accesible**: link “¿Qué significa…?” en el footer o en un menú de ayuda, con definiciones breves de PPPoE, ONU, recibo, corte, etc. |
| **Media** | **Etiquetas más amigables**: “Cortar servicios” → “Suspender por mora” o “Aplicar corte a servicios vencidos”. Mantener el término técnico en tooltip si hace falta. |
| **Baja** | **Internacionalización**: preparar las cadenas para traducción; el español debe ser consistente (ej. “Iniciar sesión” vs “Entrar”). |

---

## 6. Ayuda contextual y documentación

### Problema actual
- No hay **centro de ayuda** ni documentación integrada.
- Los tooltips existen solo en algunos formularios (documento, serial ONU).
- No hay **tour guiado** (intro.js, driver.js, shepherd.js) para pantallas clave.
- Los mensajes de error de validación suelen ser genéricos.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Icono de ayuda (?)** en el header o navbar que abra un panel/modal con: “Primeros pasos”, “Preguntas frecuentes” y “Contactar soporte”. |
| **Alta** | **Tour guiado opcional** en el primer login: 3–5 pasos señalando “Aquí gestionas clientes”, “Aquí creas planes”, etc. Con botón “No mostrar de nuevo” y “Omitir”. |
| **Media** | **Ayuda contextual por página**: en páginas complejas (planes, formulario servicio), un botón “Ayuda” que muestre un texto corto explicando la pantalla. |
| **Media** | **Mensajes de error mejorados**: mapear errores de validación a mensajes legibles; por ejemplo, “El documento ya está registrado” en lugar de “El valor del campo documento ya existe”. |
| **Baja** | Documentación externa (Markdown/HTML) con capturas y enlaces desde el panel. |

---

## 7. Portal del cliente (cliente final)

### Problema actual
- Layout simple con navegación: Inicio, Recibos, Reportar pago, Salir.
- Dashboard con “Saldo pendiente”, “Recibos pendientes”, “Últimos pagos”. Las tablas son básicas.
- En móvil, la experiencia puede ser mejorable (tablas sin scroll horizontal explícito).
- No hay explicación de “cómo reportar un pago” (qué datos enviar, qué esperar).
- Falta de personalización (logo del ISP, colores).

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Explicación en “Reportar pago”**: breve texto “Indica la fecha, monto y medio de pago. Un operador verificará y actualizará tu estado.” |
| **Alta** | **Estado claro del pago**: tras reportar, mostrar “Pago reportado. Estado: Pendiente de verificación” con tiempos estimados si aplica. |
| **Media** | **Cards en lugar de tablas** en móvil para recibos y pagos (mejor legibilidad). |
| **Media** | **Branding por tenant**: logo y colores del ISP en el portal. |
| **Baja** | Opción de **descargar recibo en PDF** directamente desde el portal. |

---

## 8. Dashboard y métricas

### Problema actual
- Dashboard con muchas tarjetas: Clientes Totales, Clientes Nuevos, Clientes al Día, Pagos de Hoy, Servicios Activos, Recibos Vencidos, Servicios a Cortar, Ingresos del Mes.
- Para un novato, **8 métricas** pueden ser abrumadoras; no queda claro cuál es la más importante.
- No hay explicación de qué significa “Servicios a Cortar” o “Recibos Vencidos” en el contexto de negocio.
- Botón “Actualizar datos” sin indicar que limpia caché.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Alta** | **Jerarquía visual**: destacar 2–3 KPIs principales (ej. Clientes al día, Ingresos del mes, Recibos vencidos) con tamaño mayor o color. El resto como secundarios. |
| **Media** | **Tooltips en métricas**: al pasar el mouse, “Clientes al día: clientes sin deudas pendientes”. |
| **Media** | Renombrar “Actualizar datos” a “Actualizar estadísticas” o añadir tooltip “Recarga los datos del dashboard”. |
| **Baja** | **Filtro por período** (este mes, último trimestre) para métricas temporales. |

---

## 9. Consistencia y patrones

### Problema actual
- Algunas vistas usan tabs (Clientes, Servicios), otras no.
- Patrón de breadcrumb correcto según `ESTANDAR_VISTAS.md`.
- Acciones primarias a veces a la derecha (header del card), a veces abajo del formulario.
- Los modales (Recibos masivos, Eliminar recibos) tienen buen contenido pero podrían tener un diseño más uniforme.

### Recomendaciones

| Prioridad | Acción |
|-----------|--------|
| **Media** | **Botón primario consistente**: en formularios, siempre “Guardar” / “Crear” como botón principal, a la derecha, con icono. Cancelar/Volver a la izquierda. |
| **Media** | **Confirmaciones destructivas**: usar diseño consistente (modal con icono de advertencia, texto claro, botón rojo “Eliminar” / “Confirmar”). |
| **Baja** | Revisar que todas las vistas sigan `ESTANDAR_VISTAS.md` (title, page-title, breadcrumb, content). |

---

## 10. Priorización sugerida

Para maximizar el impacto con el menor esfuerzo, se sugiere este orden:

1. **Fase 1 (rápido)**  
   - Empty state en Clientes sin routers con enlace a Red.  
   - Checklist de “Primeros pasos” en dashboard para ISPs nuevos.  
   - Mejora de mensajes de error en validación (mapeo a mensajes legibles).

2. **Fase 2 (medio)**  
   - Wizard de primer login (3–5 pasos).  
   - Agrupación del menú del sidebar.  
   - Tour guiado opcional en primera visita.  
   - Glosario/tooltips en campos técnicos de formularios.

3. **Fase 3 (más esfuerzo)**  
   - Flujo guiado “Nuevo cliente + servicio”.  
   - Centro de ayuda con FAQ.  
   - Mejoras en portal del cliente (explicaciones, estado de reporte de pago).

---

## Referencias

- `docs/ESTANDAR_VISTAS.md` — Estructura de vistas Blade
- `docs/ANALISIS_SIDEBAR_NAVBAR_FOOTER_CONTENIDO.md` — Layout y sidebar
- `docs/PLAN_SAAS_100.md` — Fase 2.5: Onboarding guiado
- `.cursor/skills/adminisp/SKILL.md` — Mobile-first, multi-tenant, RBAC
