<?php

use yii\db\Migration;

class m250519_235327_development_plan extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('development_plan', [
            'id' => $this->primaryKey(),
            'status' => "ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'",
            'feature_en' => $this->string(255)->notNull(),
            'feature_uk' => $this->string(255)->notNull(),
            'feature_ru' => $this->string(255)->notNull(),
            'technology_en' => $this->string(512)->notNull(),
            'technology_uk' => $this->string(512)->notNull(),
            'technology_ru' => $this->string(512)->notNull(),
            'result_en' => $this->text(),
            'result_uk' => $this->text(),
            'result_ru' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->batchInsert('development_plan',
            [
            'status',
            'feature_en', 'feature_uk', 'feature_ru',
            'technology_en', 'technology_uk', 'technology_ru',
            'result_en', 'result_uk', 'result_ru',
            'created_at', 'updated_at'
            ],
            [
                'completed',
                'REST API development',
                'Розробка REST API',
                'Разработка REST API',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Swagger integration',
                'Інтеграція swagger',
                'Интеграция swagger',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'User registration',
                'Реєстрація нових користувачів',
                'Регистрация новых пользователей',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Email confirmation after registration',
                'Підтвердження реєстрації по email',
                'Подтверждение регистрации по email',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Login for registered users',
                'Вхід для зареєстрованих користувачів',
                'Login зарегистрированных юзеров',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Access for registered users via Bearer',
                'Доступ зареєстрованих користувачів через Bearer',
                'Доступ зарегистрированных юзеров через Baerer',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Role-based access control',
                'Розмежування доступу за ролями',
                'Разграничение доступа по ролям',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Multilanguage support',
                'Підтримка мультимовності',
                'Подержка мультиязычности',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Integration of Twig templating engine and template adaptation',
                'Інтеграція Twig-шаблонізатора та адаптація шаблонів',
                'Интеграция Twig-шаблонизатора и адаптация шаблонов',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'completed',
                'Added Custom tools to Gii',
                'Додано Custom tools у Gii',
                'Добавлены Custom tools в Gii',
                'Yii2, PHP',
                'Yii2, PHP',
                'Yii2, PHP',
                null, null, null,
                time(), time()
            ],
            [
                'in_progress',
                'Integration with payment systems',
                'Інтеграція з платіжними системами',
                'Интеграция с платёжными системами',
                'Stripe SDK, PayPal SDK, LiqPay API',
                'Stripe SDK, PayPal SDK, LiqPay API',
                'Stripe SDK, PayPal SDK, LiqPay API',
                'Ability to create and process payments, webhooks',
                'Можливість створювати та обробляти платежі, webhooks',
                'Возможность создавать и обрабатывать платежи, webhooks',
                time(), time()
            ],
            [
                'pending',
                'Queues and background jobs',
                'Черги та фонові задачі',
                'Очереди и фоновые задачи',
                'yii2-queue, Redis, Supervisor',
                'yii2-queue, Redis, Supervisor',
                'yii2-queue, Redis, Supervisor',
                'Deferred and async jobs (email, reports, webhooks)',
                'Відкладені та асинхронні задачі (email, звіти, webhook-и)',
                'Отложенные и асинхронные задачи (email, отчёты, webhook-и)',
                time(), time()
            ],
            [
                'pending',
                'User and role management',
                'Управління користувачами та ролями',
                'Управление пользователями и ролями',
                'Laravel Gates/Policies, Spatie Roles; Yii2 RBAC',
                'Laravel Gates/Policies, Spatie Roles; Yii2 RBAC',
                'Laravel Gates/Policies, Spatie Roles; Yii2 RBAC',
                'UI + API for role-based access management',
                'UI + API для керування доступом за ролями',
                'UI + API для управления доступом по ролям',
                time(), time()
            ],
            [
                'pending',
                'User activity audit',
                'Аудит дій користувачів',
                'Аудит действий пользователей',
                'Spatie ActivityLog, custom AuditBehavior',
                'Spatie ActivityLog, custom AuditBehavior',
                'Spatie ActivityLog, custom AuditBehavior',
                'Storing user activity history',
                'Зберігання історії дій користувачів',
                'Хранение истории действий пользователей',time(), time()
            ],
            [
                'pending',
                'Webhooks and external APIs',
                'Вебхуки та зовнішні API',
                'Webhooks и внешние API',
                'Guzzle, subscriptions, webhook routes',
                'Guzzle, підписки, webhook routes',
                'Guzzle, подписки, webhook routes',
                'Receiving/processing events from external services',
                'Отримання/обробка подій від зовнішніх сервісів',
                'Получение/обработка событий от внешних сервисов',
                time(), time()
            ],
            [
                'pending',
                'pending',
                'Testing API and business logic',
                'Тестування API та бізнес-логіки',
                'Тестирование API и логики',
                'PHPUnit, Pest, Codeception',
                'PHPUnit, Pest, Codeception',
                'PHPUnit, Pest, Codeception',
                'Test coverage for REST and business logic',
                'Покриття тестами REST та бізнес-логіки',
                'Покрытие тестами REST и бизнес-логики',
                time(), time()
            ],
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250519_235327_development_plan cannot be reverted.\n";
        $this->dropTable('development_plan');

        return false;
    }

}
