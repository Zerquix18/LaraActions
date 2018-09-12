<?php
/**
 * Extends the functionality of each user, so it can be blocked at any time.
 *
 */

namespace Zerquix18\LaraActions;

use Zerquix18\LaraActions\Action;
use Zerquix18\LaraActions\Ban;
use Carbon\Carbon;

trait PerformsActions
{
    /******************** actions *******************/

    public function registerAction(string $action_name)
    {
        $action = new Action;

        $action->user_id     = $this->id;
        $action->action_name = $action_name;

        $action->save();
    }

    public function actions($action = null)
    {
        $relationship = $this->hasMany('Zerquix18\LaraActions\Action');
        if ($action) {
            $relationship->where('action_name', $action);
        }
        return $relationship;
    }

    /*************************** bans *************************/

    public function ban($actions, $until)
    {
        if (is_string($actions)) {
            $actions = [$actions];
        }

        if (is_int($until)) {
            $until = Carbon::createFromTimestamp($until);
        } else {
            $until = Carbon::parse($until);
        }

        foreach ($actions as $action) {
            $ban = new Ban;

            $ban->user_id      = $this->id;
            $ban->action_name  = $action;
            $ban->until        = $until;

            $ban->save();
        }
    }

    public function banIf($action, $max_times, $time_period, $penalty_time)
    {
        if (is_int($time_period)) {
            $time_period = Carbon::createFromTimestamp($time_period);
        } else {
            $time_period = Carbon::parse($time_period);
        }

        $count = Action::where('action_name', $action)
                         ->where('created_at', '>', $time_period)
                         ->count();

        $should_ban = $count >= $max_times;

        if ($should_ban) {
            $this->ban($action, $penalty_time);
        }

        return $should_ban;
    }

    public function isBannedFrom($actions)
    {
        return $this->bans($actions, false)->exists();
    }

    public function bans($actions = null, $include_expired = true)
    {
        $relationship = $this->hasMany('Zerquix18\LaraActions\Ban');

        if ($actions) {
            if (is_string($actions)) {
                $actions = [$actions];
            }
            $relationship->whereIn('action_name', $actions);
        }

        if (! $include_expired) {
            $relationship->where('until', '>', Carbon::now());
        }

        return $relationship;
    }
}
