<?php

use MenqzAdmin\Admin\Auth\Database\Administrator;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testIndex()
    {
        $this->visit('admin/')
            ->see('Dashboard')
            ->see('Description...')

            ->see('Environment')
            ->see('PHP version')
            ->see('Laravel version')

            ->see('Available extensions')
            ->seeLink('menqz-admin-ext/helpers', 'https://github.com/menqz-admin-extensions/helpers')
            ->seeLink('menqz-admin-ext/backup', 'https://github.com/menqz-admin-extensions/backup')
            ->seeLink('menqz-admin-ext/media-manager', 'https://github.com/menqz-admin-extensions/media-manager')

            ->see('Dependencies')
            ->see('php')
//            ->see('>=7.0.0')
            ->see('laravel/framework');
    }

    public function testClickMenu()
    {
        $this->visit('admin/')
            ->click('Users')
            ->seePageis('admin/auth/users')
            ->click('Roles')
            ->seePageis('admin/auth/roles')
            ->click('Permission')
            ->seePageis('admin/auth/permissions')
            ->click('Menu')
            ->seePageis('admin/auth/menu')
            ->click('Operation log')
            ->seePageis('admin/auth/logs');
    }
}
