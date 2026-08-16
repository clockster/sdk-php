<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;

/**
 * The operations of the Company API, as they are called.
 *
 * Generated from openapi/company-v3.json — see scripts/generate.php. Clockster\Client extends this,
 * so everything here hangs off the client a caller builds.
 *
 * @phpstan-import-type MeResponse from Shapes
 */
abstract class Api
{
    /** The operations of `$clockster->attendance`. */
    public readonly Attendance $attendance;

    /** The operations of `$clockster->departments`. */
    public readonly Departments $departments;

    /** The operations of `$clockster->documents`. */
    public readonly Documents $documents;

    /** The operations of `$clockster->files`. */
    public readonly Files $files;

    /** The operations of `$clockster->locations`. */
    public readonly Locations $locations;

    /** The operations of `$clockster->payroll`. */
    public readonly Payroll $payroll;

    /** The operations of `$clockster->positions`. */
    public readonly Positions $positions;

    /** The operations of `$clockster->schedules`. */
    public readonly Schedules $schedules;

    /** The operations of `$clockster->tasks`. */
    public readonly Tasks $tasks;

    /** The operations of `$clockster->timesheets`. */
    public readonly Timesheets $timesheets;

    /** The operations of `$clockster->userFilters`. */
    public readonly UserFilters $userFilters;

    /** The operations of `$clockster->userRequests`. */
    public readonly UserRequests $userRequests;

    /** The operations of `$clockster->users`. */
    public readonly Users $users;

    /** The operations of `$clockster->webhooks`. */
    public readonly Webhooks $webhooks;

    public function __construct(protected readonly Caller $caller)
    {
        $this->attendance = new Attendance($caller);
        $this->departments = new Departments($caller);
        $this->documents = new Documents($caller);
        $this->files = new Files($caller);
        $this->locations = new Locations($caller);
        $this->payroll = new Payroll($caller);
        $this->positions = new Positions($caller);
        $this->schedules = new Schedules($caller);
        $this->tasks = new Tasks($caller);
        $this->timesheets = new Timesheets($caller);
        $this->userFilters = new UserFilters($caller);
        $this->userRequests = new UserRequests($caller);
        $this->users = new Users($caller);
        $this->webhooks = new Webhooks($caller);
    }

    /**
     * Whose token this is.
     *
     * Confirms which company a key belongs to.
     *
     * Answers the id and the name, and nothing else: everything a key opens is reachable from
     * the endpoints themselves, and a company attribute this surface does not act on would
     * only read as one it does.
     *
     * @return MeResponse
     *
     * @throws ApiException|TransportException
     */
    public function me(): array
    {
        /** @var MeResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/me',
        );

        return $answer;
    }
}
