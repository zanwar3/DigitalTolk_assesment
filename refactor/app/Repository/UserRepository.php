<?php

namespace DTApi\Repository;

use Monolog\Logger;
use DTApi\Models\Job;
use DTApi\Models\User;
use Illuminate\Http\Request;
use DTApi\Mailers\MailerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class UserRepository extends BaseRepository
{
    protected $model;
    protected $mailer;
    protected $logger;

    public function __construct(Job $model, MailerInterface $mailer, Logger $logger)
    {
        parent::__construct($model);
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->logger->pushHandler(new StreamHandler(storage_path('logs/admin/laravel-' . date('Y-m-d') . '.log'), Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
    }
    /**
     * @param $user_id
     * @return array
     */
    public function getUsersJobs($userId)
    {
        $currentUser = User::find($userId);
        if (!$currentUser) {
            return $this->emptyJobResponse();
        }

        $jobs = $currentUser->is('customer') ? $this->getCustomerJobs($currentUser) : $this->getTranslatorJobs($currentUser);
        $userType = $currentUser->is('customer') ? 'customer' : 'translator';

        return $this->formatJobResponse($jobs, $currentUser, $userType);
    }
    /**
     * @param $user_id
     * @return array
     */
    public function getUsersJobsHistory($userId, Request $request)
    {
        $currentUser = User::find($userId);
        if (!$currentUser) {
            return $this->emptyJobResponse();
        }

        $jobs = $currentUser->is('customer') ? $this->getCustomerJobHistory($currentUser, $request) : $this->getTranslatorJobHistory($currentUser, $request);
        $userType = $currentUser->is('customer') ? 'customer' : 'translator';

        return $this->formatJobResponse($jobs, $currentUser, $userType);
    }

    /**
     * Function to get all Potential jobs of user with his ID
     * @param $user_id
     * @return array
     */
    public function getPotentialJobIdsWithUserId($user_id)
    {
        $user_meta = UserMeta::where('user_id', $user_id)->first();
        $translator_type = $user_meta->translator_type;
        $job_type = 'unpaid';
        if ($translator_type == 'professional')
            $job_type = 'paid';   /*show all jobs for professionals.*/
        else if ($translator_type == 'rwstranslator')
            $job_type = 'rws';  /* for rwstranslator only show rws jobs. */
        else if ($translator_type == 'volunteer')
            $job_type = 'unpaid';  /* for volunteers only show unpaid jobs. */

        $languages = UserLanguages::where('user_id', '=', $user_id)->get();
        $userlanguage = collect($languages)->pluck('lang_id')->all();
        $gender = $user_meta->gender;
        $translator_level = $user_meta->translator_level;
        $job_ids = Job::getJobs($user_id, $job_type, 'pending', $userlanguage, $gender, $translator_level);
        foreach ($job_ids as $k => $v)     // checking translator town
        {
            $job = Job::find($v->id);
            $jobuserid = $job->user_id;
            $checktown = Job::checkTowns($jobuserid, $user_id);
            if (($job->customer_phone_type == 'no' || $job->customer_phone_type == '') && $job->customer_physical_type == 'yes' && $checktown == false) {
                unset($job_ids[$k]);
            }
        }
        $jobs = TeHelper::convertJobIdsInObjs($job_ids);
        return $jobs;
    }

    private function getCustomerJobs($currentUser)
    {
        return $currentUser->jobs()
            ->with('user.userMeta', 'user.average', 'translatorJobRel.user.average', 'language', 'feedback')
            ->whereIn('status', ['pending', 'assigned', 'started'])
            ->orderBy('due', 'asc')
            ->get();
    }

    private function getTranslatorJobs($currentUser)
    {
        return Job::getTranslatorJobs($currentUser->id, 'new')->pluck('jobs')->all();
    }

    private function getCustomerJobHistory($currentUser, Request $request)
    {
        return $currentUser->jobs()
            ->with('user.userMeta', 'user.average', 'translatorJobRel.user.average', 'language', 'feedback', 'distance')
            ->whereIn('status', ['completed', 'withdrawbefore24', 'withdrawafter24', 'timedout'])
            ->orderBy('due', 'desc')
            ->paginate(15);
    }

    private function getTranslatorJobHistory($currentUser, Request $request)
    {
        return Job::getTranslatorJobsHistoric($currentUser->id, 'historic', $request->get('page', 1));
    }

    private function formatJobResponse($jobs, $currentUser, $userType)
    {
        $emergencyJobs = [];
        $normalJobs = [];

        foreach ($jobs as $job) {
            if ($job->immediate == 'yes') {
                $emergencyJobs[] = $job;
            } else {
                $normalJobs[] = $job;
            }
        }

        return [
            'emergencyJobs' => $emergencyJobs,
            'normalJobs' => $normalJobs,
            'cuser' => $currentUser,
            'usertype' => $userType
        ];
    }

    private function emptyJobResponse()
    {
        return [
            'emergencyJobs' => [],
            'normalJobs' => [],
            'cuser' => null,
            'usertype' => null
        ];
    }
}