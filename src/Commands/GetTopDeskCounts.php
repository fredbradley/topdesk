<?php

namespace FredBradley\TOPDesk\Commands;

use FredBradley\Cacher\EasySeconds;
use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetTopDeskCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topdesk:get-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JSON of API Counts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cache = Cache::remember('topdeskApiCounts',
            EasySeconds::minutes(5), function () {
                $userClosedCounts = collect(TOPDesk::resolveCountsForOperatorGroup(
                    'I.T. Services',
                    ['HELPDESK']
                ))->sortByDesc('closed_week');

                return response()->json([
                    'unresolvedIncidents' => TOPDesk::countOpenTickets(),
                    'openChangeActivities' => count(TOPDesk::allOpenChangeActivities()),
                    'loggedToday' => TOPDesk::countTicketsLoggedToday(), // + TOPDesk::countChangesLoggedToday(),
                    'unassigned' => TOPDesk::countUnassignedTickets() + count(TOPDesk::unassignedWaitingChangeActivities()),
                    'inProgress' => TOPDesk::countTicketsByStatus('In progress'),
                    'waitingForUser' => TOPDesk::countTicketsByStatus('Waiting for user'),
                    'updatedByUser' => TOPDesk::countTicketsByStatus('Updated by user'),
                    'waitingForSupplier' => TOPDesk::countTicketsByStatus('Waiting for supplier'),
                    'scheduled' => TOPDesk::countTicketsByStatus('Scheduled'),
                    'dueThisWeek' => TOPDesk::countTicketsDueThisWeek(),
                    'breachedTickets' => TOPDesk::countBreachedTickets(),
                    'usersClosedCounts' => $userClosedCounts,
                    'collectiveClosesByDay' => $userClosedCounts->map(function ($item) {
                        return $item['closed_day'];
                    })->sum(),
                    'collectiveClosesByWeek' => $userClosedCounts->map(function ($item) {
                        return $item['closed_week'];
                    })->sum(),
                    'collectiveClosesByTotal' => $userClosedCounts->map(function ($item) {
                        return $item['closed_total'];
                    })->sum(),
                ]);
            });

        return 0;
    }
}
