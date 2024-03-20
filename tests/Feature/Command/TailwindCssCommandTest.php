<?php

namespace Fuelviews\Tailwindcss\Tests\Feature\Command;

use Fuelviews\Tailwindcss\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TailwindCssCommandTest extends TestCase
{
    /** @test */
    public function it_installs_tailwindcss_command(): void
    {
        $files = ['tailwind.config.js', 'postcss.config.js', 'resources/css/app.css'];
        foreach ($files as $file) {
            if (File::exists(base_path($file))) {
                File::delete(base_path($file));
            }
        }

        $packageJsonPath = base_path('package.json');
        if (File::exists($packageJsonPath)) {
            File::put($packageJsonPath, '
            {
                "devDependencies": {}
            }');
        }

        $this->artisan('tailwindcss:install')
            ->expectsConfirmation('tailwind.config.js does not exist. Would you like to install it now?', 'yes')
            ->expectsConfirmation('postcss.config.js does not exist. Would you like to install it now?', 'yes')
            ->expectsConfirmation('css/app.css file does not exist. Would you like to install it now?', 'yes')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_asks_to_overwrite_tailwindcss_command(): void
    {
        $packageJsonPath = base_path('package.json');
        if (File::exists($packageJsonPath)) {
            File::put($packageJsonPath, '
            {
              "devDependencies": {
                "@tailwindcss/forms": "^0.5.7",
                "@tailwindcss/typography": "^0.5.10",
                "autoprefixer": "^10.4.18",
                "dotenv": "^16.4.5",
                "postcss": "^8.4.37",
                "tailwindcss": "^3.4.1"
              }
            }');
        }

        $this->artisan('tailwindcss:install')
            ->expectsConfirmation('tailwind.config.js already exists. Do you want to overwrite it?', 'no')
            ->expectsConfirmation('postcss.config.js already exists. Do you want to overwrite it?', 'no')
            ->expectsConfirmation('css/app.css file already exists. Do you want to overwrite it?', 'no')
            ->assertExitCode(0);
    }
}
