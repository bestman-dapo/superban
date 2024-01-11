<?php

namespace Superban\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SuperbanMiddleware
{
    protected $maxReq;
    protected $rateMins;
    protected $banMins;
    protected $reqTime;
    protected $route;
    protected $routeName;
    protected $sdsc;
    

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $maxReq, $rateMins, $banMins)
    {
        $this->maxReq = $maxReq;
        $this->rateMins = $rateMins;
        $this->banMins = $banMins;
        $this->reqTime = date("Y-m-d H:i:s");
        $this->routeName = $request->url();

        //check for defined user identifier method in project env
        $identifier = env('RATE_LIMIT_ID', 'ipaddress');

        if ($identifier == 'userid') {
            if (!Auth::check()) {
                abort(403);
            } else {
                $userid = Auth::user()->id;
                $userRateResult = DB::select("SELECT * FROM superbancontroltable WHERE user_identifier = '$userid' AND `type` = '$identifier' AND `reqUrl` = '$this->routeName' LIMIT 1");

                $this->processRate($userRateResult, $userid, "userid");
            }
        } elseif ($identifier == 'ipaddress') {
            //use ip address
            $userIp = $request->ip();
            $userRateResult = DB::select("SELECT * FROM superbancontroltable WHERE user_identifier = ? AND `type` = ? AND `reqUrl` = ? LIMIT 1", [$userIp, $identifier, $this->routeName]);

            $this->processRate($userRateResult, $userIp, "ipaddress");
        } elseif ($identifier == 'useremail') {
            //use email address
            if (!Auth::check()) {
                abort(403);
            } else {
                $useremail = Auth::user()->email;
                $userRateResult = DB::select("SELECT * FROM superbancontroltable WHERE user_identifier = '$useremail' AND `type` = '$identifier' AND `reqUrl` = '$this->routeName' LIMIT 1");

                $this->processRate($userRateResult, $useremail, "useremail");
            }
        }

        return $next($request);
    }

    private function processRate($userRateResult, $user_identifier, $identifier)
    {
        if (!empty($userRateResult)) {
            $currentRate = $userRateResult[0]->requestCount;

            //get difference in last successful request and new request
            $initialReqTime = $userRateResult[0]->initialRequestTime;
            $lastSuccessTime = $userRateResult[0]->lastSuccessTime;
            $newReqTime = $this->reqTime;

            $from_time = strtotime($initialReqTime);
            $to_time = strtotime($newReqTime);
            $diff_minutes = round(abs($from_time - $to_time) / 60, 2);

            $lastSuccess_time = strtotime($lastSuccessTime);
            $banTimeDiff = round(abs($lastSuccess_time - $to_time) / 60, 2);

            if ($currentRate >= $this->maxReq && $diff_minutes <= $this->rateMins) {
                //throw exception
                abort(403, "Rate limit exceeded");
            } elseif ($currentRate >= $this->maxReq && $diff_minutes > $this->rateMins) {

                if ($banTimeDiff >= $this->banMins) {
                    //reset rate limit
                    DB::update("UPDATE superbancontroltable SET requestCount = 1, initialRequestTime = ?, lastSuccessTime = ? WHERE user_identifier = ? AND `type` = ? AND `reqUrl` = ?", [$this->reqTime, $this->reqTime, $user_identifier, $identifier, $this->routeName]);
                } else {
                    abort(403, "Rate limit exceeded. User under ban.");
                }
            } elseif ($currentRate <= $this->maxReq && $diff_minutes > $this->rateMins) {
                //reset rate limit
                DB::update("UPDATE superbancontroltable SET requestCount = 1, initialRequestTime = ?, lastSuccessTime = ? WHERE user_identifier = ? AND `type` = ? AND `reqUrl` = ?", [$this->reqTime, $this->reqTime, $user_identifier, $identifier, $this->routeName]);
            } else {
                //update last success time
                DB::update("UPDATE superbancontroltable SET requestCount = (requestCount + 1), lastSuccessTime = ? WHERE user_identifier = ? AND `type` = ? AND `reqUrl` = ?", [$this->reqTime, $user_identifier, $identifier, $this->routeName]);
            }
        } elseif (empty($userRateResult)) {
            //create rate entry
            DB::insert("INSERT INTO superbancontroltable (user_identifier, type, requestCount, initialRequestTime, lastSuccessTime, reqUrl) VALUES (?, ?, 1, ?, ?, ?)", [$user_identifier, $identifier, $this->reqTime, $this->reqTime, $this->routeName]);
        }
    }
}
