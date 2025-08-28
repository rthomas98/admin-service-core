<?php

namespace App\Filament\Widgets;

use App\Enums\NotificationCategory;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NotificationStats extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        
        $pending = Notification::pending()->count();
        $sentToday = Notification::sent()
            ->where('sent_at', '>=', $today)
            ->count();
        $failedToday = Notification::failed()
            ->where('updated_at', '>=', $today)
            ->count();
        $scheduled = Notification::scheduled()->count();
        
        return [
            Stat::make('Pending Notifications', $pending)
                ->description('Waiting to be sent')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($pending > 10 ? 'warning' : 'primary')
                ->chart($this->getHourlyChart('pending')),
                
            Stat::make('Sent Today', $sentToday)
                ->description('Successfully delivered')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->chart($this->getHourlyChart('sent')),
                
            Stat::make('Failed Today', $failedToday)
                ->description('Need attention')
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->color($failedToday > 0 ? 'danger' : 'gray')
                ->chart($this->getHourlyChart('failed')),
                
            Stat::make('Scheduled', $scheduled)
                ->description('Future notifications')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->color('info')
                ->chart($this->getDailyScheduled()),
        ];
    }

    protected function getHourlyChart(string $type): array
    {
        $data = [];
        $now = now();
        
        for ($i = 6; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i);
            
            $count = match($type) {
                'pending' => Notification::pending()
                    ->where('created_at', '>=', $hour->startOfHour())
                    ->where('created_at', '<', $hour->endOfHour())
                    ->count(),
                'sent' => Notification::sent()
                    ->where('sent_at', '>=', $hour->startOfHour())
                    ->where('sent_at', '<', $hour->endOfHour())
                    ->count(),
                'failed' => Notification::failed()
                    ->where('updated_at', '>=', $hour->startOfHour())
                    ->where('updated_at', '<', $hour->endOfHour())
                    ->count(),
                default => 0,
            };
            
            $data[] = $count;
        }
        
        return $data;
    }

    protected function getDailyScheduled(): array
    {
        $data = [];
        $today = now()->startOfDay();
        
        for ($i = 0; $i < 7; $i++) {
            $day = $today->copy()->addDays($i);
            
            $count = Notification::scheduled()
                ->whereDate('scheduled_at', $day)
                ->count();
                
            $data[] = $count;
        }
        
        return $data;
    }
}