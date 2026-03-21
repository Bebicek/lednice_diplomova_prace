<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems()
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/dashboard',
            ],
            [
                'icon' => 'ecommerce',
                'name' => 'Katalog',
                'path' => '/catalog',
            ],
            [
                'icon' => 'ecommerce',
                'name' => 'Košík',
                'path' => '/cart',
            ],
            [
                'icon' => 'tables',
                'name' => 'Moje dluhy',
                'path' => '/my-debts',
            ],
            [
                'icon' => 'calendar',
                'name' => 'Obědy',
                'path' => '/lunches',
            ],
        ];
    }

    public static function getAdminNavItems()
    {
        return [
            [
                'icon' => 'dashboard',
                'name' => 'Přehled',
                'path' => '/admin',
            ],
            [
                'icon' => 'forms',
                'name' => 'Produkty',
                'path' => '/admin/commodities',
            ],
            [
                'icon' => 'tables',
                'name' => 'Kategorie',
                'path' => '/admin/categories',
            ],
            [
                'icon' => 'pages',
                'name' => 'Sklad',
                'path' => '/admin/stock',
            ],
            [
                'icon' => 'user-profile',
                'name' => 'Uživatelé',
                'path' => '/admin/users',
            ],
            [
                'icon' => 'charts',
                'name' => 'Účty a dluhy',
                'path' => '/admin/accounts',
            ],
            [
                'icon' => 'expiry',
                'name' => 'Datum spotřeby',
                'path' => '/admin/expiry',
            ],
            [
                'icon' => 'lunches',
                'name' => 'Obědy',
                'path' => '/admin/lunches',
            ],
            [
                // TODO: Maybe delete it later in project and let it be only in a profile icon to click on settings idk
                'icon' => 'settings',
                'name' => 'Nastavení',
                'path' => '/admin/settings',
            ],
        ];
    }

    public static function getMenuGroups()
    {
        // Admin sidebar only shows admin navigation
        return [
            [
                'title' => 'Administrace',
                'items' => self::getAdminNavItems()
            ],
        ];
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconComponent($iconName)
    {
        $map = [
            'dashboard'    => 'heroicon-o-squares-2x2',
            'ecommerce'    => 'heroicon-o-shopping-cart',
            'calendar'     => 'heroicon-o-calendar-days',
            'user-profile' => 'heroicon-o-user-circle',
            'forms'        => 'heroicon-o-cube',
            'tables'       => 'heroicon-o-tag',
            'pages'        => 'heroicon-o-archive-box',
            'charts'       => 'heroicon-o-banknotes',
            'expiry'       => 'heroicon-o-clock',
            'lunches'      => 'heroicon-o-cake',
            'settings'     => 'heroicon-o-cog-6-tooth',
        ];

        return $map[$iconName] ?? 'heroicon-o-squares-2x2';
    }
}
