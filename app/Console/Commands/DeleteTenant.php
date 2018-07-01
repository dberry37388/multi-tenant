<?php

namespace App\Console\Commands;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Illuminate\Console\Command;

class DeleteTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {tenant} {--force}';

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
        $force = $this->option('force');
        
        if ($tenant = $this->getTenantByFqdn($fqdn)) {
            app(WebsiteRepository::class)->delete($tenant->website, $force);
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
            ->withTrashed()
            ->where('fqdn', $fqdn)
            ->first();
    }
}
