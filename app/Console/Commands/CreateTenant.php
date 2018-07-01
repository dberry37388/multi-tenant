<?php

namespace App\Console\Commands;

use App\Notifications\TenantCreated;
use App\Tenant;
use App\User;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {tenant} {name} {email}';

    /**
     * The console command description.
     *
     * @var void
     */
    protected $description = 'Creates a tenant with the provided tenant e.g. php artisan tenant:create boise johndoe@example.org';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $tenant = $this->argument('tenant');
        $email = $this->argument('email');
        $name = $this->argument('name');
    
        if ($this->tenantExists($tenant)) {
            $this->error("The tenant '{$tenant}' already exists.");
            return;
        }
        
        $tenant = Tenant::createFrom($tenant, $name, $email);
    
        $this->info("Tenant was created and is now accessible at {$tenant->getHostname()->fqdn}");
    
        // invite admin
        $tenant->getAdmin()->notify(new TenantCreated($tenant->getHostname()));
        $this->info("Admin {$email} has been invited!");
        
//        $website = new Website;
//        //$website->managed_by_database_connection = 'tenant';
//
//        app(WebsiteRepository::class)->create($website);
//
//        // associate the website with a hostname
//        $hostname = new Hostname;
//        $hostname->fqdn = $fqdn;
//
//        app(HostnameRepository::class)->attach($hostname, $website);
//
//        app(Environment::class)->tenant($website);
//
//        $user = User::create([
//            'name' => 'John Doe',
//            'email' => 'jdoe@example.org',
//            'password' => bcrypt('secret')
//        ]);
//
//        $this->info("The user {$user->name} was added.");
    }
    
    /**
     * Checks to see if the fqdn already exists.
     *
     * @param $fqdn
     * @return bool
     */
    protected function tenantExists($tenant)
    {
        $fqdn = $tenant . '.' . config('app.url_base');
        
        return app(HostnameRepository::class)->query()
            ->where('fqdn', $fqdn)
            ->exists();
    }
}
