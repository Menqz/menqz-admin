<?php

namespace MenqzAdmin\Admin\Form;

use Illuminate\Support\Collection;
use MenqzAdmin\Admin\Form;

class Part
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Collection
     */
    protected $parts;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * Tab constructor.
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

        $this->parts = new Collection();
    }

    /**
     * Append a part section.
     *
     * @param string   $title
     * @param \Closure $content
     * @param bool     $active
     *
     * @return $this
     */
    public function append($title, $class, $parentClass, $parentId, $active = false)
    {

        $id = 'form-'.($this->parts->count() + 1);

        $this->parts->push(compact('id', 'title', 'class', 'parentClass', 'parentId', 'active'));

        return $this;
    }

    /**
     * Collect fields under current part.
     *
     * @param \Closure $content
     *
     * @return Collection
     */
    protected function collectFields(\Closure $content)
    {
        call_user_func($content, $this->form);

        $fields = clone $this->form->fields();

        $all = $fields->toArray();

        foreach ($this->form->rows as $row) {
            $rowFields = array_map(function ($field) {
                return $field['element'];
            }, $row->getFields());

            $match = false;

            foreach ($rowFields as $field) {
                if (($index = array_search($field, $all)) !== false) {
                    if (!$match) {
                        $fields->put($index, $row);
                    } else {
                        $fields->pull($index);
                    }

                    $match = true;
                }
            }
        }

        $fields = $fields->slice($this->offset);

        $this->offset += $fields->count();

        return $fields;
    }

    /**
     * Get all tabs.
     *
     * @return Collection
     */
    public function getParts()
    {
        // If there is no active tab, then active the first.
        $activeParts = $this->parts->filter(function ($part) {
            return $part['active'];
        });

        // if empty only first
        if ($activeParts->isEmpty()) {
            $first           = $this->parts->first();
            $first['active'] = true;
            $this->parts->offsetSet(0, $first);
        }

        // if multiple only first
        if ($activeParts->count() > 1) {
            foreach ($this->parts as $i => $part) {
                if ($activeParts[0] != $part) {
                    $part['active'] = false;
                    $this->parts->offsetSet($i, $part);
                }
            }
        }

        return $this->parts;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->parts->isEmpty();
    }
}
