<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRolesAndPermissions();
    }
    
    /**
     * Seeds the Tenants default roles and permissions.
     *
     * @return void
     */
    private function addRolesAndPermissions()
    {
        // create permissions for an admin
        $adminPermissions = collect(['create user', 'edit user', 'delete user'])->map(function ($name) {
            return Permission::create(['name' => $name]);
        });
        
        // add admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($adminPermissions);
        
        // add a default user role
        Role::create(['name' => 'user']);
    }
}
