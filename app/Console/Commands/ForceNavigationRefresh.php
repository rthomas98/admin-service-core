<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ForceNavigationRefresh extends Command
{
    protected $signature = 'filament:refresh-navigation';
    protected $description = 'Force complete Filament navigation refresh';

    public function handle()
    {
        $this->info('🔄 Starting complete Filament navigation refresh...');
        
        // Step 1: Clear all Laravel caches
        $this->info('1. Clearing Laravel caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        // Step 2: Clear Filament specific caches
        $this->info('2. Clearing Filament caches...');
        try {
            Artisan::call('filament:clear-cached-components');
            $this->line('   ✅ Filament components cleared');
        } catch (\Exception $e) {
            $this->line('   ⚠️  Filament component cache not available');
        }
        
        // Step 3: Clear any custom cache keys that might be storing navigation
        $this->info('3. Clearing navigation-related cache keys...');
        $cacheKeys = [
            'filament.navigation',
            'filament.resources',
            'filament.panels.admin.navigation',
            'filament.panels.admin.resources',
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
            $this->line("   ✅ Cleared cache key: $key");
        }
        
        // Step 4: Force asset rebuild
        $this->info('4. Rebuilding assets...');
        $result = shell_exec('cd ' . base_path() . ' && npm run build 2>&1');
        if ($result) {
            $this->line('   ✅ Assets rebuilt successfully');
        } else {
            $this->error('   ❌ Asset rebuild failed');
        }
        
        // Step 5: Test navigation registration
        $this->info('5. Testing navigation registration...');
        try {
            $resources = \Filament\Facades\Filament::getPanel('admin')->getResources();
            $this->info('   ✅ Found ' . count($resources) . ' resources');
            
            $financialCount = 0;
            $operationsCount = 0;
            
            foreach ($resources as $resource) {
                $group = $resource::getNavigationGroup();
                if ($group === 'Financial') $financialCount++;
                if ($group === 'Operations') $operationsCount++;
            }
            
            $this->line("   📊 Financial resources: $financialCount");
            $this->line("   📊 Operations resources: $operationsCount");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error testing navigation: ' . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('🎉 Navigation refresh complete!');
        $this->info('💡 Please hard refresh your browser (Ctrl+Shift+R / Cmd+Shift+R) to see changes.');
        
        return 0;
    }
}