<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Generator;

/**
 * The operations of `$clockster->documents`.
 *
 * @phpstan-import-type DocumentsDeleteResponse from Shapes
 * @phpstan-import-type DocumentsEmploymentType from Shapes
 * @phpstan-import-type DocumentsGetResponse from Shapes
 * @phpstan-import-type DocumentsInclude from Shapes
 * @phpstan-import-type DocumentsListResponse from Shapes
 * @phpstan-import-type DocumentsListRow from Shapes
 * @phpstan-import-type DocumentsParty from Shapes
 * @phpstan-import-type DocumentsType from Shapes
 * @phpstan-import-type DocumentsUpsertBody from Shapes
 * @phpstan-import-type DocumentsUpsertResponse from Shapes
 */
final class Documents
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Delete a document.
     *
     * Deletes the document, its file records and the stored objects behind them.
     *
     * **A document everyone has signed cannot be deleted** — `409`, code `document_signed`.
     *
     * Another company's id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     *
     * @return DocumentsDeleteResponse
     *
     * @throws ApiException|TransportException
     */
    public function delete(int $id): array
    {
        /** @var DocumentsDeleteResponse $answer */
        $answer = $this->caller->call(
            'DELETE',
            sprintf('/company/v3/documents/%d', $id),
        );

        return $answer;
    }

    /**
     * Read one document.
     *
     * The same keys the listing answers with, and the same `include` vocabulary. Another company's
     * id is a `404`.
     *
     * @param int $id The id of the row, as this API issued it.
     * @param list<DocumentsInclude> $include Relations to load, comma-separated. Anything not named
     * is absent from the answer rather than null.
     *
     * @return DocumentsGetResponse
     *
     * @throws ApiException|TransportException
     */
    public function get(int $id, array $include = []): array
    {
        /** @var DocumentsGetResponse $answer */
        $answer = $this->caller->call(
            'GET',
            sprintf('/company/v3/documents/%d', $id),
            [
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * List documents.
     *
     * The company's paperwork, oldest first, paged on a cursor.
     *
     * **Both kinds of document.** `party` is `employee` for a document about one of your
     * people and `counterparty` for one a counterparty signs. Filter with `party=employee` or
     * `party=counterparty`; leave it out for both.
     *
     * **`signature.state` is derived, because there is no column for it.** `none` when nobody
     * has to sign, then `rejected`, `revoked`, `pending` in that order of precedence, and
     * `signed` only when every signer has. Refusal outranks an outstanding signature, so a
     * document one person refused reads `rejected` even while others are still pending.
     * `completed_at` is set only for `signed`, and is when the last signer signed.
     *
     * **Dates are three shapes in one payload.** `start_date`, `end_date` and
     * `expiration_date` are plain `YYYY-MM-DD` — they are date columns and carry no time or
     * zone. `created_at` is an instant with an offset.
     *
     * `expires_after` and `expires_before` are a plain range of dates.
     *
     * `effective_from` and `effective_to` select documents valid during a window: a document
     * starts on or before your `effective_to` and either has no end date or ends on or after
     * your `effective_from`. Both are required together. **A document with no `start_date`
     * never matches** — a row with no interval cannot overlap one.
     *
     * `locations`, `departments`, `positions` and `user_filters` all reach the document
     * through the person it is about, so **a document with no `user_id` matches none of
     * them**. The web app files company-level documents that way.
     *
     * **`updated_since` reads only what changed**, as an instant rather than a date so a
     * caller polling every few minutes can say which minute. Two things it will not show you,
     * and both are ours rather than yours: a document changed by a path inside the product
     * that writes the row directly keeps its old timestamp, and so does one the back office
     * modifies. Signature progress is the common case of the second — a signature completed
     * through the web app does not move `updated_at`. Re-read with overlap if you depend on
     * catching those.
     *
     * Documents the product files for its own machinery are never listed.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param string|null $cursor The `meta.next_cursor` of the previous page. Omit it for the first. A cursor is bound to the filters it was issued under — change them and start again.
     * @param DocumentsParty|null $party Whose side of the document to read.
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param list<int> $userFilters Only these user filters, by id.
     * @param list<DocumentsType> $types Only rows of these types.
     * @param list<DocumentsEmploymentType> $employmentTypes Only documents covering these
     * employment terms.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param string|null $expiresAfter Expiring after this date (YYYY-MM-DD) — how you find what is about to lapse.
     * @param string|null $expiresBefore Expiring before this date (YYYY-MM-DD).
     * @param string|null $effectiveFrom In force at or after this date (YYYY-MM-DD).
     * @param string|null $effectiveTo In force at or before this date (YYYY-MM-DD).
     * @param list<DocumentsInclude> $include Relations to load, comma-separated. Anything not named
     * is absent from the answer rather than null.
     *
     * @return DocumentsListResponse
     *
     * @throws ApiException|TransportException
     */
    public function list(
        ?int $perPage = null,
        ?string $cursor = null,
        ?string $party = null,
        array $externalIds = [],
        array $users = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        array $userFilters = [],
        array $types = [],
        array $employmentTypes = [],
        ?string $search = null,
        ?string $updatedSince = null,
        ?string $expiresAfter = null,
        ?string $expiresBefore = null,
        ?string $effectiveFrom = null,
        ?string $effectiveTo = null,
        array $include = [],
    ): array {
        /** @var DocumentsListResponse $answer */
        $answer = $this->caller->call(
            'GET',
            '/company/v3/documents',
            [
                'per_page' => $perPage,
                'cursor' => $cursor,
                'party' => $party,
                'external_ids' => $externalIds,
                'users' => $users,
                'locations' => $locations,
                'departments' => $departments,
                'positions' => $positions,
                'user_filters' => $userFilters,
                'types' => $types,
                'employment_types' => $employmentTypes,
                'search' => $search,
                'updated_since' => $updatedSince,
                'expires_after' => $expiresAfter,
                'expires_before' => $expiresBefore,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'include' => $include,
            ],
        );

        return $answer;
    }

    /**
     * Every row of list(), a page at a time.
     *
     * A refused page is thrown where it was refused, so half a listing is never mistaken for the
     * whole of one. A cursor belongs to the filters it was issued under: change them and walk
     * again.
     *
     * @param int|null $perPage How many rows one page holds. Defaults to 50.
     * @param DocumentsParty|null $party Whose side of the document to read.
     * @param list<string> $externalIds Only rows carrying these keys of yours. The other half of an
     * upsert: write with your key, read back with it.
     * @param list<int> $users Only rows belonging to these people, by id.
     * @param list<int> $locations Only these locations, by id.
     * @param list<int> $departments Only these departments, by id.
     * @param list<int> $positions Only these positions, by id.
     * @param list<int> $userFilters Only these user filters, by id.
     * @param list<DocumentsType> $types Only rows of these types.
     * @param list<DocumentsEmploymentType> $employmentTypes Only documents covering these
     * employment terms.
     * @param string|null $search Free text over the names the section lists.
     * @param string|null $updatedSince Only rows changed at or after this instant (ISO 8601). The cheap way to sync: ask for what moved, not for everything.
     * @param string|null $expiresAfter Expiring after this date (YYYY-MM-DD) — how you find what is about to lapse.
     * @param string|null $expiresBefore Expiring before this date (YYYY-MM-DD).
     * @param string|null $effectiveFrom In force at or after this date (YYYY-MM-DD).
     * @param string|null $effectiveTo In force at or before this date (YYYY-MM-DD).
     * @param list<DocumentsInclude> $include Relations to load, comma-separated. Anything not named
     * is absent from the answer rather than null.
     *
     * @return \Generator<int, DocumentsListRow>
     *
     * @throws ApiException|TransportException
     */
    public function listAll(
        ?int $perPage = null,
        ?string $party = null,
        array $externalIds = [],
        array $users = [],
        array $locations = [],
        array $departments = [],
        array $positions = [],
        array $userFilters = [],
        array $types = [],
        array $employmentTypes = [],
        ?string $search = null,
        ?string $updatedSince = null,
        ?string $expiresAfter = null,
        ?string $expiresBefore = null,
        ?string $effectiveFrom = null,
        ?string $effectiveTo = null,
        array $include = [],
    ): Generator {
        $cursor = null;
        $seen = [];

        while (true) {
            $page = $this->list(
                perPage: $perPage,
                party: $party,
                externalIds: $externalIds,
                users: $users,
                locations: $locations,
                departments: $departments,
                positions: $positions,
                userFilters: $userFilters,
                types: $types,
                employmentTypes: $employmentTypes,
                search: $search,
                updatedSince: $updatedSince,
                expiresAfter: $expiresAfter,
                expiresBefore: $expiresBefore,
                effectiveFrom: $effectiveFrom,
                effectiveTo: $effectiveTo,
                include: $include,
                cursor: $cursor,
            );

            foreach ($page['data'] as $row) {
                yield $row;
            }

            $cursor = $page['meta']['next_cursor'] ?? null;

            // A cursor that repeats would page until the process is killed, which is worse
            // than stopping.
            if ($cursor === null || isset($seen[$cursor])) {
                return;
            }

            $seen[$cursor] = true;
        }
    }

    /**
     * File documents.
     *
     * Up to 100 documents in one call, matched on `external_id`, which is required here.
     *
     * **The bytes go up separately.** `POST /company/v3/files` takes one file and answers an
     * id; send that as `file_id`. A file may be claimed once: one already hanging off a
     * document is refused rather than moved.
     *
     * **An uploaded file waits to be claimed and is not cleaned up.** A batch refused at the
     * hundredth document leaves all hundred files staged and still valid, so a retry should
     * send the same `file_id`s rather than uploading them again.
     *
     * `parent_external_id` links a supplementary agreement to what it amends, by your key
     * rather than ours. It resolves against documents already filed — not against another item
     * of the same batch — and a key matching nothing clears the link rather than failing.
     *
     * `user_id` is required and may name somebody who has left: termination paperwork is filed
     * after a dismissal, which is exactly when it is needed.
     *
     * **No signers.** Signing runs conversions and outbound calls that do not belong under a
     * versioned contract, so documents filed here are always `party: employee`. Signature
     * state is readable; creating a signing request is not offered yet.
     *
     * `author_id` is set to the subject: this token authenticates a company, not a person.
     *
     * @param DocumentsUpsertBody $body
     *
     * @return DocumentsUpsertResponse
     *
     * @throws ApiException|InvalidBodyException|TransportException
     */
    public function upsert(array $body): array
    {
        /** @var DocumentsUpsertResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/documents/upsert',
            body: $body,
        );

        return $answer;
    }
}
