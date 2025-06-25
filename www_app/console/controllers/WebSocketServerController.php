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
        $this->clients = [];

        $cfg = \Yii::$app->params['websocket'];
        $worker = new Worker("{$cfg['protocol']}://{$cfg['host']}:{$cfg['port']}");

        // @mkdir(\Yii::getAlias('@runtime') . '/socket', 0777, true);
        // @mkdir(\Yii::getAlias('@runtime') . '/logs', 0777, true);
        Worker::$pidFile = \Yii::getAlias('@runtime') . '/socket/workerman.yii.pid';
        Worker::$logFile = \Yii::getAlias('@runtime') . '/logs/workerman.log';

        $worker->count = 1;

        // To use $this inside closures, pass a reference to the object
        $that = $this;

        /**
         * Handles new connections to the WebSocket server.
         * Assigns a user name from the available names list and sends it to the client.
         */
        $worker->onConnect = function ($connection) use ($that) {
            // Determine used names
            $usedNames = array_map(fn($c) => $c->user, $that->clients);

            // Find the first available letter
            $name = 'X'; // fallback
            foreach ($that->availableNames as $candidate) {
                if (!in_array($candidate, $usedNames, true)) {
                    $name = $candidate;
                    break;
                }
            }

            $connection->user = $name;
            $that->clients[$connection->id] = $connection;

            $connection->send(json_encode([
                'type' => 'assign_name',
                'name' => $name,
            ]));

            $that->broadcastUsers();

            echo $that->t('connected', ['name' => $name]) . "\n";
        };

        /**
         * Handles incoming messages from clients.
         * Broadcasts the message to all connected clients.
         */
        $worker->onMessage = function ($connection, $data) use ($that) {
            $message = trim($data);
            if ($message === '') {
                return;
            }

            $payload = [
                'type' => 'message',
                'user' => $connection->user,
                'text' => $message,
            ];

            foreach ($that->clients as $client) {
                $client->send(json_encode($payload));
            }

            echo $that->t('message', ['name' => $connection->user, 'message' => $message]) . "\n";
        };

        /**
         * Handles disconnections from the WebSocket server.
         * Removes the client from the list and adds their name back to the available names.
         */
        $worker->onClose = function ($connection) use ($that) {
            unset($that->clients[$connection->id]);

            $that->broadcastUsers();

            echo $that->t('disconnected', ['name' => $connection->user]) . "\n";
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
