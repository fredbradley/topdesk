<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Exceptions\ConfigNotFound;
use FredBradley\TOPDesk\Exceptions\OperatorGroupNotFound;
use FredBradley\TOPDesk\Exceptions\OperatorNotFound;
use FredBradley\TOPDesk\Exceptions\PersonNotFound;

// --- PersonNotFound ---

it('PersonNotFound extends RuntimeException', function () {
    expect(new PersonNotFound('jsmith'))->toBeInstanceOf(RuntimeException::class);
});

it('PersonNotFound carries HTTP 404 as its code', function () {
    $e = new PersonNotFound('jsmith');

    expect($e->getCode())->toBe(404);
});

it('PersonNotFound message includes the identifier', function () {
    $e = new PersonNotFound('jsmith');

    expect($e->getMessage())->toContain('jsmith');
});

it('PersonNotFound can be caught as RuntimeException', function () {
    $caught = false;

    try {
        throw new PersonNotFound('testuser');
    } catch (RuntimeException) {
        $caught = true;
    }

    expect($caught)->toBeTrue();
});

// --- OperatorNotFound ---

it('OperatorNotFound extends Exception', function () {
    expect(new OperatorNotFound)->toBeInstanceOf(Exception::class);
});

it('OperatorNotFound accepts a custom message', function () {
    $e = new OperatorNotFound('Operator jsmith not found');

    expect($e->getMessage())->toBe('Operator jsmith not found');
});

// --- OperatorGroupNotFound ---

it('OperatorGroupNotFound extends Exception', function () {
    expect(new OperatorGroupNotFound)->toBeInstanceOf(Exception::class);
});

it('OperatorGroupNotFound accepts a custom message', function () {
    $e = new OperatorGroupNotFound('Group I.T. Services not found');

    expect($e->getMessage())->toBe('Group I.T. Services not found');
});

// --- ConfigNotFound ---

it('ConfigNotFound extends RuntimeException', function () {
    expect(new ConfigNotFound)->toBeInstanceOf(RuntimeException::class);
});

it('ConfigNotFound can be thrown and caught', function () {
    $caught = false;

    try {
        throw new ConfigNotFound("Config value 'topdesk.endpoint' is not set.");
    } catch (ConfigNotFound $e) {
        $caught = true;
        expect($e->getMessage())->toContain('topdesk.endpoint');
    }

    expect($caught)->toBeTrue();
});
