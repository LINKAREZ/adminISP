/**
 * DESHABILITADO: Este archivo está deshabilitado
 * El control del sidebar ahora se maneja en resources/views/layouts/app.blade.php
 * Este archivo se mantiene solo por compatibilidad pero no se ejecuta
 */

// DESHABILITADO - No ejecutar este código
if (false) {
(function() {
    'use strict';
    
    let sidebarOpen = false;
    let isDesktop = false;
    
    // Función para aplicar el estado del sidebar
    function applySidebarState() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const header = document.querySelector('header');
        const main = document.querySelector('main');
        
        if (!sidebar) return;
        
        // Determinar si es desktop
        isDesktop = window.innerWidth >= 1024;
        
        if (sidebarOpen) {
            // Mostrar sidebar
            sidebar.style.setProperty('transform', 'translateX(0)', 'important');
            sidebar.style.setProperty('-webkit-transform', 'translateX(0)', 'important');
            sidebar.style.setProperty('-moz-transform', 'translateX(0)', 'important');
            sidebar.style.setProperty('-ms-transform', 'translateX(0)', 'important');
            sidebar.style.setProperty('-o-transform', 'translateX(0)', 'important');
            sidebar.style.setProperty('display', 'block', 'important');
            sidebar.style.setProperty('visibility', 'visible', 'important');
            sidebar.style.setProperty('opacity', '1', 'important');
            
            // Mostrar overlay solo en móvil
            if (overlay && !isDesktop) {
                overlay.style.setProperty('display', 'block', 'important');
                overlay.style.setProperty('opacity', '0.4', 'important');
            }
            
            // Ajustar header y main en desktop
            if (isDesktop && header) {
                header.style.setProperty('left', '18rem', 'important');
            }
            if (isDesktop && main) {
                main.style.setProperty('margin-left', '18rem', 'important');
            }
        } else {
            // Ocultar sidebar
            sidebar.style.setProperty('transform', 'translateX(-100%)', 'important');
            sidebar.style.setProperty('-webkit-transform', 'translateX(-100%)', 'important');
            sidebar.style.setProperty('-moz-transform', 'translateX(-100%)', 'important');
            sidebar.style.setProperty('-ms-transform', 'translateX(-100%)', 'important');
            sidebar.style.setProperty('-o-transform', 'translateX(-100%)', 'important');
            
            // Ocultar overlay
            if (overlay) {
                overlay.style.setProperty('display', 'none', 'important');
            }
            
            // Ajustar header y main en desktop
            if (isDesktop && header) {
                header.style.setProperty('left', '0', 'important');
            }
            if (isDesktop && main) {
                main.style.setProperty('margin-left', '0', 'important');
            }
        }
    }
    
    // Función para toggle del sidebar
    function toggleSidebar() {
        sidebarOpen = !sidebarOpen;
        applySidebarState();
    }
    
    // Inicializar cuando el DOM esté listo
    function init() {
        // Estado inicial: abierto en desktop, cerrado en móvil
        isDesktop = window.innerWidth >= 1024;
        sidebarOpen = isDesktop;
        
        // Aplicar estado inicial
        applySidebarState();
        
        // Agregar event listener al botón
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        // Cerrar sidebar al hacer click en overlay (solo móvil)
        const overlay = document.getElementById('sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                if (!isDesktop) {
                    sidebarOpen = false;
                    applySidebarState();
                }
            });
        }
        
        // Manejar resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const wasDesktop = isDesktop;
                isDesktop = window.innerWidth >= 1024;
                
                // Si cambió de móvil a desktop, abrir sidebar
                if (!wasDesktop && isDesktop) {
                    sidebarOpen = true;
                }
                // Si cambió de desktop a móvil, cerrar sidebar
                else if (wasDesktop && !isDesktop) {
                    sidebarOpen = false;
                }
                
                applySidebarState();
            }, 100);
        });
        
        // Cerrar sidebar al hacer click fuera (solo móvil)
        document.addEventListener('click', function(e) {
            if (!isDesktop && sidebarOpen) {
                const sidebar = document.getElementById('sidebar');
                const toggleBtn = document.getElementById('sidebar-toggle-btn');
                
                if (sidebar && toggleBtn) {
                    const isClickInsideSidebar = sidebar.contains(e.target);
                    const isClickOnToggle = toggleBtn.contains(e.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle) {
                        sidebarOpen = false;
                        applySidebarState();
                    }
                }
            }
        });
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM ya está listo
        init();
    }
    
    // También ejecutar después de un pequeño delay para asegurar que todo esté cargado
    setTimeout(init, 100);
    
})();
} // Fin del if (false) - código deshabilitado

































