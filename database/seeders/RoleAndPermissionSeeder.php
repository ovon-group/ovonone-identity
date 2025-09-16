<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->setupPermissionsAndRoles();
    }

    private function setupPermissionsAndRoles()
    {
        $roles = [
            'Admin' => [
                'is_internal' => true,
                'permissions' => [
                    'accounts.manage',
                    'dealerships.view',
                    'dealerships.create',
                    'dealerships.update',
                    'dealerships.delete',
                    'dealerships.restore',
                    'sales-people.manage',
                    'repairers.view',
                    'repairers.manage',
                    'products.manage',
                    'customers.create',
                    'customers.view',
                    'customers.update',
                    'pay-later-agreements.view',
                    'pay-later-agreements.create',
                    'pay-later-agreements.update',
                    'sales.view',
                    'sales.create',
                    'sales.update',
                    'sales.view-dashboard',
                    'claims.view',
                    'claims.update',
                    'claims.create',
                    'claims.delete',
                    'claims.uses-browser-plugin',
                    'claim-estimates.view',
                    'claim-estimates.create',
                    'claim-estimates.update',
                    'claim-estimates.delete',
                    'claim-authorisations.view',
                    'claim-authorisations.create',
                    'claim-authorisations.update',
                    'claim-rejections.create',
                    'breakdown-claims.view',
                    'breakdown-claims.create',
                    'breakdown-claims.update',
                    'authorised-services.view',
                    'authorised-services.create',
                    'authorised-services.update',
                    'invoices.view',
                    'invoices.create',
                    'payments.view',
                    'users.manage',
                    'warranties.view',
                    'warranties.update',
                    'warranties.cancel',
                    'warranties.export',
                    'breakdown-plans.view',
                    'breakdown-plans.update',
                    'breakdown-plans.cancel',
                    'breakdown-plans.export',
                    'service-plans.view',
                    'service-plans.update',
                    'service-plans.cancel',
                    'service-plans.export',
                    'claims.export',
                    'component-list.view',
                    'workflows.view',
                    'offers.view',
                    'vehicle-components.list',
                    'audits.view',
                    'support-tickets.view',
                    'support-tickets.update',
                    'support-tickets.create',
                    'support-tickets.delete',
                ],
            ],
            'Claims Handler' => [
                'is_internal' => true,
                'permissions' => [
                    'customers.view',
                    'sales.view',
                    'warranties.view',
                    'claims.view',
                    'claims.update',
                    'claims.create',
                    'claims.delete',
                    'claims.uses-browser-plugin',
                    'claim-estimates.view',
                    'claim-estimates.create',
                    'claim-estimates.update',
                    'claim-estimates.delete',
                    'claim-authorisations.view',
                    'claim-authorisations.create',
                    'claim-authorisations.update',
                    'claim-rejections.create',
                ],
            ],
            'Service Authorisation' => [
                'is_internal' => false,
                'permissions' => [
                    'authorised-services.view',
                    'authorised-services.create',
                    'authorised-services.update',
                ],
            ],
            'Owner' => [
                'is_internal' => true,
                'permissions' => [
                    'admin-users.manage',
                    'users.impersonate',
                    'accounts.update-funding-types',
                    'sales-leads.call',
                    'sales-leads.view',
                    'payments.update',
                    'pay-later-agreements.view-margins',
                    'workflows.view',
                    'workflows.create',
                    'workflows.update',
                    'workflows.delete',
                    'offers.view',
                    'offers.create',
                    'offers.update',
                    'offers.delete',
                    'customers.impersonate',
                    'feature-videos.manage',
                    'audits.view',
                    'audits.restore',
                ],
            ],
            'Developer' => [
                'is_internal' => true,
                'permissions' => [
                    'admin-users.manage',
                    'users.impersonate',
                    'accounts.update-funding-types',
                    'sales-leads.call',
                    'sales-leads.view',
                    'pay-later-agreements.view-margins',
                    'internal-emails.view',
                    'sales.delete',
                    'breakdown-claims.delete',
                    'customers.impersonate',
                    'audits.view',
                    'audits.restore',
                    'feature-videos.manage',
                    'workflows.view',
                    'workflows.create',
                    'workflows.update',
                    'workflows.delete',
                    'workflows.view',
                    'workflows.create',
                    'workflows.update',
                    'workflows.delete',
                ],
            ],
            'CRM Sales' => [
                'is_internal' => true,
                'permissions' => [
                    'sales-leads.call',
                    'sales-leads.view',
                    'workflows.view',
                ],
            ],
            'Dealership Manager' => [
                'is_internal' => false,
                'permissions' => [
                    'sales-people.manage',
                    'products.manage',
                    'pay-later-agreements.view',
                    'pay-later-agreements.create',
                    'pay-later-agreements.update',
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'sales.view',
                    'sales.view-dashboard',
                    'sales.create',
                    'claims.view',
                    'claims.create',
                    'claim-estimates.view',
                    'claim-estimates.create',
                    'claim-authorisations.view',
                    'claim-authorisations.create',
                    'claim-rejections.create',
                    'breakdown-claims.view',
                    'authorised-services.view',
                    'invoices.view',
                    'warranties.view',
                    'warranties.export',
                    'breakdown-plans.view',
                    'breakdown-plans.export',
                    'service-plans.view',
                ],
            ],
            'Dealership Sales' => [
                'is_internal' => false,
                'permissions' => [
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'pay-later-agreements.view',
                    'pay-later-agreements.create',
                    'pay-later-agreements.update',
                ],
            ],
            'Dealership Admin' => [
                'is_internal' => false,
                'permissions' => [
                    'customers.view',
                    'customers.create',
                    'customers.update',
                    'sales.view',
                    'sales.create',
                    'pay-later-agreements.view',
                    'pay-later-agreements.create',
                    'pay-later-agreements.update',
                ],
            ],
        ];

        $app = 'Protego';

        $allPermissions = collect($roles)->pluck('permissions')->flatten()->unique()->values();
        // Create permissions that don't exist
        Permission::upsert(
            $allPermissions->map(fn ($permissionName) => [
                'app' => $app,
                'name' => $permissionName,
                'guard_name' => 'web',
            ])->toArray(),
            ['name', 'guard_name']
        );
        // Delete removed permissions
        Permission::whereNotIn('name', $allPermissions)->delete();

        // Seed roles and permissions
        foreach ($roles as $roleName => $config) {
            Role::updateOrCreate([
                'app' => $app,
                'name' => $roleName,
            ], [
                'is_internal' => $config['is_internal'],
            ])->syncPermissions($config['permissions']);
        }

        if (! app()->isProduction()) {
            $this->syncUserRoles();
        }
    }

    private function syncUserRoles(): void
    {
        User::query()
            ->whereIn('email', [
                'leon@minstercarco.co.uk',
                'chris@cloudest.co.uk',
                'chris@ovon.co.uk',
                'chris.loftus@ovon.co.uk',
            ])->each(function (User $user) {
                $user->syncRoles('Admin', 'Owner', 'Developer');
//                $user->update(['is_internal' => true]);
            });
    }
}
