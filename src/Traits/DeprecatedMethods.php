<?php

namespace FredBradley\TOPDesk\Traits;

trait DeprecatedMethods
{
    /**
     * @deprecated
     */
    public function countWaitingForUserTickets(): int
    {
        return $this->countTicketsByStatus('Waiting for user');
    }

    /**
     * @deprecated
     */
    public function countUpdatedByUserTickets(): int
    {
        return $this->countTicketsByStatus('Updated by user');
    }

    /**
     * @deprecated
     */
    public function countWaitingForSupplier(): int
    {
        return $this->countTicketsByStatus('Waiting for supplier');
    }

    /**
     * @deprecated
     */
    public function countScheduledTickets(): int
    {
        return $this->countTicketsByStatus('Scheduled');
    }

    /**
     * @deprecated
     */
    public function countInProgressTickets(): int
    {
        return $this->countTicketsByStatus('In progress');
    }

    /**
     * @deprecated
     */
    public function countLoggedTickets(): int
    {
        return $this->countTicketsByStatus('Logged');
    }
}
