/**
 * Script para editar servicios PPPoE
 * Este archivo se carga después de que jQuery esté disponible
 *
 * IMPORTANTE: NO ejecutar NADA hasta que jQuery esté 100% disponible
 */

// SOLUCIÓN RADICAL: NO ejecutar NADA hasta que jQuery esté 100% disponible
// Envolver TODO en una IIFE que NO se ejecuta hasta que jQuery esté disponible
(function () {
  'use strict';

  const logError = (...args) => {
    if (window.logger && typeof window.logger.error === 'function') {
      window.logger.error(...args);
      return;
    }
    if (console && typeof console.error === 'function') {
      console.error(...args);
    }
  };

  // Verificar que window y document estén disponibles
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return;
  }

  // VERIFICAR INMEDIATAMENTE que jQuery esté disponible ANTES de hacer cualquier cosa
  if (
    typeof window.jQuery === 'undefined' ||
    typeof window.$ === 'undefined' ||
    !window.jQuery ||
    !window.jQuery.fn
  ) {
    // jQuery no está disponible, esperar
    let attempts = 0;
    const maxAttempts = 200; // 10 segundos máximo

    const checkAndInit = function () {
      attempts++;

      if (
        typeof window.jQuery !== 'undefined' &&
        typeof window.$ !== 'undefined' &&
        window.jQuery &&
        window.jQuery.fn
      ) {
        // jQuery está disponible, continuar
        initScript();
      } else if (attempts < maxAttempts) {
        // Reintentar
        setTimeout(checkAndInit, 50);
      } else {
        logError('jQuery no se cargó después de 10 segundos');
      }
    };

    // Escuchar adminlte:ready
    if (document.addEventListener) {
      document.addEventListener(
        'adminlte:ready',
        function () {
          if (typeof window.jQuery !== 'undefined' && window.jQuery && window.jQuery.fn) {
            initScript();
          }
        },
        { once: true }
      );
    }

    // Verificar periódicamente
    setTimeout(checkAndInit, 50);
    return;
  }

  // jQuery está disponible, continuar
  initScript();

  function initScript() {
    // Datos del servicio (se pasan desde Blade)
    if (typeof window.servicioEditData === 'undefined') {
      logError('servicioEditData no está definido');
      return;
    }

    const servicioEditData = window.servicioEditData;

    function initServicioEdit() {
      // Verificar que jQuery esté disponible
      if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined') {
        logError('jQuery no está disponible en initServicioEdit');
        return;
      }

      const $ = window.jQuery || window.$;

      const ServicioEditManager = {
        todasMarcas: servicioEditData.todasMarcas,
        todosModelos: servicioEditData.todosModelos,
        modelosConTransformacion: servicioEditData.modelosConTransformacion,

        init: function () {
          this.initTabs();
          this.initModoPppoe();
          this.initONU();
        },

        initTabs: function () {
          $('#servicioTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
          });
        },

        initModoPppoe: function () {
          const self = this;
          $('#tipo-pppoe').on('change', function () {
            const modo = $(this).val();
            if (modo === 'usuario_unico') {
              $('#grupo-pppoe-diferente').show();
              $('#grupo-pppoe-unico').hide();
            } else {
              $('#grupo-pppoe-diferente').hide();
              $('#grupo-pppoe-unico').show();
            }
          });
          $('#tipo-pppoe').trigger('change');
        },

        initONU: function () {
          const self = this;

          // Marca: si elige "Crear nueva marca", ir a la URL (usar attr por compatibilidad)
          $('#onu-marca-id').on('change', function () {
            const val = $(this).val();
            if (val === '__crear_marca__') {
              const url = $(this).attr('data-url-crear-marca') || $(this).data('url-crear-marca');
              if (url) window.location.href = url;
              $(this).val($(this).data('prev-marca') || '');
              return;
            }
            $(this).data('prev-marca', val);
            self.cargarModelosPorMarca();
          });

          // Modelo: si elige "Crear nuevo modelo", ir a la URL
          $('#onu-modelo-id').on('change', function () {
            const val = $(this).val();
            if (val === '__crear_modelo__') {
              const marcaId = $('#onu-marca-id').val();
              if (!marcaId || marcaId === '__crear_marca__') return;
              const baseUrl = $(this).attr('data-base-url') || $(this).data('base-url');
              const returnUrl = $(this).attr('data-return-url') || $(this).data('return-url') || '';
              const sep = baseUrl && baseUrl.indexOf('?') >= 0 ? '&' : '?';
              const url = baseUrl + sep + 'marca_id=' + encodeURIComponent(marcaId) + '&return_url=' + encodeURIComponent(returnUrl);
              window.location.href = url;
              $(this).val($(this).data('prev-modelo') || '');
              return;
            }
            $(this).data('prev-modelo', val);
            self.actualizarModeloDesdeSelect();
          });

          $('#onu-serial-completo').on('input', function () {
            self.transformarSerialCompleto();
          });

          if ($('#onu-marca-id').val()) {
            this.cargarModelosPorMarca();
          }

          $('#onu-marca-id').data('prev-marca', $('#onu-marca-id').val());
          $('#onu-modelo-id').data('prev-modelo', $('#onu-modelo-id').val());

          if ($('#onu-serial-completo').val()) {
            setTimeout(() => this.transformarSerialCompleto(), 100);
          }
        },

        cargarModelosPorMarca: function () {
          const marcaId = $('#onu-marca-id').val();
          const $modeloSelect = $('#onu-modelo-id');
          const $marcaHidden = $('#onu-marca');

          if (!marcaId || marcaId === '__crear_marca__') {
            $modeloSelect.empty().append('<option value="">Seleccione un modelo</option>');
            $marcaHidden.val('');
            return;
          }

          const marcaSeleccionada = this.todasMarcas.find(m => m.id == marcaId);
          if (marcaSeleccionada) {
            $marcaHidden.val(marcaSeleccionada.nombre);
          }

          const modelosFiltrados = this.todosModelos.filter(m => m.marca_id == marcaId && m.estado);
          const modeloActual = $modeloSelect.val();

          $modeloSelect.empty().append('<option value="">Seleccione un modelo</option>');

          modelosFiltrados.forEach(modelo => {
            const $option = $('<option>')
              .val(modelo.id)
              .text(modelo.nombre)
              .attr('data-modelo-nombre', modelo.nombre)
              .attr('data-requiere-transformacion', modelo.requiere_transformacion ? '1' : '0');

            if (modelo.id == modeloActual) {
              $option.prop('selected', true);
            }

            $modeloSelect.append($option);
          });

          $modeloSelect.append($('<option>').val('__crear_modelo__').text('+ Crear nuevo modelo...'));

          if (modeloActual && modeloActual !== '__crear_modelo__') {
            this.actualizarModeloDesdeSelect();
          }
        },

        actualizarModeloDesdeSelect: function () {
          const modeloId = $('#onu-modelo-id').val();
          if (modeloId === '__crear_modelo__') return;
          const $modeloHidden = $('#onu-modelo');

          if (!modeloId) {
            $modeloHidden.val('');
            this.transformarSerialCompleto();
            return;
          }

          const $option = $('#onu-modelo-id option:selected');
          const modeloNombre = $option.data('modelo-nombre');
          const requiereTransformacion = $option.data('requiere-transformacion') == '1';

          $modeloHidden.val(modeloNombre);

          const $serialCompleto = $('#onu-serial-completo');
          const $serialOlt = $('#onu-serial-olt');

          if (requiereTransformacion) {
            $serialCompleto.attr('maxlength', '16').attr('placeholder', '41434847183001f9');
            $serialOlt.addClass('bg-light').prop('readonly', true);
            $('#serial-help-transformacion').show();
            $('#serial-help-normal').hide();
            $('#serial-olt-help').show();
          } else {
            $serialCompleto
              .attr('maxlength', '255')
              .attr('placeholder', 'FHTCaf7548a8 o GPON00bbc6ec');
            $serialOlt.removeClass('bg-light').prop('readonly', false);
            $('#serial-help-transformacion').hide();
            $('#serial-help-normal').show();
            $('#serial-olt-help').hide();
          }

          if ($serialCompleto.val()) {
            this.transformarSerialCompleto();
          }
        },

        requiereTransformacion: function () {
          const modeloId = $('#onu-modelo-id').val();
          if (!modeloId) return false;

          const $option = $('#onu-modelo-id option:selected');
          return $option.data('requiere-transformacion') == '1';
        },

        transformarSerialCompleto: function () {
          const serial = $('#onu-serial-completo').val().trim().toUpperCase();
          const $serialOlt = $('#onu-serial-olt');

          if (!serial) {
            $serialOlt.val('');
            return;
          }

          const requiereTransform = this.requiereTransformacion();

          if (!requiereTransform) {
            $serialOlt.val(serial);
            return;
          }

          if (serial.length !== 16) {
            $serialOlt.val('');
            return;
          }

          if (!/^[0-9A-F]{16}$/.test(serial)) {
            $serialOlt.val('');
            return;
          }

          const prefijoHex = serial.substring(0, 8);
          const sufijo = serial.substring(8);

          let prefijoAscii = '';
          for (let i = 0; i < 8; i += 2) {
            const hexPair = prefijoHex.substring(i, i + 2);
            const decimalValue = parseInt(hexPair, 16);
            const asciiChar = String.fromCharCode(decimalValue);

            if (decimalValue >= 32 && decimalValue <= 126) {
              prefijoAscii += asciiChar;
            } else {
              prefijoAscii += hexPair;
            }
          }

          $serialOlt.val(prefijoAscii.toUpperCase() + sufijo);
        },
      };

      $(document).ready(function () {
        ServicioEditManager.init();
      });
    }

    // Esperar a que jQuery esté disponible
    function waitForjQueryAndInit() {
      if (
        typeof window.jQuery !== 'undefined' &&
        typeof window.$ !== 'undefined' &&
        window.jQuery &&
        window.jQuery.fn
      ) {
        initServicioEdit();
        return;
      }

      let resolved = false;
      const maxWait = 10000;
      const startTime = Date.now();

      const verificarYEjecutar = function () {
        if (resolved) return;

        if (
          typeof window.jQuery !== 'undefined' &&
          typeof window.$ !== 'undefined' &&
          window.jQuery &&
          window.jQuery.fn
        ) {
          resolved = true;
          initServicioEdit();
          return true;
        }

        if (Date.now() - startTime > maxWait) {
      logError('jQuery no se cargó después de 10 segundos');
          return false;
        }

        return false;
      };

      if (document.addEventListener) {
        document.addEventListener('adminlte:ready', verificarYEjecutar, { once: true });
      }

      const interval = setInterval(function () {
        if (verificarYEjecutar()) {
          clearInterval(interval);
        }
      }, 50);

      if (window.addEventListener) {
        window.addEventListener(
          'load',
          function () {
            setTimeout(function () {
              if (verificarYEjecutar()) {
                clearInterval(interval);
              }
            }, 100);
          },
          { once: true }
        );
      }
    }

    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'complete') {
      setTimeout(waitForjQueryAndInit, 50);
    } else {
      window.addEventListener(
        'load',
        function () {
          setTimeout(waitForjQueryAndInit, 100);
        },
        { once: true }
      );
    }
  }
})(); // Cerrar IIFE
