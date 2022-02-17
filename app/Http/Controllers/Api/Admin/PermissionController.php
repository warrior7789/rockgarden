<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Api\BaseController;


class PermissionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if (! Gate::allows('permission_manage')) {
            return abort(401);
        }

        $permissions = Permission::all();
        return $this->sendResponse($permissions,'Success.',1);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('permission_manage')) {
            return abort(401);
        }
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePermissionsRequest $request)
    {
        if (! Gate::allows('permission_manage')) {
           return abort(401);
        }
        Permission::create($request->all());

        return redirect()->route('admin.permissions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        if (! Gate::allows('permission_manage')) {
            return abort(401);
        }

        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        if (! Gate::allows('permission_manage')) {
            return abort(401);
        }

        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePermissionsRequest $request, Permission $permission)
    {
        if (! Gate::allows('permission_manage')) {
           return abort(401);
        }

        $permission->update($request->all());

        return redirect()->route('admin.permissions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        if (! Gate::allows('permission_manage')) {
           return abort(401);
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index');
    }
}
