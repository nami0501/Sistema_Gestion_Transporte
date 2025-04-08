/**
 * Sistema de Transporte - Módulo de Asignaciones (tipo carrito)
 * 
 * Este archivo maneja las funcionalidades para la interfaz de asignaciones,
 * permitiendo configurar carrito, seleccionar conductores y vehículos
 * disponibles, y gestionar el carrito de asignaciones.
 */

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Token CSRF para peticiones AJAX - intenta varias fuentes
    let csrfToken = '';
    
    // Intenta obtener el token de la meta etiqueta
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }
    
    // Si no hay token en meta, intenta obtenerlo de un formulario con _token
    if (!csrfToken) {
        const tokenInput = document.querySelector('input[name="_token"]');
        if (tokenInput) {
            csrfToken = tokenInput.value;
        }
    }
    
    // Si aún no hay token, intenta buscarlo en cualquier formulario
    if (!csrfToken) {
        const forms = document.querySelectorAll('form');
        for (const form of forms) {
            const hiddenTokens = form.querySelectorAll('input[type="hidden"][name="_token"]');
            if (hiddenTokens.length > 0) {
                csrfToken = hiddenTokens[0].value;
                break;
            }
        }
    }
    // Elementos del DOM para la configuración
    const btnConfigurar = document.getElementById('btnConfigurar');
    const fecha = document.getElementById('fecha');
    const idTurno = document.getElementById('id_turno');
    const idLinea = document.getElementById('id_linea');
    
    // Elementos para el estado
    const conductorStatus = document.getElementById('conductor-status');
    const conductorStatusText = document.getElementById('conductor-status-text');
    const conductorStatusIndicator = conductorStatus.querySelector('.status-indicator');
    
    const vehiculoStatus = document.getElementById('vehiculo-status');
    const vehiculoStatusText = document.getElementById('vehiculo-status-text');
    const vehiculoStatusIndicator = vehiculoStatus.querySelector('.status-indicator');

    // Elementos para los contenedores
    const conductoresContainer = document.getElementById('conductores-container');
    const conductoresLoading = document.getElementById('conductores-loading');
    const conductoresEmpty = document.getElementById('conductores-empty');
    const conductoresError = document.getElementById('conductores-error');
    const conductoresList = document.getElementById('conductores-list');
    const conductoresErrorMessage = document.getElementById('conductores-error-message');
    
    const vehiculosContainer = document.getElementById('vehiculos-container');
    const vehiculosLoading = document.getElementById('vehiculos-loading');
    const vehiculosEmpty = document.getElementById('vehiculos-empty');
    const vehiculosError = document.getElementById('vehiculos-error');
    const vehiculosList = document.getElementById('vehiculos-list');
    const vehiculosErrorMessage = document.getElementById('vehiculos-error-message');

    // Elementos para modal
    const agregarModal = new bootstrap.Modal(document.getElementById('agregarModal'));
    const btnAgregarCarrito = document.getElementById('btnAgregarCarrito');
    const agregarForm = document.getElementById('agregarForm');
    const kilometrajeInicial = document.getElementById('kilometraje_inicial');
    const kmActual = document.getElementById('km-actual');
    const modalConductor = document.getElementById('modal-conductor');
    const modalVehiculo = document.getElementById('modal-vehiculo');
    const agregarError = document.getElementById('agregar-error');

    // Variables para los elementos seleccionados
    let conductorSeleccionado = null;
    let vehiculoSeleccionado = null;

    // Inicializar eventos
    inicializarEventos();

    /**
     * Inicializa los eventos de los elementos del DOM
     */
    function inicializarEventos() {
        // Evento para configurar el carrito
        if (btnConfigurar) {
            btnConfigurar.addEventListener('click', configurarCarrito);
        }

        // Evento para agregar al carrito
        if (btnAgregarCarrito) {
            btnAgregarCarrito.addEventListener('click', agregarAlCarrito);
        }

        // Inicializar eventos para quitar elementos del carrito
        inicializarEventosCarrito();
    }

    /**
     * Inicializa los eventos de los elementos del carrito
     */
    function inicializarEventosCarrito() {
        const btnQuitarCarrito = document.querySelectorAll('.btn-quitar-carrito');
        
        btnQuitarCarrito.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                quitarDelCarrito(id);
            });
        });
    }

    /**
     * Configura el carrito con la fecha, turno y línea
     */
    function configurarCarrito() {
        // Validar formulario
        if (!fecha.value) {
            alert('Debe seleccionar una fecha');
            return;
        }

        if (!idTurno.value) {
            alert('Debe seleccionar un turno');
            return;
        }

        if (!idLinea.value) {
            alert('Debe seleccionar una línea');
            return;
        }

        // Mostrar indicadores de carga
        actualizarEstadoConductores('loading', 'Cargando...');
        actualizarEstadoVehiculos('loading', 'Cargando...');

        // Mostrar loaders
        mostrarElemento(conductoresLoading);
        ocultarElemento(conductoresEmpty);
        ocultarElemento(conductoresError);
        ocultarElemento(conductoresList);

        mostrarElemento(vehiculosLoading);
        ocultarElemento(vehiculosEmpty);
        ocultarElemento(vehiculosError);
        ocultarElemento(vehiculosList);

        // Enviar petición AJAX
        fetch('/asignaciones/configurar-carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                fecha: fecha.value,
                id_turno: idTurno.value,
                id_linea: idLinea.value
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('Error en la respuesta del servidor');
                });
            }
            return response.json();
        })
        .then(data => {
            // Esta parte es la que podría faltar
            console.log('Data received:', data);
            
            if (data.success) {
                // Actualizar interfaz de configuración
                actualizarInterfazConfiguracion(data.data);
                
                // Cargar conductores y vehículos disponibles
                cargarConductoresDisponibles();
                cargarVehiculosDisponibles();
            } else {
                throw new Error(data.message || 'Error en la configuración');
            }
        })
        // resto del código
        .catch(error => {
            console.error('Error:', error);
            
            // Actualizar estado con error
            actualizarEstadoConductores('error', 'Error de configuración');
            actualizarEstadoVehiculos('error', 'Error de configuración');
            
            // Mostrar mensajes de error
            ocultarElemento(conductoresLoading);
            mostrarElemento(conductoresEmpty);
            
            ocultarElemento(vehiculosLoading);
            mostrarElemento(vehiculosEmpty);
            
            alert('Error al configurar: ' + error.message);
        });
    }

    /**
     * Actualiza la interfaz de configuración con los datos recibidos
     */
    function actualizarInterfazConfiguracion(config) {
        // Actualizar detalles de configuración
        document.getElementById('config-empty').classList.add('d-none');
        document.getElementById('config-details').classList.remove('d-none');
        
        document.getElementById('config-fecha').textContent = new Date(config.fecha).toLocaleDateString('es-ES');
        document.getElementById('config-turno').textContent = config.turno.nombre;
        document.getElementById('config-horario').textContent = config.turno.hora_inicio + ' - ' + config.turno.hora_fin;
        
        const configLinea = document.getElementById('config-linea');
        configLinea.textContent = config.linea.nombre;
        configLinea.style.backgroundColor = config.linea.color;
        configLinea.classList.remove('bg-secondary');
    }

    /**
     * Carga los conductores disponibles para la configuración actual
     */
    function cargarConductoresDisponibles() {
        // Actualizar estado
        actualizarEstadoConductores('loading', 'Cargando conductores...');
        
        // Mostrar loader
        mostrarElemento(conductoresLoading);
        ocultarElemento(conductoresEmpty);
        ocultarElemento(conductoresError);
        ocultarElemento(conductoresList);
        
        // Hacer petición AJAX
        fetch(`/api/conductores-disponibles?fecha=${fecha.value}&id_turno=${idTurno.value}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mostrar conductores
                mostrarConductores(data.data);
                actualizarEstadoConductores('ready', 'Conductores cargados');
            } else {
                throw new Error(data.message || 'Error al cargar conductores');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Mostrar error
            conductoresErrorMessage.textContent = error.message;
            
            ocultarElemento(conductoresLoading);
            ocultarElemento(conductoresEmpty);
            mostrarElemento(conductoresError);
            ocultarElemento(conductoresList);
            
            actualizarEstadoConductores('error', 'Error al cargar');
        });
    }

    /**
     * Muestra los conductores disponibles en la interfaz
     */
    function mostrarConductores(conductores) {
        // Ocultar loader y mostrar lista
        ocultarElemento(conductoresLoading);
        ocultarElemento(conductoresEmpty);
        ocultarElemento(conductoresError);
        
        // Si no hay conductores
        if (!conductores || conductores.length === 0) {
            mostrarElemento(conductoresEmpty);
            return;
        }
        
        // Limpiar y mostrar la lista
        conductoresList.innerHTML = '';
        mostrarElemento(conductoresList);
        
        // Crear tarjetas de conductores
        conductores.forEach(conductor => {
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4';
            card.innerHTML = `
                <div class="card conductor-card mb-3" data-id="${conductor.id_usuario}">
                    <div class="card-body">
                        <h6 class="card-title mb-1">${conductor.nombre_completo}</h6>
                        <p class="card-text text-muted small mb-1">DNI: ${conductor.dni}</p>
                        <p class="card-text text-muted small mb-0">Licencia: ${conductor.numero_licencia || 'No registrada'}</p>
                    </div>
                </div>
            `;
            conductoresList.appendChild(card);
            
            // Agregar evento click a la tarjeta
            const tarjeta = card.querySelector('.conductor-card');
            tarjeta.addEventListener('click', function() {
                seleccionarConductor(conductor);
            });
        });
    }

    /**
     * Carga los vehículos disponibles para la configuración actual
     */
    function cargarVehiculosDisponibles() {
        // Actualizar estado
        actualizarEstadoVehiculos('loading', 'Cargando vehículos...');
        
        // Mostrar loader
        mostrarElemento(vehiculosLoading);
        ocultarElemento(vehiculosEmpty);
        ocultarElemento(vehiculosError);
        ocultarElemento(vehiculosList);
        
        // Hacer petición AJAX
        fetch(`/api/vehiculos-disponibles?fecha=${fecha.value}&id_turno=${idTurno.value}&id_linea=${idLinea.value}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mostrar vehículos
                mostrarVehiculos(data.data);
                actualizarEstadoVehiculos('ready', 'Vehículos cargados');
            } else {
                throw new Error(data.message || 'Error al cargar vehículos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Mostrar error
            vehiculosErrorMessage.textContent = error.message;
            
            ocultarElemento(vehiculosLoading);
            ocultarElemento(vehiculosEmpty);
            mostrarElemento(vehiculosError);
            ocultarElemento(vehiculosList);
            
            actualizarEstadoVehiculos('error', 'Error al cargar');
        });
    }

    /**
     * Muestra los vehículos disponibles en la interfaz
     */
    function mostrarVehiculos(vehiculos) {
        // Ocultar loader y mostrar lista
        ocultarElemento(vehiculosLoading);
        ocultarElemento(vehiculosEmpty);
        ocultarElemento(vehiculosError);
        
        // Si no hay vehículos
        if (!vehiculos || vehiculos.length === 0) {
            mostrarElemento(vehiculosEmpty);
            return;
        }
        
        // Limpiar y mostrar la lista
        vehiculosList.innerHTML = '';
        mostrarElemento(vehiculosList);
        
        // Crear tarjetas de vehículos
        vehiculos.forEach(vehiculo => {
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4';
            card.innerHTML = `
                <div class="card vehiculo-card mb-3" data-id="${vehiculo.id_vehiculo}" data-km="${vehiculo.kilometraje}">
                    <div class="card-body">
                        <h6 class="card-title mb-1">${vehiculo.placa}</h6>
                        <p class="card-text text-muted small mb-1">${vehiculo.tipo} ${vehiculo.marca}</p>
                        <p class="card-text text-muted small mb-0">Modelo: ${vehiculo.modelo}</p>
                        <p class="card-text text-muted small mb-0">KM: ${vehiculo.kilometraje}</p>
                    </div>
                </div>
            `;
            vehiculosList.appendChild(card);
            
            // Agregar evento click a la tarjeta
            const tarjeta = card.querySelector('.vehiculo-card');
            tarjeta.addEventListener('click', function() {
                seleccionarVehiculo(vehiculo);
            });
        });
    }

    /**
     * Selecciona un conductor y lo resalta
     */
    function seleccionarConductor(conductor) {
        // Guardar referencia del conductor seleccionado
        conductorSeleccionado = conductor;
        
        // Quitar selección anterior
        const tarjetasSeleccionadas = conductoresList.querySelectorAll('.conductor-card.selected');
        tarjetasSeleccionadas.forEach(tarjeta => {
            tarjeta.classList.remove('selected');
        });
        
        // Seleccionar tarjeta actual
        const tarjeta = conductoresList.querySelector(`.conductor-card[data-id="${conductor.id_usuario}"]`);
        if (tarjeta) {
            tarjeta.classList.add('selected');
        }
        
        // Si ya hay un vehículo seleccionado, abrir modal para agregar
        if (vehiculoSeleccionado) {
            abrirModalAgregar();
        }
    }

    /**
     * Selecciona un vehículo y lo resalta
     */
    function seleccionarVehiculo(vehiculo) {
        // Guardar referencia del vehículo seleccionado
        vehiculoSeleccionado = vehiculo;
        
        // Quitar selección anterior
        const tarjetasSeleccionadas = vehiculosList.querySelectorAll('.vehiculo-card.selected');
        tarjetasSeleccionadas.forEach(tarjeta => {
            tarjeta.classList.remove('selected');
        });
        
        // Seleccionar tarjeta actual
        const tarjeta = vehiculosList.querySelector(`.vehiculo-card[data-id="${vehiculo.id_vehiculo}"]`);
        if (tarjeta) {
            tarjeta.classList.add('selected');
        }
        
        // Si ya hay un conductor seleccionado, abrir modal para agregar
        if (conductorSeleccionado) {
            abrirModalAgregar();
        }
    }

    /**
     * Abre el modal para agregar la asignación al carrito
     */
    function abrirModalAgregar() {
        // Verificar que hay un conductor y un vehículo seleccionados
        if (!conductorSeleccionado || !vehiculoSeleccionado) {
            return;
        }
        
        // Mostrar datos en el modal
        modalConductor.innerHTML = `
            <p class="mb-1"><strong>${conductorSeleccionado.nombre_completo}</strong></p>
            <p class="mb-1 small text-muted">DNI: ${conductorSeleccionado.dni}</p>
            <p class="mb-0 small text-muted">Licencia: ${conductorSeleccionado.numero_licencia || 'No registrada'}</p>
        `;
        
        modalVehiculo.innerHTML = `
            <p class="mb-1"><strong>${vehiculoSeleccionado.placa}</strong></p>
            <p class="mb-1 small text-muted">${vehiculoSeleccionado.tipo} ${vehiculoSeleccionado.marca}</p>
            <p class="mb-0 small text-muted">Modelo: ${vehiculoSeleccionado.modelo}</p>
        `;
        
        // Establecer kilometraje actual y mínimo
        kmActual.textContent = vehiculoSeleccionado.kilometraje;
        kilometrajeInicial.value = vehiculoSeleccionado.kilometraje;
        kilometrajeInicial.min = vehiculoSeleccionado.kilometraje;
        
        // Ocultar mensaje de error si existe
        ocultarElemento(agregarError);
        
        // Mostrar modal
        agregarModal.show();
    }

    /**
     * Agrega la asignación al carrito
     */
    function agregarAlCarrito() {
        // Verificar que haya conductor y vehículo seleccionados
        if (!conductorSeleccionado || !vehiculoSeleccionado) {
            mostrarErrorAgregar('Debe seleccionar un conductor y un vehículo');
            return;
        }
        
        // Validar kilometraje
        if (!kilometrajeInicial.value || parseInt(kilometrajeInicial.value) < parseInt(kilometrajeInicial.min)) {
            mostrarErrorAgregar(`El kilometraje inicial debe ser mayor o igual a ${kilometrajeInicial.min} km`);
            return;
        }
        
        // Preparar datos
        const datos = {
            id_usuario: conductorSeleccionado.id_usuario,
            id_vehiculo: vehiculoSeleccionado.id_vehiculo,
            kilometraje_inicial: parseInt(kilometrajeInicial.value)
        };
        
        // Enviar petición AJAX
        fetch('/api/asignaciones/agregar-al-carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Cerrar modal
                agregarModal.hide();
                
                // Actualizar carrito
                actualizarCarrito(data.data.asignacion);
                
                // Limpiar selección
                limpiarSeleccion();
                
                // Recargar conductores y vehículos disponibles
                cargarConductoresDisponibles();
                cargarVehiculosDisponibles();
            } else {
                throw new Error(data.message || 'Error al agregar al carrito');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarErrorAgregar(error.message);
        });
    }

    /**
     * Quita una asignación del carrito
     */
    function quitarDelCarrito(tempId) {
        if (!confirm('¿Está seguro que desea quitar esta asignación del carrito?')) {
            return;
        }
        
        // Enviar petición AJAX
        fetch('/api/asignaciones/quitar-del-carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                temp_id: tempId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Eliminar elemento del carrito
                const carritoItem = document.querySelector(`.carrito-item[data-id="${tempId}"]`);
                if (carritoItem) {
                    carritoItem.remove();
                }
                
                // Si no hay elementos, mostrar mensaje de vacío
                const carritoItems = document.querySelectorAll('.carrito-item');
                if (carritoItems.length === 0) {
                    document.getElementById('carrito-empty').classList.remove('d-none');
                    document.getElementById('btnProcesar').classList.add('disabled');
                }
                
                // Recargar conductores y vehículos disponibles
                cargarConductoresDisponibles();
                cargarVehiculosDisponibles();
            } else {
                throw new Error(data.message || 'Error al quitar del carrito');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }

    /**
     * Actualiza el carrito agregando la nueva asignación
     */
    function actualizarCarrito(asignacion) {
        // Ocultar mensaje de carrito vacío
        document.getElementById('carrito-empty').classList.add('d-none');
        
        // Habilitar botón de procesar
        document.getElementById('btnProcesar').classList.remove('disabled');
        
        // Crear elemento de asignación
        const carritoItem = document.createElement('div');
        carritoItem.className = 'carrito-item p-3 border-bottom';
        carritoItem.setAttribute('data-id', asignacion.temp_id);
        
        carritoItem.innerHTML = `
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="mb-0">
                        <i class="fas fa-user me-1"></i> ${asignacion.usuario.nombre_completo}
                    </h6>
                    <small class="text-muted d-block">DNI: ${asignacion.usuario.dni}</small>
                    <small class="text-muted d-block">Licencia: ${asignacion.usuario.numero_licencia}</small>
                </div>
                <div class="text-end">
                    <h6 class="mb-0">
                        <i class="fas fa-bus me-1"></i> ${asignacion.vehiculo.placa}
                    </h6>
                    <small class="text-muted d-block">${asignacion.vehiculo.tipo}</small>
                    <small class="text-muted d-block">${asignacion.vehiculo.marca} ${asignacion.vehiculo.modelo}</small>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-danger btn-quitar-carrito" data-id="${asignacion.temp_id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="mt-1">
                <small class="text-muted">Kilometraje inicial: ${asignacion.kilometraje_inicial.toLocaleString('es-ES')} km</small>
            </div>
        `;
        
        // Agregar al contenedor de carrito
        document.getElementById('carrito-items').appendChild(carritoItem);
        
        // Agregar evento para quitar del carrito
        const btnQuitar = carritoItem.querySelector('.btn-quitar-carrito');
        btnQuitar.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            quitarDelCarrito(id);
        });
    }

    /**
     * Muestra un mensaje de error en el modal de agregar
     */
    function mostrarErrorAgregar(mensaje) {
        agregarError.textContent = mensaje;
        agregarError.classList.remove('d-none');
    }

    /**
     * Limpia la selección de conductor y vehículo
     */
    function limpiarSeleccion() {
        conductorSeleccionado = null;
        vehiculoSeleccionado = null;
        
        // Quitar clase selected de las tarjetas
        const tarjetasConductores = conductoresList.querySelectorAll('.conductor-card.selected');
        tarjetasConductores.forEach(tarjeta => {
            tarjeta.classList.remove('selected');
        });
        
        const tarjetasVehiculos = vehiculosList.querySelectorAll('.vehiculo-card.selected');
        tarjetasVehiculos.forEach(tarjeta => {
            tarjeta.classList.remove('selected');
        });
    }

    /**
     * Actualiza el estado visual del panel de conductores
     */
    function actualizarEstadoConductores(estado, texto) {
        conductorStatusText.textContent = texto;
        
        conductorStatusIndicator.classList.remove('status-ready', 'status-pending', 'status-error');
        
        if (estado === 'ready') {
            conductorStatusIndicator.classList.add('status-ready');
        } else if (estado === 'loading') {
            conductorStatusIndicator.classList.add('status-pending');
        } else if (estado === 'error') {
            conductorStatusIndicator.classList.add('status-error');
        } else {
            conductorStatusIndicator.classList.add('status-pending');
        }
    }

    /**
     * Actualiza el estado visual del panel de vehículos
     */
    function actualizarEstadoVehiculos(estado, texto) {
        vehiculoStatusText.textContent = texto;
        
        vehiculoStatusIndicator.classList.remove('status-ready', 'status-pending', 'status-error');
        
        if (estado === 'ready') {
            vehiculoStatusIndicator.classList.add('status-ready');
        } else if (estado === 'loading') {
            vehiculoStatusIndicator.classList.add('status-pending');
        } else if (estado === 'error') {
            vehiculoStatusIndicator.classList.add('status-error');
        } else {
            vehiculoStatusIndicator.classList.add('status-pending');
        }
    }

    /**
     * Muestra un elemento del DOM
     */
    function mostrarElemento(elemento) {
        if (elemento) {
            elemento.classList.remove('d-none');
        }
    }

    /**
     * Oculta un elemento del DOM
     */
    function ocultarElemento(elemento) {
        if (elemento) {
            elemento.classList.add('d-none');
        }
    }
});