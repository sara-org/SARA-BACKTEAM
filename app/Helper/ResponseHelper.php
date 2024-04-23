<?php

namespace App\Helper;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ResponseHelper
{
    public static function success($data = [], $service = null, $message = 'success', $status = 200)
    {
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        if ($service == 1) {
            return $response;
        }
        if ($service == null || $service == false) {
            if ($data instanceof ResourceCollection || $data instanceof Collection) {
                $response['data'] = response()->json(array($response['data']))->getData();
            }
        }
        return response()->json($response, $status);
    }


    public static function created($data = [], $message = 'created'): JsonResponse
    {
        return self::success($data, null, $message, 201);
    }

    public static function updated($data = [], $message = 'updated'): JsonResponse
    {
        return self::success($data, null, $message, 200);
    }

    public static function deleted($message = 'deleted'): JsonResponse
    {
        return self::success([], null, $message, 204);
    }

    public static function error($data = [], $service = null, $message = 'error', $status = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => $data,
            'status' => $status
        ];

        if ($service == 1) {
            return $response;
        }

        return response()->json($response, $status);
    }

    public static function email(
        $messageId,
        $senderEmail,
        $senderName,
        $gravatarUrl,
        $toValue,
        $subjectValue,
        $messageData,
        $attachments,
        $isStarred,
        $labelStatus,
        $dateValue,
        $replies,
        $isInbox,
        $isUnread,
        $service = null,
        $message = 'true',
        $status = 200
    ) {
        $response = [
            'success' => true,
            'message' => $message,
            'emails' => [
                [
                    'id' => $messageId,
                    'from' => [
                        'email' => $senderEmail,
                        'name' => $senderName,
                        'avatar' => $gravatarUrl
                    ],
                    'to' => [
                        [
                            'name' => 'me',
                            'email' => $toValue
                        ]
                    ],
                    'subject' => $subjectValue,
                    'cc' => [],
                    'bcc' => [],
                    'message' => $messageData,
                    'attachments' => $attachments,
                    'isStarred' => $isStarred,
                    'labels' => $labelStatus,
                    'time' => $dateValue,
                    'replies' => $replies,
                    'folder' => $isInbox ? 'inbox' : '',
                    'isRead' => !$isUnread
                ]
            ],
            'status' => $status
        ];

        if ($service == 1) {
            return $response;
        }

        return response()->json($response, $status);
    }


    public static function paginate($data)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $data->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginator = new LengthAwarePaginator($currentItems, count($data), $perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);

        return $paginator;
    }

    public static function convertDate($date, $time)
    {
        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
        $formattedDateTime = $dateTime->format('D M d Y H:i:s \G\M\TO (e:O)');
        return $formattedDateTime;
    }
}
