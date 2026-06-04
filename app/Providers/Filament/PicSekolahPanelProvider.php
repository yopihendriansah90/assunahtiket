<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Widgets\PicSekolahClassesTableWidget;
use App\Filament\Widgets\PicSekolahEventsTableWidget;
use App\Filament\Widgets\PicSekolahOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PicSekolahPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('picsekolah')
            ->path('picsekolah')
            ->login()
            ->brandName('PIC Sekolah')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->resources([
                EventResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                PicSekolahOverviewWidget::class,
                PicSekolahEventsTableWidget::class,
                PicSekolahClassesTableWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Sekolah'),
            ])
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
