<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PartController extends Controller
{
    public function handle(Request $request, ?string $id = null, ?string $modo = null)
    {
        $class = $request->get('class');
        $parentId = $request->get('parent_id');
        $parentClass = $request->get('parent_class');

        if (!$class || !class_exists($class)) {
            return "<div class='alert alert-danger'>Part class not found: {$class}</div>";
        }

        $parentModel = null;
        if ($parentId && $parentClass && class_exists($parentClass)) {
            $parentModel = $parentClass::find($parentId);
        }

        if (!$modo && !is_numeric($id)) {
            $modo = $id;
        } else if (!$modo && is_numeric($id)) {
            $modo = 'show';
        }

        try {
            $part = new $class($parentModel);
            return $part->handle($request, $id, $modo);
        } catch (\Exception $e) {
             return "<div class='alert alert-danger'>Error loading part: " . $e->getMessage() . "</div>";
        }
    }

    public function handleStore(Request $request)
    {
        $class = $request->get('class');
        $parentId = $request->get('parent_id');
        $parentClass = $request->get('parent_class');

        if (!$class || !class_exists($class)) {
            return "<div class='alert alert-danger'>Part class not found: {$class}</div>";
        }

        $parentModel = null;
        if ($parentId && $parentClass && class_exists($parentClass)) {
            $parentModel = $parentClass::find($parentId);
        }

        try {
            $part = new $class($parentModel);
            return $part->store();
        } catch (\Exception $e) {
            return "<div class='alert alert-danger'>Error loading part: " . $e->getMessage() . "</div>";
        }
    }

    public function handleUpdate(Request $request, $id)
    {
        $class = $request->get('class');
        $parentId = $request->get('parent_id');
        $parentClass = $request->get('parent_class');

        if (!$class || !class_exists($class)) {
            return "<div class='alert alert-danger'>Part class not found: {$class}</div>";
        }

        $parentModel = null;
        if ($parentId && $parentClass && class_exists($parentClass)) {
            $parentModel = $parentClass::find($parentId);
        }

        try {
            $part = new $class($parentModel);
            return $part->update($id);
        } catch (\Exception $e) {
            return "<div class='alert alert-danger'>Error loading part: " . $e->getMessage() . "</div>";
        }
    }
}
