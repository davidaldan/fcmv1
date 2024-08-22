<?php

namespace Daldan26\Fcmv1;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Daldan26\Fcmv1\FcmGoogleHelper;


class FcmNotification
{
    const PRIORITY_NORMAL   = 'NORMAL';
    const PRIORITY_HIGH     = 'HIGH';
    protected $title;
    protected $body;
    protected $icon;
    protected $click_action;
    protected $token;
    protected $tokens;
    protected $topic;
    private $additionalData = [
        'data'                      => 'my_data'
    ];
    private $notification_foreground = [
        'notification_foreground'   => "true"
    ];
    private $image;
    private $sound      = 'default';
    protected $channel  = 'general_channel_id';
    private $priority   = self::PRIORITY_NORMAL;
    /*private $additionalData = [
        'data'                      => 'my_data',
        'notification_foreground'   => "true"
    ];*/

    /** 
     *Title of the notification.
     *@param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /** 
     *Body of the notification.
     *@param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /** 
     *Icon of the notification.
     *@param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     *Link of the notification when user click on it.
     *@param string $click_action
     */
    public function setClickAction($click_action)
    {
        $this->click_action = $click_action;
        return $this;
    }

    /**
     *Token used to send notification to specific device. Unusable with setTopic() at same time.
     *@param string $string
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     *Topic of the notification. Unusable with setToken() at same time.
     *@param string $topic
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     *Topic of the notification. Unusable with setToken() at same time.
     *@param array $additionalData
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData['data'] = json_encode($additionalData);

        return $this;
    }

    public function setNotificationForeground($status)
    {
        $this->notification_foreground['notification_foreground'] = $status;

        return $this;
    }

     /** 
     *priority of the notification.
     *@param string $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /** 
     *sound of the notification.
     *@param string $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    /** 
     *image of the notification.
     *@param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Verify the conformity of the notification. If everything is ok, send the notification.
     */
    public function send()
    {
        // Token and topic combinaison verification
        if ($this->token != null && $this->topic != null) {
            throw new Exception("A notification need to have at least one target: token or topic. Please select only one type of target.");
        }

        // Empty token or topic verification
        if ($this->token == null && $this->topic == null) {
            throw new Exception("A notification need to have at least one target: token or topic. Please add a target using setToken() or setTopic().");
        }

        if ($this->token != null && !is_array($this->token)) {
            throw new Exception('Token format error. Received: ' . gettype($this->token) . ". Expected type: array");
        }

        // Title verification
        if (!isset($this->title)) {
            throw new Exception('Empty notification title. Please add a title to the notification with the setTitle() method.');
        }

        // Body verification
        if (!isset($this->body)) {
            throw new Exception('Empty notification body. Please add a body to the notification with the setBody() method');
        }

        // Icon verification
        /*if ($this->icon !=null && !file_exists(public_path($this->icon))) {
            throw new Exception("Icon not found. Please verify the path of your icon(Path of the icon you tried to set: " . asset($this->icon));
        }*/

        return $this->prepareSend();
    }

    private function prepareSend()
    {

        $extra_data = array_merge($this->additionalData, $this->notification_foreground);

        if (isset($this->topic)) {
            
            $data = [
                "message" => [
                    "topic"         => $this->topic,
                    "data"          => $extra_data,
                    "notification"  => [
                        "title" => $this->title,
                        "body"  => $this->body,
                        "image" => $this->image !=null ? $this->image : '',
                    ],
                    "android" => [
                        "priority"      => $this->priority,
                        "sound"         => $this->sound,
                        "click_action"  => $this->click_action ? $this->click_action : '',
                        "channel_id"    => $this->channel,
                        "notification"  => [
                            "icon"  =>  $this->icon !=null ? $this->icon : '',
                        ]
                    ]
                ]
            ];

            $encodedData = json_encode($data);

            return $this->handleSend($encodedData);

        } elseif (isset($this->token)) {

            $array_notification = [];

            foreach($this->token as $item){

                $data = [
                    "message" => [
                        "token"         => $item,
                        "data"          => $extra_data,
                        "notification"  => [
                            "title" => $this->title,
                            "body"  => $this->body,
                            "image" => $this->image !=null ? $this->image : '',
                        ],
                        "android" => [
                            "priority"      => $this->priority,
                            "notification"  => [
                                "click_action"  => $this->click_action ? $this->click_action : '',
                                "sound"         => $this->sound,
                                "channel_id"    => $this->channel,
                                "icon"          => $this->icon !=null ? $this->icon : '',
                            ]
                        ]
                    ]
                ];

                $encodedData = json_encode($data);

                array_push($array_notification, $encodedData);

            }

            return $this->handleSendTokens($array_notification);
 
        }
        
    }

    private function handleSend($encodedData)
    {
        $url = config('fcm_config.fcm_api_url');

        $oauthToken = FcmGoogleHelper::configureClient();

        $headers = [
            'Authorization' => 'Bearer ' . $oauthToken,
            'Content-Type' =>  'application/json',
        ];

        $client = new Client();

        try {
            $request = $client->post($url, [
                'headers'   => $headers,
                "body"      => $encodedData,
            ]);

            Log::info("[Notification] SENT", [$encodedData]);

            $response = $request->getBody();

            Log::info("[Notification response] ", [$response]);

            return $response;
        } catch (Exception $e) {
            Log::error("[Notification] ERROR", [$e->getMessage()]);

            return $e;
        }
    }

    private function handleSendTokens($arrayEncodedData)
    {
        $url = config('fcm_config.fcm_api_url');

        $oauthToken = FcmGoogleHelper::configureClient();

        $headers = [
            'Authorization' => 'Bearer ' . $oauthToken,
            'Content-Type' =>  'application/json',
        ];

        try {

            $array_response = [];

            foreach($arrayEncodedData as $encodedData){

                $client = new Client();

                $request = $client->post($url, [
                    'headers'   => $headers,
                    "body"      => $encodedData,
                ]);

                Log::info("[Notification] SENT", [$encodedData]);

                $response = $request->getBody();

                Log::info("[Notification response] ", [$response]);

                array_push($array_response, $response);

            }

            return $array_response;

        } catch (Exception $e) {
            Log::error("[Notification] ERROR", [$e->getMessage()]);

            return $e;
        }
    }
}
