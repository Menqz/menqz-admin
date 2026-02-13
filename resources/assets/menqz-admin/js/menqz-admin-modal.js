/*-------------------------------------------------*/
/* modal */
/*-------------------------------------------------*/

admin.modal = {
    modalContainer: null,
    modalElement: null,
    modalInstance: null,
    titleElement: null,
    alertElement: null,
    alertBodyElement: null,
    bodyElement: null,
    footerCustomElement: null,
    actionButton: null,
    cancelButton: null,
    closeButton: null,
    ajaxUrl: null,
    body: null,
    actionText: '',

    init: function () {
        this.modalContainer = document.getElementById('container-modal');
        if (!this.modalContainer) {
            console.warn('container-modal not found, modal cannot be initialized');
            return;
        }

        this.loadModalHTML();

        this.modalElement = document.getElementById('modal-geral');
        this.modalInstance = bootstrap.Modal.getOrCreateInstance(this.modalElement, {backdrop: 'static'});
        this.titleElement = this.modalElement.querySelector('#modal-geral-label');
        this.alertElement = this.modalElement.querySelector('#modal-geral-alert');
        this.alertBodyElement = this.modalElement.querySelector('#modal-geral-alert-body');
        this.bodyElement = this.modalElement.querySelector('#modal-geral-body');
        this.footerCustomElement = this.modalElement.querySelector('#modal-footer-custom');
        this.actionButton = this.modalElement.querySelector('#modal-geral-enviar');
        this.cancelButton = this.modalElement.querySelector('#modal-geral-cancelar');
        this.closeButton = this.modalElement.querySelector('#modal-geral-close');
    },

    /**
     * Exibe o modal com os parâmetros especificados.
     * @param {Object} options - Configurações do modal.
     * @param {string} options.title - Título do modal.
     * @param {string} [options.body] - Conteúdo do corpo do modal (HTML ou texto). Ignorado se `ajaxUrl` for fornecido.
     * @param {string} [options.ajaxUrl] - URL para carregar conteúdo via AJAX.
     * @param {string} [options.actionText] - Texto do botão de ação (opcional).
     * @param {Function} [options.onAction] - Callback executado ao clicar no botão de ação (opcional).
     * @param {Function} [options.onCancel] - Callback executado ao clicar no botão de cancelar (opcional).
     * @param {boolean} [options.showCancelButton] - Se o botão "Cancelar" deve ser exibido.
     * @param {boolean} [options.closeOnlyActionResultTrue] - Se deve fechar apenas se a ação retornar true.
     * @param {boolean} [options.showSpinOnActionButton] - Se deve mostrar spinner no botão de ação.
     */
    open: function ({
        title,
        body = '',
        ajaxUrl = null,
        actionText = 'OK',
        cancelText = 'Cancelar',
        onAction = null,
        onCancel = null,
        showCancelButton = true,
        closeOnlyActionResultTrue = false,
        showSpinOnActionButton = true
    }) {
        if (!this.modalInstance) {
            this.init();
        }

        // Configura o título
        this.titleElement.textContent = title;

        this.ajaxUrl = ajaxUrl;
        this.body = body;
        this.actionText = actionText;

        this.reload();

        // Configura o botão de ação
        if (onAction) {
            this.actionButton.style.display = 'inline-block';
            this.actionButton.textContent = this.actionText;
            this.actionButton.onclick = () => {
                if (closeOnlyActionResultTrue) {
                    // Espera a resposta do onAction (promise ou valor síncrono)
                    if (showSpinOnActionButton) {
                        this.setLoading(true);
                    }
                    Promise.resolve(onAction()).then(result => {
                        if (result === true) {
                            this.close();
                        }
                    }).finally(() => {
                        if (showSpinOnActionButton) {
                            this.setLoading(false);
                        }
                    });
                } else {
                    onAction();
                    this.close();
                }

            };
        } else {
            this.actionButton.style.display = 'none';
        }

        this.cancelButton.textContent = cancelText;

        // Configura o botão de cancelar
        this.cancelButton.onclick = () => {
            if (onCancel) {
                onCancel();
            }
            this.close();
        };

        this.closeButton.onclick = () => {
            if (onCancel) {
                onCancel();
            }
            this.close();
        };

        // Configura o botão "Cancelar"
        this.cancelButton.style.display = showCancelButton ? 'inline-block' : 'none';

        // Limpa o rodapé customizado
        this.footerCustomElement.innerHTML = '';

        // Esconde o alerta por padrão
        this.hideAlert();

        // Exibe o modal
        this.modalInstance.show();
    },

    reload: function () {
        // Carrega o conteúdo via AJAX ou define o corpo diretamente
        if (this.ajaxUrl) {
            this.bodyElement.innerHTML = this.getLoadingHTML();
            fetch(this.ajaxUrl)
                .then(response => {
                    if (!response.ok) {
                        // Lança um erro para respostas HTTP que não são 2xx
                        throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    this.bodyElement.innerHTML = html;
                    this.executeScripts(this.bodyElement);
                })
                .catch(error => {
                    this.bodyElement.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Erro!</strong> Não foi possível carregar o conteúdo. Por favor, tente novamente mais tarde.
                        </div>`;
                    console.error('Erro ao carregar o conteúdo:', error);
                });
        } else {
            this.bodyElement.innerHTML = this.body;
            this.executeScripts(this.bodyElement);
        }
    },

    getLoadingHTML: function() {
         return '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    },

    loadModalHTML: function () {
        this.modalContainer.innerHTML = `
            <div id="blur-modal">
                <div class="modal fade" id="modal-geral" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal-geral-label" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content shadow" >
                            <div class="modal-header">
                                <h5 class="modal-title" id="modal-geral-label">Modal title</h5>
                                <button id="modal-geral-close" type="button" class="btn-close" aria-label="Close"></button>
                            </div>
                            <div id="modal-geral-alert" class="alert alert-warning alert-dismissible fade visually-hidden" role="alert">
                                <div id="modal-geral-alert-body"></div>
                            </div>
                            <div id="modal-geral-body" class="modal-body">
                                ...
                            </div>
                            <div id="modal-geral-footer d-flex" class="modal-footer">
                                <div id="modal-footer-custom" class="me-auto">
                                </div>
                                <div>
                                    <button type="button" id="modal-geral-cancelar" class="btn btn-secondary">Cancelar</button>
                                    <button type="button" id="modal-geral-enviar" class="btn btn-primary">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    clearModalHTML: function () {
        this.modalContainer.innerHTML = '';
    },

    /**
     * Executa os scripts encontrados no conteúdo HTML.
     * @param {HTMLElement} element - O elemento onde procurar scripts.
     */
    executeScripts: function (element) {
        const scripts = element.querySelectorAll('script');
        scripts.forEach((script) => {
            const newScript = document.createElement('script');
            newScript.type = script.type || 'text/javascript';
            if (script.src) {
                // Para scripts externos
                newScript.src = script.src;
                document.head.appendChild(newScript);
            } else {
                // Para scripts inline
                newScript.textContent = script.textContent;
                document.body.appendChild(newScript);
            }
        });
    },

    /**
     * Fecha o modal manualmente.
     */
    close: function () {
        if (this.modalInstance) {
            this.modalInstance.hide();
            this.clearModalHTML();
        }
    },

    /**
     * Exibe o alerta dinamicamente.
     * @param {Object} alert - Configurações do alerta.
     * @param {string} alert.type - Tipo do alerta (e.g., 'warning', 'danger', 'success').
     * @param {string} alert.message - Mensagem do alerta.
     */
    showAlert: function ({ type, message }) {
        this.alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        this.alertBodyElement.innerHTML = message;
    },

    /**
     * Esconde o alerta.
     */
    hideAlert: function () {
        this.alertElement.className = 'alert alert-dismissible fade visually-hidden';
        this.alertBodyElement.innerHTML = '';
    },

    /**
     * Esconde o Action Button.
     */
    hideActionButton: function () {
        this.actionButton.style.display = 'none';
    },

    /**
     * Exibe o Action Button.
     */
    showActionButton: function () {
        this.actionButton.style.display = 'inline-block';
    },

    showSpinOnActionButton: function () {
        this.actionButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> ' + this.actionText;
    },

    hideSpinOnActionButton: function () {
        this.actionButton.textContent = this.actionText;
    },

    /**
     * Exibe o Action Button.
     */
    changeActionButtonName: function (name) {
        this.actionButton.innerHTML = name;
    },

    /**
     * Define o estado de carregamento do modal.
     * @param {boolean} loading - Se verdadeiro, exibe o spinner; caso contrário, remove o spinner.
     */
    setLoading: function (loading) {
        if (loading) {
            this.showSpinOnActionButton();
            this.actionButton.disabled = true;
        } else {
            this.hideSpinOnActionButton();
            this.actionButton.disabled = false;
        }
    },

    /**
     * Adiciona conteúdo personalizado ao rodapé.
     * @param {string} html - Conteúdo HTML a ser adicionado ao rodapé.
     */
    addFooterContent: function (html) {
        this.footerCustomElement.innerHTML = html;
    }
};
