admin.document = {
    documents: [],
    signatureEnabled: false,
    signatureOptions: { parceiroId: null, allowAvulso: true, allowColaborador: true, origem: { tabela: null, id: null } },
    currentPdfBlob: null,
    currentPdfUrl: null,
    currentDocumentName: null,
    currentFileName: null,
    currentRoute: null,
    pdfDocument: null,

    setDocuments: function (documents) {
        this.documents = Array.isArray(documents) ? documents : [];
    },

    configureSignature: function (options = {}) {
        this.signatureEnabled = !!options.enabled;
        this.signatureOptions = {
            parceiroId: options.parceiroId ?? null,
            allowAvulso: options.allowAvulso ?? true,
            allowColaborador: options.allowColaborador ?? true,
            origem: {
                tabela: options.origem?.tabela ?? null,
                id: options.origem?.id ?? null,
            },
        };
    },

    downloadDirect: function (route, name, fileName) {
        const downloadUrl = this.buildDownloadUrl(route);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = fileName ?? name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },

    showDirect: function (route, name, fileName) {
        this.emitDocument(route, name, fileName);
    },

    show: function () {
        const documentListHTML = this.documents
            .map((doc, index) => `<li class="list-group-item list-group-item-action" role="button" style="padding: 15px;" data-index="${index}">${doc.name}</li>`)
            .join('');

        const content = `
            <div id="document-emitter-selection">
                <p>Selecione o documento que deseja emitir:</p>
                <ul id="document-emitter-list" class="list-group py-2">
                    ${documentListHTML}
                </ul>
            </div>
            <div id="document-emitter-viewer" class="d-none">
                <button type="button" id="document-emitter-back" class="btn btn-secondary mb-3">Voltar</button>
                <div id="document-emitter-content"></div>
            </div>
        `;

        admin.modal.open({
            title: 'Emitir Documento',
            body: content,
            showCancelButton: true,
        });

        this.addSelectionEventListeners();
    },

    addSelectionEventListeners: function () {
        const listItems = document.querySelectorAll('#document-emitter-list li');
        listItems.forEach((item) => {
            item.addEventListener('click', function () {
                const index = Number(this.getAttribute('data-index'));
                const doc = admin.document.documents[index];
                if (!doc) {
                    return;
                }
                admin.document.emitDocument(doc.route, doc.name, doc.fileName);
            });
        });

        const backButton = document.getElementById('document-emitter-back');
        if (backButton) {
            backButton.addEventListener('click', () => admin.document.show());
        }
    },

    emitDocument: async function (route, name, fileName) {
        try {
            admin.modal.open({
                title: 'Carregando documento...',
                body: admin.modal.getLoadingHTML(),
                showCancelButton: false,
            });

            const response = await fetch(route);
            if (!response.ok) {
                throw new Error('Erro ao carregar o documento.');
            }

            const blob = await response.blob();
            this.cleanupCurrentPdf();

            this.currentPdfBlob = blob;
            this.currentDocumentName = name;
            this.currentFileName = (fileName ?? name).endsWith('.pdf')
                    ? (fileName ?? name)
                    : `${fileName ?? name}.pdf`;
            this.currentRoute = route;

            const pdfUrl = URL.createObjectURL(blob);
            this.currentPdfUrl = pdfUrl;

            const viewerHTML = `<div id="pdf-viewer-container" class="d-flex flex-column" style="padding: 0 30px; width: 100%; max-height: 100%; margin-left: auto; margin-right: auto;"></div>`;

            admin.modal.open({
                title: `Visualizando: ${name}`,
                body: viewerHTML,
                onCancel: () => admin.document.cleanupCurrentPdf(),
                showCancelButton: true,
            });

            const downloadUrl = this.buildDownloadUrl(route);

            let footerButtons = `
                <div class="d-flex gap-2">
                    <a href="${downloadUrl}" target="_blank" class="btn btn-primary" download><i class="icon-download"></i></a>
                `;

            if (this.signatureEnabled) {
                footerButtons += `<button type="button" id="document-emitter-send-signature" class="btn btn-success">Enviar para Assinatura</button>`;
            }

             if (isMobile() || navigator.canShare) {
                footerButtons += `
                    <button type="button" id="document-emitter-share-native" class="btn btn-info">
                        <i class="icon-share-alt"></i>
                    </button>
                `;
            }

            footerButtons += `</div>`;

            admin.modal.addFooterContent(footerButtons);

            const sendBtn = document.getElementById('document-emitter-send-signature');
            if (sendBtn) {
                sendBtn.addEventListener('click', () => admin.document.openSignatureModal());
            }

            const shareBtn = document.getElementById('document-emitter-share-native');
            if (shareBtn) {
                shareBtn.addEventListener('click', () => admin.document.compartilharPDF());
            }

            await this.renderPdf(pdfUrl);
        } catch (error) {
            console.error(error);
            admin.modal.open({
                title: 'Erro',
                body: `<div class="alert alert-danger">Não foi possível carregar o documento. Tente novamente mais tarde.</div>`,
                actionText: 'Fechar',
                showCancelButton: false,
                onAction: () => true,
            });
        }
    },

    renderPdf: async function (pdfUrl) {
        if (typeof pdfjsLib === 'undefined') {
            admin.modal.showAlert({ type: 'danger', message: 'PDF.js não está disponível para renderizar o documento.' });
            return;
        }

        const container = document.getElementById('pdf-viewer-container');
        if (!container) {
            return;
        }

        try {
            const loadingTask = pdfjsLib.getDocument(pdfUrl);
            const pdf = await loadingTask.promise;
            this.pdfDocument = pdf;
            container.innerHTML = '';

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const scale = 2.5;
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.style = 'margin: 10px 0; border: solid 0px #000; box-shadow: 0 0 10px rgba(0,0,0,0.1);';
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                container.appendChild(canvas);

                await page.render({ canvasContext: context, viewport });
            }
        } catch (error) {
            console.error('Erro ao carregar o PDF:', error);
            admin.modal.showAlert({ type: 'danger', message: 'Erro ao renderizar o documento.' });
        }
    },

    openSignatureModal: function () {
        const formHTML = `
            <form id="clicksign-send-form" class="p-2">
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-add-contact">+ Contato do Parceiro</button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-add-avulso">+ Signatário Avulso</button>
                        <button type="button" class="btn btn-outline-success" id="btn-add-colaborador">+ Colaborador</button>
                    </div>
                </div>
                <div id="signature-signers-list" class="mb-3"></div>
                <div class="mb-3">
                    <label for="signature_message" class="form-label">Mensagem (opcional)</label>
                    <textarea id="signature_message" class="form-control" rows="3" placeholder="Mensagem ao(s) destinatário(s)"></textarea>
                </div>
            </form>
        `;

        admin.modal.open({
            title: 'Enviar para Assinatura',
            body: formHTML,
            actionText: 'Enviar',
            showCancelButton: true,
            closeOnlyActionResultTrue: true,
            showSpinOnActionButton: true,
            onAction: async () => {
                const messageInput = document.getElementById('signature_message');
                const message = (messageInput?.value || '').trim();

                try {
                    const signers = await admin.document.collectSigners();
                    if (!signers.length) {
                        admin.modal.showAlert({ type: 'warning', message: 'Adicione ao menos um signatário.' });
                        return false;
                    }

                    const base64 = await admin.document.blobToBase64(admin.document.currentPdfBlob);
                    const contentBase64 = String(base64).replace(/^data:application\/pdf;base64,/, '');
                    const fileBase = admin.document.currentFileName || 'documento';
                    const normalizedFileName = String(fileBase).toLowerCase().endsWith('.pdf') ? fileBase : `${fileBase}.pdf`;

                    const payload = {
                        envelope_name: admin.document.currentDocumentName || 'Documento',
                        file_name: normalizedFileName,
                        content_base64: contentBase64,
                        signers: signers,
                        message: message || null,
                        origem_tabela: admin.document.signatureOptions?.origem?.tabela ?? null,
                        id_origem: admin.document.signatureOptions?.origem?.id ?? null,
                        origem_rota: admin.document.currentRoute || null,
                        id_parceiro: admin.document.signatureOptions?.parceiroId ?? null,
                    };

                    const response = await fetch(`${admin.document.getAdminPrefix()}/assinaturas/clicksign/enviar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': admin.document.getCsrfToken(),
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text || 'Falha ao enviar para assinatura.');
                    }

                    const result = await response.json();
                    if (result.success) {
                        admin.modal.showAlert({ type: 'success', message: 'Documento enviado para assinatura com sucesso.' });
                        await Swal.fire({
                            title: 'Documento Enviado',
                            icon: 'info',
                            html: '<p>O documento foi enviado para assinatura. Aguarde o envio do e-mail para começar a assinar.</p>',
                            confirmButtonText: __('confirm'),
                            cancelButtonText: __('cancel'),
                            showCancelButton: false,
                        });
                        return true;
                    }

                    admin.modal.showAlert({ type: 'danger', message: result.error || 'Erro ao enviar para assinatura.' });
                    return false;
                } catch (error) {
                    console.error('Erro ao enviar para assinatura:', error);
                    let message = error?.message || 'Erro ao enviar para assinatura.';
                    try {
                        const parsed = JSON.parse(message);
                        message = parsed?.error || message;
                    } catch (_) {}
                    admin.modal.showAlert({ type: 'danger', message: `Erro ao enviar para assinatura.<br/>${message}` });
                    return false;
                }
            },
        });

        const addContactBtn = document.getElementById('btn-add-contact');
        const addAvulsoBtn = document.getElementById('btn-add-avulso');
        const addColabBtn = document.getElementById('btn-add-colaborador');

        if (addContactBtn) addContactBtn.addEventListener('click', () => admin.document.addContactRow());
        if (addAvulsoBtn && this.signatureOptions.allowAvulso) addAvulsoBtn.addEventListener('click', () => admin.document.addAvulsoRow());
        if (addColabBtn && this.signatureOptions.allowColaborador) addColabBtn.addEventListener('click', () => admin.document.addColaboradorRow());
    },

    addContactRow: function () {
        if (!this.signatureOptions.parceiroId) {
            admin.modal.showAlert({ type: 'warning', message: 'Selecione um parceiro para buscar contatos.' });
            return;
        }

        const container = document.getElementById('signature-signers-list');
        if (!container) {
            return;
        }

        const id = `contact-${Date.now()}`;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2';
        row.dataset.type = 'contato';
        row.innerHTML = `
            <div class="col-11">
                <select id="${id}" class="form-control" placeholder="Contato do Parceiro"></select>
            </div>
            <div class="col-1 d-flex">
                <button type="button" class="btn btn-danger btn-sm ms-auto" style="color: #fff;" data-action="remove"><i class="icon-trash"></i></button>
            </div>
        `;
        container.appendChild(row);

        const selectEl = row.querySelector(`#${id}`);
        if (!selectEl || typeof Choices === 'undefined') {
            return;
        }

        const choices = new Choices(selectEl, {
            removeItems: true,
            removeItemButton: true,
            allowHTML: true,
            placeholder: 'Contato do Parceiro',
            classNames: { containerOuter: `choices ${id}` },
        });

        let lookupTimeout;
        selectEl.addEventListener('search', () => {
            clearTimeout(lookupTimeout);
            lookupTimeout = setTimeout(() => {
                const query = choices.input.value;
                admin.ajax.post('/api/contatos', { query: query, paramId: admin.document.signatureOptions.parceiroId, exibeEmail: true }, function (resp) {
                    const items = resp?.data?.data || [];
                    choices.setChoices(items, 'id', 'text', true);
                });
            }, 250);
        });

        selectEl.addEventListener('choice', () => {
            choices.setChoices([], 'id', 'text', true);
        });

        const removeBtn = row.querySelector('[data-action="remove"]');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => row.remove());
        }
    },

    addAvulsoRow: function () {
        const container = document.getElementById('signature-signers-list');
        if (!container) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'row g-2 mb-2';
        row.dataset.type = 'avulso';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" class="form-control" placeholder="Nome do signatário" data-field="name">
            </div>
            <div class="col-6">
                <input type="email" class="form-control" placeholder="Email do signatário" data-field="email">
            </div>
            <div class="col-1 d-flex">
                <button type="button" class="btn btn-danger btn-sm ms-auto" style="color: #fff;" data-action="remove"><i class="icon-trash"></i></button>
            </div>
        `;
        container.appendChild(row);

        const removeBtn = row.querySelector('[data-action="remove"]');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => row.remove());
        }
    },

    addColaboradorRow: function () {
        const container = document.getElementById('signature-signers-list');
        if (!container) {
            return;
        }

        const id = `colab-${Date.now()}`;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2';
        row.dataset.type = 'colaborador';
        row.innerHTML = `
            <div class="col-11">
                <select id="${id}" class="form-control" placeholder="Colaborador"></select>
            </div>
            <div class="col-1 d-flex">
                <button type="button" class="btn btn-danger btn-sm ms-auto" style="color: #fff;" data-action="remove"><i class="icon-trash"></i></button>
            </div>
        `;
        container.appendChild(row);

        const selectEl = row.querySelector(`#${id}`);
        if (!selectEl || typeof Choices === 'undefined') {
            return;
        }

        const choices = new Choices(selectEl, {
            removeItems: true,
            removeItemButton: true,
            allowHTML: true,
            placeholder: 'Colaborador',
            classNames: { containerOuter: `choices ${id}` },
        });

        let lookupTimeout;
        selectEl.addEventListener('search', () => {
            clearTimeout(lookupTimeout);
            lookupTimeout = setTimeout(() => {
                const query = choices.input.value;
                admin.ajax.post('/api/colaboradores', { query: query, exibeEmail: true }, function (resp) {
                    const items = resp?.data?.data || [];
                    choices.setChoices(items, 'id', 'text', true);
                });
            }, 250);
        });

        selectEl.addEventListener('choice', () => {
            choices.setChoices([], 'id', 'text', true);
        });

        const removeBtn = row.querySelector('[data-action="remove"]');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => row.remove());
        }
    },

    collectSigners: async function () {
        const rows = Array.from(document.querySelectorAll('#signature-signers-list > *'));
        const signers = [];

        for (const row of rows) {
            const type = row.dataset.type;
            if (type === 'avulso') {
                const name = row.querySelector('[data-field="name"]')?.value?.trim() || '';
                const email = row.querySelector('[data-field="email"]')?.value?.trim() || '';
                if (name && email) {
                    signers.push({ name, email });
                }
                continue;
            }

            if (type === 'contato') {
                const select = row.querySelector('select');
                const id = select ? (select.value || '').trim() : '';
                if (!id) {
                    continue;
                }
                const resp = await fetch(`/api/contatos/${id}`);
                const data = await resp.json();
                const registro = data?.registro ?? {};
                const name = registro.NomeExibicao || '';
                const email = registro.EMail1 || '';
                if (name && email) {
                    signers.push({ name, email });
                }
                continue;
            }

            if (type === 'colaborador') {
                const select = row.querySelector('select');
                const id = select ? (select.value || '').trim() : '';
                if (!id) {
                    continue;
                }
                const resp = await fetch(`/api/parceiros/${id}`);
                const data = await resp.json();
                const registro = data?.registro ?? {};
                const name = registro.NomeExibicao || '';
                const email = registro.EMail1 || '';
                if (name && email) {
                    signers.push({ name, email });
                }
            }
        }

        return signers;
    },

    compartilharPDF: async function() {
        if (!this.currentPdfBlob) {
            alert('Nenhum PDF carregado para compartilhar.');
            return;
        }

        const fileName = this.currentFileName || 'documento.pdf';

        const file = new File([this.currentPdfBlob], fileName, {
            type: 'application/pdf'
        });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: fileName,
                text: 'Segue o documento',
                files: [file]
            });
        } else {
            alert('Compartilhamento de arquivo não suportado neste navegador/dispositivo.');
        }
    },

    blobToBase64: function (blob) {
        return new Promise((resolve, reject) => {
            try {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = (e) => reject(e);
                reader.readAsDataURL(blob);
            } catch (e) {
                reject(e);
            }
        });
    },

    buildDownloadUrl: function (route) {
        const separator = String(route).includes('?') ? '&' : '?';
        return `${route}${separator}download=1&_t=${Date.now()}`;
    },

    cleanupCurrentPdf: function () {
        if (this.currentPdfUrl) {
            URL.revokeObjectURL(this.currentPdfUrl);
        }
        this.currentPdfUrl = null;
        this.currentPdfBlob = null;
        this.currentDocumentName = null;
        this.currentFileName = null;
        this.currentRoute = null;

        if (this.pdfDocument) {
            try {
                this.pdfDocument.destroy();
            } catch (_) {}
        }
        this.pdfDocument = null;
    },

    getAdminPrefix: function () {
        const prefixFromMeta = document.querySelector('meta[name="admin-prefix"]')?.content;
        if (prefixFromMeta) {
            return prefixFromMeta.startsWith('/') ? prefixFromMeta : `/${prefixFromMeta}`;
        }

        const path = window.location.pathname || '';
        if (path.startsWith('/admin')) {
            return '/admin';
        }

        return '';
    },

    getCsrfToken: function () {
        if (typeof LA !== 'undefined' && LA?.token) {
            return LA.token;
        }
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
};
