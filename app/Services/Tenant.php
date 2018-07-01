<?php

namespace App;


use Hyn\Tenancy\Contracts\Repositories\CustomerRepository;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Customer;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Hash;

class Tenant
{
    /**
     * @var \Hyn\Tenancy\Models\Website|null
     */
    protected $website;
    
    /**
     * @var \Hyn\Tenancy\Models\Hostname|null
     */
    protected $hostname;
    
    /**
     * @var \App\User|null
     */
    protected $admin;
    
    /**
     * Tenant constructor.
     *
     * @param \Hyn\Tenancy\Models\Website|null $website
     * @param \Hyn\Tenancy\Models\Hostname|null $hostname
     * @param \App\User|null $admin
     */
    public function __construct(Website $website = null, Hostname $hostname = null, User $admin = null)
    {
        $this->website = $website;
        $this->hostname = $hostname;
        $this->admin = $admin;
    }
    
    /**
     * Deletes a Tenant.
     *
     * @return void
     */
    public function delete()
    {
        app(HostnameRepository::class)->delete($this->hostname, true);
        app(WebsiteRepository::class)->delete($this->website, true);
    }
    
    /**
     * Creates a new Tenant from the given information.
     *
     * @param $tenant string
     * @param $name string
     * @param $email string
     * @return \App\Tenant
     */
    public static function createFrom($tenant, $name, $email): Tenant
    {
        $website = new Website;
        
        app(WebsiteRepository::class)->create($website);
        
        // associate the website with a hostname
        $hostname = new Hostname;
        $baseUrl = config('app.url_base');
        $hostname->fqdn = "{$tenant}.{$baseUrl}";
        
        app(HostnameRepository::class)->attach($hostname, $website);
        
        // make hostname current
        app(Environment::class)->tenant($website);
        $admin = static::makeAdmin($name, $email, str_random());
        
        return new Tenant($website, $hostname, $admin);
    }
    
    /**
     * Creates an Admin user.
     *
     * @param $name
     * @param $email
     * @param $password
     * @return \App\User
     */
    private static function makeAdmin($name, $email, $password): User
    {
        $admin = User::create(['name' => $name, 'email' => $email, 'password' => bcrypt($password)]);
        $admin->guard_name = 'web';
        $admin->assignRole('admin');
        
        return $admin;
    }
    
    /**
     * @return \Hyn\Tenancy\Models\Website|null
     */
    public function getWebsite()
    {
        return $this->website;
    }
    
    /**
     * @return \Hyn\Tenancy\Models\Hostname|null
     */
    public function getHostname()
    {
        return $this->hostname;
    }
    
    /**
     * @return \App\User|null
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
