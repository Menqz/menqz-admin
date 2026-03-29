/*-------------------------------------------------*/
/* forms-part */
/*-------------------------------------------------*/

admin.form.part = {
    parts: null,

    init: function () {
        this.parts = [];
    },

    add: function ({
        title = '',
        id_part = null,
        url = null,
        main_class = null,
        parent_id = null,
        parent_class = null,
        form_id = null,
        active = false,
    }) {
        if(id_part == null){
            return;
        }
        const container = document.querySelector(`#tab-part-${id_part}`);
        const urlIndex = url + '?class='+main_class+'&parent_id='+parent_id+'&parent_class='+parent_class;
        const part = {
            title: title,
            url: url,
            url_index: urlIndex,
            id_part: id_part,
            main_class: main_class,
            parent_id: parent_id,
            parent_class: parent_class,
            active: active,
            container: container,
            form_id: form_id,
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
                admin.form.part.openActionWithMainSave(url, partObj, trans('create'));
            }

            target = e.target.closest('.grid-edit-btn');
            if (target) {
                let url = target.getAttribute('href');
                admin.form.part.openActionWithMainSave(url, partObj, trans('edit'));
            }

            target = e.target.closest('.grid-show-btn');
            if (target) {
                let url = target.getAttribute('href');
                admin.modal.open({
                    title: partObj.title,
                    subTitle: trans('show'),
                    ajaxUrl: url,
                    actionText: trans('close'),
                    showSpinOnActionButton: true,
                    closeOnlyActionResultTrue: true,
                });
            }

            target = e.target.closest('.grid-delete-btn');
            if (target) {
                let url = target.getAttribute('data-url');
                admin.form.part.delete(url, partObj);
            }

            target = e.target.closest('.icon-fw');
            if (target) {
                let url = target.getAttribute('href');
                admin.form.part.loadPart(url, partObj.container);
            }
        });
    },

    openActionWithMainSave: function (url, partObj, subTitle) {
        if (!partObj || !url) {
            return;
        }

        const mainForm = this.getMainForm(partObj);
        if (!mainForm) {
            this.showAction(url, partObj);
            return;
        }

        if (!admin.form.validate(mainForm)) {
            return;
        }

        Swal.fire({
            title: trans('submiting'),
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        admin.form.submit(mainForm, function(data) {
            admin.form.enableSubmitButton(mainForm);
            Swal.close();
            admin.form.part.showAction(url, partObj, subTitle);
        }, function (error) {
            console.log(error);
            Swal.close();
        });
    },

    showAction: function (url, partObj, subTitle) {
        admin.modal.open({
            title: partObj.title,
            subTitle: subTitle,
            ajaxUrl: url,
            actionText: trans('submit'),
            showSpinOnActionButton: false,
            closeOnlyActionResultTrue: true,
            onAction: async function () {
                admin.modal.setLoading(true);
                let form = admin.modal.getForm();
                if (!form) {
                    admin.modal.showAlert({ type: 'danger', message: 'Formulário inválido.' });
                    admin.modal.setLoading(false);
                    return false;
                }
                let url = admin.modal.getUrlForm();
                if (!url) {
                    admin.modal.showAlert({ type: 'danger', message: 'URL inválida.' });
                    admin.modal.setLoading(false);
                    return false;
                }
                url += '?class='+partObj.main_class+'&parent_id='+partObj.parent_id+'&parent_class='+partObj.parent_class;

                form.setAttribute('action', url);

                if (!admin.form.validate(form)) {
                    admin.modal.setLoading(false);
                    return false;
                }

                admin.form.part.clearFormErrors(form);

                const method = (form.getAttribute('method') || 'post').toLowerCase();
                const formData = new FormData(form);
                const headers = Object.assign({}, admin.ajax.defaults.headers || {}, { Accept: 'application/json' });

                try {
                    const response = await axios({
                        url: url,
                        method: method,
                        data: formData,
                        headers: headers,
                    });

                    if (response && response.status >= 200 && response.status < 300) {
                        admin.form.part.loadPart(partObj.url_index, partObj.container);
                        admin.modal.setLoading(false);
                        admin.modal.close();
                        return true;
                    }
                    admin.modal.setLoading(false);
                    return false;
                } catch (error) {
                    const res = error && error.response ? error.response : null;

                    if (res && res.status === 422 && res.data && res.data.errors) {
                        admin.form.part.applyValidationErrors(form, res.data.errors);
                        const headerMessage = res.data.message || 'Há erros de validação no formulário.';
                    } else if (res && res.data && res.data.message) {
                        admin.modal.showAlert({ type: 'danger', message: res.data.message });
                    } else {
                        admin.modal.showAlert({ type: 'danger', message: 'Erro ao salvar registro.' });
                    }

                    admin.modal.setLoading(false);
                    return false;
                }
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

    getMainForm: function (partObj) {
        if (!partObj || !partObj.form_id) {
            return null;
        }
        const form = document.getElementById(partObj.form_id);

        return form || null;
    },

    sendForm: async function (form, callback) {
        return admin.form.submit(form, callback);
    },

    delete: function (url, partObj) {
        Swal.fire({
            title: __('delete_confirm'),
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: __('confirm'),
            showLoaderOnConfirm: true,
            cancelButtonText:  __('cancel'),
            preConfirm: function() {
                return new Promise(function(resolve) {
                    let data = {_method:'delete'};
                    admin.ajax.post(url,data,function(data){
                        resolve(data);
                        admin.form.part.loadPart(partObj.url_index, partObj.container);
                    });
                });
            }
        }).then(admin.resource.default_swal_response);
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
    },

    clearFormErrors: function (form) {
        if (!form) {
            return;
        }

        form.querySelectorAll('.form-group.has-error').forEach(function (group) {
            group.classList.remove('has-error');
        });

        form.querySelectorAll('.form-group div.alert.alert-danger').forEach(function (alert) {
            if (alert.querySelector('li[for="inputError"]')) {
                alert.remove();
            }
        });
    },

    applyValidationErrors: function (form, errors) {
        if (!form || !errors) {
            return;
        }

        this.clearFormErrors(form);

        var fieldErrors = {};

        Object.keys(errors).forEach(function (fieldName) {
            var messages = errors[fieldName];
            if (!messages) {
                return;
            }

            var fieldBaseName = fieldName.replace(/\.\d+$/, '');

            if (!fieldErrors[fieldBaseName]) {
                fieldErrors[fieldBaseName] = [];
            }

            if (Array.isArray(messages)) {
                fieldErrors[fieldBaseName] = fieldErrors[fieldBaseName].concat(messages);
            } else {
                fieldErrors[fieldBaseName].push(messages);
            }
        });

        Object.keys(fieldErrors).forEach(function (fieldBaseName) {
            var messages = fieldErrors[fieldBaseName];
            if (!messages || !messages.length) {
                return;
            }

            var field =
                form.querySelector('[name="' + fieldBaseName + '"]') ||
                form.querySelector('[name="' + fieldBaseName + '[]"]');

            if (!field) {
                return;
            }

            var group = field.closest('.form-group') || field.parentElement || field;
            group.classList.add('has-error');

            var content =
                group.querySelector('.col-sm-8') ||
                group.querySelector('.col-sm-9') ||
                group.querySelector('.col-sm-10') ||
                group;

            var inputGroup = content.querySelector('.input-group') || field.closest('.input-group');

            var alert = document.createElement('div');
            alert.className = 'alert alert-danger';

            var ul = document.createElement('ul');
            ul.className = 'm-0 ps-3';

            messages.forEach(function (msg) {
                var li = document.createElement('li');
                li.setAttribute('for', 'inputError');
                li.textContent = msg;
                ul.appendChild(li);
            });

            alert.appendChild(ul);

            if (inputGroup && inputGroup.parentNode === content) {
                content.insertBefore(alert, inputGroup);
            } else if (field.parentNode === content) {
                content.insertBefore(alert, field);
            } else {
                content.insertBefore(alert, content.firstChild);
            }
        });
    }
}
