document.addEventListener('DOMContentLoaded', function () {

    // --- Selectores DOM principales ---
    const allNavLinks = document.querySelectorAll('.main-menu a');
    const contentSections = document.querySelectorAll('.main-content .content-section');
    const mainContent = document.querySelector('.main-content');

    // --- LÓGICA DE NAVEGACIÓN Y VISUALIZACIÓN DE SECCIONES ---
    function handleNavigation(targetId) {
        allNavLinks.forEach(link => {
            link.classList.toggle('active', link.dataset.target === targetId);
        });
        if (window.innerWidth > 1024) {
            contentSections.forEach(section => {
                section.classList.toggle('active', section.id === targetId);
            });
        } else {
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    // --- LÓGICA DE CONTROL DE ESTADOS DE INPUTS (HABILITAR/DESHABILITAR) ---
    function AjustValNotif() {
        const notifInactiv = document.getElementById('notif_inactiv');
        if (notifInactiv) document.getElementById('periodSinActiv').disabled = !notifInactiv.checked;
        const timNot1 = document.getElementById('tim_not_1');
        if (timNot1) {
            const isDisabled1 = !timNot1.checked;
            document.getElementById('sel_tim_not_1').disabled = isDisabled1;
            document.getElementById('stat_not_1').disabled = isDisabled1;
            document.getElementById('medio_cto_1').disabled = isDisabled1;
            if (isDisabled1) document.getElementById('sel_tim_not_1').value = '0';
        }
        const timNot2 = document.getElementById('tim_not_2');
        if (timNot2) {
            const isDisabled2 = !timNot2.checked;
            document.getElementById('sel_tim_not_2').disabled = isDisabled2;
            document.getElementById('stat_not_2').disabled = isDisabled2;
            document.getElementById('medio_cto_2').disabled = isDisabled2;
            if (isDisabled2) document.getElementById('sel_tim_not_2').value = '0';
        }
        const bandSegInsum = document.getElementById('bandSegInsum');
        if (bandSegInsum) {
            document.querySelectorAll('#opc_seg_insum input[type="radio"]').forEach(input => input.disabled = !bandSegInsum.checked);
        }
        const notifCtespr = document.getElementById('notif_ctespr');
        if (notifCtespr) {
            document.querySelectorAll('#panel_ajuste_agenda select').forEach(select => select.disabled = !notifCtespr.checked);
        }
    }

    // --- LÓGICA DE VALIDACIÓN DEL FORMULARIO ---
    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorContainer = document.createElement('div');
        errorContainer.className = 'error-message';
        errorContainer.textContent = message;
        inputElement.parentNode.appendChild(errorContainer);
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }

    function validateForm() {
        clearErrors();
        let isValid = true;

        const nombreEmpresa = document.getElementById('empresa_nombre');
        if (!nombreEmpresa.value.trim()) {
            showError(nombreEmpresa, 'El nombre de la empresa es obligatorio.');
            isValid = false;
        }

        const emailContacto = document.getElementById('email_contacto');
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (emailContacto.value.trim() && !emailRegex.test(emailContacto.value)) {
            showError(emailContacto, 'Por favor, ingrese un correo electrónico válido.');
            isValid = false;
        }

        const telefonoRegex = /^[0-9\s()+-]+$/;
        ['tel1_contacto', 'tel2_contacto'].forEach(id => {
            const input = document.getElementById(id);
            if (input.value.trim()) {
                if (!telefonoRegex.test(input.value)) {
                    showError(input, 'Formato no válido. Solo números y símbolos + - ( ).');
                    isValid = false;
                } else {
                    const digitos = input.value.replace(/\D/g, '');
                    if (digitos.length !== 10) {
                        showError(input, 'El número de teléfono debe contener 10 dígitos.');
                        isValid = false;
                    }
                }
            }
        });
        return isValid;
    }

    // --- LÓGICA DE NOTIFICACIONES TOAST ---
    let statusToast = null;
    let hideToastTimeout = null;

    function manageStatusToast(state, message) {
        clearTimeout(hideToastTimeout);
        if (!statusToast) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            statusToast = document.createElement('div');
            statusToast.className = 'toast';
            container.appendChild(statusToast);
            setTimeout(() => statusToast.classList.add('is-visible'), 10);
        }
        let iconHtml = '';
        statusToast.className = 'toast is-visible';
        switch (state) {
            case 'pending':
                iconHtml = '<i class="fas fa-pencil-alt" style="margin-right: 8px;"></i>';
                statusToast.classList.add('toast--info');
                break;
            case 'saving':
                iconHtml = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>';
                statusToast.classList.add('toast--info');
                break;
            case 'success':
                iconHtml = '<i class="fas fa-check-circle" style="margin-right: 8px;"></i>';
                statusToast.classList.add('toast--success');
                hideToastTimeout = setTimeout(hideAndRemoveStatusToast, 2000);
                break;
            case 'error':
                iconHtml = '<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>';
                statusToast.classList.add('toast--error');
                hideToastTimeout = setTimeout(hideAndRemoveStatusToast, 5000);
                break;
        }
        statusToast.innerHTML = `${iconHtml}${message}`;
    }

    function hideAndRemoveStatusToast() {
        if (statusToast) {
            statusToast.classList.remove('is-visible');
            statusToast.addEventListener('transitionend', () => {
                statusToast?.remove();
                statusToast = null;
            });
        }
    }

    // --- FUNCIÓN PRINCIPAL PARA GUARDAR (ACTUALIZAR) ---
    function funcSavAjusGral() {
        if (!validateForm()) {
            manageStatusToast('error', 'Hay errores en el formulario.');
            return;
        }

        manageStatusToast('saving', 'Guardando...');

        let params = {
            index: "1",
            nombre_empresa: document.getElementById('empresa_nombre').value,
            nit_empresa: document.getElementById('empresa_nit').value,
            domicilio_empresa: document.getElementById('empresa_direccion').value,
            nombre_contacto: document.getElementById('nombre_contacto').value,
            tel1_contacto: document.getElementById('tel1_contacto').value,
            tel2_contacto: document.getElementById('tel2_contacto').value,
            email_contacto: document.getElementById('email_contacto').value,
            nota_especial: document.getElementById('nota_especial').value,
            activa: document.getElementById('activa').checked ? '1' : '0',
            notif_cumple: document.getElementById('notif_cumple').checked ? '1' : '0',
            notif_inactiv: document.getElementById('notif_inactiv').checked ? '1' : '0',
            periodSinActiv: document.getElementById('periodSinActiv').value,
            tiempo_notificacion_1: document.getElementById('tim_not_1').checked ? document.getElementById('sel_tim_not_1').value : '0',
            status_send_1: document.getElementById('stat_not_1').value,
            send_type_1: document.getElementById('medio_cto_1').value,
            tiempo_notificacion_2: document.getElementById('tim_not_2').checked ? document.getElementById('sel_tim_not_2').value : '0',
            status_send_2: document.getElementById('stat_not_2').value,
            send_type_2: document.getElementById('medio_cto_2').value,
            marginPrint: document.getElementById('marginPrint').value,
            seg_insumos: document.getElementById('bandSegInsum').checked ? '1' : '0',
            seg_rf: document.querySelector('input[name="verf_cierr"]:checked')?.value || '2',
            seg_crf: document.querySelector('input[name="seg_all"]:checked')?.value || '2',
            notif_ctespr: document.getElementById('notif_ctespr').checked ? '1' : '0',
            nventa_ctespr: document.getElementById('nventa_ctespr').value,
            dias_ctespr: document.getElementById('dias_ctespr').value,
        };

        fetch("comandos_ajustes.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(params),
        })
        .then(async response => {
            const resJson = await response.json();
            if (!response.ok) {
                const errorMsg = resJson.error || `Error del servidor: ${response.status}`;
                throw new Error(errorMsg);
            }
            return resJson;
        })
        .then(resp => {
            if (resp.success) {
                manageStatusToast('success', resp.message || 'Cambios guardados');
                clearErrors();
            } else {
                manageStatusToast('error', `Error al guardar: ${resp.error || 'Ocurrió un error.'}`);
            }
        })
        .catch(e => {
            console.error('Error en la petición de guardado:', e);
            manageStatusToast('error', e.message || 'Error de conexión.');
        });
    }

    // --- FUNCIÓN PRINCIPAL PARA CARGAR DATOS ---
    function getValsAjusGral() {
        document.title = "Cargando Ajustes...";

        fetch("comandos_ajustes.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ index: "2" }),
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => Promise.reject(`Error: ${response.status} - ${text}`));
            }
            return response.json();
        })
        .then(obj => {
            if (!obj) {
                console.warn("No se recibieron datos del servidor.");
                return;
            };
            
            // Rellenar todos los campos del formulario con los datos recibidos.
            document.getElementById('empresa_nombre').value = obj.nombre_empresa || '';
            document.getElementById('empresa_nit').value = obj.nit_empresa || '';
            document.getElementById('empresa_direccion').value = obj.domicilio_empresa || '';
            document.getElementById('nombre_contacto').value = obj.nombre_contacto || '';
            document.getElementById('tel1_contacto').value = obj.tel1_contacto || '';
            document.getElementById('tel2_contacto').value = obj.tel2_contacto || '';
            document.getElementById('email_contacto').value = obj.email_contacto || '';
            document.getElementById('nota_especial').value = obj.nota_especial || '';
            document.getElementById('activa').checked = obj.activa == "1";
            document.getElementById('notif_cumple').checked = obj.notif_cumple == "1";
            document.getElementById('notif_inactiv').checked = obj.notif_inactiv == "1";
            document.getElementById('periodSinActiv').value = obj.periodSinActiv || '21';
            document.getElementById('tim_not_1').checked = obj.tiempo_notificacion_1 != "0";
            document.getElementById('sel_tim_not_1').value = obj.tiempo_notificacion_1 || '0';
            document.getElementById('stat_not_1').value = obj.status_send_1 || '-Indistinto-';
            document.getElementById('medio_cto_1').value = obj.send_type_1 || '- Todos -';
            document.getElementById('tim_not_2').checked = obj.tiempo_notificacion_2 != "0";
            document.getElementById('sel_tim_not_2').value = obj.tiempo_notificacion_2 || '0';
            document.getElementById('stat_not_2').value = obj.status_send_2 || '-Indistinto-';
            document.getElementById('medio_cto_2').value = obj.send_type_2 || '- Todos -';
            document.getElementById('marginPrint').value = obj.marginPrint || '0px';
            document.getElementById('bandSegInsum').checked = obj.seg_insumos == "1";
            if (obj.seg_rf == "1") document.getElementById('verf_cierr_vta').checked = true;
            else document.getElementById('verf_cierr_tur').checked = true;
            if (obj.seg_crf == "1") document.getElementById('seg_all_vta').checked = true;
            else document.getElementById('seg_all_ins').checked = true;
            document.getElementById('notif_ctespr').checked = obj.nventa_ctespr != '-1';
            document.getElementById('nventa_ctespr').value = obj.nventa_ctespr > -1 ? obj.nventa_ctespr : '0';
            document.getElementById('dias_ctespr').value = obj.dias_ctespr || '365';
            
            document.title = "Ajustes - " + (obj.nombre_empresa || 'SyServ');

            // Preparamos la UI y el autoguardado después de cargar los datos.
            AjustValNotif();
            setupAutosave();
        })
        .catch(e => {
            console.error('Error en la petición de carga:', e);
            manageStatusToast('error', 'No se pudieron cargar los datos de la empresa.');
        });
    }

    // --- Lógica de Autoguardado ("Debouncing") ---
    let debounceTimer;
    function setupAutosave() {
        const formElements = mainContent.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            const eventType = (element.tagName === 'SELECT' || ['checkbox', 'radio'].includes(element.type)) ? 'change' : 'input';
            element.addEventListener(eventType, () => {
                if (validateForm()) {
                    manageStatusToast('pending', 'Cambios detectados...');
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(funcSavAjusGral, 1500);
                } else {
                    manageStatusToast('error', 'Hay errores en el formulario.');
                    clearTimeout(debounceTimer);
                }
            });
        });
    }

    // --- BLOQUE DE INICIALIZACIÓN ---
    allNavLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            handleNavigation(this.dataset.target);
        });
    });

    document.querySelectorAll('#notif_inactiv, #tim_not_1, #tim_not_2, #bandSegInsum, #notif_ctespr').forEach(el => {
        el?.addEventListener('change', AjustValNotif);
    });

    // Inicia todo el proceso
    AjustValNotif();
    getValsAjusGral();
    handleNavigation('datos-empresa');
});