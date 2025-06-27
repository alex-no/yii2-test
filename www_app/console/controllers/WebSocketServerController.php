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
     * @var bool Whether to run the server as a daemon.
     * If true, the server will run in the background and not block the terminal.
     */
    public bool $daemon = false;

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
     * Returns the options for the WebSocket server command.
     * This method allows the command to run as a daemon.
     *
     * @param string $actionID The action ID.
     * @return array The options for the WebSocket server command.
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['daemon']);
    }

    /**
     * Starts the WebSocket server.
     * This method initializes the server, handles connections, messages, and disconnections.
     * It also assigns user names from A to Z and broadcasts user updates.
     *
     * Usage:
     *     php yii web-socket-server start --daemon=1
     *
     * @return void
     */
    public function actionIndex(): void
    {
        $this->lang = getenv('APP_LANG') ?: 'en';

        $this->stdout($this->t('starting') . "\n", Console::FG_GREEN);

        // User names: A, B, C, ..., Z
        $this->availableNames = range('A', 'Z');
        $this->clients = [];

        $cfg = \Yii::$app->params['websocket'];
        if ($cfg['is_ssl']) {
            $context = [
                'ssl' => [
                    'local_cert'  => $cfg['ssl_cert'],
                    'local_pk'    => $cfg['ssl_key'],
                    'verify_peer' => false,
                ],
            ];

            $worker = new Worker("{$cfg['protocol']}://{$cfg['host']}:{$cfg['port']}", $context);
            $worker->transport = 'ssl';
        } else {
            $worker = new Worker("{$cfg['protocol']}://{$cfg['host']}:{$cfg['port']}");
        }

        // @mkdir($this->getFilePath('socket'), 0777, true);
        // @mkdir($this->getFilePath('logs'), 0777, true);
        Worker::$pidFile = $this->getFilePath('socket/workerman.yii.pid');
        Worker::$logFile = $this->getFilePath('logs/workerman.log');
        if ($this->daemon) {
            Worker::$daemonize = true; // Run as a daemon
            $this->stdout("Running as a daemon...\n", Console::FG_YELLOW);
            $this->stdout("To stop the daemon, run: php yii web-socket-server/stop\n", Console::FG_BLUE);
        }

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
     * Stops the WebSocket server.
     * This method sends a SIGTERM signal to the process running the server.
     * It reads the PID from a file and attempts to terminate the process gracefully.
     *
     * Usage:
     *    php yii web-socket-server/stop
     *
     * @return void
     */
    public function actionStop(): void
    {
        $pidFile = $this->getFilePath('socket/workerman.yii.pid');
        if (!file_exists($pidFile)) {
            $this->stdout("PID file not found: $pidFile\n");
            return;
        }

        $pid = (int)file_get_contents($pidFile);
        if ($pid <= 0) {
            $this->stdout("Invalid PID in file: $pidFile\n");
            return;
        }

        if (posix_kill($pid, SIGTERM)) {
            $this->stdout("Sent SIGTERM to process $pid\n");
            @unlink($pidFile);
        } else {
            $this->stdout("Failed to send SIGTERM to process $pid\n");
        }
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

    /**
     * Get the file path for the WebSocket server files.
     * This method constructs the file path based on the runtime directory.
     *
     * @param string $filePart The part of the file path to construct.
     * @return string The file path for the WebSocket server files.
     */
    private function getFilePath(string $filePart): string
    {
        return \Yii::getAlias('@runtime') . '/' . $filePart;

    }
}
