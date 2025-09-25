<?php
use Telegram\Bot\Api;

$telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

$webhookStatus = $telegram->setWebhook(['url' => 'https://48b97abbd17b.ngrok-free.app/telegram/webhook']);

if ($webhookStatus->get('ok')) {
    echo "Webhook berhasil diset.";
} else {
    echo "Gagal menyetel webhook.";
}
