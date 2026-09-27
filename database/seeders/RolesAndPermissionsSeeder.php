<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and permissions for the admin panel. Idempotent — re-run it after
 * adding a permission: super_admin is always synced to the full list, so it
 * effectively has every permission while policies (and their safety rules)
 * still run for it.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'admin.access',
        'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',
        'articles.view', 'articles.create', 'articles.update', 'articles.delete', 'articles.publish',
        'categories.view', 'categories.manage',
        'media.view', 'media.manage',
        'team.view', 'team.manage',
        'businesses.view', 'businesses.manage', 'businesses.publish',
        'jobs.view', 'jobs.manage',
        'applications.view', 'applications.manage',
        'menus.view', 'menus.manage',
        'settings.view', 'settings.update',
        'redirects.view', 'redirects.manage',
        'users.view', 'users.manage',
        'roles.view', 'roles.manage',
        'seo.manage',
    ];

    public const ROLES = [
        // Everything except user administration.
        'admin' => [
            'admin.access',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',
            'articles.view', 'articles.create', 'articles.update', 'articles.delete', 'articles.publish',
            'categories.view', 'categories.manage',
            'media.view', 'media.manage',
            'team.view', 'team.manage',
            'businesses.view', 'businesses.manage', 'businesses.publish',
            'jobs.view', 'jobs.manage',
            'applications.view', 'applications.manage',
            'menus.view', 'menus.manage',
            'settings.view', 'settings.update',
            'redirects.view', 'redirects.manage',
            'users.view',
            'roles.view',
            'seo.manage',
        ],
        // Content work only: no publishing, deleting, configuration or users.
        'editor' => [
            'admin.access',
            'pages.view', 'pages.create', 'pages.update',
            'articles.view', 'articles.create', 'articles.update',
            'categories.view',
            'media.view', 'media.manage',
            'team.view',
            'businesses.view', 'businesses.manage',
            'jobs.view', 'jobs.manage',
            'applications.view',
            'menus.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (self::ROLES as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }

        Role::findOrCreate('super_admin', 'web')->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
