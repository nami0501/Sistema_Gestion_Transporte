/**
 * Sistema de Transporte - Módulo de Monitoreo GPS
 * 
 * Este archivo proporciona funciones auxiliares para el mapa de monitoreo GPS
 * y complementa el script en la vista mapa.blade.php
 */

// Clase para manejar el sistema de iconos personalizados
class IconoVehiculo {
    /**
     * Crea un icono para un vehículo según su tipo y estado
     * 
     * @param {string} tipo - Tipo de vehículo (Bus, Articulado, Minibus, etc.)
     * @param {string} estado - Estado del vehículo (En movimiento, Detenido, etc.)
     * @returns {L.Icon} Objeto icono de Leaflet
     */
    static crear(tipo, estado) {
        // Ruta base para iconos
        const basePath = '/img/iconos/';
        
        // Determinar archivo de icono según tipo
        let fileName = 'vehiculo.png'; // Icono por defecto
        
        switch (tipo.toLowerCase()) {
            case 'bus':
                fileName = 'bus.png';
                break;
            case 'articulado':
                fileName = 'bus-articulado.png';
                break;
            case 'biarticulado':
                fileName = 'bus-biarticulado.png';
                break;
            case 'minibus':
                fileName = 'minibus.png';
                break;
            case 'tren':
            case 'vagón':
                fileName = 'tren.png';
                break;
        }
        
        // Crear y retornar el icono
        return L.icon({
            iconUrl: basePath + fileName,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16],
            className: `vehiculo-icono estado-${estado.toLowerCase().replace(' ', '-')}`
        });
    }
}

// Clase para manejar la rotación de marcadores según dirección
class RotacionMarcador {
    /**
     * Inicializa la rotación para un marcador
     * 
     * @param {L.Marker} marcador - Marcador de Leaflet
     * @param {number} angulo - Ángulo de rotación en grados
     */
    static aplicar(marcador, angulo) {
        if (!marcador) return;
        
        const icono = marcador.getElement();
        if (icono) {
            icono.style.transform = `${icono.style.transform} rotate(${angulo}deg)`;
        }
    }
}

// Clase para calcular colores en gradiente según valores
class ColorGradiente {
    /**
     * Calcula un color en gradiente entre rojo y verde según un valor porcentual
     * 
     * @param {number} valor - Valor entre 0 y 100
     * @returns {string} Color en formato hexadecimal
     */
    static calcular(valor) {
        // Asegurar que el valor esté entre 0 y 100
        const v = Math.max(0, Math.min(100, valor));
        
        // Calcular componentes RGB
        let r, g;
        if (v < 50) {
            // De rojo a amarillo
            r = 255;
            g = Math.round(5.1 * v);
        } else {
            // De amarillo a verde
            r = Math.round(510 - 5.1 * v);
            g = 255;
        }
        
        // Convertir a hexadecimal
        const rHex = r.toString(16).padStart(2, '0');
        const gHex = g.toString(16).padStart(2, '0');
        
        return `#${rHex}${gHex}00`;
    }
}

// Función para formatear la distancia en forma legible
function formatearDistancia(distanciaMetros) {
    if (distanciaMetros < 1000) {
        return `${Math.round(distanciaMetros)} m`;
    } else {
        return `${(distanciaMetros / 1000).toFixed(2)} km`;
    }
}

// Función para formatear el tiempo en forma legible
function formatearTiempo(minutos) {
    if (minutos < 60) {
        return `${Math.round(minutos)} min`;
    } else {
        const horas = Math.floor(minutos / 60);
        const min = Math.round(minutos % 60);
        return `${horas} h ${min} min`;
    }
}

// Función para calcular el tiempo estimado de llegada
function calcularTiempoEstimado(distanciaMetros, velocidadKmh) {
    if (!velocidadKmh || velocidadKmh <= 0) return null;
    
    // Convertir distancia a km y calcular tiempo en horas
    const distanciaKm = distanciaMetros / 1000;
    const tiempoHoras = distanciaKm / velocidadKmh;
    
    // Convertir a minutos
    return tiempoHoras * 60;
}

// Añadir método para medir distancia entre dos puntos
function calcularDistanciaEntrePuntos(lat1, lng1, lat2, lng2) {
    // Earth radius in meters
    const R = 6371000;
    
    const dLat = deg2rad(lat2 - lat1);
    const dLng = deg2rad(lng2 - lng1);
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
              Math.sin(dLng/2) * Math.sin(dLng/2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distancia = R * c;
    
    return distancia;
}

// Función auxiliar para convertir grados a radianes
function deg2rad(deg) {
    return deg * (Math.PI/180);
}