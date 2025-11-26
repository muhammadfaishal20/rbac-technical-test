<?php

namespace App\Http\Controllers\RBAC;

use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\RoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::where('guard_name', 'web')->with('permissions');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $roles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web', // Explicitly set guard to web
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::where('guard_name', 'web')
                ->whereIn('id', $request->permissions)
                ->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role,
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $role->update([
            'name' => $request->name,
            'guard_name' => 'web', // Explicitly set guard to web
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::where('guard_name', 'web')
                ->whereIn('id', $request->permissions)
                ->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role,
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deletion of default roles
        if (in_array($role->name, ['admin', 'management-user', 'management-file'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default roles.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }

    /**
     * Get all permissions for role assignment.
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'web')->get();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}

