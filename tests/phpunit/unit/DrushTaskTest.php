<?php

namespace DigitalPolygon\PolymerDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_drupal\Plugin\Tasks\DrushTask;
use PHPUnit\Framework\TestCase;
use Robo\Config\Config;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exposes the protected assembly step so command lines can be asserted
 * without executing anything.
 */
class InspectableDrushTask extends DrushTask
{
    public function assembledCommand(): string
    {
        $this->setupExecution();
        return $this->getCommand();
    }
}

class DrushTaskTest extends TestCase
{
    /**
     * @param array<string, mixed> $drushConfig
     */
    private function task(array $drushConfig = []): InspectableDrushTask
    {
        $task = new InspectableDrushTask();
        $task->setConfig(new Config(['drupal' => ['drush' => $drushConfig]]));
        $task->setVerbosityThreshold(OutputInterface::VERBOSITY_NORMAL);
        return $task;
    }

    public function testMinimalCommandFallsBackToBareDrush(): void
    {
        $task = $this->task(['alias' => '', 'dir' => '/tmp', 'uri' => '']);
        $task->interactive(true);
        $task->drush('status');

        $this->assertSame('drush status', $task->assembledCommand());
    }

    public function testConfigDrivesExecutableAliasAndUri(): void
    {
        $task = $this->task([
            'bin' => '/project/vendor/bin/drush',
            'alias' => 'self',
            'dir' => '/project/web',
            'uri' => 'local.test',
        ]);
        $task->drush('status');

        $command = $task->assembledCommand();
        $this->assertStringStartsWith('/project/vendor/bin/drush @self status', $command);
        $this->assertStringContainsString('--uri=local.test', $command);
        $this->assertStringContainsString('--no-interaction', $command);
    }

    public function testOptionsBindToThePrecedingCommandOnly(): void
    {
        $task = $this->task(['alias' => '', 'dir' => '/tmp', 'uri' => '']);
        $task->interactive(true);
        $task->drush('config:import')->option('preview');
        $task->drush('cache:rebuild');

        [$first, $second] = explode(' && ', $task->assembledCommand());
        $this->assertStringContainsString('config:import --preview', $first);
        $this->assertStringNotContainsString('--preview', $second);
        $this->assertStringContainsString('cache:rebuild', $second);
    }

    public function testGlobalOptionsApplyToEveryCommandInTheStack(): void
    {
        $task = $this->task(['alias' => 'self', 'dir' => '/tmp', 'uri' => 'local.test']);
        $task->drush('updb');
        $task->drush('cr');

        foreach (explode(' && ', $task->assembledCommand()) as $command) {
            $this->assertStringContainsString('@self', $command);
            $this->assertStringContainsString('--uri=local.test', $command);
            $this->assertStringContainsString('--no-interaction', $command);
        }
    }

    public function testInteractiveModeOmitsNoInteraction(): void
    {
        $task = $this->task(['alias' => '', 'dir' => '/tmp', 'uri' => '']);
        $task->interactive(true);
        $task->drush('status');

        $this->assertStringNotContainsString('--no-interaction', $task->assembledCommand());
    }

    public function testAnsiAndIncludeOptions(): void
    {
        $task = $this->task(['alias' => '', 'dir' => '/tmp', 'uri' => '']);
        $task->interactive(true);
        $task->ansi(true);
        $task->includePath('/extra/commands');
        $task->drush('status');

        $command = $task->assembledCommand();
        $this->assertStringContainsString('--ansi', $command);
        $this->assertStringContainsString('--include=/extra/commands', $command);
    }

    public function testConfiguredVerbosityAppendsVerboseFlag(): void
    {
        $task = $this->task(['alias' => '', 'dir' => '/tmp', 'uri' => '', 'verbose' => true]);
        $task->interactive(true);
        $task->drush('status');

        $this->assertStringContainsString(' -v', $task->assembledCommand());
    }

    public function testExecutablePathWithSpacesIsEscaped(): void
    {
        $task = $this->task(['bin' => '/pro ject/drush', 'alias' => '', 'dir' => '/tmp', 'uri' => '']);
        $task->interactive(true);
        $task->drush('status');

        $this->assertStringStartsWith('/pro\\ ject/drush', $task->assembledCommand());
    }
}
