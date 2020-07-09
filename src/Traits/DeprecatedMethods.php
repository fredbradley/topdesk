<?php


namespace FredBradley\TOPDesk\Traits;


trait DeprecatedMethods
{
    /**
     * @return int
     * @deprecated
     */
    public function countWaitingForUserTickets(): int
    {
        return $this->countTicketsByStatus('Waiting for user');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countUpdatedByUserTickets(): int
    {
        return $this->countTicketsByStatus('Updated by user');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countWaitingForSupplier(): int
    {
        return $this->countTicketsByStatus('Waiting for supplier');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countScheduledTickets(): int
    {
        return $this->countTicketsByStatus('Scheduled');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countInProgressTickets(): int
    {
        return $this->countTicketsByStatus('In progress');
    }

    /**
     * @return int
     * @deprecated
     */
    public function countLoggedTickets(): int
    {
        return $this->countTicketsByStatus('Logged');
    }
}
