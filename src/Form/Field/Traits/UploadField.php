<?php

namespace MenqzAdmin\Admin\Form\Field\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use MenqzAdmin\Admin\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait UploadField
{
    /**
     * Upload directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * File name.
     *
     * @var null
     */
    protected $name = null;

    /**
     * Storage instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $storage = '';

    /**
     * If use unique name to store upload file.
     *
     * @var bool
     */
    protected $useUniqueName = false;

    /**
     * If use sequence name to store upload file.
     *
     * @var bool
     */
    protected $useSequenceName = true;

    /**
     * Retain file when delete record from DB.
     *
     * @var bool
     */
    protected $retainable = false;

    /**
     * @var bool
     */
    protected $downloadable = true;

    /**
     * Configuration for setting up file actions for newly selected file thumbnails in the preview window.
     *
     * @var array
     */
    protected $fileActionSettings = [
        'showRemove' => false,
        'showDrag'   => false,
    ];

    /**
     * Controls the storage permission. Could be 'private' or 'public'.
     *
     * @var string
     */
    protected $storagePermission;

    /**
     * @var array
     */
    protected $fileTypes = [
        'image'      => '/^(gif|png|jpe?g|svg|webp|bpm|tiff)$/i',
        'html'       => '/^(htm|html)$/i',
        'word'       => '/^(doc|docx|rtf)$/i',
        'excel'      => '/^(xls|xlsx|csv)$/i',
        'powerpoint' => '/^(ppt|pptx|pps|potx)$/i',
        'text'       => '/^(txt|rtf|md|csv|nfo|ini|json|php|js|css|ts|sql)$/i',
        'video'      => '/^(og?|mp4|webm|mp?g|mov|3gp|avi|)$/i',
        'audio'      => '/^(og?|mp3|mp?g|wav)$/i',
        'pdf'        => '/^(pdf)$/i',
        'archive'    => '/^(zip|rar)$/i',
    ];

    /**
     * @var array
     */
    protected $fileTypesIcons = [
        'file'       => 'icon-file',
        'image'      => 'icon-file-image',
        'html'       => 'icon-file-code',
        'word'       => 'icon-file-word',
        'excel'      => 'icon-file-excel',
        'powerpoint' => 'icon-file-powerpoint',
        'text'       => 'icon-file-alt',
        'video'      => 'icon-file-video',
        'audio'      => 'icon-file-audio',
        'pdf'        => 'icon-file-pdf',
        'archive'    => 'icon-file-archive',
    ];

    /**
     * @var string
     */
    protected $pathColumn;

    /**
     * @var string
     */
    protected $sortColumn;

    /**
     * Initialize the storage instance.
     *
     * @return void.
     */
    protected function initStorage()
    {
        $this->disk(config('admin.upload.disk'));
    }

    /**
     * Set default options form image field.
     *
     * @return void
     */
    protected function setupDefaultOptions()
    {
        $defaults = [
            'retainable'     => false,
            'sortable'       => true,
            'download'       => true,
            'delete'         => true,
            'confirm_delete' => true,
        ];

        $this->options($defaults);
    }

    /**
     * Set preview options form image field.
     *
     * @return void
     */
    protected function setupPreviewOptions()
    {
        $initialPreviewConfig = $this->initialPreviewConfig();

        $this->options(compact('initialPreviewConfig'));
    }

    /**
     * @return array|bool
     */
    protected function guessPreviewType($file)
    {
        $filetype = 'other';
        $ext = strtok(strtolower(pathinfo($file, PATHINFO_EXTENSION)), '?');

        foreach ($this->fileTypes as $type => $pattern) {
            if (preg_match($pattern, $ext) === 1) {
                $filetype = $type;
                break;
            }
        }

        $extra = [
            'type' => $filetype,
            'icon' => $this->fileTypesIcons[$filetype],
        ];

        if ($filetype == 'video') {
            $extra['filetype'] = "video/{$ext}";
        }

        if ($filetype == 'audio') {
            $extra['filetype'] = "audio/{$ext}";
        }

        if ($this->downloadable) {
            $extra['downloadUrl'] = $this->objectUrl($file);
        }

        return $extra;
    }

    /**
     * Indicates if the underlying field is downloadable.
     *
     * @param bool $downloadable
     *
     * @return $this
     */
    public function downloadable($downloadable = true)
    {
        $this->options['download'] = $downloadable;

        return $this;
    }

    /**
     * Allow use to remove file.
     *
     * @return $this
     */
    public function removable($removable = true)
    {
        $this->options['delete'] = $removable;

        return $this;
    }

    /**
     * Disable upload.
     *
     * @return $this
     */
    public function disableUpload()
    {
        $this->attribute('disabled', true);

        return $this;
    }

    /**
     * Indicates if the underlying field is retainable.
     *
     * @return $this
     */
    public function retainable($retainable = true)
    {
        if (!empty($this->picker) && $retainable == false) {
            throw new \InvalidArgumentException(
                'retainable can not be set to false when using pick()'
            );
        }
        $this->options['retainable'] = $retainable; // for js
        $this->retainable = $retainable;            // for form save

        return $this;
    }

    /**
     * Set options for file-upload plugin.
     *
     * @param array $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        $this->options = array_merge($options, $this->options);

        return $this;
    }

    /**
     * Set disk for storage.
     *
     * @param string $disk Disks defined in `config/filesystems.php`.
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function disk($disk)
    {
        try {
            $this->storage = Storage::disk($disk);
        } catch (\Exception $exception) {
            if (!array_key_exists($disk, config('filesystems.disks'))) {
                admin_error(
                    'Config error.',
                    "Disk [$disk] not configured, please add a disk config in `config/filesystems.php`."
                );

                return $this;
            }

            throw $exception;
        }

        return $this;
    }

    /**
     * Specify the directory and name for upload file.
     *
     * @param string      $directory
     * @param null|string $name
     *
     * @return $this
     */
    public function move($directory, $name = null)
    {
        $this->dir($directory);

        $this->name($name);

        return $this;
    }

    /**
     * Specify the directory upload file.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function dir($dir)
    {
        if ($dir) {
            $this->directory = $dir;
        }

        return $this;
    }

    /**
     * Set name of store name.
     *
     * @param string|callable $name
     *
     * @return $this
     */
    public function name($name)
    {
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Use unique name for store upload file.
     *
     * @return $this
     */
    public function uniqueName()
    {
        $this->useUniqueName = true;

        return $this;
    }

    /**
     * Use sequence name for store upload file.
     *
     * @return $this
     */
    public function sequenceName()
    {
        $this->useSequenceName = true;

        return $this;
    }

    /**
     * Get store name of upload file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getStoreName(UploadedFile $file)
    {
        if ($this->useUniqueName) {
            return $this->generateUniqueName($file);
        }

        if ($this->useSequenceName) {
            return $this->generateSequenceName($file);
        }

        if ($this->name instanceof \Closure) {
            return $this->name->call($this, $file);
        }

        if (is_string($this->name)) {
            return $this->name;
        }

        return $file->getClientOriginalName();
    }

    /**
     * Get directory for store file.
     *
     * @return mixed|string
     */
    public function getDirectory()
    {
        if ($this->directory instanceof \Closure) {
            return call_user_func($this->directory, $this->form);
        }

        return $this->directory ?: $this->defaultDirectory();
    }

    /**
     * Set path column in has-many related model.
     *
     * @param string $column
     *
     * @return $this
     */
    public function pathColumn($column = 'path')
    {
        $this->pathColumn = $column;

        return $this;
    }

    /**
     * Set path column in has-many related model.
     *
     * @param string $column
     *
     * @return $this
     */
    public function sortColumn($column = 'order')
    {
        $this->sortColumn = $column;

        return $this;
    }

    /**
     * Upload file and delete original file.
     *
     * @param UploadedFile $file
     *
     * @return mixed
     */
    protected function upload(UploadedFile $file)
    {
        $this->renameIfExists($file);

        if (!is_null($this->storagePermission)) {
            return $this->storage->putFileAs($this->getDirectory(), $file, $this->name, $this->storagePermission);
        }

        return $this->storage->putFileAs($this->getDirectory(), $file, $this->name);
    }

    /**
     * If name already exists, rename it.
     *
     * @param $file
     *
     * @return void
     */
    public function renameIfExists(UploadedFile $file)
    {
        if ($this->storage->exists("{$this->getDirectory()}/$this->name")) {
            $this->name = $this->generateUniqueName($file);
        }
    }

    /**
     * Get file visit url.
     *
     * @param $path
     *
     * @return string
     */
    public function objectUrl($path)
    {
        if ($this->pathColumn && is_array($path)) {
            $path = Arr::get($path, $this->pathColumn);
        }

        if (URL::isValidUrl($path)) {
            return $path;
        }

        if ($this->storage) {
            return $this->storage->url($path);
        }

        return Storage::disk(config('admin.upload.disk'))->url($path);
    }

    /**
     * Get file path from url.
     *
     * @param $url
     *
     * @return string
     */
    public function objectPath($url)
    {
        if (URL::isValidUrl($url)) {
            $storage_url = $this->storage->url('');

            return str_replace($storage_url, '', $url);
        }

        return $url;
    }

    /**
     * Get the storage url.
     *
     * @return string
     */
    public function storageUrl()
    {
        return $this->storage->url('');
    }

    /**
     * Generate a unique name for uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function generateUniqueName(UploadedFile $file)
    {
        return md5(uniqid()).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Generate a sequence name for uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function generateSequenceName(UploadedFile $file)
    {
        $index = 1;
        $extension = $file->getClientOriginalExtension();
        $original = str_replace('.'.$extension, '', $file->getClientOriginalName());

        if (!$this->storage->exists($this->getDirectory().'/'.$file->getClientOriginalName())) {
            return $file->getClientOriginalName();
        }

        $new = sprintf('%s_%s.%s', $original, $index, $extension);

        while ($this->storage->exists("{$this->getDirectory()}/$new")) {
            $index++;
            $new = sprintf('%s_%s.%s', $original, $index, $extension);
        }

        return $new;
    }

    /**
     * Destroy original files.
     *
     * @return void.
     */
    public function destroy()
    {
        if ($this->retainable) {
            return;
        }

        if (method_exists($this, 'destroyThumbnail')) {
            $delete_all = true;
            $this->destroyThumbnail($delete_all);
        }

        if (!empty($this->original) && $this->storage->exists($this->original)) {
            $this->storage->delete($this->original);
        }
    }

    /**
     * Set file permission when stored into storage.
     *
     * @param string $permission
     *
     * @return $this
     */
    public function storagePermission($permission)
    {
        $this->storagePermission = $permission;

        return $this;
    }
}
