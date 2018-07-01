<?php

namespace App\Console\Commands;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Illuminate\Console\Command;

class DeleteTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fqdn = $this->argument('tenant') .'.'. config('app.url_base');
        
        if ($tenant = $this->getTenantByFqdn($fqdn)) {
            $tenant->website()->delete();
            $tenant->delete();
        }
    }
    
    /**
     * Checks to see if the fqdn already exists.
     *
     * @param $fqdn
     * @return bool
     */
    protected function getTenantByFqdn($fqdn)
    {
        return app(HostnameRepository::class)->query()
            ->where('fqdn', $fqdn)
            ->first();
    }
}
