<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\ExpenseRequest;
use App\Http\Requests\ExpenseBuyRequest;
use App\Repositories\Contracts\ExpenseInterface;
use App\Repositories\Contracts\EventInterface;
use App\Repositories\Contracts\ActionInterface;
use App\Repositories\Contracts\GoalInterface;

class ExpenseController extends ApiController
{
    protected $expenseRepository;
    protected $eventRepository;
    protected $actionRepository;
    protected $goalRepository;

    public function __construct(
        ExpenseInterface $expenseRepository,
        EventInterface $eventRepository,
        ActionInterface $actionRepository,
        GoalInterface $goalRepository
    ) {
        parent::__construct();
        $this->expenseRepository = $expenseRepository;
        $this->eventRepository = $eventRepository;
        $this->actionRepository = $actionRepository;
        $this->goalRepository = $goalRepository;
    }

    public function index(Request $request)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($request->event_id);

        if ($this->user->cant('view', $event)) {
            throw new UnknowException('You can not see expenses in this event');
        }

        $isManager = $this->user->can('manage', $event);
        $expenses = $event
            ->expenses()
            ->withTrashed()
            ->with([
                'goal' => function ($query1) {
                    $query1->withTrashed()->with([
                        'donationType' => function ($query2) {
                            $query2->withTrashed()->with([
                                'quality' => function ($query3) {
                                    $query3->withTrashed();
                                },
                            ]);
                        },
                    ]);
                },
                'products' => function ($query) {
                    $query->withTrashed();
                },
                'qualitys' => function ($query) {
                    $query->withTrashed();
                },
            ])
            ->orderBy('time', 'desc')
            ->paginate(config('settings.expense.paginate_list'));

        return $this->getData(function () use ($expenses, $isManager) {
            $this->compacts['expenses'] = $expenses;
            $this->compacts['isManager'] = $isManager;
        });
    }

    public function store(ExpenseRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->user->id;
        $event = $this->eventRepository->findOrFail($data['event_id']);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Have error when create expense');
        }


        return $this->doAction(function () use ($data, $event) {
            $this->compacts['expense'] = $this->expenseRepository->create($data);
            $goal = $this->goalRepository->getInfoGoal($data['goal_id']);
            $data['expense_id'] = $this->compacts['expense']->id;
            $data['user_id'] = $this->user->id;
            $this->compacts['action'] = $this->actionRepository->createFromExpense($data, $goal);
        });
    }

    public function createBy(ExpenseBuyRequest $request)
    {
        $data = $request->all();
        $data['expense']['user_id'] = $this->user->id;
        $event = $this->eventRepository->findOrFail($data['expense']['event_id']);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Have error when create expense');
        }


        return $this->doAction(function () use ($data, $event) {
            $this->compacts['expense'] = $this->expenseRepository->createExpenseBuy($data);
            $goal = $this->goalRepository->getInfoGoal($data['expense']['goal_id']);
            $data['expense']['expense_id'] = $this->compacts['expense']->id;
            $data['expense']['user_id'] = $this->user->id;
            $this->compacts['action'] = $this->actionRepository->createFromExpense($data['expense'], $goal);
        });
    }

    public function update(ExpenseRequest $request, $id)
    {
        $data = $request->all();
        $event = $this->eventRepository->findOrFail($data['event_id']);
        $expense = $this->expenseRepository->findOrFail($id);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Have error when update expense');
        }

        $goal = $this->goalRepository->getInfoGoal($data['goal_id']);

        return $this->doAction(function () use ($id, $data, $goal) {
            $this->compacts['expense'] = $this->expenseRepository->update($id, $data);
            $data['expense_id'] = $id;
            $data['user_id'] = $this->user->id;
            $this->actionRepository->updateFromExpense($data, $goal);
        });
    }

    public function updateExpenseBuy(ExpenseBuyRequest $request, $id)
    {
        $data = $request->all();
        $event = $this->eventRepository->findOrFail($data['expense']['event_id']);
        $expense = $this->expenseRepository->findOrFail($id);

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Have error when update expense');
        }

        $goal = $this->goalRepository->getInfoGoal($data['expense']['goal_id']);

        return $this->doAction(function () use ($id, $data, $goal) {
            $this->compacts['expense'] = $this->expenseRepository->updateExpenseBuy($id, $data);
            $data['expense']['expense_id'] = $id;
            $data['expense']['user_id'] = $this->user->id;
            $this->actionRepository->updateFromExpense($data['expense'], $goal);
        });
    }

    public function destroy($id)
    {
        $expense = $this->expenseRepository->findOrFail($id);
        $event = $expense->event;

        if ($this->user->cant('manage', $event)) {
            throw new UnknowException('Have error when delete expense');
        }

        return $this->doAction(function () use ($id, $expense) {
            $this->compacts['expense'] = $this->expenseRepository->forceDelete($expense);
            $this->actionRepository->deleteFromExpense($id);
        });
    }

    public function statistic($eventId)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($eventId);

        return $this->getData(function () use ($event) {
            $this->compacts['goal'] = $event->goals()->withTrashed()->get()->each(function($goal) {
                $goal->makeVisible(['calculate'])->load([
                    'donationType' => function ($query1) {
                        $query1->withTrashed()->with([
                            'quality' => function ($query2) {
                                $query2->withTrashed();
                            },
                        ]);
                    },
                ]);
            });
        });
    }

    public function getList(Request $request)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($request->get('event_id'));

        return $this->getData(function () use ($request, $event) {
            $expenses = $event
                ->expenses()
                ->withTrashed()
                ->with([
                    'goal' => function($query1) {
                        $query1->withTrashed()->with([
                            'donationType' => function ($query2) {
                                $query2->withTrashed();
                            },
                        ]);
                    },
                ])
                ->orderBy($request->get('order_by'), 'desc');

            if ($request->has('goal_id')) {
                $expenses->withTrashed()->where('goal_id', $request->goal_id);
            }

            $this->compacts['expenses'] = $expenses->take(config('settings.pagination.expense_statistic'))->get();
        });
    }

    public function getStatisticData(Request $request)
    {
        $event = $this->eventRepository->withTrashed()->findOrFail($request->get('event_id'));
        $query = $event
            ->expenses()
            ->withTrashed()
            ->select(\DB::raw('count(*) as count, time, sum(cost) as cost'))
            ->groupBy('time');

        if ($request->has('goal_id')) {
            $query->withTrashed()->where('goal_id', $request->goal_id);
        }

        $statistic = new \stdClass();
        $locale = \App::getLocale();
        \Carbon\Carbon::setLocale($locale);

        $statistic->time = $query->pluck('time')->map(function($time) {
            return \Carbon\Carbon::parse($time)->toDateString();
        });

        $statistic->cost = $query->pluck('cost');
        $statistic->count = $query->pluck('count');

        return $this->getData(function () use ($event, $request, $statistic) {
            $this->compacts['statistic'] = $statistic;
        });
    }
}
