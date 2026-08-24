<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Client;
use Clockster\Exception\InvalidBodyException;
use Clockster\Generated\Enum\UsersRole;
use Clockster\Validator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Reading a body against the document, which happens only where a caller asked for it.
 *
 * Most of these hand a body straight to the validator rather than to a client, and for the reason
 * the exercise exists: a static analyser refuses the bad ones outright, so a test writing one
 * through a generated method has to tell it that the body is wrong on purpose. Two do exactly that,
 * because what is worth proving end to end is that nothing was sent.
 */
#[CoversNothing]
final class ValidatorTest extends TestCase
{
    private const UPSERT = '/company/v3/users/upsert';

    /**
     * @param array<string, mixed> $changed
     *
     * @return array<string, mixed>
     */
    private function person(array $changed = []): array
    {
        return array_merge([
            'external_id' => 'HR-1',
            'first_name' => 'Aisulu',
            'role' => UsersRole::EMPLOYEE,
            'location_id' => 3,
        ], $changed);
    }

    /** @param array<string, mixed> $body */
    private function refusal(string $method, string $path, array $body): InvalidBodyException
    {
        try {
            Validator::check($method, $path, $body);
        } catch (InvalidBodyException $refused) {
            return $refused;
        }

        self::fail('The document does not describe this body, and nothing said so.');
    }

    public function testAGoodBodyGoesOutUntouched(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');

        (new Client('token', transport: $transport, validate: true))->users->upsert(['users' => [[
            'external_id' => 'HR-1',
            'first_name' => 'Aisulu',
            'role' => UsersRole::EMPLOYEE,
            'location_id' => 3,
        ]]]);

        $sent = $transport->body();

        self::assertCount(1, $transport->seen);
        self::assertIsArray($sent['users']);
        self::assertSame($this->person(), $sent['users'][0]);
    }

    public function testAMisspelledFieldIsCaughtBeforeTheCallIsMade(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');
        $client = new Client('token', transport: $transport, validate: true);

        // `first_nane` is the mistake under test. An analyser refuses it right here, which is the
        // better place to hear it; this asserts what reaches somebody who is not running one.
        try {
            // @phpstan-ignore argument.type
            $client->users->upsert(['users' => [$this->person(['first_nane' => 'Aisulu'])]]);

            self::fail('A body the document does not describe was sent.');
        } catch (InvalidBodyException $refused) {
            self::assertArrayHasKey('body.users.0.first_nane', $refused->errors());
            self::assertSame('POST /company/v3/users/upsert', $refused->route());
            // The whole of it: the call never left, so there is nothing to undo.
            self::assertSame([], $transport->seen);
        }
    }

    public function testNothingIsReadUnlessTheClientWasBuiltToReadIt(): void
    {
        $transport = RecordingTransport::answering('{"data":[]}');
        $client = new Client('token', transport: $transport);

        // The same body the test above refuses.
        // @phpstan-ignore argument.type
        $client->users->upsert(['users' => [$this->person(['first_nane' => 'Aisulu'])]]);

        $sent = $transport->body();

        // Off is the default, and off means the API is the one that decides.
        self::assertCount(1, $transport->seen);
        self::assertIsArray($sent['users']);
        self::assertIsArray($person = $sent['users'][0]);
        self::assertArrayHasKey('first_nane', $person);
    }

    public function testAMisspelledFieldIsNamedWithWhatTheDocumentDoesName(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, [
            'users' => [$this->person(['first_nane' => 'Aisulu'])],
        ]);

        self::assertStringContainsString('first_name', $refused->errors()['body.users.0.first_nane'][0]);
    }

    public function testAMissingRequiredFieldIsNamed(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => [['first_name' => 'Aisulu']]]);

        self::assertArrayHasKey('body.users.0.role', $refused->errors());
        self::assertArrayHasKey('body.users.0.location_id', $refused->errors());
    }

    public function testAValueOutsideTheSetIsNamedWithTheSet(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => [$this->person(['role' => 'manager'])]]);

        self::assertStringContainsString("'admin', 'employee'", $refused->errors()['body.users.0.role'][0]);
    }

    public function testTheWrongTypeIsSaidOnceAndNothingElseIsSaid(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => [$this->person(['location_id' => '3'])]]);
        $said = $refused->errors()['body.users.0.location_id'];

        self::assertCount(1, $said);
        self::assertStringContainsString('wants a whole number, and this is a string', $said[0]);
    }

    public function testNullIsRefusedWhereTheDocumentDoesNotAllowIt(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => [$this->person(['first_name' => null])]]);

        self::assertStringContainsString('cannot be null', $refused->errors()['body.users.0.first_name'][0]);
    }

    public function testNullIsFineWhereItClears(): void
    {
        // Clearing a stored value is a thing you may well mean, so nothing is said about it.
        self::expectNotToPerformAssertions();

        Validator::check('POST', self::UPSERT, ['users' => [$this->person(['position_id' => null])]]);
    }

    public function testALengthTheApiWouldRefuseIsRefusedHere(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, [
            'users' => [$this->person(['first_name' => str_repeat('a', 21)])],
        ]);

        self::assertStringContainsString('20 characters at most', $refused->errors()['body.users.0.first_name'][0]);
    }

    public function testABatchOverTheLimitSaysToSplitIt(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => array_fill(0, 101, $this->person())]);

        self::assertStringContainsString('takes 100 at a time, and holds 101', $refused->errors()['body.users'][0]);
    }

    public function testADateThatIsNotADayIsRefused(): void
    {
        $refused = $this->refusal('POST', '/company/v3/schedules', ['schedules' => [[
            'type' => 'free',
            'dates' => ['2026-02-31'],
            'users' => [1],
            'timezone' => 'Asia/Almaty',
        ]]]);

        self::assertStringContainsString('YYYY-MM-DD', $refused->errors()['body.schedules.0.dates.0'][0]);
    }

    public function testTheShapeIsChosenByTheFieldThatSaysWhichItIs(): void
    {
        $refused = $this->refusal('POST', '/company/v3/schedules', ['schedules' => [[
            'type' => 'holiday',
            'dates' => ['2026-08-01'],
            'users' => [1],
            'timezone' => 'Asia/Almaty',
        ]]]);

        $said = $refused->errors()['body.schedules.0.type'][0];

        self::assertStringContainsString('says which shape this is', $said);
        self::assertStringContainsString("'work', 'free', 'leave'", $said);
    }

    public function testTheShapeChosenIsTheOneChecked(): void
    {
        // `leave_type` belongs to one of the three shapes and not to the others, so naming the
        // shape is what makes the field checkable at all.
        $refused = $this->refusal('POST', '/company/v3/schedules', ['schedules' => [[
            'type' => 'leave',
            'dates' => ['2026-08-01'],
            'users' => [1],
            'timezone' => 'Asia/Almaty',
            'leave_type' => 'sabbatical',
        ]]]);

        self::assertArrayHasKey('body.schedules.0.leave_type', $refused->errors());
    }

    public function testARouteHoldingAnIdIsMatchedRatherThanLookedUp(): void
    {
        $refused = $this->refusal('PUT', '/company/v3/webhooks/5', [
            'url' => 'https://example.test/hook',
            'events' => ['user.exploded'],
            'active' => true,
        ]);

        self::assertSame('PUT /company/v3/webhooks/{id}', $refused->route());
        self::assertArrayHasKey('body.events.0', $refused->errors());
    }

    public function testAnOperationWithoutABodyIsNotDescribedAndNotRead(): void
    {
        // A route the document gives no body for is left alone rather than guessed at.
        self::expectNotToPerformAssertions();

        Validator::check('GET', '/company/v3/users', ['whatever' => true]);
    }

    public function testTheMessageNamesTheFirstFewAndCountsTheRest(): void
    {
        $refused = $this->refusal('POST', self::UPSERT, ['users' => [
            $this->person(['role' => 'manager']),
            $this->person(['role' => 'manager']),
            $this->person(['role' => 'manager']),
            $this->person(['role' => 'manager']),
        ]]);

        self::assertStringContainsString('Nothing was sent', $refused->getMessage());
        self::assertStringContainsString('And 1 more.', $refused->getMessage());
    }
}
