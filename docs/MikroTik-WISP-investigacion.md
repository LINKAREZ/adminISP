# Investigación a fondo: MikroTik para WISP

Documento de investigación basado en la documentación oficial de MikroTik, manuales y foros. Enfocado en despliegues WISP (Wireless Internet Service Provider).

---

## 1. Fuentes oficiales de documentación

| Recurso | URL | Descripción |
|--------|-----|-------------|
| **RouterOS (manual principal)** | https://help.mikrotik.com/docs/display/ROS/RouterOS | Manual oficial del sistema operativo. Aplica a la última versión estable. |
| **Manual en PDF** | https://box.mikrotik.com/f/c3ea66e750444a6383be/ | PDF actualizado mensualmente para uso offline. |
| **Wireless** | https://help.mikrotik.com/docs/display/ROS/Wireless | Configuración 802.11 y ejemplos de uso. |
| **Blog / Newsletter** | help.mikrotik.com → Blog | Novedades de productos y software. |

**Importante:** No existe un “manual WISP” único; el WISP se arma combinando Wireless, PPPoE, Hotspot, RADIUS, User Manager, colas, etc. Cada módulo tiene su sección en la documentación RouterOS.

---

## 2. Wireless (inalámbrico)

### 2.1 Paquetes y drivers (RouterOS v7.13+)

- **CAPsMAN** puede ejecutarse en cualquier dispositivo RouterOS (incluso sin interfaz inalámbrica).
- Dispositivos **MIPS**: solo drivers legacy (`wireless.npk`).
- **ARM 802.11ac**: elección entre `wireless.npk` (Nstreme, Nv2) o `wifi-qcom-ac.npk` (WPA3, Fast Roaming).
- **802.11ax**: solo drivers nuevos (`wifi-qcom.npk`); Nv2/Nstreme no disponibles.

### 2.2 Bandas de frecuencia

| Banda | Uso típico | Alcance | Throughput | Notas |
|-------|------------|---------|------------|--------|
| **2.4 GHz** | Legacy, IoT, clientes genéricos | Mayor | Menor | Poca cantidad de canales no solapados; mucha interferencia. |
| **5 GHz** | Enlaces y clientes ac/ax | Menor | Mayor | Más canales; ideal para PtP/PtMP. |
| **60 GHz** | Enlaces punto a punto (Wireless Wire) | Hasta ~1500 m LoS | 1 Gbps dúplex | Solo línea de vista clara. |

### 2.3 Casos de uso

- **AP para móviles/portátiles:** 20–50 clientes por interfaz para buen rendimiento; hasta ~100 según condiciones. Menos clientes si se requiere alto throughput o baja latencia.
- **CPE a AP (PtP/PtMP):** Distancia, necesidad de PtP vs PtMP y velocidad definen equipo (sector, antena, 60 GHz vs 5 GHz).
- **PtMP:** El AP debe tener al menos **nivel de licencia 4**. Clientes pueden ser nivel 3.
- **CAPsMAN:** Gestión centralizada de muchos AP. Cualquier RouterOS puede ser servidor CAPsMAN; dispositivos con interfaz 2.4/5 GHz y nivel ≥4 pueden ser CAPs.

### 2.4 CAPsMAN (Controller)

- **CAPsMAN** = Controlled Access Point system Manager.
- Los CAP (Controlled Access Points) solo necesitan configuración mínima para conectarse al manager.
- Control centralizado: múltiples SSIDs, VLANs, AAA/RADIUS, reenvío local o centralizado.
- Sin límite teórico de CAPs; hasta 32 radios por CAP y 32 interfaces virtuales por radio maestra.

---

## 3. PPPoE (Point-to-Point over Ethernet)

Documentación: https://help.mikrotik.com/docs/display/ROS/PPPoE

### 3.1 Rol en WISP

- Asignación de IP por **usuario/contraseña** (no solo por estación).
- Funciona sobre cualquier interfaz capa 2: Ethernet, Wireless, EoIP, VLAN.
- **MTU/MRU típico:** 1492 (Ethernet 1500 − 8 PPPoE/PPP). Ajustar MSS si hay fragmentación.

### 3.2 PPPoE Server (Access Concentrator)

- Pool de IP, perfil PPP, secretos (usuarios) y servidor PPPoE por interfaz.
- **one-session-per-host:** una sesión por MAC.
- **keepalive-timeout:** detectar clientes desconectados sin cierre correcto.
- **pppoe-over-vlan-range:** PPPoE sobre VLANs 802.1Q (rango de VLAN IDs).
- Múltiples instancias de servidor por interfaz con distintos **service-name**.

### 3.3 Integración con RADIUS

- El cliente RADIUS del router consulta al servidor RADIUS para autenticar PPP (PPPoE, PPP, PPTP, L2TP).
- Si no hay usuario local que coincida, se usa RADIUS. Los atributos devueltos sobrescriben el perfil por defecto.
- Atributo clave para velocidad: **Mikrotik-Rate-Limit** (desde el punto de vista del router: rx = subida del usuario, tx = bajada del usuario). Ejemplos: `1M`, `1M/2M`, con burst y prioridad.

---

## 4. Hotspot (captive portal)

Documentación: https://help.mikrotik.com/docs/display/ROS/HotSpot+-+Captive+portal

### 4.1 Características

- **Autenticación:** base local y/o RADIUS.
- **Accounting:** local o RADIUS (tiempo, datos).
- **Walled garden:** acceso a URLs sin autenticación.
- **Universal Client:** reasignación transparente de IP al cliente.
- **RFC 7710:** notificación a clientes DHCP de que hay captive portal (opción DHCP con URL de login).
- **Requisitos:** Hotspot habilitado en `system/device-mode`; **solo IPv4** (NAT; IPv6 no soportado para Hotspot). Licencia nivel 4 mínima.

### 4.2 Menús principales

- `/ip hotspot`: servidores Hotspot (uno por interfaz).
- `/ip hotspot active`: sesiones activas (solo lectura).
- `/ip hotspot host`: tabla de hosts (autorizados y no).
- `/ip hotspot ip-binding`: bypass, bloqueo, NAT 1:1.
- Perfiles: timeouts, límites de sesión/bytes, uso de RADIUS.

Útil para WISP en escenarios de “zona wifi pública” o complemento a PPPoE; para clientes residenciales suele preferirse PPPoE + RADIUS.

---

## 5. RADIUS

Documentación: https://help.mikrotik.com/docs/display/ROS/RADIUS

### 5.1 Servicios que pueden usar RADIUS

- Login (usuarios del router), **Hotspot**, **PPP** (PPPoE, PPTP, L2TP), OVPN, SSTP, IPsec, **Wireless**, DHCP, Dot1x.

### 5.2 Cliente RADIUS en RouterOS

- `/radius`: dirección, puertos auth (1812) y accounting (1813), secret, **service** (ppp, hotspot, login, wireless, dhcp, ipsec, dot1x).
- Opción **RadSec** (RFC 6614) con certificados.
- **Connection terminating:** `/radius incoming` para aceptar Disconnect Messages (DM) desde el RADIUS y cerrar sesiones.

### 5.3 Atributos relevantes para WISP (resumen)

- **Mikrotik-Rate-Limit:** límite de velocidad (rx/tx desde vista router). Formato: `rx[/tx]` y opcionalmente burst/threshold/time y prioridad.
- **Framed-IP-Address / Framed-Pool:** IP o pool para el cliente.
- **Session-Timeout, Idle-Timeout:** tiempo de sesión e inactividad.
- **Mikrotik-Recv-Limit / Mikrotik-Xmit-Limit:** cuota en bytes (recibir/enviar desde vista router).
- **Filter-Id:** cadena de firewall para reglas dinámicas.
- **Mikrotik-Group:** perfil (PPP, HotSpot) o grupo de usuario.
- WISPr: redirección, ancho de banda min/max, sesión.

En la documentación hay un **RADIUS reference dictionary** y **MikroTik Vendor attributes** descargables para integrar con FreeRADIUS u otro servidor.

---

## 6. User Manager (RADIUS en RouterOS)

Documentación: https://help.mikrotik.com/docs/display/ROS/User+Manager

### 6.1 Descripción

- **User Manager** es un servidor RADIUS dentro de RouterOS.
- Autenticación centralizada para PPP, Hotspot, DHCP, Dot1x, IPsec, Wireless.
- Métodos: PAP, CHAP, MS-CHAP, EAP-TLS, EAP-TTLS, EAP-PEAP.
- Base de datos propia (SQLite); perfiles, limitaciones (cuota, tiempo, rate-limit), sesiones, pagos (PayPal).
- Interfaz web para usuarios: `/um/` (estadísticas, perfiles, compra de planes).

### 6.2 Elementos clave

- **Users / User groups:** usuarios y atributos RADIUS por grupo.
- **Profiles:** perfiles con validez, precio, límites.
- **Limitations:** rate-limit, download/upload/uptime limits; enlazados a perfiles vía profile-limitation.
- **Routers (NAS):** se definen los routers (IP + shared-secret) que pueden usar User Manager como RADIUS.
- **Sessions:** sesiones contabilizadas (requiere accounting en el NAS).
- **CoA:** Change of Authorization (cambios en vivo de límites, desconexión, etc.) si el cliente RADIUS acepta mensajes entrantes.

Muy útil para WISP pequeños/medianos que no quieren montar FreeRADIUS externo; para muchos clientes o integración con billing externo suele usarse RADIUS externo.

---

## 7. Nv2 (protocolo inalámbrico propietario)

Documentación: https://help.mikrotik.com/docs/display/ROS/Nv2

### 7.1 Conceptos

- **Nv2** es un protocolo MikroTik para chips Atheros 802.11 (11n y legacy a/b/g desde AR5212).
- **TDMA** (Time Division Multiple Access) en lugar de CSMA; el AP asigna tiempos de emisión.
- Reduce el problema del “nodo oculto” y mejora uso del medio en **PtMP**.
- Hasta **511 clientes por interfaz**.
- **No es compatible con 802.11 estándar:** solo equipos MikroTik con Nv2 pueden participar. En el mismo canal puede haber interferencia con otras redes.

### 7.2 Características

- TDMA, WDS, QoS (colas por prioridad), cifrado (AES-CCM), autenticación RADIUS, Nv2-security (preshared key).
- **Nv2-cell-radius:** distancia al cliente más lejano (km); afecta slots de conexión y “ranging”.
- **tdma-period-size:** tamaño del periodo TDMA (ms); menor periodo = menor latencia pero más overhead.
- **Nv2-mode:** dynamic-downlink, fixed-downlink, **sync-master**, **sync-slave** (sincronización entre varios AP Nv2 en la misma torre para reutilizar frecuencia).

### 7.3 Migración desde Nstreme / 802.11

- En clientes: `wireless-protocol=Nv2-nstreme-802.11` para que intenten Nv2, luego Nstreme, luego 802.11.
- Primero actualizar AP y clientes, luego activar Nv2 en el AP. Así se minimiza el tiempo sin servicio.

**Nota:** En dispositivos 802.11ax solo hay drivers nuevos; Nv2 no está disponible ahí.

---

## 8. Colas (Queue) y ancho de banda

- **Simple Queue** (`/queue simple`): límite por IP/subred/interfaz; sencillo para “por cliente” (upload/download). Se combina con PCQ para reparto entre conexiones.
- **Queue Tree** (`/queue tree`): políticas avanzadas; requiere marcar tráfico con `/ip firewall mangle`. Jerarquía padre–hijo (HTB).
- **PCQ:** reparto de ancho de banda por conexión (muy usado en WISP para repartir entre usuarios).
- En WISP es habitual: **RADIUS** (Mikrotik-Rate-Limit) para el límite por usuario y, si se quiere, Simple Queue/PCQ en el router para aplicar y repartir.

---

## 9. Esquema típico WISP (resumen)

1. **Núcleo:** Router principal (ej. RB3011, CCR) con salida a internet.
2. **Switch:** VLANs si hay segmentación (clientes, gestión, etc.).
3. **AP / sector:** En torre o punto alto; modo AP (802.11 o Nv2); puede ser gestionado por CAPsMAN.
4. **Cliente (CPE):** Estación inalámbrica (SXT, hAP, etc.) en modo cliente, conectada al AP.
5. **Autenticación/Accounting:**  
   - **Opción A:** PPPoE server en router de agregación + RADIUS (User Manager o FreeRADIUS).  
   - **Opción B:** Hotspot en segmento “wifi público” + RADIUS.
6. **Velocidad y cuotas:** Atributos RADIUS (Mikrotik-Rate-Limit, Recv-Limit, Xmit-Limit, Session-Timeout) o perfiles/limitaciones en User Manager. Opcionalmente Simple Queue/PCQ en el router.
7. **Aislamiento:** VLANs, reglas firewall, y en wireless evitar reenvío entre clientes (Mikrotik-Wireless-Forward u diseño de red).

Ejemplo de equipo sugerido en foro (escenario pequeño, &lt;50 clientes, &lt;50 Mbps): modem de ISP → router (RB3011/RB2011/CCR) → switch → AP (NetMetal, mANTBox, SXT 5 ac según cobertura) → en cliente: SXT 5ac o QRT 5ac + hAP ac lite en interior.

---

## 10. Licencias RouterOS

- **Nivel 3:** Clientes inalámbricos, PtP.
- **Nivel 4:** Necesario para **AP en PtMP**, Hotspot, y otras funciones de “servidor” de acceso. Es el mínimo típico en el equipo que hace de concentrador/AP/PPPoE.

---

## 11. Enlaces rápidos por tema

| Tema | Enlace documentación |
|------|----------------------|
| RouterOS (índice) | https://help.mikrotik.com/docs/display/ROS/RouterOS |
| Wireless | https://help.mikrotik.com/docs/display/ROS/Wireless |
| WiFi (nuevo menú v7) | https://help.mikrotik.com/docs/display/ROS/WiFi |
| PPPoE | https://help.mikrotik.com/docs/display/ROS/PPPoE |
| Hotspot | https://help.mikrotik.com/docs/display/ROS/HotSpot+-+Captive+portal |
| RADIUS | https://help.mikrotik.com/docs/display/ROS/RADIUS |
| User Manager | https://help.mikrotik.com/docs/display/ROS/User+Manager |
| Nv2 | https://help.mikrotik.com/docs/display/ROS/Nv2 |
| CAPsMAN | Buscar "CAPsMAN" o "AP Controller" en help.mikrotik.com |
| Queues | https://help.mikrotik.com/docs/display/ROS/Queues |
| Device-mode | https://help.mikrotik.com/docs/display/ROS/Device-mode (Hotspot, etc.) |

---

## 12. Foros y guías no oficiales

- **Foro MikroTik – WISP setup:** https://forum.mikrotik.com/viewtopic.php?t=110883 (equipamiento básico y flujo; recomendación de tener conocimientos de red o administrador con experiencia).
- **Guía “Starting a Basic WISP with MikroTik”:** cursos/recursos tipo mynetworktraining.com (configuración core, AP, CPE, aislamiento, QoS, seguridad).
- **FreeRADIUS + MikroTik:** documentación RADIUS de MikroTik incluye diccionario para FreeRADIUS; muchos WISP usan FreeRADIUS como RADIUS central con PPPoE en MikroTik.

---

*Documento generado a partir de la documentación oficial de MikroTik (help.mikrotik.com) y recursos del foro. Para implementación concreta, usar siempre la versión actual del manual RouterOS y comprobar requisitos de licencia y device-mode en tu versión.*
