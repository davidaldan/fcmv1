
# Laravel FCM Http V1 API Package

 A [Laravel](https://laravel.com/) package that lets you use the new FCM Http V1 API and send push notifications with ease.

### Firebase

1. Go to the [Firebase console](https://console.firebase.google.com/u/0/).

### Laravel

```env
FCM_API_KEY="<firebase apiKey>"
FCM_AUTH_DOMAIN="<firebase authDomain>"
FCM_PROJECT_ID="<firebase projectId>"
FCM_STORAGE_BUCKET="<firebase storageBucket>"
FCM_MESSAGIN_SENDER_ID="<firebase messagingSenderId>"
FCM_APP_ID="<firebase appId>"
FCM_JSON="<name of the json file downloaded at firebase step 7 install>"
FCM_API_SERVER_KEY=<api server key step 8-9 of firebase install>
```

4. Package installation
```
composer require daldan26/fcmv1
```

5. Register the provider in config/app.php

```php
Daldan26\Fcmv1\FcmProvider::class,
```

6. Publish config file
```
php artisan vendor:publish --tag=fcmv1 --ansi --force
```

## Usage

### Topics

Topics are used to make groups of device tokens. They will allow you to send notification directly to the topic where users are registered in.

#### Subscribe

To subscribe tokens to a topic :

```php
use Daldan26\Fcmv1\FcmTopicHelper;

$tokens = ["first token", ... , "last token"];
FcmTopicHelper::subscribeToTopic($tokens, "myTopic");
```
#### Unsubscribe

```php
use Daldan26\Fcmv1\FcmTopicHelper;

$tokens = ["first token", ... , "last token"];
FcmTopicHelper::unsubscribeToTopic($tokens, "myTopic");
```

#### List subscriptions

```php
use Daldan26\Fcmv1\FcmTopicHelper;

$token = "your awesome device token";
FcmTopicHelper::getTopicsByToken($token);

```

## Notification

You can send notification to specific user or to topics.

### Send to tokens
```php
use Daldan26\Fcmv1\FcmNotification;

$notify = new FcmNotification();
$notify->setTitle("Title")->setBody("Message here")->setToken(["token_here"])->setClickAction("NEWS")->send();

```

### Send to topic
```php
use Daldan26\Fcmv1\FcmNotification;

$notify = new FcmNotification();
$notify->setTitle("Title")->setBody("Message here")->setTopic("general_topic")->setClickAction("NEWS")->send();

```

