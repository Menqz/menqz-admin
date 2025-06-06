<?php

namespace MenqzAdmin\Admin\Form;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use MenqzAdmin\Admin\Admin;
use MenqzAdmin\Admin\Form;
use MenqzAdmin\Admin\Form\Field\Hidden;

/**
 * Class Builder.
 */
class Builder
{
    /**
     *  Previous url key.
     */
    public const PREVIOUS_URL_KEY = '_previous_';

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var
     */
    protected $action;

    /**
     * @var Collection
     */
    protected $fields;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Modes constants.
     */
    public const MODE_EDIT = 'edit';
    public const MODE_CREATE = 'create';

    /**
     * Form action mode, could be create|view|edit.
     *
     * @var string
     */
    protected $mode = 'create';

    /**
     * @var array
     */
    protected $hiddenFields = [];

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Footer
     */
    protected $footer;

    /**
     * Width for label and field.
     *
     * @var array
     */
    protected $width = [
        'label' => 2,
        'field' => 8,
    ];

    /**
     * View for this form.
     *
     * @var string
     */
    protected $view = 'admin::form';

    /**
     * Form title.
     *
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $formClass;

    /**
     * Builder constructor.
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

        $this->fields = new Collection();

        $this->init();
    }

    /**
     * Do initialize.
     */
    public function init()
    {
        $this->tools = new Tools($this);
        $this->footer = new Footer($this);

        $this->formClass = 'model-form-'.uniqid();
    }

    /**
     * Get form tools instance.
     *
     * @return Tools
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * Get form footer instance.
     *
     * @return Footer
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * Set the builder mode.
     *
     * @param string $mode
     *
     * @return void
     */
    public function setMode($mode = 'create')
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Returns builder is $mode.
     *
     * @param $mode
     *
     * @return bool
     */
    public function isMode($mode): bool
    {
        return $this->mode === $mode;
    }

    /**
     * Check if is creating resource.
     *
     * @return bool
     */
    public function isCreating(): bool
    {
        return $this->isMode(static::MODE_CREATE);
    }

    /**
     * Check if is editing resource.
     *
     * @return bool
     */
    public function isEditing(): bool
    {
        return $this->isMode(static::MODE_EDIT);
    }

    /**
     * Set resource Id.
     *
     * @param $id
     *
     * @return void
     */
    public function setResourceId($id)
    {
        $this->id = $id;
    }

    /**
     * Get Resource id.
     *
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->id;
    }

    /**
     * @param int|null $slice
     *
     * @return string
     */
    public function getResource(int $slice = null): string
    {
        if ($this->mode === self::MODE_CREATE) {
            return $this->form->resource(-1);
        }
        if ($slice !== null) {
            return $this->form->resource($slice);
        }

        return $this->form->resource();
    }

    /**
     * @param int $field
     * @param int $label
     *
     * @return $this
     */
    public function setWidth($field = 8, $label = 2): self
    {
        $this->width = [
            'label' => $label,
            'field' => $field,
        ];

        return $this;
    }

    /**
     * Get label and field width.
     *
     * @return array
     */
    public function getWidth(): array
    {
        return $this->width;
    }

    /**
     * Set form action.
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get Form action.
     *
     * @return string
     */
    public function getAction(): string
    {
        if ($this->action) {
            return $this->action;
        }

        if ($this->isMode(static::MODE_EDIT)) {
            return $this->form->resource().'/'.$this->id;
        }

        if ($this->isMode(static::MODE_CREATE)) {
            return $this->form->resource(-1);
        }

        return '';
    }

    /**
     * Set view for this form.
     *
     * @param string $view
     *
     * @return $this
     */
    public function setView($view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set title for form.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get fields of this builder.
     *
     * @return Collection
     */
    public function fields(): Collection
    {
        return $this->fields;
    }

    /**
     * Get specify field.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function field($name)
    {
        return $this->fields()->first(function (Field $field) use ($name) {
            return $field->column() === $name;
        });
    }

    /**
     * If the parant form has rows.
     *
     * @return bool
     */
    public function hasRows(): bool
    {
        return !empty($this->form->rows);
    }

    /**
     * Get field rows of form.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->form->rows;
    }

    /**
     * @return array
     */
    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    /**
     * @param Field $field
     *
     * @return void
     */
    public function addHiddenField(Field $field)
    {
        $this->hiddenFields[] = $field;
    }

    /**
     * Add or get options.
     *
     * @param array $options
     *
     * @return array|null
     */
    public function options($options = [])
    {
        if (empty($options)) {
            return $this->options;
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get or set option.
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return $this
     */
    public function option($option, $value = null)
    {
        if (func_num_args() === 1) {
            return Arr::get($this->options, $option);
        }

        $this->options[$option] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->mode === static::MODE_CREATE) {
            return trans('admin.create');
        }

        if ($this->mode === static::MODE_EDIT) {
            return trans('admin.edit');
        }

        return '';
    }

    /**
     * Determine if form fields has files.
     *
     * @return bool
     */
    public function hasFile(): bool
    {
        foreach ($this->fields() as $field) {
            if ($field instanceof Field\File || $field instanceof Field\MultipleFile) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add field for store redirect url after update or store.
     *
     * @return void
     */
    protected function addRedirectUrlField()
    {
        $previous = URL::previous();
        $current = URL::current();
        $full = URL::full();

        if (!$previous || $previous === $current || $previous === $full) {
            return;
        }

        $set_previous = $previous;
        $ids = request('ids');
        if (!empty($ids)) {
            $set_previous = $current.'?ids[]='.implode('&ids[]=', $ids);
        }

        if (Str::contains($previous, url($this->getResource()))) {
            $this->addHiddenField((new Hidden(static::PREVIOUS_URL_KEY))->value($set_previous));
        }
    }

    /**
     * Open up a new HTML form.
     *
     * @param array $options
     *
     * @return string
     */
    public function open($options = []): string
    {
        if ($this->isMode(self::MODE_EDIT)) {
            $this->addHiddenField((new Hidden('_method'))->value('PUT'));
        }
        $this->addRedirectUrlField();

        $attributes = [];
        $attributes['action'] = $this->getAction();
        $attributes['method'] = Arr::get($options, 'method', 'post');
        $attributes['class'] = $this->formClass;
        if ($this->hasFile()) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $attributes_str = $this->form->formatAttribute($attributes);

        return '<form '.$attributes_str.'>';
    }

    /**
     * Close the current form.
     *
     * @return string
     */
    public function close(): string
    {
        $this->form = null;
        $this->fields = null;

        return '</form>';
    }

    /**
     * @param string $message
     */
    public function confirm(string $message)
    {
        $trans = [
            'confirm' => trans('admin.confirm'),
            'cancel'  => trans('admin.cancel'),
        ];

        $script = <<<JS
document.querySelector('form.{$this->formClass} button[type=submit]').addEventListener("click",function (e) {
    e.preventDefault();
    var form = e.target.closest('form');
    Swal.fire({
        title: "$message",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "{$trans['confirm']}",
        cancelButtonText: "{$trans['cancel']}",
    }).then(function (result) {
        if (result.value == true) {
            if (admin.form.validate(form)){
                form.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        }
    });

});
JS;

        Admin::script($script);
    }

    /**
     * Remove reserved fields like `id` `created_at` `updated_at` in form fields.
     *
     * @return void
     */
    protected function removeReservedFields()
    {
        if (!$this->isCreating()) {
            return;
        }

        $reservedColumns = [
            $this->form->model()->getCreatedAtColumn(),
            $this->form->model()->getUpdatedAtColumn(),
        ];

        if ($this->form->model()->incrementing) {
            $reservedColumns[] = $this->form->model()->getKeyName();
        }

        $this->form->getLayout()->removeReservedFields($reservedColumns);

        $this->fields = $this->fields()->reject(function (Field $field) use ($reservedColumns) {
            return in_array($field->column(), $reservedColumns, true);
        });
    }

    /**
     * Render form header tools.
     *
     * @return string
     */
    public function renderTools(): string
    {
        return $this->tools->render();
    }

    /**
     * Render form footer.
     *
     * @return string
     */
    public function renderFooter(): string
    {
        return $this->footer->render();
    }

    protected function addCascadeScript()
    {
        $script = <<<JS
        admin.form.disable_cascaded_forms("form.{$this->formClass}");
JS;

        Admin::script($script);
    }

    /**
     * Render form.
     *
     * @return string
     */
    public function render(): string
    {
        $this->removeReservedFields();

        $tabObj = $this->form->setTab();

        $this->addCascadeScript();

        $data = [
            'form'   => $this,
            'tabObj' => $tabObj,
            'width'  => $this->width,
            'layout' => $this->form->getLayout(),
        ];

        return view($this->view, $data)->render();
    }
}
