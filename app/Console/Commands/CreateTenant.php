<?php

namespace App\Console\Commands;

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
    protected $signature = 'tenant:create {tenant}';

    /**
     * The console command description.
     *
     * @var void
     */
    protected $description = 'Creates a tenant with the provided tenant e.g. php artisan tenant:create boise';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $fqdn = $this->argument('tenant') .'.'. config('app.url_base');
    
        if ($this->tenantExists($fqdn)) {
            $this->error("The tenant '{$fqdn}' already exists.");
            return;
        }
        
        $website = new Website;
        //$website->managed_by_database_connection = 'tenant';
        
        app(WebsiteRepository::class)->create($website);
    
        // associate the website with a hostname
        $hostname = new Hostname;
        $hostname->fqdn = $fqdn;
    
        app(HostnameRepository::class)->attach($hostname, $website);
        
        app(Environment::class)->tenant($website);
        
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'jdoe@example.org',
            'password' => bcrypt('secret')
        ]);
        
        $this->info("The user {$user->name} was added.");
    }
    
    /**
     * Checks to see if the fqdn already exists.
     *
     * @param $fqdn
     * @return bool
     */
    protected function tenantExists($fqdn)
    {
        return app(HostnameRepository::class)->query()
            ->where('fqdn', $fqdn)
            ->exists();
    }
}
