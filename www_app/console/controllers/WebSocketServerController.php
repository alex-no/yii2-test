<?php

namespace console\controllers;

use Workerman\Worker;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * WebSocket server for chat. Run: php yii websocket-server
 */
class WebSocketServerController extends Controller
{
    private array $clients = [];
    private array $availableNames = [];

    public function actionIndex()
    {
        $this->stdout("Starting WebSocket server on port 3000...\n", Console::FG_GREEN);

        // User names: A, B, C, ..., Z
        $this->availableNames = range('A', 'Z');

        $worker = new Worker('websocket://0.0.0.0:3000');
        $worker->count = 1; // Simplified for compatibility

        $worker->onConnect = function ($connection) {
            $name = array_shift($this->availableNames) ?? 'X';
            $connection->user = $name;
            $this->clients[$connection->id] = $connection;

            $connection->send(json_encode([
                'type' => 'assign_name',
                'name' => $name,
            ]));

            $this->broadcastUsers();
            $message = "✅ Подключился пользователь: $name\n";
            if (php_sapi_name() === 'cli') {
                // Simple language switch, e.g. via env or config
                $lang = getenv('APP_LANG') ?: 'ru';
                if ($lang === 'en') {
                    $message = "✅ User connected: $name\n";
                }
            }
            $this->stdout($message, Console::FG_YELLOW);
        };

        $worker->onMessage = function ($connection, $data) {
            $message = trim($data);
            if ($message === '') {
                return;
            }

            $payload = [
                'type' => 'message',
                'user' => $connection->user,
                'text' => $message,
            ];

            foreach ($this->clients as $client) {
                $client->send(json_encode($payload));
            }

            $this->stdout("💬 {$connection->user}: $message\n", Console::FG_CYAN);
        };

        $worker->onClose = function ($connection) {
            unset($this->clients[$connection->id]);
            $this->availableNames[] = $connection->user;
            $this->broadcastUsers();
            $message = "❌ Отключился: {$connection->user}\n";
            if (php_sapi_name() === 'cli') {
                $lang = getenv('APP_LANG') ?: 'ru';
                if ($lang === 'en') {
                    $message = "❌ Disconnected: {$connection->user}\n";
                }
            }
            $this->stdout($message, Console::FG_RED);
        };

        Worker::runAll();
    }

    private function broadcastUsers(): void
    {
        $names = array_map(fn($c) => $c->user, $this->clients);
        $payload = json_encode([
            'type' => 'users_update',
            'users' => $names,
        ]);

        foreach ($this->clients as $client) {
            $client->send($payload);
        }
    }
}
