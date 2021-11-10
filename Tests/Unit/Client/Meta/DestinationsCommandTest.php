<?php

namespace Oro\Component\MessageQueue\Tests\Unit\Client\Meta;

use Oro\Component\MessageQueue\Client\Meta\DestinationMeta;
use Oro\Component\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Oro\Component\MessageQueue\Client\Meta\DestinationsCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DestinationsCommandTest extends \PHPUnit\Framework\TestCase
{
    private DestinationsCommand $command;

    private DestinationMetaRegistry|\PHPUnit\Framework\MockObject\MockObject $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(DestinationMetaRegistry::class);

        $this->command = new DestinationsCommand($this->registry);
    }

    public function testShouldHaveCommandName(): void
    {
        self::assertEquals('oro:message-queue:destinations', $this->command->getName());
    }

    public function testShouldShowMessageFoundZeroDestinationsIfAnythingInRegistry(): void
    {
        $this->registry->expects(self::once())
            ->method('getDestinationsMeta')
            ->willReturn([]);

        $output = $this->executeCommand();

        static::assertStringContainsString('Found 0 destinations', $output);
    }

    public function testShouldShowMessageFoundTwoDestinations(): void
    {
        $this->registry->expects(self::once())
            ->method('getDestinationsMeta')
            ->willReturn([
                             new DestinationMeta('aClientName', 'aDestinationName'),
                             new DestinationMeta('anotherClientName', 'anotherDestinationName'),
                         ]);

        $output = $this->executeCommand();

        static::assertStringContainsString('Found 2 destinations', $output);
    }

    public function testShouldShowInfoAboutDestinations(): void
    {
        $this->registry->expects(self::once())
            ->method('getDestinationsMeta')
            ->willReturn(
                [
                    new DestinationMeta('aFooClientName', 'aFooDestinationName', ['fooSubscriber']),
                    new DestinationMeta('aBarClientName', 'aBarDestinationName', ['barSubscriber']),
                ]
            );

        $output = $this->executeCommand();

        static::assertStringContainsString('aFooClientName', $output);
        static::assertStringContainsString('aFooDestinationName', $output);
        static::assertStringContainsString('fooSubscriber', $output);
        static::assertStringContainsString('aBarClientName', $output);
        static::assertStringContainsString('aBarDestinationName', $output);
        static::assertStringContainsString('barSubscriber', $output);
    }

    /**
     * @param string[] $arguments
     *
     * @return string
     */
    private function executeCommand(array $arguments = []): string
    {
        $tester = new CommandTester($this->command);
        $tester->execute($arguments);

        return $tester->getDisplay();
    }
}
