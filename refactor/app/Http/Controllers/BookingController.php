<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use DTApi\Repository\UserRepository;
use DTApi\Repository\JobRepository;
use DTApi\Repository\NotificationRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    /**
     * @var NotificationRepository
     */
    protected $notificationRepository;

    /**
     * BookingController constructor.
     * @param UserRepository $userRepository
     * @param JobRepository $jobRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(UserRepository $userRepository, JobRepository $jobRepository, NotificationRepository $notificationRepository)
    {
        $this->userRepository = $userRepository;
        $this->jobRepository = $jobRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {

            $response = $this->userRepository->getUsersJobs($user_id);

        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->jobRepository->getAll($request);
        }

        return $this->sendResponse($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->jobRepository->with('translatorJobRel.user')->find($id);

        return $this->sendResponse($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $response = $this->jobRepository->store($request->__authenticatedUser, $data);
            return $this->sendResponse($response);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->jobRepository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return $this->sendResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $response = $this->jobRepository->storeJobEmail($data);

        return $this->sendResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {

            $response = $this->userRepository->getUsersJobsHistory($user_id, $request);
            return $this->sendResponse($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->jobRepository->acceptJob($data, $user);

        return $this->sendResponse($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->jobRepository->acceptJobWithId($data, $user);

        return $this->sendResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->jobRepository->cancelJobAjax($data, $user);

        return $this->sendResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->jobRepository->endJob($data);

        return $this->sendResponse($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->jobRepository->customerNotCall($data);

        return $this->sendResponse($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->jobRepository->getPotentialJobs($user);

        return $this->sendResponse($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        if (isset($data['distance']) && $data['distance'] != "") {
            $distance = $data['distance'];
        } else {
            $distance = "";
        }
        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        } else {
            $time = "";
        }
        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        } else {
            $session = "";
        }

        if ($data['flagged'] == 'true') {
            if ($data['admincomment'] == '')
                return "Please, add comment";
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = "";
        }
        if ($time || $distance) {

            $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {

            $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));

        }

        return $this->sendResponse('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->jobRepository->reopen($data);

        return $this->sendResponse($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->jobRepository->find($data['jobid']);
        $job_data = $this->jobRepository->jobToData($job);
        $this->notificationRepository->sendNotificationTranslator($job, $job_data, '*');

        return $this->sendResponse(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->jobRepository->find($data['jobid']);
        $job_data = $this->jobRepository->jobToData($job);

        try {
            $this->notificationRepository->sendSMSNotificationToTranslator($job);
            return $this->sendResponse(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return $this->sendResponse(['success' => $e->getMessage()]);
        }
    }

}
