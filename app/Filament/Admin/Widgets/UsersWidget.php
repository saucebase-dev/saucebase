<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Saucebase\Core\Filament\Concerns\ReadsPeriod;
use Saucebase\Core\Filament\TrendStat;

/** Opens the dashboard's stats band (10). */
class UsersWidget extends StatsOverviewWidget
{
    use ReadsPeriod;

    protected static ?int $sort = 10;

    protected ?string $pollingInterval = null;

    /** Half the dashboard: pairs with its neighbour in the band. */
    protected int|string|array $columnSpan = 1;

    protected int|array|null $columns = 2;

    /** The permission behind Auth's Users page. */
    public static function canView(): bool
    {
        return auth()->user()?->can('manage users') ?? false;
    }

    protected function getStats(): array
    {
        $period = $this->period();
        $previous = $period->previous();

        return [
            TrendStat::make(
                __('New users'),
                User::whereBetween('created_at', [$period->start, $period->end])->count(),
                User::whereBetween('created_at', [$previous->start, $previous->end])->count(),
                $period->series(User::query()),
            ),
            Stat::make(__('Total users'), number_format(User::count()))
                ->descriptionIcon('heroicon-m-users'),
        ];
    }
}
