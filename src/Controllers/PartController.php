<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
        $usePersistent = config('admin.database.use_persistent', false);
        if ($parentId && $parentClass && class_exists($parentClass)) {
            if ($usePersistent) {
                $parentModel = $parentClass::withoutGlobalScope('persistent')->find($parentId);
            } else {
                $parentModel = $parentClass::find($parentId);
            }
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
             return response()->json(['success'=> false, 'message'=>$e->getMessage()], 500);
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
            return response()->json(['success'=> false, 'message'=>$e->getMessage()], 500);
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
            $result = $part->update($id);
            return $result;
        } catch (\Exception $e) {
            return response()->json(['success'=> false, 'message'=>$e->getMessage()], 500);
        }
    }

    public function handleDestroy(Request $request, $id)
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
            return $part->destroy($id);
        } catch (\Exception $e) {
            return response()->json(['success'=> false, 'message'=>$e->getMessage()], 500);
        }
    }
}
