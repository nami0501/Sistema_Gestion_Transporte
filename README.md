Sistema de Gestión y Monitoreo de Transporte

![image](https://github.com/user-attachments/assets/2ee4120e-7530-499c-b8aa-cbad03435682)


Descripción
Sistema web integral para la administración, asignación y monitoreo en tiempo real de flotas de transporte público. Esta plataforma permite gestionar de manera eficiente vehículos, rutas, conductores y proporciona capacidades avanzadas de monitoreo GPS para optimizar las operaciones de transporte.
Características Principales

1. Gestión de Vehículos
Registro y mantenimiento completo de la flota vehicular
Seguimiento de estado operativo y disponibilidad
Historial de mantenimientos y asignaciones
Filtros avanzados por estado, tipo y línea asignada

2. Gestión de Rutas
Definición y optimización de líneas y recorridos
Configuración de estaciones/paraderos con geolocalización
Administración de horarios y frecuencias por línea
Indicadores de rendimiento y puntualidad

3. Gestión de Conductores
Registro completo de conductores con información de licencias
Control de disponibilidad y asignación a vehículos y rutas
Seguimiento de horas de trabajo y descanso
Historial de desempeño y asignaciones

4. Sistema de Asignaciones
Interfaz tipo "carrito" para asignación intuitiva de recursos
Selección dinámica de conductores y vehículos disponibles
Validación en tiempo real de compatibilidad de recursos
Programación de turnos y horarios optimizados

5. Monitoreo en Tiempo Real
Seguimiento GPS de toda la flota en mapa interactivo
Actualización en tiempo real de posición y estado
Alertas automáticas para desviaciones o retrasos
Comparación entre recorridos planificados y ejecutados

Tecnologías Implementadas

Backend: Laravel/PHP
Frontend: JavaScript, HTML5, CSS3
Base de datos: MySQL (o la que estés utilizando)
Mapas: Leaflet/Google Maps API (o la que estés utilizando)
Comunicación en tiempo real: AJAX/fetch/WebSockets

Roles de Usuario

Administrador: Acceso completo a todas las funcionalidades y configuración del sistema
Supervisor: Gestión de asignaciones, monitoreo y reportes
Operador: Monitoreo en tiempo real y gestión de incidentes
Conductor: Visualización de asignaciones y reporte de incidentes

Beneficios

Optimización de recursos de transporte
Mejora en la puntualidad y calidad del servicio
Respuesta rápida ante incidentes
Toma de decisiones basada en datos
Reducción de costos operativos

requisitos
node js 18>= validar con node -v
php
instalar composer : https://getcomposer.org/
pasos iniciales para la instalacion
crear archivo .env con el contenido de .env.example
configurar base de datos psql en .env (nombre bd, usuario y clave)
ejecutar: npm install
ejecutar: composer install
ejecutar: php artisan key:generate
ejecutar: php artisan migrate
ejecutar: php artisan db:seed
pasos para ejecutar el proyecto ( en paralelo ambas terminales)
ejecutar en una terminal: npm run dev
ejecutar en una terminal: php artisan serve


Alumna:

Leslie Karina Virto Cueva.
