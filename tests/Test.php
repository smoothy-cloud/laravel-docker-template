<?php

namespace Tests;

use SmoothyCloud\ApplicationTemplateValidator\Testing\Browser\Browser;
use SmoothyCloud\ApplicationTemplateValidator\Testing\TemplateTest;

class Test extends TemplateTest
{
    /** @test */
    public function the_syntax_of_the_template_is_correct()
    {
        $this->validateTemplate();
    }

    /** @test */
    public function the_application_works_correctly_when_deployed()
    {
        $variables = [
            "laravel_application" => __DIR__."/concerns/application",
            "laravel_version" => '5.6',
            "php_version" => '7.4',
            "private_composer_registries" => [],
            "system_dependencies" => [],
            "timezone" => "Europe/Brussels",
            "environment" => [
                'APP_KEY' => 'base64:c3SzeMQZZHPT+eLQH6BnpDhw/uKH2N5zgM2x2a8qpcA=',
                'APP_ENV' => 'production',
                'APP_DEBUG' => false,
            ],
            "opcache_enabled" => false,
            "maximum_file_upload_size" => 100,
            "run_scheduler" => true,
            "daemons" => [],
            "build_assets" => true,
            "package_manager" => 'npm',
            "build_assets_script" => <<<EOF
npm run production
EOF,
            "deploy_script" => <<<EOF
php artisan route:cache
php artisan config:cache
EOF,
        ];

        $this->deployApplication($variables);
        $this->assertApplicationWorksCorrectly();
    }

    private function assertApplicationWorksCorrectly(): void
    {
        $browser = new Browser('http://localhost:50000');

        $browser->visit('/');
        $this->assertTrue($browser->pathIs("/"));
        $this->assertTrue($browser->see("Laravel"));

        $browser->visit('/phpinfo');
        $this->assertTrue($browser->pathIs("/phpinfo"));
        $this->assertTrue($browser->seeHtml(
            '<tr><td class="e">post_max_size</td><td class="v">100M</td><td class="v">100M</td></tr>'
        ));
        $this->assertTrue($browser->seeHtml(
            '<tr><td class="e">upload_max_filesize</td><td class="v">100M</td><td class="v">100M</td></tr>'
        ));
    }
}
