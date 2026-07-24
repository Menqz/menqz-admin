/*-------------------------------------------------*/
/* Dialogs */
/*-------------------------------------------------*/

admin.dialog = {
    defaults: {
        confirmButtonText: () => __('confirm'),
        cancelButtonText: () => __('cancel'),
        closeButtonText: () => __('close'),

        confirmButtonClass: 'btn btn-primary ms-2',
        cancelButtonClass: 'btn btn-secondary',
        dangerButtonClass: 'btn btn-danger',
    },

    /**
     * Abre um Swal genérico.
     *
     * @param {Object} options
     * @returns {Promise<SweetAlertResult>}
     */
    fire: function (options = {}) {
        return Swal.fire({
            buttonsStyling: false,

            customClass: {
                confirmButton: this.defaults.confirmButtonClass,
                cancelButton: `${this.defaults.cancelButtonClass} ms-2`,
            },

            ...options,

            // Permite sobrescrever parcialmente o customClass sem perder
            // as classes padrão.
            customClass: {
                confirmButton:
                    options.customClass?.confirmButton ??
                    this.defaults.confirmButtonClass,

                cancelButton:
                    options.customClass?.cancelButton ??
                    `${this.defaults.cancelButtonClass} ms-2`,

                ...options.customClass,
            },
        });
    },

    /**
     * Alert genérico.
     *
     * @param {Object} options
     * @returns {Promise<SweetAlertResult>}
     */
    alert: function ({
        title = '',
        message = '',
        html = null,
        icon = null,
        confirmText = null,
        ...options
    } = {}) {
        return this.fire({
            title: title,
            text: html === null ? message : undefined,
            html: html,
            icon: icon,
            confirmButtonText:
                confirmText ?? this.defaults.closeButtonText(),
            ...options,
        });
    },

    /**
     * Exibe uma mensagem de sucesso.
     */
    success: function (message, title = null, options = {}) {
        return this.alert({
            title: title ?? __('success'),
            message: message,
            icon: 'success',
            ...options,
        });
    },

    /**
     * Exibe uma mensagem informativa.
     */
    info: function (message, title = null, options = {}) {
        return this.alert({
            title: title ?? __('information'),
            message: message,
            icon: 'info',
            ...options,
        });
    },

    /**
     * Exibe um alerta de atenção.
     */
    warning: function (message, title = null, options = {}) {
        return this.alert({
            title: title ?? __('warning'),
            message: message,
            icon: 'warning',
            ...options,
        });
    },

    /**
     * Exibe uma mensagem de erro.
     *
     * O SweetAlert2 utiliza o icon "error".
     * Dentro do MenqzAdmin podemos expor o nome "danger".
     */
    danger: function (message, title = null, options = {}) {
        return this.alert({
            title: title ?? __('error'),
            message: message,
            icon: 'error',
            confirmButtonClass: this.defaults.dangerButtonClass,
            ...options,
        });
    },

    /**
     * Alias para danger.
     */
    error: function (message, title = null, options = {}) {
        return this.danger(message, title, options);
    },

    /**
     * Exibe uma pergunta simples.
     */
    question: function (message, title = null, options = {}) {
        return this.alert({
            title: title ?? __('confirmation'),
            message: message,
            icon: 'question',
            ...options,
        });
    },

    /**
     * Solicita uma confirmação.
     *
     * @returns {Promise<boolean>}
     */
    confirm: async function ({
        title = null,
        message = '',
        html = null,
        icon = 'question',
        confirmText = null,
        cancelText = null,
        confirmButtonClass = null,
        cancelButtonClass = null,
        reverseButtons = true,
        focusCancel = true,
        allowOutsideClick = true,
        allowEscapeKey = true,
        ...options
    } = {}) {
        const result = await this.fire({
            title: title ?? __('confirmation'),
            text: html === null ? message : undefined,
            html: html,
            icon: icon,

            showCancelButton: true,

            confirmButtonText:
                confirmText ?? this.defaults.confirmButtonText(),

            cancelButtonText:
                cancelText ?? this.defaults.cancelButtonText(),

            reverseButtons: reverseButtons,
            focusCancel: focusCancel,
            allowOutsideClick: allowOutsideClick,
            allowEscapeKey: allowEscapeKey,

            customClass: {
                confirmButton:
                    confirmButtonClass ??
                    this.defaults.confirmButtonClass,

                cancelButton:
                    cancelButtonClass ??
                    `${this.defaults.cancelButtonClass} ms-2`,
            },

            ...options,
        });

        return result.isConfirmed === true;
    },

    /**
     * Confirmação para ações destrutivas.
     *
     * @returns {Promise<boolean>}
     */
    confirmDanger: function ({
        title = null,
        message = '',
        confirmText = null,
        ...options
    } = {}) {
        return this.confirm({
            title: title ?? __('attention'),
            message: message,
            icon: 'warning',

            confirmText:
                confirmText ?? __('confirm'),

            confirmButtonClass:
                this.defaults.dangerButtonClass,

            ...options,
        });
    },

    /**
     * Exibe um dialog de carregamento.
     */
    loading: function ({
        title = null,
        message = '',
        allowOutsideClick = false,
        allowEscapeKey = false,
        ...options
    } = {}) {
        return this.fire({
            title: title ?? __('loading'),
            text: message,
            allowOutsideClick: allowOutsideClick,
            allowEscapeKey: allowEscapeKey,
            showConfirmButton: false,

            didOpen: function () {
                Swal.showLoading();
            },

            ...options,
        });
    },

    /**
     * Fecha o dialog atual.
     */
    close: function () {
        Swal.close();
    },

    /**
     * Exibe um toast.
     */
    toast: function ({
        message = '',
        icon = 'success',
        position = 'top-end',
        timer = 3000,
        ...options
    } = {}) {
        return Swal.fire({
            toast: true,
            position: position,
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,

            didOpen: function (toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },

            ...options,
        });
    },
};
