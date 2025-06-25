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
    /**
     * @var array Clients connected to the WebSocket server.
     * This array stores connections indexed by their IDs.
     */
    private array $clients = [];
    /**
     * @var array Available user names for the chat.
     * This array contains names from A to Z, which are assigned to users when they connect
     */
    private array $availableNames = [];
    /**
     * @var string Language for messages.
     * This can be set via environment variable APP_LANG, default is 'en'.
     */
    private string $lang = 'en';

    /**
     * @var array Messages for the WebSocket server.
     * This array contains messages in different languages for various events:
     */
    private const MESSAGES = [
        'starting' => [
            'en' => 'Starting WebSocket server on port 3000...',
            'ru' => 'Запуск WebSocket-сервера на порту 3000...',
            'uk' => 'Запуск WebSocket-сервера на порту 3000...',
        ],
        'connected' => [
            'en' => '✅ User connected: {name}',
            'ru' => '✅ Подключился пользователь: {name}',
            'uk' => '✅ Підключився користувач: {name}',
        ],
        'disconnected' => [
            'en' => '❌ Disconnected: {name}',
            'ru' => '❌ Отключился: {name}',
            'uk' => '❌ Відключився: {name}',
        ],
        'message' => [
            'en' => '💬 {name}: {message}',
            'ru' => '💬 {name}: {message}',
            'uk' => '💬 {name}: {message}',
        ],
    ];

    /**
     * Starts the WebSocket server.
     * This method initializes the server, handles connections, messages, and disconnections.
     * It also assigns user names from A to Z and broadcasts user updates.
     * @return void
     */
    public function actionIndex(): void
    {
        $this->lang = getenv('APP_LANG') ?: 'ru';

        $this->stdout($this->t('starting') . "\n", Console::FG_GREEN);

        // User names: A, B, C, ..., Z
        $this->availableNames = range('A', 'Z');

        // $worker = new Worker('websocket://0.0.0.0:3000');
        $cfg = \Yii::$app->params['websocket'];
        $worker = new Worker("{$cfg['protocol']}://{$cfg['host']}:{$cfg['port']}");

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

            $this->stdout($this->t('connected', ['name' => $name]) . "\n", Console::FG_YELLOW);
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

            $this->stdout($this->t('message', [
                'name' => $connection->user,
                'message' => $message
            ]) . "\n", Console::FG_CYAN);
        };

        $worker->onClose = function ($connection) {
            unset($this->clients[$connection->id]);
            $this->availableNames[] = $connection->user;
            $this->broadcastUsers();

            $this->stdout($this->t('disconnected', ['name' => $connection->user]) . "\n", Console::FG_RED);
        };

        Worker::runAll();
    }

    /**
     * Broadcast the list of connected users to all clients.
     * This updates the user list in the chat interface.
     * @return void
     */
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

    /**
     * Translate message key to the current language.
     * @param string $key The message key.
     * @param array $params The message parameters.
     * @return string The translated message.
     */
    private function t(string $key, array $params = []): string
    {
        $template = self::MESSAGES[$key][$this->lang] ?? self::MESSAGES[$key]['en'] ?? '';
        foreach ($params as $k => $v) {
            $template = str_replace('{' . $k . '}', $v, $template);
        }
        return $template;
    }
}
