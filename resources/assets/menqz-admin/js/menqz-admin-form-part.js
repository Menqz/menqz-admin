/*-------------------------------------------------*/
/* forms-part */
/*-------------------------------------------------*/

admin.form.part = {
    parts: null,

    init: function () {
        this.parts = [];
    },

    add: function ({
        id_part = null,
        url = null,
        main_class = null,
        parent_id = null,
        parent_class = null,
        active = false,
    }) {
        if(id_part == null){
            return;
        }
        const container = document.querySelector(`#tab-part-${id_part}`);
        const urlIndex = url + '?class='+main_class+'&parent_id='+parent_id+'&parent_class='+parent_class;
        const part = {
            url: url,
            url_index: urlIndex,
            id_part: id_part,
            main_class: main_class,
            parent_id: parent_id,
            parent_class: parent_class,
            active: active,
            container: container,
        };
        this.parts.push(part);
        this.setEvents(part);

        if(active){
            this.setActive(part);
        }
    },

    setEvents: function (part) {
        const tabLink = document.querySelector(`button[data-bs-toggle="tab"][data-bs-target="#tab-part-${part.id_part}"]`);
        tabLink.addEventListener('click', function (e) {
            e.preventDefault();
            const partId = tabLink.getAttribute('data-part-id');
            const partObj = admin.form.part.getPartById(partId);
            if(!partObj){
                return;
            }
            var url = partObj.url+'?class='+partObj.main_class+'&parent_id='+partObj.parent_id+'&parent_class='+partObj.parent_class;

            admin.form.part.loadPart(url, partObj.container);
        });

        part.container.addEventListener('click', function (e) {
            e.preventDefault();

            const partId = this.getAttribute('data-part-id');
            const partObj = admin.form.part.getPartById(partId);

            let target = e.target.closest('.grid-create-btn');
            if (target) {
                let url = target.getAttribute('href');
                admin.form.part.showAction(url, partObj);
            }

            target = e.target.closest('.grid-edit-btn');
            if (target) {
                let url = target.getAttribute('href');
                admin.form.part.showAction(url, partObj);
            }

            target = e.target.closest('.grid-show-btn');
            if (target) {
                let url = target.getAttribute('href');
                admin.modal.open({
                    title: '',
                    ajaxUrl: url,
                    actionText: 'Salvar',
                    showSpinOnActionButton: true,
                    closeOnlyActionResultTrue: true,
                });
            }

            target = e.target.closest('.icon-fw');
            if (target) {
                let url = target.getAttribute('href');
                admin.form.part.loadPart(url, partObj.container);
            }
        });
    },

    showAction: function (url, partObj) {
        admin.modal.open({
            title: '',
            ajaxUrl: url,
            actionText: 'Salvar',
            showSpinOnActionButton: false,
            closeOnlyActionResultTrue: true,
            onAction: async function () {
                admin.modal.setLoading(true);
                let form = admin.modal.getForm();
                if (!form) {
                    admin.modal.showAlert('Formulário inválido.');
                    return false;
                }
                let url = admin.modal.getUrlForm();
                if (!url) {
                    admin.modal.showAlert('URL inválida.');
                    return false;
                }
                url += '?class='+partObj.main_class+'&parent_id='+partObj.parent_id+'&parent_class='+partObj.parent_class;

                form.setAttribute('action', url);

                admin.form.submit(form, function (response) {
                    if (response.status == 200) {
                        admin.modal.showAlert('Registro salvo com sucesso.');
                        admin.form.part.loadPart(partObj.url_index, partObj.container);
                        admin.modal.close();
                    } else {
                        admin.modal.showAlert('Erro ao salvar registro.');
                    }
                    admin.modal.setLoading(false);
                }, function () {
                    admin.modal.setLoading(false);
                });
            }
        });
    },

    setActive: function (part) {
        const tabLink = document.querySelector(`button[data-bs-toggle="tab"][data-bs-target="#tab-part-${part.id_part}"]`);
        tabLink.click();
    },

    getPartById: function (id_part) {
        return this.parts.find((p) => p.id_part == id_part);
    },

    sendForm: async function (form, callback) {
        return admin.form.submit(form, callback);
    },

    loadPart: function (url, container) {
        container.innerHTML = this.getLoadingHtml();
        fetch(url)
            .then(response => response.text())
            .then(data => {
                container.innerHTML = data;
            });
    },

    getLoadingHtml: function () {
        return `
            <div class="loading text-center" style="padding: 50px;">
                <i class="spinner-border" role="status"></i>
            </div>
        `;
    }
}
